<?php
// backend-index.php
session_start();
require __DIR__ . '/../../db_connect.php';

// ดึงรายชื่อสมาชิกทั้งหมดเพื่อมาสร้าง Dropdown
$members = $conn->query("SELECT member_id, member_name FROM team_members ORDER BY member_name ASC")->fetchAll(PDO::FETCH_ASSOC);

// กำหนดประเภทรางวัล
$award_models = [
    'รางวัลชนะเลิศ', 'รางวัลรองชนะเลิศ อันดับที่ 1', 'รางวัลรองชนะเลิศ อันดับที่ 2',
    'รางวัลรองชนะเลิศ อันดับที่ 3', 'ผู้เข้าร่วมการแข่งขัน', 'ผู้ควบคุมทีม',
    'กรรมการจัดการแข่งขัน', 'ผู้เข้าร่วมจัดการแข่งขัน'
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <title>เพิ่มเกียรติบัตร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>เพิ่มเกียรติบัตรใหม่</h2>

    <?php
    // แสดงข้อความ feedback ถ้ามี
    if (isset($_SESSION['feedback'])) {
        echo '<div class="alert alert-' . $_SESSION['feedback']['type'] . '">' . $_SESSION['feedback']['message'] . '</div>';
        // ลบข้อความ feedback ออกไปเพื่อไม่ให้แสดงซ้ำเมื่อรีเฟรช
        unset($_SESSION['feedback']);
    }
    ?>

    <div class="card shadow p-4">
        <form action="backend-submit.php" method="POST">
            <div class="mb-3">
                <label for="member_id" class="form-label">เลือกสมาชิก</label>
                <select name="member_id" id="member_id" class="form-select" required>
                    <option value="">-- กรุณาเลือกสมาชิก --</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member['member_id'] ?>">
                            <?= htmlspecialchars($member['member_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="model" class="form-label">ประเภทรางวัล</label>
                <select name="model" id="model" class="form-select" required>
                     <option value="">-- กรุณาเลือกรางวัล --</option>
                    <?php foreach ($award_models as $model): ?>
                        <option value="<?= $model ?>"><?= $model ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="detail" class="form-label">รายละเอียด (ถ้ามี)</label>
                <input type="text" name="detail" id="detail" class="form-control" placeholder="เช่น สำหรับผลงานอันโดดเด่น">
            </div>

            <div class="mb-3">
                <label for="date" class="form-label">วันที่ (ถ้ามี)</label>
                <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>

            <button type="submit" class="btn btn-primary w-100">สร้างเกียรติบัตร</button>
        </form>
    </div>
</div>
</body>
</html>