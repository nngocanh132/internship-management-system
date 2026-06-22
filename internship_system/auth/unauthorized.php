<?php
// Standalone page — không cần Controller, chỉ cần functions + BASE_PATH
require_once '../app/Controllers/_bootstrap.php';
?>
<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8">
<title>Không có quyền — ISchool Internship</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#eef5f2;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
.box{background:#fff;border-radius:20px;padding:48px;text-align:center;max-width:400px;box-shadow:0 8px 32px rgba(93,123,111,.12)}</style>
</head><body>
<div class="box">
  <div style="font-size:3.5rem;margin-bottom:12px">🔒</div>
  <h4 style="font-weight:800;color:#1A2E28">Không có quyền truy cập</h4>
  <p class="text-muted">Trang này không dành cho tài khoản của bạn.</p>
  <a href="<?=BASE_PATH?>/auth/login.php" class="btn btn-primary">Về trang đăng nhập</a>
  <button onclick="history.back()" class="btn btn-secondary ms-2">Quay lại</button>
</div>
</body></html>
