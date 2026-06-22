<?php
require_once __DIR__ . '/../Controllers/BaseController.php';
require_once __DIR__ . '/../Models/EvaluationModel.php';

class EvaluationController extends BaseController
{
    private EvaluationModel $evalModel;

    public function __construct($conn)
    {
        parent::__construct($conn);
        $this->evalModel = new EvaluationModel($conn);
    }

    // ── Admin/Company/Student: danh sách đánh giá ────────────────────
    public function list(): void
    {
        requireRole(['admin', 'company', 'student']);

        $uid   = $_SESSION['user_id'];
        $role  = getRole();
        $evals = $this->evalModel->getList($role, $uid);

        $this->render(BASE_PATH_FS . 'app/Views/evaluations/list.php', [
            'evals' => $evals,
            'role'  => $role,
        ]);
    }

    // ── Company: thêm đánh giá ────────────────────────────────────────
    public function add(): void
    {
        requireRole('company');

        $uid = $_SESSION['user_id'];
        $cq  = $this->conn->prepare("SELECT company_id FROM company_profiles WHERE user_id=?");
        $cid = 0;
        if ($cq) { $cq->bind_param('i', $uid); $cq->execute(); $cid = $cq->get_result()->fetch_assoc()['company_id'] ?? 0; }

        $pending = $this->evalModel->getPendingByCompany($cid);
        $errors  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reg_id  = (int)($_POST['registration_id'] ?? 0);
            $tech    = max(1, min(10, (int)($_POST['technical_skill'] ?? 5)));
            $team    = max(1, min(10, (int)($_POST['teamwork'] ?? 5)));
            $comm    = max(1, min(10, (int)($_POST['communication'] ?? 5)));
            $att     = max(1, min(10, (int)($_POST['attitude'] ?? 5)));
            $comment = sanitize($_POST['comment'] ?? '');

            if (!$reg_id) $errors[] = 'Vui lòng chọn sinh viên.';

            if (empty($errors)) {
                if ($this->evalModel->create($reg_id, $tech, $team, $comm, $att, $comment)) {
                    setFlash('success', '✅ Đã lưu đánh giá sinh viên!');
                    redirect('list.php');
                } else {
                    $errors[] = 'Lỗi lưu: ' . $this->evalModel->lastError();
                }
            }
        }

        $this->render(BASE_PATH_FS . 'app/Views/evaluations/add.php', [
            'pending' => $pending,
            'errors'  => $errors,
        ]);
    }
}
