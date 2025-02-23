<?php
require('database.php');

// กำหนดค่าเริ่มต้น
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;
$totalPages = 1;
$participants = [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
        // นับจำนวนรายการทั้งหมดก่อน
        if ($search) {
            $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE name LIKE :search");
            $totalStmt->execute([':search' => "%$search%"]);
        } else {
            $totalStmt = $pdo->query("SELECT COUNT(*) FROM participants");
        }
        $totalRows = $totalStmt->fetchColumn();
        $totalPages = ceil($totalRows / $limit);

        // ดึงข้อมูลตามหน้าที่ต้องการ
        if ($search) {
            $stmt = $pdo->prepare("SELECT * FROM participants WHERE name LIKE :search ORDER BY id DESC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM participants ORDER BY id DESC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($page > $totalPages) {
            header("Location: ?page=" . $totalPages . "&search=" . urlencode($search));
            exit();
        }

        } catch (PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $participants = [];
        $totalPages = 1;
        }
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
                                    <td><?= htmlspecialchars($row['datenew']) ?></td>
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
                <?php if ($page > 1): ?>
                    <li><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">⬅️</a></li>
                <?php endif; ?>

                <?php if ($page > 3): ?>
                    <li><span>...</span></li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++): ?>
                    <li <?= ($page == $i) ? 'class="active"' : '' ?>>
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages - 2): ?>
                    <li><span>...</span></li>
                <?php endif; ?>

                <?php if ($page < $totalPages): ?>
                    <li><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">➡️</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="search-container">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="🔍 ค้นหาชื่อ..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">ค้นหา</button>
            <a href="backend-list.php" class="back-button">กลับ</a>
        </form>
    </div>

        <div class="text-center">
            <a href="backend-index.php" class="btn back-button">กลับไปลงทะเบียน</a>
        </div>
    </div>
</body>
</html>
