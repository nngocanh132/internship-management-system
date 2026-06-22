<?php
require_once __DIR__ . '/BaseController.php';

class InterviewController extends BaseController
{
    // ── Danh sách lịch phỏng vấn ──────────────────────────────────────
    public function list(): void
    {
        requireRole(['admin', 'company', 'student']);
        $uid  = $_SESSION['user_id'];
        $role = getRole();

        $sql = "SELECT iv.*,a.status AS app_status,sp.full_name,sp.student_code,sp.avatar AS s_av,
                cp.company_name,cp.logo AS c_logo,i.title,a.application_id,
                sp.student_id, cp.company_id
                FROM interviews iv
                JOIN applications a ON iv.application_id=a.application_id
                JOIN student_profiles sp ON a.student_id=sp.student_id
                JOIN internships i ON a.internship_id=i.internship_id
                JOIN company_profiles cp ON i.company_id=cp.company_id
                WHERE 1=1";

        if ($role === 'student') {
            $sq = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
            $sq->bind_param('i', $uid); $sq->execute();
            $sid = $sq->get_result()->fetch_assoc()['student_id'] ?? 0;
            $sql .= " AND a.student_id=$sid";
        } elseif ($role === 'company') {
            $cq = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
            $cq->bind_param('i', $uid); $cq->execute();
            $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0;
            $sql .= " AND cp.company_id=$cid";
        }
        $sql .= " ORDER BY iv.interview_date DESC";
        $ivs = $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);

        $this->render(BASE_PATH_FS . 'app/Views/interviews/list.php', [
            'ivs'  => $ivs,
            'role' => $role,
        ]);
    }

    // ── Đặt/cập nhật lịch phỏng vấn (Company POST) ───────────────────
    public function setInterview(): void
    {
        requireRole('company');

        $app_id = (int)($_POST['app_id'] ?? 0);
        $redir  = sanitize($_POST['redirect'] ?? '');
        if (!$app_id) redirect('inbox.php');

        $date = sanitize($_POST['interview_date'] ?? '');
        $addr = sanitize($_POST['address'] ?? '');
        $link = sanitize($_POST['meeting_link'] ?? '');

        if (empty($date)) {
            setFlash('error', '⚠️ Vui lòng chọn ngày giờ phỏng vấn.');
            redirect('chat.php?' . $redir);
        }
        if (strtotime($date) <= time()) {
            setFlash('error', '⚠️ Ngày giờ phỏng vấn phải ở tương lai.');
            redirect('chat.php?' . $redir);
        }

        $chk = $this->conn->prepare("SELECT interview_id FROM interviews WHERE application_id=?");
        $chk->bind_param('i', $app_id); $chk->execute();
        $ex = $chk->get_result()->fetch_assoc();

        if ($ex) {
            $u = $this->conn->prepare("UPDATE interviews SET interview_date=?,address=?,meeting_link=? WHERE application_id=?");
            $u->bind_param('sssi', $date, $addr, $link, $app_id); $u->execute();
        } else {
            $ins = $this->conn->prepare("INSERT INTO interviews (application_id,interview_date,address,meeting_link,result) VALUES (?,?,?,?,'pending')");
            $ins->bind_param('isss', $app_id, $date, $addr, $link); $ins->execute();
        }
        setFlash('success', 'Đã lưu lịch phỏng vấn!');
        redirect('chat.php?' . $redir);
    }

    // ── Cập nhật kết quả phỏng vấn (Company GET) ─────────────────────
    public function setResult(): void
    {
        requireRole('company');

        $app_id = (int)($_GET['app_id'] ?? 0);
        $result = sanitize($_GET['result'] ?? '');
        if (!$app_id || !in_array($result, ['passed', 'failed'])) redirect('inbox.php');

        $ui = $this->conn->prepare("UPDATE interviews SET result=? WHERE application_id=?");
        $ui->bind_param('si', $result, $app_id); $ui->execute();

        $new_status = $result === 'passed' ? 'interview_passed' : 'interview_failed';
        $ua = $this->conn->prepare("UPDATE applications SET status=? WHERE application_id=?");
        $ua->bind_param('si', $new_status, $app_id); $ua->execute();

        setFlash($result === 'passed' ? 'success' : 'info',
            $result === 'passed' ? '🎉 Sinh viên đậu phỏng vấn!' : 'Đã cập nhật kết quả phỏng vấn.');

        $params = [];
        foreach (['company_id', 'student_id', 'app_id'] as $k)
            if (isset($_GET[$k])) $params[$k] = $_GET[$k];
        redirect('chat.php?' . http_build_query($params));
    }
}
