<?php
// list.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../db_connect.php';

// รับค่าจาก URL สำหรับการค้นหา, การเลือกทัวร์นาเมนต์, และหน้าปัจจุบัน
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tournament_id = isset($_GET['tournament_id']) && !empty($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$items_per_page = 10;
$offset = ($current_page - 1) * $items_per_page;

try {
    // ดึงรายชื่อทัวร์นาเมนต์ทั้งหมดมาสร้าง Dropdown
    $tournaments = $conn->query("SELECT id, tournament_name FROM tournaments ORDER BY tournament_name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // --- สร้างคำสั่ง SQL และ Parameters ---
    $base_sql = "FROM team_members AS tm
                 JOIN teams AS t ON tm.team_id = t.team_id
                 JOIN tournaments AS tn ON t.tournament_id = tn.id";

    $where_clauses = [];
    $params = [];

    if ($tournament_id) {
        $where_clauses[] = "tn.id = ?";
        $params[] = $tournament_id;
    }

    if ($search) {
        $where_clauses[] = "(tm.member_name LIKE ? OR t.team_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $where_sql = "";
    if (!empty($where_clauses)) {
        $where_sql = " WHERE " . implode(" AND ", $where_clauses);
    }

    // --- ส่วนนับจำนวน ---
    $count_sql = "SELECT COUNT(tm.member_id) as total " . $base_sql . $where_sql;
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_items / $items_per_page);

    // --- ส่วนดึงข้อมูล ---
    $sql = "SELECT tm.member_name, tm.member_id, t.team_name, tn.tournament_name " . $base_sql . $where_sql . " ORDER BY tn.tournament_name, t.team_name, tm.member_name ASC LIMIT ? OFFSET ?";
    
    $params[] = $items_per_page;
    $params[] = $offset;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .suggestions-container { position: relative; }
        .suggestions-box {
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            width: 100%;
            max-height: 250px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .suggestion-item { padding: 10px 15px; cursor: pointer; }
        .suggestion-item:hover { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">รายชื่อสมาชิก</h2>
                <a href="../../annunciate.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    กลับหน้าหลัก
                </a>
            </div>
            <div class="card-body">
                
                <form method="GET" action="list.php" class="mb-4" id="filter-form">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <select name="tournament_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- เลือกทัวร์นาเมนต์ทั้งหมด --</option>
                                <?php foreach ($tournaments as $tournament): ?>
                                    <option value="<?= $tournament['id'] ?>" <?= ($tournament['id'] == $tournament_id) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tournament['tournament_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5 suggestions-container">
                            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อสมาชิก หรือ ชื่อทีม..." value="<?= htmlspecialchars($search) ?>" autocomplete="off">
                            <div class="suggestions-box"></div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>
                    </div>
                </form>

                <p>พบข้อมูลทั้งหมด <?= $total_items ?> รายการ</p>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>ชื่อทีม</th>
                                <th>ทัวร์นาเมนต์</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                                </tr>
                            <?php else: ?>
                                <?php $i = $offset + 1; ?>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($member['member_name']) ?></td>
                                        <td><?= htmlspecialchars($member['team_name']) ?></td>
                                        <td><?= htmlspecialchars($member['tournament_name']) ?></td>
                                        <td class="text-center">
                                            <a href="generate.php?member_id=<?= $member['member_id'] ?>" target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fas fa-file-pdf me-2"></i>สร้างเกียรติบัตร
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center mt-4">
                            <?php
                                $query_params = [];
                                if ($tournament_id) $query_params['tournament_id'] = $tournament_id;
                                if ($search) $query_params['search'] = $search;
                            ?>
                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page - 1 ?>&<?= http_build_query($query_params) ?>">&laquo;</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($query_params) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page + 1 ?>&<?= http_build_query($query_params) ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        let searchTimeout;
        const searchInput = $('input[name="search"]');
        const suggestionsBox = $('.suggestions-box');

        searchInput.on('keyup', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();

            if (query.length < 2) {
                suggestionsBox.hide().empty();
                return;
            }

            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: 'search-api.php',
                    method: 'GET',
                    data: { query: query },
                    dataType: 'json',
                    success: function(data) {
                        suggestionsBox.empty();
                        if (data.length > 0) {
                            data.forEach(function(item) {
                                suggestionsBox.append(
                                    $('<div class="suggestion-item"></div>').text(item.suggestion)
                                );
                            });
                            suggestionsBox.show();
                        } else {
                            suggestionsBox.hide();
                        }
                    }
                });
            }, 300);
        });

        $(document).on('click', '.suggestion-item', function() {
            const selectedText = $(this).text();
            searchInput.val(selectedText);
            suggestionsBox.hide().empty();
            $('#filter-form').submit();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.suggestions-container').length) {
                suggestionsBox.hide();
            }
        });
    });
    </script>
</body>
</html>