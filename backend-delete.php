<?php
require('database.php');

$alertType = "";
$alertTitle = "";
$alertText = "";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM certificate WHERE id = ?");
        $stmt->execute([$id]);

        $alertType = "success";
        $alertTitle = "🗑️ ลบข้อมูลสำเร็จ!";
        $alertText = "รายการถูกลบเรียบร้อยแล้ว";
    } catch (PDOException $e) {
        $alertType = "error";
        $alertTitle = "❌ ลบข้อมูลไม่สำเร็จ!";
        $alertText = "เกิดข้อผิดพลาด ไม่สามารถลบข้อมูลได้";
    }
} else {
    $alertType = "warning";
    $alertTitle = "❌ ไม่พบ ID ที่ต้องการลบ";
    $alertText = "กรุณาลองใหม่อีกครั้ง";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ลบข้อมูล</title>
    <link rel="icon" type="image/png" href="img/b.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: '<?= $alertType ?>',
            title: '<?= $alertTitle ?>',
            text: '<?= $alertText ?>',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'ตกลง'
        }).then(() => {
            window.location.href = 'backend-list.php';
        });
    </script>
</body>
</html>
