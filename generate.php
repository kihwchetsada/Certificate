<?php
// generate.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Path ไปยัง autoload.php ของ Composer
require_once __DIR__ . '/../../vendor/autoload.php';
// Path ไปยังไฟล์เชื่อมต่อฐานข้อมูลของคุณ
require_once __DIR__ . '/../../db_connect.php'; 

use setasign\Fpdi\Fpdi;

// 1. รับและตรวจสอบ member_id จาก URL
$member_id = isset($_GET['member_id']) ? filter_var($_GET['member_id'], FILTER_VALIDATE_INT) : null;

if (!$member_id) {
    die("Error: Missing or invalid member_id parameter.");
}

try {
    // 2. ดึงข้อมูลที่จำเป็นจากฐานข้อมูล
    // แก้ไข JOIN และ WHERE clause ตามโครงสร้างตารางล่าสุดของคุณ
    $sql = "SELECT
                tm.member_name,
                t.team_name,
                tn.tournament_name
            FROM
                team_members AS tm
            JOIN
                teams AS t ON tm.team_id = t.team_id
            JOIN
                tournaments AS tn ON t.tournament_id = tn.id
            WHERE
                tm.member_id = :member_id"; 

    $stmt = $conn->prepare($sql);
    $stmt->execute(['member_id' => $member_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception("Could not find member data for ID: {$member_id}");
    }

    $memberName = $result['member_name'];
    //$position = $result['position'];
    $tournamentName = $result['tournament_name'];

    $header_line1 = "คณะเกษตรศาสตร์และเทคโนโลยี";
    $header_line2 = "มหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตสุรินทร์";
    $header_line3 = "ขอมอบเกียรติบัตรนี้ให้ไว้เพื่อแสดงว่า";

    $detail_line1 = "การแข่งขัน “ราชมงคลสุรินทร์ อีสปอร์ต ครั้งที่ " . (date('Y') - 2020) . "” ";
    $detail_line2 = "การแข่งขัน \"" . $tournamentName . "\"";
    $detail_line3 = "งานวันเกษตรและเทคโนโลยีอีสาน ครั้งที่ " . (date('Y') - 2013) . "";
    $detail_line4 = "ขอให้มีความสุข ความเจริญ ประสบความสำเร็จสืบไป";
    $thai_months = [
    "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน",
    "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม",
    "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
];

// ดึงข้อมูลวัน, เดือน, ปี ปัจจุบัน
$day = date('j');
$month_index = date('n') - 1; // date('n') ให้ค่า 1-12, เราต้องใช้ index 0-11
$year = date('Y') + 543; // แปลง ค.ศ. เป็น พ.ศ.

// นำมาประกอบเป็นประโยค
$detail_line5 = "ให้ไว้ ณ วันที่ " . $day . " " . $thai_months[$month_index] . " พ.ศ. " . $year;

    // 4. สร้างรูปภาพเกียรติบัตร
    // แก้ไข Path ให้ถูกต้องตามโครงสร้างโฟลเดอร์ของคุณ
    $templatePath = __DIR__ . '/assets/certificate_template.jpg'; 
    if (!file_exists($templatePath)) {
      throw new Exception("Certificate template not found. Path checked: " . $templatePath);
    }
    
    $fontPath = __DIR__ . '/assets/fonts/TH Sarabun New Bold.ttf'; 
    if (!file_exists($fontPath)) {
      throw new Exception("Font file not found. Path checked: " . $fontPath);
    }

    $template = @imagecreatefromjpeg($templatePath);
    if (!$template) {
        throw new Exception("Could not create image from template. The file might be corrupted.");
    }
    $color = imagecolorallocate($template, 0, 0, 0);

    // วาดข้อความลงบน Template
    $header1Box = imagettfbbox(45, 0, $fontPath, $header_line1);
    $header1X = (imagesx($template) - ($header1Box[2] - $header1Box[0])) / 2;
    imagettftext($template, 45, 0, (int)$header1X, 355, $color, $fontPath, $header_line1);
    $header2Box = imagettfbbox(50, 0, $fontPath, $header_line2);
    $header2X = (imagesx($template) - ($header2Box[2] - $header2Box[0])) / 2;
    imagettftext($template, 50, 0, (int)$header2X, 430, $color, $fontPath, $header_line2);
    $header3Box = imagettfbbox(45, 0, $fontPath, $header_line3);
    $header3X = (imagesx($template) - ($header3Box[2] - $header3Box[0])) / 2;
    imagettftext($template, 45, 0, (int)$header3X, 500, $color, $fontPath, $header_line3);

    $nameBox = imagettfbbox(65, 0, $fontPath, $memberName);
    $nameX = (imagesx($template) - ($nameBox[2] - $nameBox[0])) / 2;
    imagettftext($template, 65, 0, (int)$nameX, 630, $color, $fontPath, $memberName);

    $detail1Box = imagettfbbox(50, 0, $fontPath, $detail_line1);
    $detail1X = (imagesx($template) - ($detail1Box[2] - $detail1Box[0])) / 2;
    imagettftext($template, 50, 0, (int)$detail1X, 720, $color, $fontPath, $detail_line1);

    $detail2Box = imagettfbbox(45, 0, $fontPath, $detail_line2);
    $detail2X = (imagesx($template) - ($detail2Box[2] - $detail2Box[0])) / 2;
    imagettftext($template, 45, 0, (int)$detail2X, 800, $color, $fontPath, $detail_line2);
    $detail3Box = imagettfbbox(50, 0, $fontPath, $detail_line3);
    $detail3X = (imagesx($template) - ($detail3Box[2] - $detail3Box[0])) / 2;
    imagettftext($template, 50, 0, (int)$detail3X, 870, $color, $fontPath, $detail_line3);
    $detail4Box = imagettfbbox(45, 0, $fontPath, $detail_line4);
    $detail4X = (imagesx($template) - ($detail4Box[2] - $detail4Box[0])) / 2;
    imagettftext($template, 45, 0, (int)$detail4X, 930, $color, $fontPath, $detail_line4);
    $detail5Box = imagettfbbox(40, 0, $fontPath, $detail_line5);
    $detail5X = (imagesx($template) - ($detail5Box[2] - $detail5Box[0])) / 2;
    imagettftext($template, 40, 0, (int)$detail5X, 990, $color, $fontPath, $detail_line5);

    // 5. สร้างไฟล์ PDF และส่งออกไปที่เบราว์เซอร์
    $tempImage = tempnam(sys_get_temp_dir(), 'cert_') . '.jpg';
    imagejpeg($template, $tempImage, 90);
    imagedestroy($template);

    $pdf = new Fpdi();
    $pdf->AddPage('L', 'A4');
    $pdf->Image($tempImage, 0, 0, 297, 210);
    unlink($tempImage);

    // ส่ง PDF ไปที่เบราว์เซอร์เพื่อแสดงผล (Inline)
    $pdf->Output('I', 'certificate.pdf');
    
} catch (Exception $e) {
    // หากเกิดข้อผิดพลาด ให้แสดงข้อความเป็น Text ธรรมดาเพื่อให้อ่านง่าย
    header('Content-Type: text/plain; charset=utf-8');
    die("An error occurred: " . $e->getMessage());
}
?>