<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['target'])) {
    $file = $_FILES['image'];
    $target = $_POST['target'];
    // ปรับเป็นชื่อไฟล์ที่ต้องการแทนที่
    $allowedTargets = ['certificate_template', 'certificate_template1'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] !== 0) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลด']);
        exit;
    }

    if ($ext !== 'jpg') {
        echo json_encode(['success' => false, 'message' => 'รองรับเฉพาะไฟล์ .jpg เท่านั้น']);
        exit;
    }

    if (!in_array($target, $allowedTargets)) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบประเภทภาพที่เลือก']);
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'ขนาดไฟล์เกิน 2MB']);
        exit;
    }

    // ตรวจสอบว่าโฟลเดอร์ assets มีอยู่หรือไม่
    if (!is_dir('assets')) {
        mkdir('assets', 0755, true);
    }

    $targetPath = "assets/{$target}.jpg";
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['success' => true, 'message' => "อัปโหลดและแทนที่ $target.jpg สำเร็จ!"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกไฟล์ได้']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'คำขอไม่ถูกต้อง']);
}