<?php
/**
 * BaseController
 * Lớp cha cho tất cả Controller.
 * Inject $conn, cung cấp helper render view.
 */
class BaseController
{
    protected $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Render một View file, truyền biến vào scope của View.
     *
     * @param string $viewPath  Đường dẫn tuyệt đối tới file view
     * @param array  $data      Mảng biến sẽ được extract vào View
     */
    protected function render(string $viewPath, array $data = []): void
    {
        // Đưa $conn vào scope để header.php/footer.php dùng (getUnreadCount, v.v.)
        $conn = $this->conn;
        extract($data, EXTR_SKIP);
        require $viewPath;
    }
}
