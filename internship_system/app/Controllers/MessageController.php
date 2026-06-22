<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/MessageModel.php';

class MessageController extends BaseController
{
    private MessageModel $msgModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->msgModel = new MessageModel($conn);
    }

    // ── Student/Company: hộp thư ──────────────────────────────────────
    public function inbox(): void
    {
        requireRole(['student', 'company']);

        $uid  = $_SESSION['user_id'];
        $role = getRole();

        if ($role === 'student') {
            $sq = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
            $sq->bind_param('i', $uid); $sq->execute();
            $my_pid = $sq->get_result()->fetch_assoc()['student_id'] ?? 0;
            $convos = $this->msgModel->getConversationsForStudent($uid, $my_pid);
        } else {
            $cq = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
            $cq->bind_param('i', $uid); $cq->execute();
            $my_pid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0;
            $convos = $this->msgModel->getConversationsForCompany($uid, $my_pid);
        }

        $this->render(BASE_PATH_FS . 'app/Views/messages/inbox.php', [
            'convos' => $convos,
            'role'   => $role,
        ]);
    }

    // ── Student/Company: chat trong conversation ──────────────────────
    public function chat(): void
    {
        requireRole(['student', 'company']);

        $uid    = $_SESSION['user_id'];
        $role   = getRole();
        $app_id = (int)($_GET['app_id'] ?? 0);

        if ($role === 'student') {
            $sp = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
            $sp->bind_param('i', $uid); $sp->execute();
            $my_pid       = $sp->get_result()->fetch_assoc()['student_id'] ?? 0;
            $partner_cid  = (int)($_GET['company_id'] ?? 0);
            $conv_student = $my_pid;
            $conv_company = $partner_cid;
            $partner      = $this->msgModel->getCompanyProfile($partner_cid);
        } else {
            $cp = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
            $cp->bind_param('i', $uid); $cp->execute();
            $my_pid       = $cp->get_result()->fetch_assoc()['company_id'] ?? 0;
            $partner_sid  = (int)($_GET['student_id'] ?? 0);
            $conv_student = $partner_sid;
            $conv_company = $my_pid;
            $partner      = $this->msgModel->getStudentProfile($partner_sid);
        }

        if (!$partner) { setFlash('error', 'Không tìm thấy đối thoại.'); redirect('inbox.php'); }

        $conv_id = getOrCreateConversation($this->conn, $conv_student, $conv_company, $app_id ?: null);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');
            if (!empty($content)) $this->msgModel->sendToConversation($conv_id, $uid, $content);
            redirect('chat.php?' . http_build_query($_GET));
        }

        $this->msgModel->markReadInConversation($conv_id, $uid);
        $thread    = $this->msgModel->getThreadByConversation($conv_id);
        $interview = $app_id ? $this->msgModel->getInterviewByApp($app_id) : null;

        $partner_name   = $partner['name'] ?? '—';
        $partner_raw_av = $partner['logo'] ?? $partner['avatar'] ?? null;
        $partner_av     = $partner_raw_av
            ? UPLOAD_URL . '/' . $partner_raw_av
            : 'https://ui-avatars.com/api/?name=' . urlencode($partner_name) . '&background=5D7B6F&color=fff&size=60';
        $my_av = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['full_name'] ?? 'U') . '&background=A4C3A2&color=2A3F38&size=60';

        $this->render(BASE_PATH_FS . 'app/Views/messages/chat.php', [
            'thread'       => $thread,
            'partner_name' => $partner_name,
            'partner_av'   => $partner_av,
            'my_av'        => $my_av,
            'interview'    => $interview,
            'role'         => $role,
            'app_id'       => $app_id,
            'uid'          => $uid,
        ]);
    }

    // ── Lecturer/Student: chat direct ────────────────────────────────
    public function lecturerChat(): void
    {
        requireRole(['lecturer', 'student']);

        $uid  = $_SESSION['user_id'];
        $role = getRole();

        if ($role === 'lecturer') {
            $partner_uid = (int)($_GET['student_uid'] ?? 0);

            // Chưa chọn SV → hiện danh sách
            if (!$partner_uid) {
                $lq2 = $this->conn->prepare("SELECT lecturer_id FROM lecturer_profiles WHERE user_id=?");
                $lid2 = 0;
                if ($lq2) { $lq2->bind_param('i', $uid); $lq2->execute(); $lid2 = $lq2->get_result()->fetch_assoc()['lecturer_id'] ?? 0; }

                $my_students = $lid2 ? $this->msgModel->getStudentsForLecturer($lid2) : [];
                foreach ($my_students as &$s)
                    $s['unread_from'] = $this->msgModel->countUnreadDirect($s['s_user_id'], $uid);
                unset($s);

                $this->render(BASE_PATH_FS . 'app/Views/messages/lecturer_student_list.php', [
                    'my_students' => $my_students,
                    'uid'         => $uid,
                ]);
                return;
            }

            $partner = $this->msgModel->getStudentUserProfile($partner_uid);
            if (!$partner) { setFlash('error', 'Không tìm thấy sinh viên.'); redirect(BASE_PATH . '/registrations/my_students.php'); }
            $partner_name = $partner['full_name'] ?: $partner['email'];
            $partner_av   = ($partner['avatar'] ?? '')
                ? UPLOAD_URL . '/' . $partner['avatar']
                : 'https://ui-avatars.com/api/?name=' . urlencode($partner_name) . '&background=5D7B6F&color=fff&size=60';
            $back_url = BASE_PATH . '/registrations/my_students.php';

        } else {
            // Student → tìm GVHD
            $partner_uid = (int)($_GET['lecturer_uid'] ?? 0);
            if (!$partner_uid)
                $partner_uid = $this->msgModel->findLecturerUidForStudent($uid);

            if (!$partner_uid) {
                setFlash('warning', 'Bạn chưa có GVHD. Vui lòng chờ Admin phân công.');
                redirect(BASE_PATH . '/registrations/my_internship.php');
            }

            $partner = $this->msgModel->getLecturerProfile($partner_uid);
            if (!$partner) { setFlash('error', 'Không tìm thấy giảng viên.'); redirect(getDashboardUrl()); }
            $partner_name = $partner['full_name'] ?: $partner['email'];
            $partner_av   = 'https://ui-avatars.com/api/?name=' . urlencode($partner_name) . '&background=4ab8c4&color=fff&size=60';
            $back_url = BASE_PATH . '/registrations/my_internship.php';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');
            if (!empty($content)) $this->msgModel->sendDirect($uid, $partner_uid, $content);
            $params = ($role === 'lecturer') ? "student_uid=$partner_uid" : "lecturer_uid=$partner_uid";
            redirect('lecturer_chat.php?' . $params);
        }

        $this->msgModel->markReadDirect($partner_uid, $uid);
        $thread = $this->msgModel->getDirectThread($uid, $partner_uid);
        $my_av  = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['full_name'] ?? 'Me') . '&background=A4C3A2&color=2A3F38&size=60';

        $this->render(BASE_PATH_FS . 'app/Views/messages/lecturer_chat.php', [
            'thread'       => $thread,
            'partner_name' => $partner_name,
            'partner_av'   => $partner_av,
            'my_av'        => $my_av,
            'role'         => $role,
            'uid'          => $uid,
            'partner_uid'  => $partner_uid,
            'partner'      => $partner,
            'back_url'     => $back_url,
        ]);
    }
}
