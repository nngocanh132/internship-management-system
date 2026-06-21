<?php
// Tự động tạo internship_registration khi Admin xác nhận interview_passed
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
requireRole('admin');

$app_id=(int)($_GET['app_id']??0);
if(!$app_id) redirect(BASE_PATH.'/modules/registrations/list.php');

$aq=$conn->prepare("SELECT a.*,sp.student_id,cp.company_id,i.start_date,i.end_date FROM applications a JOIN student_profiles sp ON a.student_id=sp.student_id JOIN internships i ON a.internship_id=i.internship_id JOIN company_profiles cp ON i.company_id=cp.company_id WHERE a.application_id=? AND a.status='interview_passed'");
$aq->bind_param('i',$app_id); $aq->execute(); $app=$aq->get_result()->fetch_assoc();

if(!$app){ setFlash('error','Đơn chưa đạt trạng thái đậu phỏng vấn.'); redirect(BASE_PATH.'/modules/registrations/list.php'); }

// Check already registered
$chk=$conn->prepare("SELECT registration_id FROM internship_registrations WHERE student_id=? AND internship_id=?");
$chk->bind_param('ii',$app['student_id'],$app['internship_id']); $chk->execute();
if($chk->get_result()->num_rows>0){ setFlash('info','Sinh viên đã được tạo hợp đồng rồi.'); redirect(BASE_PATH.'/modules/registrations/list.php'); }

// Create registration
$sd=$app['start_date']??null; $ed=$app['end_date']??null;
$ins=$conn->prepare("INSERT INTO internship_registrations (student_id,company_id,internship_id,start_date,end_date,status) VALUES (?,?,?,?,?,'active')");
$ins->bind_param('iiiss',$app['student_id'],$app['company_id'],$app['internship_id'],$sd,$ed);
if($ins->execute()){
    // Update application status
    $ua=$conn->prepare("UPDATE applications SET status='internship_active' WHERE application_id=?");
    $ua->bind_param('i',$app_id); $ua->execute();
    setFlash('success','✅ Đã tạo hợp đồng thực tập! Hãy phân công giảng viên hướng dẫn.');
    redirect(BASE_PATH.'/modules/registrations/list.php');
} else {
    setFlash('error','Lỗi: '.$conn->error);
    redirect(BASE_PATH.'/modules/registrations/list.php');
}
