<?php
// search-api.php
header('Content-Type: application/json');
require __DIR__ . '/../../db_connect.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$suggestions = [];

if (strlen($query) > 1) {
    try {
        // ค้นหาชื่อสมาชิกที่ตรงกัน
        $sql = "SELECT DISTINCT member_name AS suggestion 
                FROM team_members 
                WHERE member_name LIKE ? 
                LIMIT 5";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(["%$query%"]);
        $member_suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ค้นหาชื่อทีมที่ตรงกัน
        $sql_team = "SELECT DISTINCT team_name AS suggestion 
                     FROM teams 
                     WHERE team_name LIKE ? 
                     LIMIT 5";

        $stmt_team = $conn->prepare($sql_team);
        $stmt_team->execute(["%$query%"]);
        $team_suggestions = $stmt_team->fetchAll(PDO::FETCH_ASSOC);

        // รวมผลลัพธ์และกำจัดรายการที่ซ้ำกัน
        $combined = array_merge($member_suggestions, $team_suggestions);
        $suggestions = array_values(array_unique($combined, SORT_REGULAR));

    } catch (PDOException $e) {
        // ในกรณีที่เกิดข้อผิดพลาด, ส่ง array ว่างกลับไป
        $suggestions = [];
    }
}

echo json_encode($suggestions);
?>