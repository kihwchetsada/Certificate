<?php
// generate_coach.php

// ADDED: Force PHP to display all errors to find the problem
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Corrected paths for your project structure
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../db_connect.php'; 

use setasign\Fpdi\Fpdi;

/**
 * Helper function to draw centered text on an image.
 */
function drawCenteredText($image, $y, $size, $color, $font, $text) {
    $bbox = imagettfbbox($size, 0, $font, $text);
    $x = (imagesx($image) - ($bbox[2] - $bbox[0])) / 2;
    imagettftext($image, $size, 0, (int)$x, $y, $color, $font, $text);
}

$team_id = isset($_GET['team_id']) ? filter_var($_GET['team_id'], FILTER_VALIDATE_INT) : null;

if (!$team_id) {
    die("Error: Missing or invalid team_id parameter.");
}

try {
    $sql = "SELECT
                t.coach_name,
                tn.tournament_name
            FROM
                teams AS t
            JOIN
                tournaments AS tn ON t.tournament_id = tn.id
            WHERE
                t.team_id = :team_id"; 

    $stmt = $conn->prepare($sql);
    $stmt->execute(['team_id' => $team_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception("Could not find coach data for this team (ID: {$team_id}).");
    }

    $coachName = $result['coach_name'];
    $tournamentName = $result['tournament_name'];
    $awardModel = 'ผู้ควบคุมทีม';

    $header_line1 = "คณะเกษตรศาสตร์และเทคโนโลยี";
    $header_line2 = "มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตสุรินทร์";
    $header_line3 = "ขอมอบเกียรติบัตรนี้ให้ไว้เพื่อแสดงว่า";

    $detail_line1 =  $awardModel;
    $detail_line2 = "ในการแข่งขัน E-Sport รายการ \"" . $tournamentName . "\"";
    $detail_line3 = "งานวันเกษตรและเทคโนโลยีอีสาน ครั้งที่ 12";
    
    $thai_months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    $day = date('j');
    $month_index = date('n') - 1;
    $year = date('Y') + 543;
    $detail_line4 = "ให้ไว้ ณ วันที่ " . $day . " " . $thai_months[$month_index] . " พ.ศ. " . $year;

    $templatePath = __DIR__ . '/assets/certificate_template.jpg'; 
    if (!file_exists($templatePath)) {
      throw new Exception("Certificate template not found. Path checked: " . $templatePath);
    }
    
    $fontPath = __DIR__ . '/assets/fonts/TH Sarabun New Bold.ttf'; 
    if (!file_exists($fontPath)) {
      throw new Exception("Font file not found. Path checked: " . $fontPath);
    }

    $template = @imagecreatefromjpeg($templatePath);
    $color = imagecolorallocate($template, 0, 0, 0);

    drawCenteredText($template, 355, 45, $color, $fontPath, $header_line1);
    drawCenteredText($template, 430, 50, $color, $fontPath, $header_line2);
    drawCenteredText($template, 500, 45, $color, $fontPath, $header_line3);
    drawCenteredText($template, 630, 65, $color, $fontPath, $coachName);
    drawCenteredText($template, 740, 55, $color, $fontPath, $detail_line1);
    drawCenteredText($template, 820, 45, $color, $fontPath, $detail_line2);
    drawCenteredText($template, 890, 50, $color, $fontPath, $detail_line3);
    drawCenteredText($template, 960, 40, $color, $fontPath, $detail_line4);

    $tempImage = tempnam(sys_get_temp_dir(), 'cert_coach_') . '.jpg';
    imagejpeg($template, $tempImage, 90);
    imagedestroy($template);

    $pdf = new Fpdi();
    $pdf->AddPage('L', 'A4');
    $pdf->Image($tempImage, 0, 0, 297, 210);
    unlink($tempImage);

    $pdf->Output('I', 'certificate_coach.pdf');
    
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    die("An error occurred: " . $e->getMessage());
}
?>