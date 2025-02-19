<?php
require('database.php');

// กำหนดค่าเริ่มต้น
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;
$totalPages = 1;
$participants = [];

try {
    // นับจำนวนรายการทั้งหมดก่อน
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM participants");
    $totalRows = $totalStmt->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // ดึงข้อมูลตามหน้าที่ต้องการ
    $stmt = $pdo->prepare("SELECT * FROM participants ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($page > $totalPages) {
        header("Location: ?page=" . $totalPages);
        exit();
    }

} catch (PDOException $e) {
    $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    $participants = [];
    $totalPages = 1;
}

$hasData = !empty($participants);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อผู้ลงทะเบียน</title>
    <link rel="icon" type="image/png" href="img/b.png">
    <link rel="stylesheet" href="css/backend-list.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body>
    <div class="container">
        <h2 class="page-title animate__animated animate__fadeInDown">📋 รายชื่อผู้ลงทะเบียน</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger animate__animated animate__fadeIn">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($participants)): ?>
            <div class="alert alert-info animate__animated animate__fadeIn">
                ไม่พบข้อมูลผู้ลงทะเบียน
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th> ชื่อ-นามสกุล</th>
                                <th> รายละเอียด</th>
                                <th> วันที่</th>
                                <th> รุ่นเกียรติบัตร</th> 
                                <th> จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $index => $row): ?>
                                <tr>
                                    <td><?= ($offset + $index + 1) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['detail']) ?></td>
                                    <td><?= htmlspecialchars($row['date']) ?></td>
                                    <td><?= htmlspecialchars($row['model']) ?></td> 
                                    <td>
                                        <a href="backend-edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ แก้ไข</a>
                                        <a href="backend-delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('❗ ยืนยันการลบ?');">🗑️ ลบ</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">⬅️ ก่อนหน้า</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">ถัดไป ➡️</a>
                </li>
            </ul>
        </nav>

        <div class="text-center">
            <a href="backend-index.php" class="btn back-button">กลับไปลงทะเบียน</a>
        </div>
    </div>
</body>
</html>
