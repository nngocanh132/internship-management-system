<?php
require_once __DIR__ . '/BaseModel.php';

class MessageModel extends BaseModel
{
    /** Lấy danh sách conversation của SV */
    public function getConversationsForStudent(int $uid, int $student_id): array
    {
        $sql = "SELECT c.*,
                cp.company_name AS partner_name,cp.logo AS partner_av,cp.company_id AS partner_cid,
                (SELECT COUNT(*) FROM messages m2 WHERE m2.conversation_id=c.conversation_id AND m2.sender_id!=? AND m2.is_read=0) AS unread,
                (SELECT message_content FROM messages m3 WHERE m3.conversation_id=c.conversation_id ORDER BY m3.sent_at DESC LIMIT 1) AS last_msg,
                (SELECT sent_at FROM messages m4 WHERE m4.conversation_id=c.conversation_id ORDER BY m4.sent_at DESC LIMIT 1) AS last_at,
                a.application_id,i.title AS job_title
                FROM conversations c
                JOIN company_profiles cp ON c.company_id=cp.company_id
                LEFT JOIN applications a ON c.application_id=a.application_id
                LEFT JOIN internships i ON a.internship_id=i.internship_id
                WHERE c.student_id=?
                ORDER BY last_at DESC";
        $st = $this->conn->prepare($sql);
        $st->bind_param('ii', $uid, $student_id);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lấy danh sách conversation của công ty */
    public function getConversationsForCompany(int $uid, int $company_id): array
    {
        $sql = "SELECT c.*,
                sp.full_name AS partner_name,sp.avatar AS partner_av,sp.student_id AS partner_sid,
                (SELECT COUNT(*) FROM messages m2 WHERE m2.conversation_id=c.conversation_id AND m2.sender_id!=? AND m2.is_read=0) AS unread,
                (SELECT message_content FROM messages m3 WHERE m3.conversation_id=c.conversation_id ORDER BY m3.sent_at DESC LIMIT 1) AS last_msg,
                (SELECT sent_at FROM messages m4 WHERE m4.conversation_id=c.conversation_id ORDER BY m4.sent_at DESC LIMIT 1) AS last_at,
                a.application_id,i.title AS job_title
                FROM conversations c
                JOIN student_profiles sp ON c.student_id=sp.student_id
                LEFT JOIN applications a ON c.application_id=a.application_id
                LEFT JOIN internships i ON a.internship_id=i.internship_id
                WHERE c.company_id=?
                ORDER BY last_at DESC";
        $st = $this->conn->prepare($sql);
        $st->bind_param('ii', $uid, $company_id);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lấy messages trong conversation */
    public function getThreadByConversation(int $conv_id): array
    {
        $msgs = $this->conn->prepare(
            "SELECT m.*,u.email FROM messages m
             JOIN users u ON m.sender_id=u.user_id
             WHERE m.conversation_id=? ORDER BY m.sent_at ASC"
        );
        $msgs->bind_param('i', $conv_id);
        $msgs->execute();
        return $msgs->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lấy messages direct (lecturer–student, không có conversation_id) */
    public function getDirectThread(int $uid, int $partner_uid): array
    {
        return safeQuery($this->conn,
            "SELECT m.* FROM messages m
             WHERE (m.sender_id=$uid AND m.receiver_id=$partner_uid)
                OR (m.sender_id=$partner_uid AND m.receiver_id=$uid)
             ORDER BY m.sent_at ASC"
        );
    }

    /** Gửi tin nhắn trong conversation */
    public function sendToConversation(int $conv_id, int $uid, string $content): bool
    {
        $ins = $this->conn->prepare(
            "INSERT INTO messages (conversation_id,sender_id,message_content) VALUES (?,?,?)"
        );
        $ins->bind_param('iis', $conv_id, $uid, $content);
        return $ins->execute();
    }

    /** Gửi tin nhắn direct (lecturer–student) */
    public function sendDirect(int $uid, int $partner_uid, string $content): bool
    {
        $ins = $this->conn->prepare(
            "INSERT INTO messages (sender_id, receiver_id, message_content, conversation_id) VALUES (?,?,?,NULL)"
        );
        $ins->bind_param('iis', $uid, $partner_uid, $content);
        return $ins->execute();
    }

    /** Đánh dấu đã đọc trong conversation */
    public function markReadInConversation(int $conv_id, int $uid): void
    {
        $mr = $this->conn->prepare("UPDATE messages SET is_read=1 WHERE conversation_id=? AND sender_id!=?");
        $mr->bind_param('ii', $conv_id, $uid);
        $mr->execute();
    }

    /** Đánh dấu đã đọc tin nhắn direct */
    public function markReadDirect(int $sender_id, int $receiver_id): void
    {
        $this->conn->query("UPDATE messages SET is_read=1 WHERE sender_id=$sender_id AND receiver_id=$receiver_id");
    }

    /** Lấy thông tin phỏng vấn theo application_id */
    public function getInterviewByApp(int $app_id): ?array
    {
        $iq = $this->conn->prepare("SELECT * FROM interviews WHERE application_id=?");
        $iq->bind_param('i', $app_id);
        $iq->execute();
        return $iq->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy partner profile (company cho student) */
    public function getCompanyProfile(int $company_id): ?array
    {
        $pq = $this->conn->prepare("SELECT company_name AS name,logo AS avatar FROM company_profiles WHERE company_id=?");
        $pq->bind_param('i', $company_id);
        $pq->execute();
        return $pq->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy partner profile (student cho company) */
    public function getStudentProfile(int $student_id): ?array
    {
        $pq = $this->conn->prepare("SELECT full_name AS name,avatar FROM student_profiles WHERE student_id=?");
        $pq->bind_param('i', $student_id);
        $pq->execute();
        return $pq->get_result()->fetch_assoc() ?: null;
    }

    /** Tìm lecturer_uid của GVHD đang hướng dẫn SV */
    public function findLecturerUidForStudent(int $uid): int
    {
        $fq = $this->conn->prepare(
            "SELECT lp.user_id FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             WHERE sp.user_id=? AND ir.status='active'
             ORDER BY ir.created_at DESC LIMIT 1"
        );
        $fq->bind_param('i', $uid);
        $fq->execute();
        $row = $fq->get_result()->fetch_assoc();
        return (int)($row['user_id'] ?? 0);
    }

    /** Lấy profile giảng viên theo user_id */
    public function getLecturerProfile(int $lecturer_uid): ?array
    {
        $pq = $this->conn->prepare(
            "SELECT u.user_id,u.email,lp.full_name,lp.department,lp.phone
             FROM users u
             LEFT JOIN lecturer_profiles lp ON u.user_id=lp.user_id
             WHERE u.user_id=? AND u.role='lecturer'"
        );
        $pq->bind_param('i', $lecturer_uid);
        $pq->execute();
        return $pq->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy profile SV theo user_id (cho GV dùng) */
    public function getStudentUserProfile(int $student_uid): ?array
    {
        $pq = $this->conn->prepare(
            "SELECT u.user_id,u.email,sp.full_name,sp.avatar
             FROM users u
             LEFT JOIN student_profiles sp ON u.user_id=sp.user_id
             WHERE u.user_id=? AND u.role='student'"
        );
        $pq->bind_param('i', $student_uid);
        $pq->execute();
        return $pq->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy danh sách SV của GV (dùng trong lecturer_chat chọn SV) */
    public function getStudentsForLecturer(int $lid): array
    {
        return safeQuery($this->conn,
            "SELECT sp.student_id,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
             u.user_id AS s_user_id,u.email,i.title,cp.company_name
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN users u ON sp.user_id=u.user_id
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             WHERE ir.lecturer_id=$lid
             ORDER BY sp.full_name"
        );
    }

    /** Đếm tin chưa đọc từ 1 sender tới receiver (direct) */
    public function countUnreadDirect(int $sender_id, int $receiver_id): int
    {
        return safeCount($this->conn,
            "SELECT COUNT(*) c FROM messages WHERE sender_id=$sender_id AND receiver_id=$receiver_id AND is_read=0"
        );
    }
}
