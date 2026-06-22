<?php
require_once __DIR__ . '/BaseModel.php';

class InternshipModel extends BaseModel
{
    /** Lấy danh sách vị trí (admin), có search + filter status */
    public function getAllWithFilter(string $search, string $status_f): array
    {
        $sql = "SELECT i.*,cp.company_name,
                (SELECT COUNT(*) FROM applications WHERE internship_id=i.internship_id) AS app_count
                FROM internships i
                JOIN company_profiles cp ON i.company_id=cp.company_id
                WHERE 1=1";
        $p = []; $t = '';
        if ($search) {
            $sql .= " AND (i.title LIKE ? OR cp.company_name LIKE ?)";
            $like = "%$search%"; $p[] = $like; $p[] = $like; $t = 'ss';
        }
        if ($status_f) { $sql .= " AND i.status=?"; $p[] = $status_f; $t .= 's'; }
        $sql .= " ORDER BY i.created_at DESC";
        $st = $this->conn->prepare($sql);
        if ($p) $st->bind_param($t, ...$p);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lấy vị trí mở để SV browse, kèm thông tin đã apply chưa */
    public function getOpenJobs(int $sid, string $search): array
    {
        $sql = "SELECT i.*,cp.company_name,cp.logo,cp.address AS c_address,
                (SELECT COUNT(*) FROM applications
                 WHERE internship_id=i.internship_id
                 AND status NOT IN ('rejected_admin','rejected_company','interview_failed')) AS applied_count,
                (SELECT application_id FROM applications WHERE student_id=? AND internship_id=i.internship_id LIMIT 1) AS my_app
                FROM internships i
                JOIN company_profiles cp ON i.company_id=cp.company_id
                WHERE i.status='open'";
        $params = [$sid]; $types = 'i';
        if ($search) {
            $sql .= " AND (i.title LIKE ? OR i.description LIKE ? OR cp.company_name LIKE ?)";
            $like = "%$search%";
            $params[] = $like; $params[] = $like; $params[] = $like;
            $types .= 'sss';
        }
        $sql .= " ORDER BY i.created_at DESC";
        $st = $this->conn->prepare($sql);
        $st->bind_param($types, ...$params);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /** Lấy 1 vị trí theo ID (phải đang mở) */
    public function getOpenById(int $id): ?array
    {
        $iq = $this->conn->prepare(
            "SELECT i.*,cp.company_name,cp.logo
             FROM internships i
             JOIN company_profiles cp ON i.company_id=cp.company_id
             WHERE i.internship_id=? AND i.status='open'"
        );
        $iq->bind_param('i', $id);
        $iq->execute();
        return $iq->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy vị trí theo ID (không lọc status) */
    public function getById(int $id): ?array
    {
        $st = $this->conn->prepare("SELECT * FROM internships WHERE internship_id=?");
        $st->bind_param('i', $id);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /** Lấy danh sách vị trí của công ty */
    public function getByCompany(int $cid): array
    {
        return safeQuery($this->conn,
            "SELECT i.*,
             (SELECT COUNT(*) FROM applications WHERE internship_id=i.internship_id) AS app_cnt
             FROM internships i
             WHERE company_id=$cid
             ORDER BY created_at DESC"
        );
    }

    /** Tạo vị trí mới */
    public function create(int $cid, string $title, string $desc, string $req,
                           int $qty, string $loc, ?string $sd, ?string $ed): bool
    {
        $ins = $this->conn->prepare(
            "INSERT INTO internships (company_id,title,description,requirements,quantity,location,start_date,end_date,status)
             VALUES (?,?,?,?,?,?,?,?,'open')"
        );
        $ins->bind_param('isssssss', $cid, $title, $desc, $req, $qty, $loc, $sd, $ed);
        return $ins->execute();
    }

    /** Cập nhật vị trí */
    public function update(int $id, string $title, string $desc, string $req,
                           int $qty, string $loc, ?string $sd, ?string $ed, string $status): bool
    {
        $u = $this->conn->prepare(
            "UPDATE internships SET title=?,description=?,requirements=?,quantity=?,
             location=?,start_date=?,end_date=?,status=? WHERE internship_id=?"
        );
        $u->bind_param('ssssisssi', $title, $desc, $req, $qty, $loc, $sd, $ed, $status, $id);
        return $u->execute();
    }

    /** Toggle open/closed */
    public function toggleStatus(int $id, int $cid): void
    {
        $cur = safeRow($this->conn, "SELECT status FROM internships WHERE internship_id=$id AND company_id=$cid");
        if ($cur) {
            $ns = $cur['status'] === 'open' ? 'closed' : 'open';
            $this->conn->query("UPDATE internships SET status='$ns' WHERE internship_id=$id");
        }
    }

    /** Xóa vị trí (chỉ khi chưa có ứng viên) */
    public function delete(int $id, int $cid): bool
    {
        $cnt = safeCount($this->conn, "SELECT COUNT(*) c FROM applications WHERE internship_id=$id");
        if ($cnt > 0) return false;
        $this->conn->query("DELETE FROM internships WHERE internship_id=$id AND company_id=$cid");
        return true;
    }
}
