<?php
require('database.php');
$stmt = $pdo->query("SELECT * FROM certificates ORDER BY created_at DESC");
$certificates = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการเกียรติบัตร</title>
    <link rel="icon" type="image/png" href="img/b.png">
</head>
<body>
    <h2>รายการเกียรติบัตร</h2>
    <table border="1">
        <tr>
            <th>ชื่อ</th>
            <th>รายละเอียด</th>
            <th>ดาวน์โหลด</th>
        </tr>
        <?php foreach ($certificates as $cert): ?>
        <tr>
            <td><?= htmlspecialchars($cert['name']) ?></td>
            <td><?= htmlspecialchars($cert['detail']) ?></td>
            <td><a href="download.php?id=<?= $cert['id'] ?>">ดาวน์โหลด</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>