<?php
// add_personnel.php
session_start();
require __DIR__ . '/../../db_connect.php'; 

$staff_team_name = 'ทีมงาน (Staff)';
$staff_team_id = null;

try {
    // 1. ตรวจสอบว่ามีทีมสำหรับ "ทีมงาน" อยู่แล้วหรือไม่
    $stmt = $conn->prepare("SELECT team_id FROM teams WHERE team_name = ?");
    $stmt->execute([$staff_team_name]);
    $staff_team_id = $stmt->fetchColumn();

    // 2. ถ้ายังไม่มี ให้สร้างขึ้นมาใหม่
    if (!$staff_team_id) {
        $insert_team_sql = "INSERT INTO teams (team_name, coach_name, coach_phone, leader_school, created_at, status, is_approved, approved_by) 
                            VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)";
        $stmt_insert_team = $conn->prepare($insert_team_sql);
        $stmt_insert_team->execute([
            $staff_team_name,
            '-', // coach_name
            '0000000000', // coach_phone
            '-', // leader_school
            'confirmed', // status
            1, // is_approved
            'system' // approved_by
        ]);
        $staff_team_id = $conn->lastInsertId();
    }

    // 3. ตรวจสอบว่ามีการส่งฟอร์มมาหรือไม่
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $personnel_name = isset($_POST['personnel_name']) ? trim($_POST['personnel_name']) : '';

        if (!empty($personnel_name) && $staff_team_id) {
            // ตรวจสอบว่ามีชื่อนี้ในทีมงานอยู่แล้วหรือไม่
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM team_members WHERE member_name = ? AND team_id = ?");
            $checkStmt->execute([$personnel_name, $staff_team_id]);
            if ($checkStmt->fetchColumn() > 0) {
                 $_SESSION['feedback'] = ['type' => 'warning', 'message' => 'มีรายชื่อ "' . htmlspecialchars($personnel_name) . '" อยู่ในระบบแล้ว'];
            } else {
                // เพิ่มรายชื่อใหม่เข้าไปในตาราง team_members โดยอ้างอิงถึงทีม "ทีมงาน"
                $insert_member_sql = "INSERT INTO team_members (team_id, member_name, position) VALUES (?, ?, ?)";
                $stmt_insert_member = $conn->prepare($insert_member_sql);
                if ($stmt_insert_member->execute([$staff_team_id, $personnel_name, 'Staff/Committee'])) {
                    $_SESSION['feedback'] = ['type' => 'success', 'message' => 'เพิ่มรายชื่อ "' . htmlspecialchars($personnel_name) . '" สำเร็จ!'];
                } else {
                    $_SESSION['feedback'] = ['type' => 'danger', 'message' => 'ไม่สามารถเพิ่มรายชื่อได้'];
                }
            }
        } else {
            $_SESSION['feedback'] = ['type' => 'warning', 'message' => 'กรุณากรอกชื่อ-นามสกุล'];
        }
        // Redirect เพื่อป้องกันการส่งฟอร์มซ้ำ
        header('Location: add_personnel.php');
        exit();
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มรายชื่อบุคลากร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <div class="card">
        <div class="card-header bg-white border-0 py-4">
            <h2 class="mb-0 text-center">เพิ่มรายชื่อบุคลากร</h2>
            <p class="text-center text-muted">สำหรับกรรมการ, ทีมงาน, หรือบุคคลอื่นที่ต้องการเกียรติบัตร</p>
        </div>
        <div class="card-body">
            <?php
            // แสดงข้อความ feedback
            if (isset($_SESSION['feedback'])) {
                echo '<div class="alert alert-' . $_SESSION['feedback']['type'] . '">' . $_SESSION['feedback']['message'] . '</div>';
                unset($_SESSION['feedback']);
            }
            ?>
            <form method="POST" action="add_personnel.php">
                <div class="mb-3">
                    <label for="personnel_name" class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="personnel_name" id="personnel_name" class="form-control" placeholder="กรอกชื่อ-นามสกุลเต็ม" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">เพิ่มรายชื่อ</button>
                    <a href="../organizer_dashboard.php" class="btn btn-secondary">กลับหน้าหลัก</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>