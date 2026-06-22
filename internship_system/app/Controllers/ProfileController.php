<?php
require_once __DIR__ . '/BaseController.php';

class ProfileController extends BaseController
{
    // ── Student: index redirect ───────────────────────────────────────
    public function indexStudent(): void
    {
        if (isStudent()) redirect(BASE_PATH . '/student_profiles/edit.php');
        if (isAdmin())   redirect(BASE_PATH . '/student_profiles/list.php');
        redirect(BASE_PATH . '/auth/login.php');
    }

    // ── Student: chỉnh sửa hồ sơ ─────────────────────────────────────
    public function editStudent(): void
    {
        requireRole('student');
        $uid  = $_SESSION['user_id'];
        $stmt = $this->conn->prepare("SELECT sp.*,u.email,u.is_profile_completed FROM student_profiles sp JOIN users u ON sp.user_id=u.user_id WHERE sp.user_id=?");
        $stmt->bind_param('i', $uid); $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if (!$p) redirect(BASE_PATH . '/auth/login.php');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $full_name    = sanitize($_POST['full_name'] ?? '');
            $student_code = sanitize($_POST['student_code'] ?? '');
            $phone        = sanitize($_POST['phone'] ?? '');
            $gpa          = floatval($_POST['gpa'] ?? 0);
            $major        = sanitize($_POST['major'] ?? '');
            $about        = sanitize($_POST['about_me'] ?? '');
            $linkedin     = sanitize($_POST['linkedin_url'] ?? '');

            if (empty($full_name))    $errors[] = 'Họ tên là bắt buộc.';
            if (empty($student_code)) $errors[] = 'Mã sinh viên là bắt buộc.';
            if (empty($phone))        $errors[] = 'Số điện thoại là bắt buộc.';
            if (empty($major))        $errors[] = 'Chuyên ngành là bắt buộc.';
            if ($gpa < 0 || $gpa > 4) $errors[] = 'GPA từ 0.0 đến 4.0.';

            $avatar = $p['avatar'];
            if (!empty($_FILES['avatar']['tmp_name'])) {
                $up = uploadFile($_FILES['avatar'], 'avatars', ['jpg','jpeg','png'], 2);
                if ($up['ok']) $avatar = $up['path']; else $errors[] = 'Ảnh: ' . $up['err'];
            }

            if (empty($errors)) {
                $chk = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE student_code=? AND user_id!=?");
                $chk->bind_param('si', $student_code, $uid); $chk->execute();
                if ($chk->get_result()->num_rows > 0) $errors[] = 'Mã sinh viên đã tồn tại.';
            }

            if (empty($errors)) {
                $u = $this->conn->prepare("UPDATE student_profiles SET full_name=?,student_code=?,phone=?,gpa=?,major=?,about_me=?,linkedin_url=?,avatar=? WHERE user_id=?");
                $u->bind_param('sssdssssi', $full_name, $student_code, $phone, $gpa, $major, $about, $linkedin, $avatar, $uid);
                if ($u->execute()) {
                    $this->conn->prepare("UPDATE users SET is_profile_completed=1 WHERE user_id=?")->bind_param('i', $uid);
                    $uc = $this->conn->prepare("UPDATE users SET is_profile_completed=1 WHERE user_id=?");
                    $uc->bind_param('i', $uid); $uc->execute();
                    $_SESSION['full_name'] = $full_name;
                    setFlash('success', '✅ Đã cập nhật hồ sơ!');
                    redirect(getDashboardUrl());
                } else { $errors[] = 'Lỗi: ' . $this->conn->error; }
            }
            $p = array_merge($p, $_POST);
        }

        $fields = [$p['student_code'], $p['phone'], $p['gpa'], $p['major'], $p['avatar'], $p['about_me']];
        $pct    = round(count(array_filter($fields)) / count($fields) * 100);
        $av     = $p['avatar'] ? UPLOAD_URL . '/' . $p['avatar']
                : 'https://ui-avatars.com/api/?name=' . urlencode($p['full_name'] ?? 'SV') . '&background=5D7B6F&color=fff&size=120';

        $this->render(BASE_PATH_FS . 'app/Views/profiles/student_edit.php', [
            'p'      => $p,
            'errors' => $errors,
            'pct'    => $pct,
            'av'     => $av,
        ]);
    }

    // ── Admin: danh sách hồ sơ sinh viên ─────────────────────────────
    public function listStudents(): void
    {
        requireRole('admin');
        $search   = sanitize($_GET['q'] ?? '');
        $sql      = "SELECT sp.*,u.email,u.is_profile_completed,u.created_at FROM student_profiles sp JOIN users u ON sp.user_id=u.user_id WHERE 1=1";
        $p = []; $t = '';
        if ($search) {
            $sql .= " AND (sp.full_name LIKE ? OR sp.student_code LIKE ? OR u.email LIKE ?)";
            $like = "%$search%"; $p = [$like, $like, $like]; $t = 'sss';
        }
        $sql .= " ORDER BY sp.full_name";
        $st = $this->conn->prepare($sql);
        if (!$st) { $students = []; }
        else {
            if ($p) $st->bind_param($t, ...$p);
            $st->execute();
            $students = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        $this->render(BASE_PATH_FS . 'app/Views/profiles/student_list.php', [
            'students' => $students,
            'search'   => $search,
        ]);
    }

    // ── Company: chỉnh sửa hồ sơ doanh nghiệp ───────────────────────
    public function editCompany(): void
    {
        requireRole('company');
        $uid  = $_SESSION['user_id'];
        $stmt = $this->conn->prepare("SELECT cp.*,u.email,u.is_profile_completed FROM company_profiles cp JOIN users u ON cp.user_id=u.user_id WHERE cp.user_id=?");
        $stmt->bind_param('i', $uid); $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if (!$p) redirect(BASE_PATH . '/auth/login.php');

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = sanitize($_POST['company_name'] ?? '');
            $tax      = sanitize($_POST['tax_code'] ?? '');
            $address  = sanitize($_POST['address'] ?? '');
            $phone    = sanitize($_POST['phone'] ?? '');
            $website  = sanitize($_POST['website'] ?? '');
            $industry = sanitize($_POST['industry'] ?? '');
            $size     = sanitize($_POST['company_size'] ?? '');
            $desc     = sanitize($_POST['description'] ?? '');

            if (empty($name))    $errors[] = 'Tên công ty là bắt buộc.';
            if (empty($tax))     $errors[] = 'Mã số thuế là bắt buộc.';
            if (empty($address)) $errors[] = 'Địa chỉ là bắt buộc.';

            $logo = $p['logo'];
            if (!empty($_FILES['logo']['tmp_name'])) {
                $up = uploadFile($_FILES['logo'], 'logos', ['jpg','jpeg','png'], 2);
                if ($up['ok']) $logo = $up['path']; else $errors[] = 'Logo: ' . $up['err'];
            }

            $license = $p['business_license_file'];
            if (!empty($_FILES['license']['tmp_name'])) {
                $up = uploadFile($_FILES['license'], 'licenses', ['pdf','jpg','jpeg','png'], 10);
                if ($up['ok']) $license = $up['path']; else $errors[] = 'Giấy phép: ' . $up['err'];
            }

            if (empty($errors) && !$license) $errors[] = 'Giấy phép kinh doanh là bắt buộc.';

            if (empty($errors)) {
                $u = $this->conn->prepare("UPDATE company_profiles SET company_name=?,tax_code=?,address=?,phone=?,website=?,industry=?,company_size=?,description=?,logo=?,business_license_file=? WHERE user_id=?");
                $u->bind_param('ssssssssssi', $name, $tax, $address, $phone, $website, $industry, $size, $desc, $logo, $license, $uid);
                if ($u->execute()) {
                    $uc = $this->conn->prepare("UPDATE users SET is_profile_completed=1 WHERE user_id=?");
                    $uc->bind_param('i', $uid); $uc->execute();
                    $_SESSION['full_name'] = $name;
                    setFlash('success', '✅ Đã cập nhật hồ sơ doanh nghiệp!');
                    redirect(getDashboardUrl());
                } else { $errors[] = 'Lỗi: ' . $this->conn->error; }
            }
            $p = array_merge($p, $_POST);
        }

        $logoUrl = $p['logo'] ? UPLOAD_URL . '/' . $p['logo']
                 : 'https://ui-avatars.com/api/?name=' . urlencode($p['company_name'] ?? 'DN') . '&background=5D7B6F&color=fff&size=120&bold=true';

        $this->render(BASE_PATH_FS . 'app/Views/profiles/company_edit.php', [
            'p'       => $p,
            'errors'  => $errors,
            'logoUrl' => $logoUrl,
        ]);
    }

    // ── Admin: danh sách hồ sơ công ty ───────────────────────────────
    public function listCompanies(): void
    {
        requireRole('admin');
        $companies = safeQuery($this->conn,
            "SELECT cp.*,u.email,u.is_profile_completed,u.created_at,
             (SELECT COUNT(*) FROM internships WHERE company_id=cp.company_id) AS job_count,
             (SELECT COUNT(*) FROM applications a JOIN internships i ON a.internship_id=i.internship_id WHERE i.company_id=cp.company_id) AS app_count
             FROM company_profiles cp JOIN users u ON cp.user_id=u.user_id ORDER BY cp.company_name"
        );
        $this->render(BASE_PATH_FS . 'app/Views/profiles/company_list.php', ['companies' => $companies]);
    }

    // ── Admin: danh sách giảng viên ───────────────────────────────────
    public function listLecturers(): void
    {
        requireRole('admin');
        $lecturers = $this->conn->query(
            "SELECT lp.*,u.email,u.created_at,
             (SELECT COUNT(*) FROM internship_registrations WHERE lecturer_id=lp.lecturer_id AND status='active') AS active_students,
             (SELECT COUNT(*) FROM internship_registrations WHERE lecturer_id=lp.lecturer_id) AS total_students
             FROM lecturer_profiles lp JOIN users u ON lp.user_id=u.user_id ORDER BY lp.full_name"
        )->fetch_all(MYSQLI_ASSOC);
        $this->render(BASE_PATH_FS . 'app/Views/profiles/lecturer_list.php', ['lecturers' => $lecturers]);
    }
}
