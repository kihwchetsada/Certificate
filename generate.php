<?php
// generate.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../db_connect.php'; 

use setasign\Fpdi\Fpdi;

function drawCenteredText($image, $y, $size, $color, $font, $text) {
    $bbox = imagettfbbox($size, 0, $font, $text);
    $x = (imagesx($image) - ($bbox[2] - $bbox[0])) / 2;
    imagettftext($image, $size, 0, (int)$x, $y, $color, $font, $text);
}

$member_id = isset($_GET['member_id']) ? filter_var($_GET['member_id'], FILTER_VALIDATE_INT) : null;

if (!$member_id) {
    die("Error: Missing or invalid member_id parameter.");
}

try {
    // FIXED: Changed the join on the 'tournaments' table to a LEFT JOIN.
    // This allows members of teams with no tournament (like staff) to be found.
    $sql = "SELECT
                tm.member_name,
                t.team_name,
                tn.tournament_name,
                c.model AS award_model 
            FROM
                team_members AS tm
            JOIN
                teams AS t ON tm.team_id = t.team_id
            LEFT JOIN 
                tournaments AS tn ON t.tournament_id = tn.id
            LEFT JOIN
                certificate AS c ON tm.member_id = c.member_id
            WHERE
                tm.member_id = :member_id"; 

    $stmt = $conn->prepare($sql);
    $stmt->execute(['member_id' => $member_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception("Could not find member with ID: {$member_id}");
    }

    $memberName = $result['member_name'];
    // Use a default tournament name if it's null (for staff)
    $tournamentName = $result['tournament_name'] ?? 'การแข่งขันราชมงคลอีสปอร์ต ครั้งที่ ' . (date('Y') - 2020);
    
    $awardModel = $result['award_model'];
    if (empty($awardModel)) {
        $awardModel = 'ผู้เข้าร่วมการแข่งขัน';
    }

    $header_line1 = "คณะเกษตรศาสตร์และเทคโนโลยี";
    $header_line2 = "มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตสุรินทร์";
    $header_line3 = "ขอมอบเกียรติบัตรนี้ให้ไว้เพื่อแสดงว่า";

    $detail_line1 =  $awardModel;
    $detail_line2 = "จากการแข่งขัน E-Sport รายการ \"" . $tournamentName . "\"";
    $detail_line3 = "งานวันเกษตรและเทคโนโลยีอีสาน ครั้งที่ " . (date('Y')-2013) . "";
    
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
    drawCenteredText($template, 630, 65, $color, $fontPath, $memberName);
    drawCenteredText($template, 740, 55, $color, $fontPath, $detail_line1);
    drawCenteredText($template, 820, 45, $color, $fontPath, $detail_line2);
    drawCenteredText($template, 890, 50, $color, $fontPath, $detail_line3);
    drawCenteredText($template, 960, 40, $color, $fontPath, $detail_line4);

    $tempImage = tempnam(sys_get_temp_dir(), 'cert_') . '.jpg';
    imagejpeg($template, $tempImage, 90);
    imagedestroy($template);

    $pdf = new Fpdi();
    $pdf->AddPage('L', 'A4');
    $pdf->Image($tempImage, 0, 0, 297, 210);
    unlink($tempImage);

    $pdf->Output('I', 'certificate.pdf');
    
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    die("An error occurred: " . $e->getMessage());
}
?>