<?php
/**
 * PublicController
 * Xử lý các trang công khai (landing page, danh sách việc, giới thiệu).
 * Không yêu cầu đăng nhập.
 */
require_once __DIR__ . '/BaseController.php';

class PublicController extends BaseController
{
    // ── Trang chủ ─────────────────────────────────────────────────────
    public function home(): void
    {
        // Stats
        $stats = [];
        foreach ([
            'jobs'      => "SELECT COUNT(*) c FROM internships WHERE status='open'",
            'students'  => "SELECT COUNT(*) c FROM users WHERE role='student'",
            'companies' => "SELECT COUNT(*) c FROM company_profiles",
            'completed' => "SELECT COUNT(*) c FROM internship_registrations WHERE status='completed'",
        ] as $k => $q) {
            $r = $this->conn->query($q);
            $stats[$k] = ($r && $r !== true) ? (int)$r->fetch_assoc()['c'] : 0;
        }

        // Vị trí mới nhất (6)
        $jobs = [];
        $r = $this->conn->query(
            "SELECT i.*,cp.company_name,cp.logo,cp.address
             FROM internships i
             LEFT JOIN company_profiles cp ON i.company_id=cp.company_id
             WHERE i.status='open'
             ORDER BY i.created_at DESC LIMIT 6"
        );
        if ($r && $r !== true) while ($row = $r->fetch_assoc()) $jobs[] = $row;

        // $base_sys và $base_pub được set trong entry-point (public/index.php)
        // trước khi gọi controller, nên dùng global scope
        $base_sys = $GLOBALS['base_sys'] ?? '/internship-management-system/internship_system';
        $base_pub = $GLOBALS['base_pub'] ?? '/internship-management-system/public';
        require __DIR__ . '/../../public/views/home.php';
    }

    // ── Danh sách vị trí thực tập (public) ───────────────────────────
    public function internships(): void
    {
        $search  = trim($_GET['q'] ?? '');
        $loc_f   = trim($_GET['loc'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $per     = 9;
        $offset  = ($page - 1) * $per;

        $where  = "i.status='open'";
        $params = []; $types = '';
        if ($search) {
            $where   .= " AND (i.title LIKE ? OR cp.company_name LIKE ? OR i.description LIKE ?)";
            $lk       = "%$search%";
            $params[] = $lk; $params[] = $lk; $params[] = $lk; $types .= 'sss';
        }
        if ($loc_f) {
            $where   .= " AND (i.location LIKE ? OR cp.address LIKE ?)";
            $lk2      = "%$loc_f%";
            $params[] = $lk2; $params[] = $lk2; $types .= 'ss';
        }

        // Count
        $total = 0;
        $csql  = "SELECT COUNT(*) c FROM internships i LEFT JOIN company_profiles cp ON i.company_id=cp.company_id WHERE $where";
        if ($types) {
            $st = $this->conn->prepare($csql);
            if ($st) { $st->bind_param($types, ...$params); $st->execute(); $total = (int)$st->get_result()->fetch_assoc()['c']; }
        } else {
            $r = $this->conn->query($csql);
            $total = ($r && $r !== true) ? (int)$r->fetch_assoc()['c'] : 0;
        }

        $total_pages = max(1, ceil($total / $per));

        // Fetch jobs
        $jobs = [];
        $jsql = "SELECT i.*,cp.company_name,cp.logo,cp.address,cp.website
                 FROM internships i LEFT JOIN company_profiles cp ON i.company_id=cp.company_id
                 WHERE $where ORDER BY i.created_at DESC LIMIT $per OFFSET $offset";
        if ($types) {
            $st2 = $this->conn->prepare($jsql);
            if ($st2) { $st2->bind_param($types, ...$params); $st2->execute(); $res = $st2->get_result(); while ($row = $res->fetch_assoc()) $jobs[] = $row; }
        } else {
            $r2 = $this->conn->query($jsql);
            if ($r2 && $r2 !== true) while ($row = $r2->fetch_assoc()) $jobs[] = $row;
        }

        // Locations for filter
        $locs = [];
        $r = $this->conn->query("SELECT DISTINCT location FROM internships WHERE location IS NOT NULL AND location!='' ORDER BY location");
        if ($r && $r !== true) while ($row = $r->fetch_assoc()) $locs[] = $row['location'];

        $base_sys = $GLOBALS['base_sys'] ?? '/internship-management-system/internship_system';
        $base_pub = $GLOBALS['base_pub'] ?? '/internship-management-system/public';
        require __DIR__ . '/../../public/views/internships.php';
    }
}
