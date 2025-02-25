<?php
require 'database.php'; // เชื่อมต่อฐานข้อมูล

// รับค่าการค้นหา
$search = isset($_GET['search']) ? $_GET['search'] : '';

// กำหนดจำนวนรายการต่อหน้า
$items_per_page = 10;

// รับค่าหน้าปัจจุบันจาก URL หรือใช้หน้าที่ 1 เป็นค่าเริ่มต้น
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

// คำนวณ offset สำหรับการดึงข้อมูลจากฐานข้อมูล
$offset = ($current_page - 1) * $items_per_page;

// นับจำนวนรายการทั้งหมดเพื่อคำนวณหน้าทั้งหมด
$count_sql = "SELECT COUNT(*) as total FROM participants";
if ($search) {
    $count_sql .= " WHERE name LIKE :search";
}
$count_stmt = $pdo->prepare($count_sql);
if ($search) {
    $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$count_stmt->execute();
$total_items = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_items / $items_per_page);

// ดึงข้อมูลตามหน้าที่ต้องการ
$sql = "SELECT id, name FROM participants";
if ($search) {
    $sql .= " WHERE name LIKE :search";
}
// เพิ่ม LIMIT และ OFFSET สำหรับการแบ่งหน้า
$sql .= " LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อทั้งหมด</title>
    <link rel="icon" type="image/png" href="img/b.png">
    <link rel="stylesheet" href="css/list.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h2>รายชื่อผู้เข้าร่วมทั้งหมด</h2>
        </header>
        
        <div class="search-container">
            <form method="GET" class="search-form">
                <input type="text" name="search" placeholder="ค้นหาชื่อ..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" >ค้นหา</button>
            </form>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อ-นามสกุล</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="2" class="empty-result">ไม่พบข้อมูลที่ค้นหา</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // คำนวณลำดับเริ่มต้นของหน้าปัจจุบัน
                        $i = $offset + 1; 
                        ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <!-- ข้อมูลจำนวนหน้า -->
            <div class="page-info">
                แสดงผล <?= $offset + 1 ?>-<?= min($offset + $items_per_page, $total_items) ?> จากทั้งหมด <?= $total_items ?> รายการ
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php 
                // ปุ่มก่อนหน้า
                if ($current_page > 1): 
                    $prev_url = '?page=' . ($current_page - 1);
                    if ($search) $prev_url .= '&search=' . urlencode($search);
                ?>
                    <a href="<?= $prev_url ?>">&laquo; ก่อนหน้า</a>
                <?php else: ?>
                    <a href="#" class="disabled">&laquo; ก่อนหน้า</a>
                <?php endif; ?>
                
                <?php
                // กำหนดจำนวนหน้าที่จะแสดงในแถบนำทาง
                $visible_pages = 5;
                $start_page = max(1, $current_page - floor($visible_pages / 2));
                $end_page = min($total_pages, $start_page + $visible_pages - 1);
                
                // ปรับค่า start_page ถ้า end_page ถึงหน้าสุดท้ายแล้ว
                $start_page = max(1, $end_page - $visible_pages + 1);
                
                // แสดงปุ่มหน้าแรกถ้าจำเป็น
                if ($start_page > 1): 
                    $first_url = '?page=1';
                    if ($search) $first_url .= '&search=' . urlencode($search);
                ?>
                    <a href="<?= $first_url ?>">1</a>
                    <?php if ($start_page > 2): ?>
                        <a href="#" class="disabled">...</a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php
                // แสดงหมายเลขหน้า
                for ($i = $start_page; $i <= $end_page; $i++):
                    $page_url = '?page=' . $i;
                    if ($search) $page_url .= '&search=' . urlencode($search);
                    $active_class = ($i == $current_page) ? 'active' : '';
                ?>
                    <a href="<?= $page_url ?>" class="<?= $active_class ?>"><?= $i ?></a>
                <?php endfor; ?>
                
                <?php
                // แสดงปุ่มหน้าสุดท้ายถ้าจำเป็น
                if ($end_page < $total_pages): 
                    $last_url = '?page=' . $total_pages;
                    if ($search) $last_url .= '&search=' . urlencode($search);
                ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <a href="#" class="disabled">...</a>
                    <?php endif; ?>
                    <a href="<?= $last_url ?>"><?= $total_pages ?></a>
                <?php endif; ?>
                
                <?php
                // ปุ่มถัดไป
                if ($current_page < $total_pages): 
                    $next_url = '?page=' . ($current_page + 1);
                    if ($search) $next_url .= '&search=' . urlencode($search);
                ?>
                    <a href="<?= $next_url ?>">ถัดไป &raquo;</a>
                <?php else: ?>
                    <a href="#" class="disabled">ถัดไป &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="d-flex flex-column align-items-center">
        <a href="index.php" class="btn btn-primary w-50 mt-3" style="width: 200px;">
            <i class="fas fa-list me-2"></i>กลับหน้าหลัก
        </a>
    </div>
</body>
</html>