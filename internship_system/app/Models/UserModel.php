<?php
require_once __DIR__ . '/BaseModel.php';

class UserModel extends BaseModel
{
    /** Lấy user theo email */
    public function getByEmail(string $email): ?array
    {
        $s = $this->conn->prepare("SELECT * FROM users WHERE email=?");
        if (!$s) return null;
        $s->bind_param('s', $email);
        $s->execute();
        return $s->get_result()->fetch_assoc() ?: null;
    }

    /** Cập nhật password */
    public function updatePassword(int $user_id, string $hash): void
    {
        $mig = $this->conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        if ($mig) { $mig->bind_param('si', $hash, $user_id); $mig->execute(); }
    }

    /** Kiểm tra email đã tồn tại chưa */
    public function emailExists(string $email): bool
    {
        $chk = $this->conn->prepare("SELECT user_id FROM users WHERE email=?");
        $chk->bind_param('s', $email);
        $chk->execute();
        return $chk->get_result()->num_rows > 0;
    }

    /** Tạo user mới */
    public function create(string $email, string $hash, string $role): int
    {
        $ins = $this->conn->prepare("INSERT INTO users (email,password,role,is_profile_completed) VALUES (?,?,?,0)");
        $ins->bind_param('sss', $email, $hash, $role);
        $ins->execute();
        return (int)$this->conn->insert_id;
    }

    /** Tạo student profile rỗng */
    public function createStudentProfile(int $uid, string $fullname): void
    {
        $p = $this->conn->prepare("INSERT INTO student_profiles (user_id,full_name) VALUES (?,?)");
        $p->bind_param('is', $uid, $fullname);
        $p->execute();
    }

    /** Tạo company profile rỗng */
    public function createCompanyProfile(int $uid, string $fullname): void
    {
        $p = $this->conn->prepare("INSERT INTO company_profiles (user_id,company_name) VALUES (?,?)");
        $p->bind_param('is', $uid, $fullname);
        $p->execute();
    }

    /** Lấy display name theo role */
    public function getDisplayName(int $uid, string $role, string $email): string
    {
        $display = $email;
        if ($role === 'student') {
            $n = $this->conn->query("SELECT full_name FROM student_profiles WHERE user_id={$uid} LIMIT 1");
            if ($n && $row = $n->fetch_assoc()) $display = $row['full_name'] ?: $email;
        } elseif ($role === 'company') {
            $n = $this->conn->query("SELECT company_name FROM company_profiles WHERE user_id={$uid} LIMIT 1");
            if ($n && $row = $n->fetch_assoc()) $display = $row['company_name'] ?: $email;
        } elseif ($role === 'lecturer') {
            $n = $this->conn->query("SELECT full_name FROM lecturer_profiles WHERE user_id={$uid} LIMIT 1");
            if ($n && $row = $n->fetch_assoc()) $display = $row['full_name'] ?: $email;
        } elseif ($role === 'admin') {
            $display = 'Admin';
        }
        return $display;
    }

    /** Lấy danh sách users (admin), có filter role và search */
    public function getAll(string $role_f = '', string $search = ''): array
    {
        $sql = "SELECT u.*,
                COALESCE(sp.full_name, cp.company_name, lp.full_name, 'Admin') AS display_name,
                sp.student_code, sp.gpa, sp.major, sp.avatar AS s_av,
                cp.company_name, cp.logo,
                lp.department
                FROM users u
                LEFT JOIN student_profiles sp  ON u.user_id=sp.user_id  AND u.role='student'
                LEFT JOIN company_profiles cp  ON u.user_id=cp.user_id  AND u.role='company'
                LEFT JOIN lecturer_profiles lp ON u.user_id=lp.user_id  AND u.role='lecturer'
                WHERE 1=1";
        $p = []; $t = '';
        if ($role_f) { $sql .= " AND u.role=?"; $p[] = $role_f; $t .= 's'; }
        if ($search) {
            $sql .= " AND (sp.full_name LIKE ? OR cp.company_name LIKE ? OR lp.full_name LIKE ? OR u.email LIKE ?)";
            $like = "%$search%"; $p[] = $like; $p[] = $like; $p[] = $like; $p[] = $like; $t .= 'ssss';
        }
        $sql .= " ORDER BY u.created_at DESC";
        $st = $this->conn->prepare($sql);
        if ($p) $st->bind_param($t, ...$p);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lỗi DB cuối */
    public function lastError(): string
    {
        return $this->conn->error;
    }
}
