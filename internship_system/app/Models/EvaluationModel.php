<?php
require_once __DIR__ . '/BaseModel.php';

class EvaluationModel extends BaseModel
{
    /** Lấy danh sách đánh giá (admin/company/student) */
    public function getList(string $role, int $uid): array
    {
        $where = "WHERE 1=1";
        if ($role === 'student') {
            $sq = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
            $sq->bind_param('i', $uid); $sq->execute();
            $sid = (int)($sq->get_result()->fetch_assoc()['student_id'] ?? 0);
            $where = "WHERE ir.student_id=$sid";
        } elseif ($role === 'company') {
            $cq = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
            $cq->bind_param('i', $uid); $cq->execute();
            $cid = (int)($cq->get_result()->fetch_assoc()['company_id'] ?? 0);
            $where = "WHERE ir.company_id=$cid";
        }
        return safeQuery($this->conn,
            "SELECT e.*,sp.full_name,sp.student_code,sp.avatar AS s_av,
             cp.company_name,i.title
             FROM evaluations e
             JOIN internship_registrations ir ON e.registration_id=ir.registration_id
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             JOIN internships i ON ir.internship_id=i.internship_id
             $where ORDER BY e.evaluated_at DESC"
        );
    }

    /** Lấy danh sách SV chưa được đánh giá (của công ty) */
    public function getPendingByCompany(int $cid): array
    {
        return safeQuery($this->conn,
            "SELECT ir.*,sp.full_name,sp.student_code,i.title
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN internships i ON ir.internship_id=i.internship_id
             WHERE ir.company_id=$cid
             AND ir.registration_id NOT IN (SELECT registration_id FROM evaluations)
             ORDER BY sp.full_name"
        );
    }

    /** Tạo đánh giá mới */
    public function create(int $reg_id, int $tech, int $team, int $comm, int $att, string $comment): bool
    {
        $overall = round(($tech + $team + $comm + $att) / 4, 2);
        $ins = $this->conn->prepare(
            "INSERT INTO evaluations (registration_id,technical_skill,teamwork,communication,attitude,overall_score,comment)
             VALUES (?,?,?,?,?,?,?)"
        );
        $ins->bind_param('iiiiiis', $reg_id, $tech, $team, $comm, $att, $overall, $comment);
        return $ins->execute();
    }

    /** Lỗi cuối */
    public function lastError(): string
    {
        return $this->conn->error;
    }
}
