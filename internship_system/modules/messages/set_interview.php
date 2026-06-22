<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('company');

$uid=$_SESSION['user_id'];
$app_id=(int)($_POST['app_id']??0);
$redir=sanitize($_POST['redirect']??'');

if(!$app_id) redirect('inbox.php');

$date=sanitize($_POST['interview_date']??'');
$addr=sanitize($_POST['address']??'');
$link=sanitize($_POST['meeting_link']??'');

// Upsert interview
$chk=$conn->prepare("SELECT interview_id FROM interviews WHERE application_id=?");
$chk->bind_param('i',$app_id); $chk->execute();
$ex=$chk->get_result()->fetch_assoc();

if($ex){
    $u=$conn->prepare("UPDATE interviews SET interview_date=?,address=?,meeting_link=? WHERE application_id=?");
    $u->bind_param('sssi',$date,$addr,$link,$app_id); $u->execute();
} else {
    $ins=$conn->prepare("INSERT INTO interviews (application_id,interview_date,address,meeting_link,result) VALUES (?,?,?,?,'pending')");
    $ins->bind_param('isss',$app_id,$date,$addr,$link); $ins->execute();
}
setFlash('success','Đã lưu lịch phỏng vấn!');
redirect('chat.php?'.$redir);
