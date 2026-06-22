<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/ApplicationModel.php';
require_once __DIR__ . '/../Models/RegistrationModel.php';
require_once __DIR__ . '/../Models/InternshipModel.php';

class DashboardController extends BaseController
{
    private ApplicationModel  $appModel;
    private RegistrationModel $regModel;
    private InternshipModel   $jobModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->appModel = new ApplicationModel($conn);
        $this->regModel = new RegistrationModel($conn);
        $this->jobModel = new InternshipModel($conn);
    }

    // ── Root index ────────────────────────────────────────────────────
    public function index(): void
    {
        if (isLoggedIn()) redirect(getDashboardUrl());
        redirect(BASE_PATH . '/auth/login.php');
    }

    // ── Admin dashboard ───────────────────────────────────────────────
    public function admin(): void
    {
        requireRole('admin');

        $db_ok = $this->conn->query("SHOW TABLES LIKE 'applications'")
                 && $this->conn->query("SHOW TABLES LIKE 'applications'")->num_rows > 0;

        if (!$db_ok) {
            $this->render(BASE_PATH_FS . 'app/Views/dashboard/db_not_ready.php', []);
            return;
        }

        $stats = [
            'students'  => safeCount($this->conn, "SELECT COUNT(*) c FROM users WHERE role='student'"),
            'companies' => safeCount($this->conn, "SELECT COUNT(*) c FROM users WHERE role='company'"),
            'jobs'      => safeCount($this->conn, "SELECT COUNT(*) c FROM internships WHERE status='open'"),
            'pending'   => safeCount($this->conn, "SELECT COUNT(*) c FROM applications WHERE status='pending_admin'"),
            'active'    => safeCount($this->conn, "SELECT COUNT(*) c FROM internship_registrations WHERE status='active'"),
            'reports'   => safeCount($this->conn, "SELECT COUNT(*) c FROM internship_reports WHERE status='pending'"),
        ];

        $pending_apps = safeQuery($this->conn,
            "SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.major,i.title,cp.company_name
             FROM applications a
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN internships i ON a.internship_id=i.internship_id
             JOIN company_profiles cp ON i.company_id=cp.company_id
             WHERE a.status='pending_admin' ORDER BY a.applied_at DESC LIMIT 8"
        );

        $iv_passed = safeQuery($this->conn,
            "SELECT a.*,sp.full_name,sp.student_code,i.title,cp.company_name
             FROM applications a
             JOIN student_profiles sp ON a.student_id=sp.student_id
             JOIN internships i ON a.internship_id=i.internship_id
             JOIN company_profiles cp ON i.company_id=cp.company_id
             WHERE a.status='interview_passed'
             AND NOT EXISTS (SELECT 1 FROM internship_registrations ir
                             WHERE ir.student_id=sp.student_id AND ir.internship_id=i.internship_id)
             ORDER BY a.applied_at DESC LIMIT 5"
        );

        $need_assign = $this->regModel->getNeedAssign();

        $this->render(BASE_PATH_FS . 'app/Views/dashboard/admin.php', [
            'stats'        => $stats,
            'pending_apps' => $pending_apps,
            'iv_passed'    => $iv_passed,
            'need_assign'  => $need_assign,
        ]);
    }

    // ── Student dashboard ─────────────────────────────────────────────
    public function student(): void
    {
        requireRole('student');

        $uid = $_SESSION['user_id'];
        $sv  = null; $sid = 0;

        $sq = $this->conn->prepare(
            "SELECT sp.*,u.is_profile_completed FROM student_profiles sp
             JOIN users u ON sp.user_id=u.user_id WHERE sp.user_id=?"
        );
        if ($sq) { $sq->bind_param('i', $uid); $sq->execute(); $sv = $sq->get_result()->fetch_assoc(); }

        if (!$sv) {
            $ins = $this->conn->prepare("INSERT IGNORE INTO student_profiles (user_id,full_name) VALUES (?,?)");
            if ($ins) { $ins->bind_param('is', $uid, $_SESSION['full_name'] ?? ''); $ins->execute(); }
            if ($sq) { $sq->bind_param('i', $uid); $sq->execute(); $sv = $sq->get_result()->fetch_assoc(); }
        }
        if ($sv) $sid = $sv['student_id'] ?? 0;

        if ($sid) $this->appModel->syncCompletedStatus($sid);

        $open_jobs  = safeCount($this->conn, "SELECT COUNT(*) c FROM internships WHERE status='open'");
        $app_cnt    = $sid ? safeCount($this->conn, "SELECT COUNT(*) c FROM applications WHERE student_id=$sid") : 0;
        $pend_cnt   = $sid ? safeCount($this->conn, "SELECT COUNT(*) c FROM applications WHERE student_id=$sid AND status='pending_admin'") : 0;
        $unread     = getUnreadCount($this->conn, $uid);
        $internship = $sid ? $this->regModel->getByStudent($sid) : null;
        $rapps      = $sid ? array_slice($this->appModel->getByStudent($sid), 0, 5) : [];

        $av = ($sv['avatar'] ?? '')
            ? UPLOAD_URL . '/' . $sv['avatar']
            : 'https://ui-avatars.com/api/?name=' . urlencode($sv['full_name'] ?? $_SESSION['full_name'] ?? 'SV') . '&background=5D7B6F&color=fff&size=100';

        $this->render(BASE_PATH_FS . 'app/Views/dashboard/student.php', [
            'sv'         => $sv,
            'sid'        => $sid,
            'open_jobs'  => $open_jobs,
            'app_cnt'    => $app_cnt,
            'pend_cnt'   => $pend_cnt,
            'unread'     => $unread,
            'internship' => $internship,
            'rapps'      => $rapps,
            'av'         => $av,
        ]);
    }

    // ── Company dashboard ─────────────────────────────────────────────
    public function company(): void
    {
        requireRole('company');

        $uid = $_SESSION['user_id'];
        $cp  = null; $cid = 0;

        $cq = $this->conn->prepare(
            "SELECT cp.*,u.is_profile_completed FROM company_profiles cp
             JOIN users u ON cp.user_id=u.user_id WHERE cp.user_id=?"
        );
        if ($cq) { $cq->bind_param('i', $uid); $cq->execute(); $cp = $cq->get_result()->fetch_assoc(); }

        if (!$cp) {
            $ins = $this->conn->prepare("INSERT IGNORE INTO company_profiles (user_id,company_name) VALUES (?,?)");
            if ($ins) { $ins->bind_param('is', $uid, $_SESSION['full_name'] ?? 'Công ty'); $ins->execute(); }
            if ($cq) { $cq->bind_param('i', $uid); $cq->execute(); $cp = $cq->get_result()->fetch_assoc(); }
        }
        if ($cp) $cid = $cp['company_id'] ?? 0;

        $stats = [
            'jobs'    => $cid ? safeCount($this->conn, "SELECT COUNT(*) c FROM internships WHERE company_id=$cid AND status='open'") : 0,
            'pending' => $cid ? safeCount($this->conn, "SELECT COUNT(*) c FROM applications a JOIN internships i ON a.internship_id=i.internship_id WHERE i.company_id=$cid AND a.status='approved_admin'") : 0,
            'active'  => $cid ? safeCount($this->conn, "SELECT COUNT(*) c FROM internship_registrations WHERE company_id=$cid AND status='active'") : 0,
        ];
        $unread = getUnreadCount($this->conn, $uid);

        $pending_apps = $cid ? safeQuery($this->conn,
            "SELECT a.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,sp.student_id,i.title
             FROM applications a JOIN internships i ON a.internship_id=i.internship_id
             JOIN student_profiles sp ON a.student_id=sp.student_id
             WHERE i.company_id=$cid AND a.status='approved_admin' ORDER BY sp.gpa DESC LIMIT 5"
        ) : [];

        $my_jobs = $cid ? $this->jobModel->getByCompany($cid) : [];
        // Hạn chế 5 vị trí mới nhất cho dashboard
        $my_jobs = array_slice($my_jobs, 0, 5);

        $logo = ($cp['logo'] ?? '')
            ? UPLOAD_URL . '/' . $cp['logo']
            : 'https://ui-avatars.com/api/?name=' . urlencode($cp['company_name'] ?? 'DN') . '&background=5D7B6F&color=fff&size=80&bold=true';

        $this->render(BASE_PATH_FS . 'app/Views/dashboard/company.php', [
            'cp'           => $cp,
            'cid'          => $cid,
            'stats'        => $stats,
            'unread'       => $unread,
            'pending_apps' => $pending_apps,
            'my_jobs'      => $my_jobs,
            'logo'         => $logo,
        ]);
    }

    // ── Lecturer dashboard ────────────────────────────────────────────
    public function lecturer(): void
    {
        requireRole('lecturer');

        $uid = $_SESSION['user_id'];
        $lp  = null; $lid = 0;

        $lq = $this->conn->prepare("SELECT * FROM lecturer_profiles WHERE user_id=?");
        if ($lq) { $lq->bind_param('i', $uid); $lq->execute(); $lp = $lq->get_result()->fetch_assoc(); }
        if ($lp) $lid = $lp['lecturer_id'] ?? 0;

        $active          = $lid ? safeCount($this->conn, "SELECT COUNT(*) c FROM internship_registrations WHERE lecturer_id=$lid AND status='active'") : 0;
        $total           = $lid ? safeCount($this->conn, "SELECT COUNT(*) c FROM internship_registrations WHERE lecturer_id=$lid") : 0;
        $pending_reports = $lid ? safeCount($this->conn,
            "SELECT COUNT(*) c FROM internship_reports rp
             JOIN internship_registrations ir ON rp.registration_id=ir.registration_id
             WHERE ir.lecturer_id=$lid AND rp.status='pending'"
        ) : 0;

        $rows = [];
        if ($lid) {
            $sq = $this->conn->prepare(
                "SELECT ir.*,sp.full_name,sp.student_code,sp.gpa,sp.avatar AS s_av,
                 cp.company_name,i.title,rp.status AS rep_status
                 FROM internship_registrations ir
                 JOIN student_profiles sp ON ir.student_id=sp.student_id
                 JOIN internships i ON ir.internship_id=i.internship_id
                 JOIN company_profiles cp ON ir.company_id=cp.company_id
                 LEFT JOIN internship_reports rp ON ir.registration_id=rp.registration_id
                 WHERE ir.lecturer_id=? ORDER BY ir.status,sp.full_name LIMIT 10"
            );
            if ($sq) { $sq->bind_param('i', $lid); $sq->execute(); $rows = $sq->get_result()->fetch_all(MYSQLI_ASSOC); }
        }

        $this->render(BASE_PATH_FS . 'app/Views/dashboard/lecturer.php', [
            'lp'              => $lp,
            'active'          => $active,
            'total'           => $total,
            'pending_reports' => $pending_reports,
            'rows'            => $rows,
        ]);
    }
}
