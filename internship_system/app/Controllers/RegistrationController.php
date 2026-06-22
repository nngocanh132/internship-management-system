<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/RegistrationModel.php';

class RegistrationController extends BaseController
{
    private RegistrationModel $regModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->regModel = new RegistrationModel($conn);
    }

    // ── Admin: tự tạo registration từ application đậu PV ─────────────
    public function createFromApp(): void
    {
        requireRole('admin');

        $app_id = (int)($_GET['app_id'] ?? 0);
        if (!$app_id) redirect(BASE_PATH . '/registrations/list.php');

        $aq = $this->conn->prepare(
            "SELECT a.*,sp.student_id,cp.company_id,i.start_date,i.end_date
             FROM applications a
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN internships i ON a.internship_id=i.internship_id
             JOIN company_profiles cp ON i.company_id=cp.company_id
             WHERE a.application_id=? AND a.status='interview_passed'"
        );
        $aq->bind_param('i', $app_id); $aq->execute();
        $app = $aq->get_result()->fetch_assoc();

        if (!$app) { setFlash('error', 'Đơn chưa đạt trạng thái đậu phỏng vấn.'); redirect(BASE_PATH . '/registrations/list.php'); }

        $chk = $this->conn->prepare("SELECT registration_id FROM internship_registrations WHERE student_id=? AND internship_id=?");
        $chk->bind_param('ii', $app['student_id'], $app['internship_id']); $chk->execute();
        if ($chk->get_result()->num_rows > 0) { setFlash('info', 'Sinh viên đã được tạo hợp đồng rồi.'); redirect(BASE_PATH . '/registrations/list.php'); }

        $ins = $this->conn->prepare("INSERT INTO internship_registrations (student_id,company_id,internship_id,start_date,end_date,status) VALUES (?,?,?,?,?,'active')");
        $ins->bind_param('iiiss', $app['student_id'], $app['company_id'], $app['internship_id'], $app['start_date'], $app['end_date']);
        if ($ins->execute()) {
            $ua = $this->conn->prepare("UPDATE applications SET status='internship_active' WHERE application_id=?");
            $ua->bind_param('i', $app_id); $ua->execute();
            setFlash('success', '✅ Đã tạo hợp đồng thực tập! Hãy phân công giảng viên hướng dẫn.');
        } else {
            setFlash('error', 'Lỗi: ' . $this->conn->error);
        }
        redirect(BASE_PATH . '/registrations/list.php');
    }

    // ── Admin: sửa trạng thái registration ───────────────────────────
    public function edit(): void
    {
        requireRole('admin');

        $id   = (int)($_GET['id'] ?? 0);
        if (!$id) redirect('list.php');

        $stmt = $this->conn->prepare("SELECT * FROM internship_registrations WHERE registration_id=?");
        $stmt->bind_param('i', $id); $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) { setFlash('error', 'Không tìm thấy.'); redirect('list.php'); }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = sanitize($_POST['status'] ?? $row['status']);
            $u = $this->conn->prepare("UPDATE internship_registrations SET status=? WHERE registration_id=?");
            $u->bind_param('si', $status, $id);
            if ($u->execute()) { setFlash('success', 'Đã cập nhật trạng thái.'); redirect('list.php'); }
            else $errors[] = 'Lỗi: ' . $this->conn->error;
        }

        $this->render(BASE_PATH_FS . 'app/Views/registrations/edit.php', ['row' => $row, 'errors' => $errors]);
    }

    // ── Admin: danh sách tất cả registration ─────────────────────────
    public function list(): void
    {
        requireRole('admin');

        // Hoàn thành kỳ TT
        if (isset($_GET['complete'])) {
            $id = (int)$_GET['complete'];
            $u  = $this->conn->prepare("UPDATE internship_registrations SET status='completed' WHERE registration_id=?");
            if ($u) { $u->bind_param('i', $id); $u->execute(); }
            $ua = $this->conn->prepare("UPDATE applications SET status='internship_completed' WHERE student_id=(SELECT student_id FROM internship_registrations WHERE registration_id=?) AND status='internship_active'");
            if ($ua) { $ua->bind_param('i', $id); $ua->execute(); }
            setFlash('success', '🏆 Kỳ thực tập hoàn thành!');
            redirect('list.php');
        }

        $status_f   = sanitize($_GET['status'] ?? '');
        $regs       = $this->regModel->getAll($status_f);
        $cnt_active = safeCount($this->conn, "SELECT COUNT(*) c FROM internship_registrations WHERE status='active'");
        $cnt_done   = safeCount($this->conn, "SELECT COUNT(*) c FROM internship_registrations WHERE status='completed'");

        $this->render(BASE_PATH_FS . 'app/Views/registrations/list.php', [
            'regs'       => $regs,
            'status_f'   => $status_f,
            'cnt_active' => $cnt_active,
            'cnt_done'   => $cnt_done,
        ]);
    }

    // ── Admin: phân công giảng viên ───────────────────────────────────
    public function assign(): void
    {
        requireRole('admin');

        // POST: phân công từ registration_id có sẵn
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_id'])) {
            $reg_id      = (int)$_POST['registration_id'];
            $lec_id      = (int)$_POST['lecturer_id'];
            $sd          = sanitize($_POST['start_date'] ?? '');
            $ed          = sanitize($_POST['end_date'] ?? '');
            if ($reg_id && $lec_id) {
                $u = $this->conn->prepare("UPDATE internship_registrations SET lecturer_id=?,start_date=?,end_date=? WHERE registration_id=?");
                if ($u) { $sdd = !empty($sd)?$sd:null; $edd = !empty($ed)?$ed:null; $u->bind_param('issi',$lec_id,$sdd,$edd,$reg_id); $u->execute(); }
                setFlash('success', '✅ Đã phân công GVHD thành công!');
            }
            redirect('assign.php');
        }

        // POST: phân công từ application_id (SV đậu PV chưa có registration)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
            $app_id = (int)$_POST['application_id'];
            $lec_id = (int)$_POST['lecturer_id'];
            $sd     = sanitize($_POST['start_date'] ?? '');
            $ed     = sanitize($_POST['end_date'] ?? '');
            if ($app_id && $lec_id) {
                $aq = $this->conn->prepare("SELECT a.*,sp.student_id,i.company_id FROM applications a JOIN student_profiles sp ON a.student_id=sp.student_id JOIN internships i ON a.internship_id=i.internship_id WHERE a.application_id=?");
                if ($aq) {
                    $aq->bind_param('i', $app_id); $aq->execute();
                    $app = $aq->get_result()->fetch_assoc();
                    if ($app) {
                        $sdd = !empty($sd)?$sd:null; $edd = !empty($ed)?$ed:null;
                        $ins = $this->conn->prepare("INSERT IGNORE INTO internship_registrations (student_id,company_id,internship_id,lecturer_id,start_date,end_date,status) VALUES (?,?,?,?,?,?,'active')");
                        if ($ins) { $ins->bind_param('iiiiss',$app['student_id'],$app['company_id'],$app['internship_id'],$lec_id,$sdd,$edd); $ins->execute(); }
                        $this->conn->query("UPDATE applications SET status='internship_active' WHERE application_id=$app_id");
                        setFlash('success', '✅ Đã phân công GVHD và bắt đầu kỳ thực tập!');
                    }
                }
            }
            redirect('assign.php');
        }

        // Lấy danh sách giảng viên kèm số SV đang TT
        $lecturers = safeQuery($this->conn,
            "SELECT lp.*,
             (SELECT COUNT(*) FROM internship_registrations ir WHERE ir.lecturer_id=lp.lecturer_id AND ir.status='active') AS sv_count
             FROM lecturer_profiles lp ORDER BY sv_count ASC, lp.full_name"
        );

        // SV đậu PV chưa có registration → cần tạo registration + phân công GV
        $need_create = safeQuery($this->conn,
            "SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
             i.title,i.start_date AS j_start,i.end_date AS j_end,cp.company_name
             FROM applications a
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN internships i ON a.internship_id=i.internship_id
             JOIN company_profiles cp ON i.company_id=cp.company_id
             WHERE a.status='interview_passed'
             AND NOT EXISTS (SELECT 1 FROM internship_registrations ir WHERE ir.student_id=sp.student_id AND ir.internship_id=i.internship_id)
             ORDER BY a.applied_at DESC"
        );

        // SV đang TT nhưng chưa có GVHD
        $no_lecturer = safeQuery($this->conn,
            "SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,cp.company_name,i.title
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             WHERE ir.lecturer_id IS NULL AND ir.status='active'
             ORDER BY ir.created_at DESC"
        );

        // SV đã có GVHD
        $has_lecturer = safeQuery($this->conn,
            "SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,
             cp.company_name,i.title,lp.full_name AS lname,lp.department
             FROM internship_registrations ir
             JOIN student_profiles sp ON ir.student_id=sp.student_id
             JOIN internships i ON ir.internship_id=i.internship_id
             JOIN company_profiles cp ON ir.company_id=cp.company_id
             LEFT JOIN lecturer_profiles lp ON ir.lecturer_id=lp.lecturer_id
             WHERE ir.lecturer_id IS NOT NULL
             ORDER BY ir.created_at DESC"
        );

        $total_need = count($need_create) + count($no_lecturer);

        $this->render(BASE_PATH_FS . 'app/Views/registrations/assign.php', [
            'need_create' => $need_create,
            'no_lecturer' => $no_lecturer,
            'has_lecturer'=> $has_lecturer,
            'lecturers'   => $lecturers,
            'total_need'  => $total_need,
        ]);
    }

    // ── Student: chi tiết kỳ thực tập của tôi ────────────────────────
    public function myInternship(): void
    {
        requireRole('student');

        $uid = $_SESSION['user_id'];
        $sq  = $this->conn->prepare("SELECT student_id FROM student_profiles WHERE user_id=?");
        $sq->bind_param('i', $uid); $sq->execute();
        $sid = $sq->get_result()->fetch_assoc()['student_id'] ?? 0;

        $reg = $sid ? $this->regModel->getDetailForStudent($sid) : null;
        if (!$reg) { setFlash('info', 'Bạn chưa có kỳ thực tập nào.'); redirect(getDashboardUrl()); }

        $this->render(BASE_PATH_FS . 'app/Views/registrations/my_internship.php', [
            'reg' => $reg,
        ]);
    }

    // ── Lecturer: SV được phân công ───────────────────────────────────
    public function myStudents(): void
    {
        requireRole('lecturer');

        $uid = $_SESSION['user_id'];
        $lq  = $this->conn->prepare("SELECT lecturer_id FROM lecturer_profiles WHERE user_id=?");
        $lid = 0;
        if ($lq) { $lq->bind_param('i', $uid); $lq->execute(); $lid = $lq->get_result()->fetch_assoc()['lecturer_id'] ?? 0; }

        $students = $lid ? $this->regModel->getByLecturer($lid) : [];

        $this->render(BASE_PATH_FS . 'app/Views/registrations/my_students.php', [
            'students' => $students,
        ]);
    }

    // ── Company: xem SV đang thực tập ────────────────────────────────
    public function companyView(): void
    {
        requireRole('company');

        $uid = $_SESSION['user_id'];
        $cq  = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
        $cid = 0;
        if ($cq) { $cq->bind_param('i', $uid); $cq->execute(); $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0; }

        $regs = $cid ? $this->regModel->getByCompany($cid) : [];

        $this->render(BASE_PATH_FS . 'app/Views/registrations/company_view.php', [
            'regs' => $regs,
        ]);
    }
}
