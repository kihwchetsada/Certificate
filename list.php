<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

try {
    // นับจำนวนรายการทั้งหมดเพื่อคำนวณหน้าทั้งหมด
    $count_sql = "SELECT COUNT(*) as total FROM certificate";
    if ($search) {
        $count_sql .= " WHERE name LIKE :search";
    }
    $count_stmt = $conn->prepare($count_sql);
    if ($search) {
        $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_items = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_items / $items_per_page);

    // ดึงข้อมูลตามหน้าที่ต้องการ
    $sql = "SELECT id, name FROM certificate";
    if ($search) {
        $sql .= " WHERE name LIKE :search";
    }
    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // บันทึกข้อผิดพลาดแต่ไม่เปิดเผยรายละเอียดให้ผู้ใช้
    error_log("Database error: " . $e->getMessage());
    $error_message = "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล กรุณาลองใหม่ภายหลัง";
    $users = [];
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อผู้เข้าร่วม</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f5f7fb;
            color: #333;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .card-header {
            background-color: white;
            border-bottom: none;
            padding: 25px 30px 0;
        }
        
        .card-body {
            padding: 25px 30px;
        }
        
        .app-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .search-container {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 20px;
            border: 1px solid #e6e6e6;
            box-shadow: none;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.15);
        }
        
        .btn-search {
            border-radius: 10px;
            padding: 12px 25px;
            background-color: var(--primary-color);
            border: none;
            color: white;
            transition: all 0.3s;
        }
        
        .btn-search:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-back {
            border-radius: 10px;
            padding: 12px 25px;
            background-color: #6c757d;
            border: none;
            color: white;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .suggestions {
            position: absolute;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            margin-top: 5px;
        }
        
        .suggestion-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        .suggestion-item:hover {
            background-color: #f8f9fa;
            padding-left: 25px;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            border-top: none;
            border-bottom: 2px solid var(--accent-color);
            color: var(--primary-color);
            font-weight: 500;
            padding: 15px;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-top: 1px solid #e6e6e6;
        }
        
        .pagination {
            margin-top: 30px;
            justify-content: center;
        }
        
        .page-link {
            border: none;
            margin: 0 5px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .page-link:hover {
            background-color: var(--accent-color);
            color: white;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
        }
        
        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: transparent;
        }
        
        .badge-count {
            background-color: var(--accent-color);
            color: white;
            border-radius: 20px;
            padding: 5px 15px;
            font-weight: 400;
            font-size: 0.85rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 0;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        
        .empty-state h5 {
            color: #6c757d;
            font-weight: 500;
        }
        
        .empty-state p {
            color: #adb5bd;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .action-buttons {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .card-header, .card-body {
                padding: 20px;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .btn-search {
                width: 100%;
            }
            
            .btn-back {
                width: 100%;
                justify-content: center;
            }
        }
        
        /* Skeleton loader animation */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
        
        .skeleton {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #f0f0f0 8%, #e0e0e0 18%, #f0f0f0 33%);
            background-size: 1000px 100%;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card">
            <div class="card-header">
                <h2 class="app-title">รายชื่อผู้เข้าร่วม</h2>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="text-muted">แสดงผลข้อมูล <span class="badge-count"><?= $total_items ?> รายการ</span></span>
                    <a href="index.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> กลับสู่หน้าหลัก
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div><?= $error_message ?></div>
                    </div>
                <?php endif; ?>
                
                <div class="search-container">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                        <button type="submit" class="btn btn-search">
                            <i class="fas fa-search me-2"></i> ค้นหา
                        </button>
                    </form>
                    <div class="suggestions"></div>
                </div>
                
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h5>ไม่พบข้อมูลที่ค้นหา</h5>
                        <p>ลองค้นหาด้วยคำค้นอื่น หรือลบคำค้นหาเพื่อดูรายการทั้งหมด</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="80">#</th>
                                    <th>ชื่อ-นามสกุล</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = $offset + 1; ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><span class="fw-bold"><?= $i++ ?></span></td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $current_page - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>" aria-label="Previous">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                // แสดงเลขหน้าแบบมีจำกัด
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1' . ($search ? '&search='.urlencode($search) : '') . '">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor;
                                
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . ($search ? '&search='.urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $current_page + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>" aria-label="Next">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="index.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> กลับสู่หน้าหลัก
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // ตัวแปรสำหรับเก็บ timeout
        let searchTimeout;
        let isLoading = false;
        
        // เมื่อพิมพ์ในช่องค้นหา
        $("input[name='search']").on("keyup", function() {
            clearTimeout(searchTimeout);
            let query = $(this).val();
            
            // ยกเลิกการแสดงผลแนะนำถ้าไม่มีการพิมพ์หรือลบข้อความ
            if (query.length <= 1) {
                $(".suggestions").hide();
                return;
            }
            
            // หากกำลังโหลดข้อมูลอยู่ ให้ยกเลิก
            if (isLoading) return;
            
            // รอสักครู่ก่อนส่งคำขอ AJAX
            searchTimeout = setTimeout(function() {
                isLoading = true;
                
                // แสดง loading indicator
                let $suggestions = $(".suggestions");
                $suggestions.html('<div class="suggestion-item skeleton" style="height: 20px; margin: 10px;"></div>'.repeat(3));
                $suggestions.show();
                
                // ส่งคำขอ AJAX
                $.ajax({
                    url: "search.php",
                    method: "GET",
                    data: { query: query },
                    dataType: "json",
                    success: function(data) {
                        isLoading = false;
                        $suggestions.empty();
                        
                        if (data.length > 0) {
                            // แสดงผลลัพธ์การค้นหา
                            data.forEach(function(item) {
                                let highlightedName = item.name.replace(
                                    new RegExp(query, 'gi'),
                                    match => `<mark>${match}</mark>`
                                );
                                $suggestions.append(`<div class='suggestion-item' data-name='${item.name}'>${highlightedName}</div>`);
                            });
                            $suggestions.show();
                        } else {
                            // แสดงข้อความเมื่อไม่พบ
                            $suggestions.append(`<div class='suggestion-item'>ไม่พบรายชื่อที่ตรงกับ "${query}"</div>`);
                            $suggestions.show();
                        }
                    },
                    error: function() {
                        isLoading = false;
                        $(".suggestions").hide();
                    }
                });
            }, 300);
        });

        // เมื่อคลิกที่รายการแนะนำ
        $(document).on("click", ".suggestion-item", function() {
            $("input[name='search']").val($(this).attr("data-name"));
            $(".suggestions").hide();
            $(".search-form").submit(); // ส่งฟอร์มทันที
        });

        // ซ่อนรายการแนะนำเมื่อคลิกนอกพื้นที่
        $(document).on("click", function(e) {
            if (!$(e.target).closest(".search-container").length) {
                $(".suggestions").hide();
            }
        });
        
        // เพิ่ม animation เมื่อโหลดหน้า
        $(".card").css({
            "opacity": 0,
            "transform": "translateY(20px)"
        }).animate({
            "opacity": 1,
            "transform": "translateY(0px)"
        }, 400);
    });
    </script>
</body>
</html>