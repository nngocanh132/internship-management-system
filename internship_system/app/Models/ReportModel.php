<?php
require_once __DIR__ . '/BaseModel.php';

class ReportModel extends BaseModel
{
    /** Lấy danh sách báo cáo (admin/lecturer) */
    public function getList(string $role, int $uid): array
    {
        $lid_cond = '';
        if ($role === 'lecturer') {
            $lq = $this->conn->prepare("SELECT lecturer_id FROM lecturer_profiles WHERE user_id=?");
            $lid = 0;
            if ($lq) { $lq->bind_param('i', $uid); $lq->execute(); $lid = (int)($lq->get_result()->fetch_assoc()['lecturer_id'] ?? 0); }
            $lid_cond = "AND ir.lecturer_id=$lid";
        }
        return safeQuery($this->conn,
            "SELECT rp.*,sp.full_name,sp.student_code,sp.avatar AS s_av,
             cp.company_name,i.title,lp.full_name AS lecturer_name
             FROM internship_reports rp
             JOIN internship_registrations ir ON rp.registration_id=ir.registration_id
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             WHERE 1=1 $lid_cond
             ORDER BY rp.submitted_at DESC"
        );
    }

    /** Lấy báo cáo hiện tại của SV */
    public function getByRegistration(int $reg_id): ?array
    {
        $eq = $this->conn->prepare("SELECT * FROM internship_reports WHERE registration_id=?");
        $eq->bind_param('i', $reg_id);
        $eq->execute();
        return $eq->get_result()->fetch_assoc() ?: null;
    }

    /** Tạo báo cáo mới */
    public function create(int $reg_id, string $file_path): bool
    {
        $ins = $this->conn->prepare(
            "INSERT INTO internship_reports (registration_id,report_file,status) VALUES (?,?,'pending')"
        );
        $ins->bind_param('is', $reg_id, $file_path);
        return $ins->execute();
    }

    /** Cập nhật báo cáo đã nộp */
    public function update(int $report_id, string $file_path): bool
    {
        $u = $this->conn->prepare(
            "UPDATE internship_reports SET report_file=?,submitted_at=NOW(),status='pending' WHERE report_id=?"
        );
        $u->bind_param('si', $file_path, $report_id);
        return $u->execute();
    }

    /** Duyệt báo cáo → kỳ TT hoàn thành */
    public function approve(int $report_id): void
    {
        // Duyệt báo cáo
        $u = $this->conn->prepare("UPDATE internship_reports SET status='approved',reviewed_at=NOW() WHERE report_id=?");
        if ($u) { $u->bind_param('i', $report_id); $u->execute(); }

        // Kỳ TT hoàn thành
        $uc = $this->conn->prepare(
            "UPDATE internship_registrations SET status='completed'
             WHERE registration_id=(SELECT registration_id FROM internship_reports WHERE report_id=?)"
        );
        if ($uc) { $uc->bind_param('i', $report_id); $uc->execute(); }

        // Đơn ứng tuyển hoàn thành
        $ua = $this->conn->prepare(
            "UPDATE applications SET status='internship_completed'
             WHERE student_id=(
                 SELECT student_id FROM internship_registrations
                 WHERE registration_id=(SELECT registration_id FROM internship_reports WHERE report_id=?)
             ) AND status='internship_active'"
        );
        if ($ua) { $ua->bind_param('i', $report_id); $ua->execute(); }
    }

    /** Từ chối báo cáo, yêu cầu SV sửa */
    public function reject(int $report_id, string $note): void
    {
        $u = $this->conn->prepare(
            "UPDATE internship_reports SET status='rejected',lecturer_comment=?,reviewed_at=NOW() WHERE report_id=?"
        );
        if ($u) { $u->bind_param('si', $note, $report_id); $u->execute(); }
    }
}
