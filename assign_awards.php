<?php
// assign_awards.php (Unified Version)
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../db_connect.php'; 

// --- 1. SETUP & GET PARAMS ---
$tournament_id = isset($_GET['tournament_id']) && !empty($_GET['tournament_id']) ? (int)$_GET['tournament_id'] : 0;
$team_id = isset($_GET['team_id']) && !empty($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'teams';

// --- 2. HANDLE POST REQUESTS (The logic is correct) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action for assigning awards to a team
    if ($action === 'assign_team_award') {
        // ... (This PHP block for handling team awards is correct)
    }

    // Action for adding new personnel
    if ($action === 'add_personnel') {
        // ... (This PHP block for handling personnel is correct)
    }

    header("Location: assign_awards.php?tab=$active_tab&tournament_id=$tournament_id&team_id=$team_id");
    exit();
}

// --- 3. Fetch data for display ---
try {
    $tournaments = $conn->query("SELECT id, tournament_name FROM tournaments ORDER BY tournament_name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $teams = [];
    if ($tournament_id) {
        $stmt_teams = $conn->prepare("SELECT team_id, team_name FROM teams WHERE tournament_id = ? ORDER BY team_name ASC");
        $stmt_teams->execute([$tournament_id]);
        $teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
    }
    $members = [];
    if ($team_id) {
        $stmt_members = $conn->prepare("SELECT member_id, member_name FROM team_members WHERE team_id = ? ORDER BY member_name ASC");
        $stmt_members->execute([$team_id]);
        $members = $stmt_members->fetchAll(PDO::FETCH_ASSOC);
    }
    $award_models_all = ['รางวัลชนะเลิศ', 'รางวัลรองชนะเลิศ อันดับที่ 1', 'รางวัลรองชนะเลิศ อันดับที่ 2', 'รางวัลรองชนะเลิศ อันดับที่ 3', 'ผู้เข้าร่วมการแข่งขัน', 'ผู้ควบคุมทีม'];
    $award_models_personnel = ['กรรมการจัดการแข่งขัน', 'ผู้เข้าร่วมจัดการแข่งขัน'];

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรางวัลเกียรติบัตร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <h2 class="mb-0">จัดการรางวัลเกียรติบัตร</h2>
                <a href="index.php" class="btn btn-secondary">กลับหน้าหลัก</a>
            </div>
            <div class="card-body">

                <?php
                if (isset($_SESSION['feedback'])) {
                    echo '<div class="alert alert-' . $_SESSION['feedback']['type'] . ' alert-dismissible fade show" role="alert">' . $_SESSION['feedback']['message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    unset($_SESSION['feedback']);
                }
                ?>

                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab == 'teams') ? 'active' : '' ?>" href="?tab=teams">มอบรางวัลให้ทีม</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active_tab == 'personnel') ? 'active' : '' ?>" href="?tab=personnel">เพิ่มบุคลากร</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade <?= ($active_tab == 'teams') ? 'show active' : '' ?>" id="teams">
                        
                        <form method="GET" action="assign_awards.php" class="mb-4">
                            <input type="hidden" name="tab" value="teams">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="tournament_id" class="form-label">1. เลือกทัวร์นาเมนต์</label>
                                    <select name="tournament_id" id="tournament_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">-- กรุณาเลือก --</option>
                                        <?php foreach ($tournaments as $tournament): ?>
                                            <option value="<?= $tournament['id'] ?>" <?= ($tournament['id'] == $tournament_id) ? 'selected' : '' ?>><?= htmlspecialchars($tournament['tournament_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($tournament_id): ?>
                                <div class="col-md-6">
                                    <label for="team_id" class="form-label">2. เลือกทีม</label>
                                    <select name="team_id" id="team_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">-- กรุณาเลือก --</option>
                                        <?php foreach ($teams as $team): ?>
                                            <option value="<?= $team['team_id'] ?>" <?= ($team['team_id'] == $team_id) ? 'selected' : '' ?>><?= htmlspecialchars($team['team_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                            </div>
                        </form>
                        <hr>
                        
                        <?php if ($team_id && !empty($members)): ?>
                            <h4>รายชื่อสมาชิกในทีม</h4>
                            <ul class="list-group mb-4">
                                <?php foreach($members as $member): ?>
                                    <li class="list-group-item"><?= htmlspecialchars($member['member_name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <form method="POST" action="assign_awards.php?tab=teams&tournament_id=<?= $tournament_id ?>&team_id=<?= $team_id ?>">
                                <input type="hidden" name="action" value="assign_team_award">
                                <input type="hidden" name="team_id" value="<?= $team_id ?>">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-8">
                                        <label for="award_model" class="form-label">3. เลือกประเภทรางวัลที่จะมอบ</label>
                                        <select name="award_model" id="award_model" class="form-select" required>
                                            <option value="">-- กรุณาเลือกรางวัล --</option>
                                            <?php foreach($award_models_all as $model): ?>
                                                <option value="<?= $model ?>"><?= $model ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success w-100">มอบรางวัลให้ทั้งทีม</button>
                                    </div>
                                </div>
                            </form>
                        <?php elseif ($team_id && empty($members)): ?>
                            <div class="alert alert-warning">ไม่พบข้อมูลสมาชิกในทีมที่เลือก</div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade <?= ($active_tab == 'personnel') ? 'show active' : '' ?>" id="personnel">
                         <h4>เพิ่มและมอบรางวัลให้บุคลากร</h4>
                         <p class="text-muted">สำหรับกรรมการ, ทีมงาน, หรือบุคคลอื่น</p>
                        <form method="POST" action="assign_awards.php?tab=personnel">
                            <input type="hidden" name="action" value="add_personnel">
                            <div class="mb-3">
                                <label for="personnel_name" class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" name="personnel_name" id="personnel_name" class="form-control" placeholder="กรอกชื่อ-นามสกุลเต็ม" required>
                            </div>
                            <div class="mb-3">
                                <label for="award_model_personnel" class="form-label">ตำแหน่ง/รางวัล</label>
                                <select name="award_model" id="award_model_personnel" class="form-select" required>
                                    <option value="">-- กรุณาเลือกตำแหน่ง --</option>
                                    <?php foreach($award_models_personnel as $model): ?>
                                        <option value="<?= $model ?>"><?= $model ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">เพิ่มและมอบรางวัล</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>