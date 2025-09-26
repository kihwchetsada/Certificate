<?php
// check_certificate.php
header('Content-Type: text/plain');
require __DIR__ . '/../../db_connect.php'; // ตรวจสอบ Path ให้ถูกต้อง

$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (empty($name)) {
    echo "empty name";
    exit();
}

try {
    // ค้นหา member_id จากชื่อที่ส่งมา
    $sql = "SELECT member_id FROM team_members WHERE member_name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // ถ้าเจอ ให้ส่ง member_id กลับไป
        echo $result['member_id'];
    } else {
        // ถ้าไม่เจอ
        echo "not found";
    }

} catch (PDOException $e) {
    // หากเกิดข้อผิดพลาด
    // error_log($e->getMessage()); // ควรบันทึก log ไว้ดู
    echo "error";
}
?>