<?php
// backend-submit.php

// เริ่ม session เพื่อใช้ flash message
session_start();
require __DIR__ . '/../../db_connect.php'; 

// ตรวจสอบว่าฟอร์มถูกส่งมาแบบ POST หรือไม่
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Invalid request method.'];
    header('Location: backend-index.php');
    exit();
}

// รับและตรวจสอบข้อมูลที่ส่งมา
$member_id = filter_input(INPUT_POST, 'member_id', FILTER_VALIDATE_INT);
$model = trim($_POST['model'] ?? '');
$detail = trim($_POST['detail'] ?? 'N/A');
// ใช้ format Y-m-d ที่ถูกต้องสำหรับ MySQL
$date = $_POST['date'] ?? date('Y-m-d'); 

if (empty($member_id) || empty($model)) {
    $_SESSION['feedback'] = ['type' => 'warning', 'message' => 'กรุณาเลือกสมาชิกและประเภทรางวัล'];
    header('Location: backend-index.php');
    exit();
}

try {
    // ตรวจสอบว่ามีเกียรติบัตรสำหรับสมาชิกนี้อยู่แล้วหรือไม่
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM certificate WHERE member_id = ?");
    $checkStmt->execute([$member_id]);
    $exists = $checkStmt->fetchColumn();

    if ($exists) {
        $_SESSION['feedback'] = ['type' => 'warning', 'message' => 'มีเกียรติบัตรสำหรับสมาชิกท่านนี้อยู่แล้ว'];
    } else {
        // ถ้ายังไม่มี, เพิ่มข้อมูลเกียรติบัตรใหม่
        // ใช้ backtick (`) คร่อม `date` เพราะเป็นคำสงวนของ SQL
        $stmt = $conn->prepare("INSERT INTO certificate (member_id, detail, `date`, model) VALUES (?, ?, ?, ?)");

        if ($stmt->execute([$member_id, $detail, $date, $model])) {
            $_SESSION['feedback'] = ['type' => 'success', 'message' => 'สร้างเกียรติบัตรสำเร็จ!'];
        } else {
            $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'ไม่สามารถบันทึกข้อมูลเกียรติบัตรได้'];
        }
    }
} catch (PDOException $e) {
    $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'Database error: ' . $e->getMessage()];
}

// Redirect กลับไปที่หน้าฟอร์ม
header('Location: backend-index.php');
exit();
?>