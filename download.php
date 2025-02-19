<?php
require('database.php');

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT file_path FROM certificates WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $certificate = $stmt->fetch();

    if ($certificate) {
        $filepath = $certificate['file_path'];
        if (file_exists($filepath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            readfile($filepath);
            exit;
        } else {
            echo "ไฟล์ไม่พบ!";
        }
    } else {
        echo "ไม่พบเกียรติบัตร!";
    }
}
?>
