<?php
require('database.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
    $stmt->execute([$id]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$participant) {
        die("❌ ไม่พบข้อมูล");
    }
} else {
    die("❌ ไม่พบ ID");
}

$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $detail = $_POST['detail'];
    $date = $_POST['date'];
    $model = $_POST['model']; 

    $stmt = $pdo->prepare("UPDATE participants SET name=?, detail=?, date=?, model=? WHERE id=?");
    if ($stmt->execute([$name, $detail, $date, $model, $id])) {
        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>แก้ไขข้อมูล</title>
    <link rel="icon" type="image/png" href="img/b.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%; border-radius: 15px;">
            <h2 class="text-center text-primary mb-4">แก้ไขข้อมูล</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" class="form-control" placeholder="กรอกชื่อ-นามสกุล" value="<?= htmlspecialchars($participant['name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รายละเอียด</label>
                    <input type="text" name="detail" class="form-control" placeholder="กรอกรายละเอียด" value="<?= htmlspecialchars($participant['detail']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">วันที่</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($participant['date']) ?>" required>
                </div>

                <div class="mb-3">
                <label class="form-label">รุ่นเกียรติบัตร</label>
    <select name="model" class="form-control" required>
        <option value="รุ่นระดับมัธยมศึกษาหรืออาชีวศึกษา" <?= $participant['model'] == 'รุ่นระดับมัธยมศึกษาหรืออาชีวศึกษา' ? 'selected' : '' ?>>
            แบบที่ 1 ( รุ่นระดับมัธยมศึกษาหรืออาชีวศึกษา )
        </option>
        <option value="รุ่นระดับอุดมศึกษาหรือบุคคลทั่วไป" <?= $participant['model'] == 'รุ่นระดับอุดมศึกษาหรือบุคคลทั่วไป' ? 'selected' : '' ?>>
            แบบที่ 2 (รุ่นระดับอุดมศึกษาหรือบุคคลทั่วไป)
        </option>
    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                    <a href="backend-list.php" class="btn btn-secondary">กลับ</a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($success): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '✅ บันทึกเสร็จสิ้น',
                text: 'ข้อมูลของคุณถูกอัปเดตเรียบร้อย',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'backend-list.php';
            });
        </script>
    <?php endif; ?>
</body>
</html>
