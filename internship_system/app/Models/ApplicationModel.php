<?php
require_once __DIR__ . '/BaseModel.php';

class ApplicationModel extends BaseModel
{
    /** Lấy tất cả đơn (admin), có filter theo status */
    public function getAllWithFilter(string $status_f): array
    {
        $sql = "SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.major,sp.avatar AS s_avatar,
                i.title,i.internship_id,cp.company_name,cp.company_id
                FROM applications a
                JOIN student_profiles sp ON a.student_id=sp.student_id
                JOIN internships i ON a.internship_id=i.internship_id
                JOIN company_profiles cp ON i.company_id=cp.company_id
                WHERE 1=1";
        $params = []; $types = '';
        if ($status_f) { $sql .= " AND a.status=?"; $params[] = $status_f; $types = 's'; }
        $sql .= " ORDER BY a.applied_at DESC";
        $st = $this->conn->prepare($sql);
        if ($params) $st->bind_param($types, ...$params);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Đếm đơn theo từng status */
    public function countByStatus(): array
    {
        $counts = [];
        $statuses = ['pending_admin','approved_admin','rejected_admin','approved_company',
                     'rejected_company','interview_passed','interview_failed',
                     'internship_active','internship_completed'];
        foreach ($statuses as $s) {
            $r = $this->conn->query("SELECT COUNT(*) c FROM applications WHERE status='$s'");
            $counts[$s] = (int)($r ? $r->fetch_assoc()['c'] : 0);
        }
        return $counts;
    }

    /** Lấy chi tiết 1 đơn kèm thông tin SV, vị trí, công ty */
    public function getDetailById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*,
             sp.full_name,sp.student_code,sp.gpa,sp.major,sp.phone,sp.about_me,
             sp.avatar AS s_avatar,sp.linkedin_url,
             i.title,i.description,i.requirements,i.quantity,i.location,
             i.start_date,i.end_date,i.internship_id,
             cp.company_name,cp.company_id,cp.logo AS c_logo,
             u.email
             FROM applications a
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN users u ON sp.user_id=u.user_id
             JOIN internships i ON a.internship_id=i.internship_id
             JOIN company_profiles cp ON i.company_id=cp.company_id
             WHERE a.application_id=?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy các đơn khác của cùng sinh viên */
    public function getOthersByStudent(int $student_id, int $exclude_id): array
    {
        $st = $this->conn->prepare(
            "SELECT a2.*,i2.title,cp2.company_name
             FROM applications a2
             JOIN internships i2 ON a2.internship_id=i2.internship_id
             JOIN company_profiles cp2 ON i2.company_id=cp2.company_id
             WHERE a2.student_id=? AND a2.application_id!=?
             ORDER BY a2.applied_at DESC"
        );
        $st->bind_param('ii', $student_id, $exclude_id);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Admin duyệt đơn */
    public function approve(int $id, string $note): bool
    {
        $u = $this->conn->prepare("UPDATE applications SET status='approved_admin',admin_note=? WHERE application_id=?");
        $u->bind_param('si', $note, $id);
        return $u->execute();
    }

    /** Admin từ chối đơn */
    public function reject(int $id, string $note): bool
    {
        $u = $this->conn->prepare("UPDATE applications SET status='rejected_admin',admin_note=? WHERE application_id=?");
        $u->bind_param('si', $note, $id);
        return $u->execute();
    }

    /** Lấy đơn của sinh viên (student dashboard) */
    public function getByStudent(int $sid): array
    {
        $st = $this->conn->prepare(
            "SELECT a.*,i.title,i.start_date,i.end_date,i.location,
             cp.company_name,cp.logo,cp.company_id,
             iv.interview_date,iv.meeting_link,iv.address AS iv_address,iv.result AS iv_result
             FROM applications a
             JOIN internships i ON a.internship_id=i.internship_id
             JOIN company_profiles cp ON i.company_id=cp.company_id
             LEFT JOIN interviews iv ON a.application_id=iv.application_id
             WHERE a.student_id=?
             ORDER BY a.applied_at DESC"
        );
        $st->bind_param('i', $sid);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Auto-sync: sửa status internship_active → internship_completed khi registration đã completed */
    public function syncCompletedStatus(int $sid): void
    {
        $sync = $this->conn->prepare(
            "UPDATE applications SET status='internship_completed'
             WHERE student_id=? AND status='internship_active'
             AND internship_id IN (
                 SELECT internship_id FROM internship_registrations
                 WHERE student_id=? AND status='completed'
             )"
        );
        if ($sync) { $sync->bind_param('ii', $sid, $sid); $sync->execute(); }
    }

    /** Kiểm tra SV đã ứng tuyển vị trí chưa */
    public function getExistingApplication(int $sid, int $iid): ?array
    {
        $st = $this->conn->prepare("SELECT application_id,status FROM applications WHERE student_id=? AND internship_id=?");
        $st->bind_param('ii', $sid, $iid);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /** Nộp đơn mới */
    public function create(int $sid, int $iid, string $cv_path): bool
    {
        $ins = $this->conn->prepare("INSERT INTO applications (student_id,internship_id,cv_file,status) VALUES (?,?,?,'pending_admin')");
        $ins->bind_param('iis', $sid, $iid, $cv_path);
        return $ins->execute();
    }

    /** Lấy danh sách ứng viên của công ty */
    public function getCandidatesByCompany(int $cid, int $job_filter = 0): array
    {
        $where = "i.company_id=$cid";
        if ($job_filter) $where .= " AND a.internship_id=$job_filter";
        return safeQuery($this->conn,
            "SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.major,sp.avatar AS s_av,
             sp.student_id AS sp_sid,u.email,i.title AS job_title,
             iv.result AS iv_result,iv.interview_date,
             ir.registration_id
             FROM applications a
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN users u ON sp.user_id=u.user_id
             JOIN internships i ON a.internship_id=i.internship_id
             LEFT JOIN interviews iv ON a.application_id=iv.application_id
             LEFT JOIN internship_registrations ir ON ir.student_id=sp.student_id AND ir.internship_id=i.internship_id
             WHERE $where
             ORDER BY a.applied_at DESC"
        );
    }

    /** Công ty chấp nhận ứng viên */
    public function approveByCompany(int $app_id, int $cid): bool
    {
        $u = $this->conn->prepare("UPDATE applications SET status='approved_company' WHERE application_id=? AND internship_id IN (SELECT internship_id FROM internships WHERE company_id=?) AND status='approved_admin'");
        $u->bind_param('ii', $app_id, $cid);
        return $u->execute();
    }

    /** Công ty từ chối ứng viên */
    public function rejectByCompany(int $app_id, int $cid): bool
    {
        $u = $this->conn->prepare("UPDATE applications SET status='rejected_company' WHERE application_id=? AND internship_id IN (SELECT internship_id FROM internships WHERE company_id=?)");
        $u->bind_param('ii', $app_id, $cid);
        return $u->execute();
    }

    /** Đánh dấu đậu phỏng vấn */
    public function markInterviewPassed(int $app_id, int $cid): bool
    {
        $u = $this->conn->prepare("UPDATE applications SET status='interview_passed' WHERE application_id=? AND internship_id IN (SELECT internship_id FROM internships WHERE company_id=?)");
        $u->bind_param('ii', $app_id, $cid);
        return $u->execute();
    }

    /** Đánh dấu rớt phỏng vấn */
    public function markInterviewFailed(int $app_id, int $cid): bool
    {
        $u = $this->conn->prepare("UPDATE applications SET status='interview_failed' WHERE application_id=? AND internship_id IN (SELECT internship_id FROM internships WHERE company_id=?)");
        $u->bind_param('ii', $app_id, $cid);
        return $u->execute();
    }

    /** Bắt đầu thực tập — lấy thông tin đơn để tạo registration */
    public function getForInternshipStart(int $app_id, int $cid): ?array
    {
        $aq = $this->conn->prepare(
            "SELECT a.*,sp.student_id,i.company_id,i.internship_id
             FROM applications a
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN internships i ON a.internship_id=i.internship_id
             WHERE a.application_id=? AND i.company_id=?"
        );
        $aq->bind_param('ii', $app_id, $cid);
        $aq->execute();
        return $aq->get_result()->fetch_assoc() ?: null;
    }

    /** Set status internship_active */
    public function setActive(int $app_id): void
    {
        $this->conn->query("UPDATE applications SET status='internship_active' WHERE application_id=$app_id");
    }

    /** Xóa đơn (trừ đang/đã thực tập) */
    public function delete(int $app_id, int $cid): bool
    {
        $u = $this->conn->prepare("DELETE FROM applications WHERE application_id=? AND internship_id IN (SELECT internship_id FROM internships WHERE company_id=?) AND status NOT IN ('internship_active','internship_completed')");
        $u->bind_param('ii', $app_id, $cid);
        return $u->execute();
    }

    /** Lỗi DB cuối */
    public function lastError(): string
    {
        return $this->conn->error;
    }
}
