<?php
// check_certificate.php

// เชื่อมต่อฐานข้อมูล
require('database.php');

// ตรวจสอบการส่งข้อมูล POST
if (isset($_POST['name'])) {
    $name = trim($_POST['name']); // ตัดช่องว่าง

    // ตรวจสอบว่าชื่อไม่ว่าง
    if (!empty($name)) {
        try {
            // ค้นหาข้อมูลในฐานข้อมูล
            $query = "SELECT * FROM certificate WHERE name = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$name]);
            $result = $stmt->fetch();

            // ตรวจสอบผลการค้นหา
            if ($result) {
                echo "found"; // พบข้อมูล
            } else {
                echo "not found"; // ไม่พบข้อมูล
            }
        } catch (PDOException $e) {
            // หากเกิดข้อผิดพลาดในการเชื่อมต่อหรือการ query ฐานข้อมูล
            echo "error: " . $e->getMessage();
        }
    } else {
        echo "empty name"; // กรณีชื่อว่าง
    }
} else {
    echo "no name provided"; // กรณีไม่มีการส่งข้อมูล
}
?>
