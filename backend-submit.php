<?php
require('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $detail = trim($_POST['detail']);
    // $date = $_POST['date']; // คงการ comment ไว้
    $model = $_POST['model'] ?? 'รุ่นระดับมัธยมศึกษาหรืออาชีวศึกษา';

    if (!empty($name) && !empty($detail) && !empty($model)) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE name = ?");
        $checkStmt->execute([$name]);
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            echo "<script>
                    alert('❌ ชื่อนี้ถูกลงทะเบียนไปแล้ว กรุณาใช้ชื่ออื่น');
                    window.location.href = 'backend-index.php';
                  </script>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO participants (name, detail, model) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $detail, $model])) {
                echo "<script>
                        alert('✅ ลงทะเบียนสำเร็จ');
                        window.location.href = 'backend-index.php';
                      </script>";
            } else {
                echo "<script>alert('❌ ไม่สามารถบันทึกข้อมูลได้');</script>";
            }
        }
    } else {
        echo "<script>alert('❌ กรุณากรอกข้อมูลให้ครบถ้วน');</script>";
    }
}
?>