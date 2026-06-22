<?php
/**
 * BaseModel — lớp cha cho tất cả Model
 * Giữ $conn (mysqli) được inject từ bên ngoài.
 * Không thay đổi cách kết nối DB hiện tại.
 */
class BaseModel
{
    protected $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
}
