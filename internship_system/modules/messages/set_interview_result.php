<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('company');

$app_id=(int)($_GET['app_id']??0);
$result=sanitize($_GET['result']??'');
$redir=sanitize($_GET['company_id']??'');

if(!$app_id||!in_array($result,['passed','failed'])) redirect('inbox.php');

// Update interview result
$conn->prepare("UPDATE interviews SET result=? WHERE application_id=?")->bind_param('si',$result,$app_id);
$ui=$conn->prepare("UPDATE interviews SET result=? WHERE application_id=?");
$ui->bind_param('si',$result,$app_id); $ui->execute();

// Update application status
$new_status=$result==='passed'?'interview_passed':'interview_failed';
$ua=$conn->prepare("UPDATE applications SET status=? WHERE application_id=?");
$ua->bind_param('si',$new_status,$app_id); $ua->execute();

if($result==='passed'){
    setFlash('success','🎉 Sinh viên đậu phỏng vấn! Admin sẽ tạo hợp đồng thực tập.');
} else {
    setFlash('info','Đã cập nhật kết quả phỏng vấn.');
}

// Build redirect params
$params=[];
foreach(['company_id','student_id','app_id'] as $k) if(isset($_GET[$k])) $params[$k]=$_GET[$k];
redirect('chat.php?'.http_build_query($params));
