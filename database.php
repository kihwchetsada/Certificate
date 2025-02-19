<?php
$host = "localhost";
$dbname = "certificate_db";
$username = "root"; // ชื่อผู้ใช้ของ MySQL (เช่น root)
$password = ""; // รหัสผ่าน (ถ้าใช้ XAMPP ค่ารหัสผ่านจะเป็นค่าว่าง)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage());
}
?>
