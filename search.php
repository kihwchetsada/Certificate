<?php
require 'database.php';

// รับคำค้นหาจาก AJAX request
$query = isset($_GET['query']) ? $_GET['query'] : '';
$result = [];

if (!empty($query)) {
    try {
        // ค้นหาชื่อที่ตรงกับคำค้นหา
        $stmt = $pdo->prepare("SELECT id, name FROM certificate WHERE name LIKE :query ORDER BY 
            CASE 
                WHEN name LIKE :exact THEN 1
                WHEN name LIKE :start THEN 2
                ELSE 3 
            END, 
            name ASC 
            LIMIT 10");
        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(':exact', "$query", PDO::PARAM_STR);    // ตรงกันทั้งหมด
        $stmt->bindValue(':start', "$query%", PDO::PARAM_STR);   // ขึ้นต้นด้วย
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // บันทึกข้อผิดพลาดแต่ส่งค่าว่างกลับไป
        error_log("Search error: " . $e->getMessage());
        $result = [];
    }
}

// เพิ่ม delay เล็กน้อย (0.2 วินาที) เพื่อให้เห็น loading indicator
// ในการใช้งานจริงอาจตัดส่วนนี้ออก
usleep(200000);

// ส่งผลลัพธ์กลับในรูปแบบ JSON
header('Content-Type: application/json');
echo json_encode($result);
?>