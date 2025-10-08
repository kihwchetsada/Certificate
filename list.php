<?php
// list.php (Corrected to hide teams with no coach)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../db_connect.php';

// --- 1. Get GET Parameters ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tournament_id = isset($_GET['tournament_id']) && !empty($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'members';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$items_per_page = 10;
$offset = ($current_page - 1) * $items_per_page;

try {
    // --- 2. Fetch Shared Data (Tournaments) ---
    $tournaments = $conn->query("SELECT id, tournament_name FROM tournaments ORDER BY tournament_name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. Prepare and Fetch Data for "Members Tab" (No changes here) ---
    $base_sql_members = "FROM team_members AS tm JOIN teams AS t ON tm.team_id = t.team_id LEFT JOIN tournaments AS tn ON t.tournament_id = tn.id";
    $where_clauses_members = [];
    $params_members = [];
    if ($tournament_id) { $where_clauses_members[] = "tn.id = ?"; $params_members[] = $tournament_id; }
    if ($search) { 
        $where_clauses_members[] = "(tm.member_name LIKE ? OR t.team_name LIKE ?)";
        $params_members[] = "%$search%"; $params_members[] = "%$search%";
    }
    $where_sql_members = !empty($where_clauses_members) ? " WHERE " . implode(" AND ", $where_clauses_members) : "";
    
    $count_sql_members = "SELECT COUNT(tm.member_id) as total " . $base_sql_members . $where_sql_members;
    $count_stmt_members = $conn->prepare($count_sql_members);
    $count_stmt_members->execute($params_members);
    $total_items_members = $count_stmt_members->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages_members = ceil($total_items_members / $items_per_page);
    
    $sql_members = "SELECT tm.member_name, tm.member_id, t.team_name, tn.tournament_name " . $base_sql_members . $where_sql_members . " ORDER BY tn.tournament_name, t.team_name, tm.member_name ASC LIMIT ? OFFSET ?";
    $params_members_paginated = array_merge($params_members, [$items_per_page, $offset]);
    $stmt_members = $conn->prepare($sql_members);
    $stmt_members->execute($params_members_paginated);
    $members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);

    // --- 4. Prepare and Fetch Data for "Coaches Tab" ---
    $base_sql_coaches = "FROM teams AS t LEFT JOIN tournaments AS tn ON t.tournament_id = tn.id";
    $where_clauses_coaches = [];
    $params_coaches = [];

    // ADDED: Filter to only show teams that have a real coach name.
    $where_clauses_coaches[] = "t.coach_name IS NOT NULL AND t.coach_name != '' AND t.coach_name != '-'";

    if ($tournament_id) { $where_clauses_coaches[] = "tn.id = ?"; $params_coaches[] = $tournament_id; }
    if ($search) {
        $where_clauses_coaches[] = "(t.coach_name LIKE ? OR t.team_name LIKE ?)";
        $params_coaches[] = "%$search%"; $params_coaches[] = "%$search%";
    }
    $where_sql_coaches = " WHERE " . implode(" AND ", $where_clauses_coaches);

    $count_sql_coaches = "SELECT COUNT(t.team_id) as total " . $base_sql_coaches . $where_sql_coaches;
    $count_stmt_coaches = $conn->prepare($count_sql_coaches);
    $count_stmt_coaches->execute($params_coaches);
    $total_items_coaches = $count_stmt_coaches->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages_coaches = ceil($total_items_coaches / $items_per_page);

    $sql_coaches = "SELECT t.team_id, t.coach_name, t.team_name, tn.tournament_name " . $base_sql_coaches . $where_sql_coaches . " ORDER BY tn.tournament_name, t.team_name ASC LIMIT ? OFFSET ?";
    $params_coaches_paginated = array_merge($params_coaches, [$items_per_page, $offset]);
    $stmt_coaches = $conn->prepare($sql_coaches);
    $stmt_coaches->execute($params_coaches_paginated);
    $coaches = $stmt_coaches->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$total_pages_for_active_tab = ($active_tab == 'coaches') ? $total_pages_coaches : $total_pages_members;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อทั้งหมด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .nav-tabs .nav-link { color: #6c757d; }
        .nav-tabs .nav-link.active { color: #0d6efd; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <h2 class="mb-0">รายชื่อทั้งหมด</h2>
                <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> กลับหน้าหลัก</a>
            </div>
            <div class="card-body">
                
                <form method="GET" action="list.php" class="mb-4">
                    <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <select name="tournament_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- เลือกทัวร์นาเมนต์ทั้งหมด --</option>
                                <?php foreach ($tournaments as $tournament): ?>
                                    <option value="<?= $tournament['id'] ?>" <?= ($tournament['id'] == $tournament_id) ? 'selected' : '' ?>><?= htmlspecialchars($tournament['tournament_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อสมาชิก, โค้ช, หรือทีม..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" type="submit"><i class="fas fa-search"></i> ค้นหา</button>
                        </div>
                    </div>
                </form>

                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab == 'members') ? 'active' : '' ?>" href="?tab=members&tournament_id=<?= $tournament_id ?>&search=<?= urlencode($search) ?>">
                            รายชื่อสมาชิก (<?= $total_items_members ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab == 'coaches') ? 'active' : '' ?>" href="?tab=coaches&tournament_id=<?= $tournament_id ?>&search=<?= urlencode($search) ?>">
                            รายชื่อโค้ช (<?= $total_items_coaches ?>)
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade <?= ($active_tab == 'members') ? 'show active' : '' ?>" id="members">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr><th>#</th><th>ชื่อ-นามสกุล</th><th>ชื่อทีม</th><th>ทัวร์นาเมนต์</th><th class="text-center">จัดการ</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($members)): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลสมาชิก</td></tr>
                                    <?php else: ?>
                                        <?php $i = $offset + 1; foreach ($members as $member): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($member['member_name'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($member['team_name'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($member['tournament_name'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <a href="generate.php?member_id=<?= $member['member_id'] ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf me-2"></i>เกียรติบัตร</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= ($active_tab == 'coaches') ? 'show active' : '' ?>" id="coaches">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr><th>#</th><th>ชื่อ-นามสกุล (โค้ช)</th><th>ชื่อทีม</th><th>ทัวร์นาเมนต์</th><th class="text-center">จัดการ</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($coaches)): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลโค้ช</td></tr>
                                    <?php else: ?>
                                        <?php $i = $offset + 1; foreach ($coaches as $coach): ?>
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td><?= htmlspecialchars($coach['coach_name'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($coach['team_name'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($coach['tournament_name'] ?? '-') ?></td>
                                                <td class="text-center">
                                                    <a href="generate_coach.php?team_id=<?= $coach['team_id'] ?>" target="_blank" class="btn btn-info btn-sm"><i class="fas fa-file-pdf me-2"></i>เกียรติบัตร</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php if ($total_pages_for_active_tab > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center mt-4">
                            <?php
                                $query_params = ['tab' => $active_tab];
                                if ($tournament_id) $query_params['tournament_id'] = $tournament_id;
                                if ($search) $query_params['search'] = $search;
                            ?>
                            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page - 1 ?>&<?= http_build_query($query_params) ?>">&laquo;</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages_for_active_tab; $i++): ?>
                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($query_params) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($current_page >= $total_pages_for_active_tab) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $current_page + 1 ?>&<?= http_build_query($query_params) ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>