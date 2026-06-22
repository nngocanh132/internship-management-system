<?php
require_once __DIR__ . '/BaseModel.php';

class RegistrationModel extends BaseModel
{
    /** Tạo registration mới khi SV bắt đầu thực tập */
    public function create(int $student_id, int $company_id, int $internship_id): bool
    {
        $ins = $this->conn->prepare(
            "INSERT IGNORE INTO internship_registrations (student_id,company_id,internship_id,status)
             VALUES (?,?,?,'active')"
        );
        $ins->bind_param('iii', $student_id, $company_id, $internship_id);
        return $ins->execute();
    }

    /** Lấy tất cả registration (admin) */
    public function getAll(string $status_f = ''): array
    {
        $where = $status_f ? "WHERE ir.status='$status_f'" : '';
        return safeQuery($this->conn,
            "SELECT ir.*,sp.full_name,sp.student_code,sp.avatar AS s_av,
             i.title,cp.company_name,lp.full_name AS lecturer_name
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             $where
             ORDER BY ir.created_at DESC"
        );
    }

    /** Lấy registration của SV */
    public function getByStudent(int $sid): ?array
    {
        $rq = $this->conn->prepare(
            "SELECT ir.*,i.title,cp.company_name,cp.logo,
             lp.full_name AS lname,
             ev.overall_score,
             rp.status AS rep_status
             FROM internship_registrations ir
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             LEFT JOIN evaluations ev ON ir.registration_id=ev.registration_id
             LEFT JOIN internship_reports rp ON ir.registration_id=rp.registration_id
             WHERE ir.student_id=?
             ORDER BY ir.created_at DESC LIMIT 1"
        );
        $rq->bind_param('i', $sid);
        $rq->execute();
        return $rq->get_result()->fetch_assoc() ?: null;
    }

    /** Kiểm tra SV đã có registration chưa */
    public function getExistingByStudent(int $sid): ?array
    {
        $rc = $this->conn->prepare("SELECT registration_id,status FROM internship_registrations WHERE student_id=? LIMIT 1");
        $rc->bind_param('i', $sid);
        $rc->execute();
        return $rc->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy danh sách SV của giảng viên */
    public function getByLecturer(int $lid): array
    {
        return safeQuery($this->conn,
            "SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
             u.user_id AS s_user_id,u.email,
             i.title,cp.company_name,
             rp.status AS rep_status,rp.submitted_at AS rep_submitted,
             ev.overall_score
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN users u ON sp.user_id=u.user_id
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             LEFT JOIN internship_reports rp ON ir.registration_id=rp.registration_id
             LEFT JOIN evaluations ev ON ir.registration_id=ev.registration_id
             WHERE ir.lecturer_id=$lid
             ORDER BY sp.full_name"
        );
    }

    /** Lấy SV cần phân công GVHD (đang active nhưng chưa có lecturer) */
    public function getNeedAssign(): array
    {
        return safeQuery($this->conn,
            "SELECT ir.*,sp.full_name,sp.student_code,i.title,cp.company_name
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             WHERE ir.lecturer_id IS NULL AND ir.status='active'
             ORDER BY ir.created_at DESC LIMIT 5"
        );
    }

    /** Phân công giảng viên */
    public function assignLecturer(int $reg_id, int $lecturer_id): bool
    {
        $u = $this->conn->prepare("UPDATE internship_registrations SET lecturer_id=? WHERE registration_id=?");
        $u->bind_param('ii', $lecturer_id, $reg_id);
        return $u->execute();
    }

    /** Lấy 1 registration theo ID */
    public function getById(int $id): ?array
    {
        $st = $this->conn->prepare("SELECT * FROM internship_registrations WHERE registration_id=?");
        $st->bind_param('i', $id);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy registration + đầy đủ JOIN để hiện chi tiết cho SV */
    public function getDetailForStudent(int $sid): ?array
    {
        $rq = $this->conn->prepare(
            "SELECT ir.*,i.title,i.description,i.requirements,i.location,
             cp.company_name,cp.logo,cp.address,cp.website,cp.phone AS c_phone,
             lp.full_name AS lecturer_name,lp.phone AS l_phone,lp.department,lp.email AS l_email,
             ev.overall_score,ev.technical_skill,ev.teamwork,ev.communication,ev.attitude,ev.comment AS ev_comment,
             rp.status AS rep_status,rp.report_file,rp.lecturer_comment,rp.submitted_at AS rep_submitted
             FROM internship_registrations ir
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             LEFT JOIN evaluations ev ON ir.registration_id=ev.registration_id
             LEFT JOIN internship_reports rp ON ir.registration_id=rp.registration_id
             WHERE ir.student_id=?
             ORDER BY ir.created_at DESC LIMIT 1"
        );
        $rq->bind_param('i', $sid);
        $rq->execute();
        return $rq->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy registration của công ty */
    public function getByCompany(int $cid): array
    {
        return safeQuery($this->conn,
            "SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
             i.title,lp.full_name AS lecturer_name
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN internships i ON ir.internship_id=i.internship_id
             LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             WHERE ir.company_id=$cid
             ORDER BY ir.created_at DESC"
        );
    }
}
