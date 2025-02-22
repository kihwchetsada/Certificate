<?php
require('vendor/autoload.php');
require('database.php');

use setasign\Fpdi\Fpdi;

// สร้างและตรวจสอบโฟลเดอร์ certificates
if (!file_exists('certificates')) {
    mkdir('certificates', 0755, true);
}

// รับและทำความสะอาดค่า name
$name = isset($_POST['name']) ? trim($_POST['name']) : (isset($_GET['name']) ? trim($_GET['name']) : '');
$name = preg_replace('/[^a-zA-Z0-9ก-๙\s]/u', '', $name);

if (!empty($name)) {
    try {
        // ดึงข้อมูลจากฐานข้อมูล
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE name = ?");
        $stmt->execute([$name]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("ไม่พบข้อมูลในระบบ กรุณาลงทะเบียนก่อน");
        }

        $participant = $stmt->fetch();
        $detail = $participant['detail'];
        $model = $participant['model'];

        // ตรวจสอบและโหลดเทมเพลต
        $templatePaths = [
            'model1' => "assets/certificate_template.jpg",
            'model2' => "assets/certificate_template1.jpg",
        ];

        $templatePath = $templatePaths[$model] ?? "assets/certificate_template.jpg"; // โมเดลเริ่มต้น 
        if (!file_exists($templatePath)) {
            throw new Exception("ไม่พบไฟล์เทมเพลต");
        }

        // สร้างรูปภาพ
        $template = @imagecreatefromjpeg($templatePath);
        if (!$template) {
            throw new Exception("ไม่สามารถโหลดเทมเพลตได้");
        }

        // ตรวจสอบฟอนต์
        $font = "assets/fonts/TH Sarabun New Bold.ttf"; // ฟอนต์ TH Sarabun New Bold 
        if (!file_exists($font)) {
            imagedestroy($template);
            throw new Exception("ไม่พบไฟล์ฟอนต์");
        }

        // สร้างสี
        $color = imagecolorallocate($template, 0, 0, 0); // สีดำ สำหรับชื่อ
        $color1 = imagecolorallocate($template, 0, 0, 0); // สีดำ สำหรับรายละเอียด

            // คำนวณตำแหน่งกึ่งกลางของชื่อ
        $nameBox = imagettfbbox(65, 0, $font, $name);
        $nameWidth = $nameBox[2] - $nameBox[0]; // ความกว้างของข้อความ
        $nameX = (imagesx($template) - $nameWidth) / 2; // คำนวณตำแหน่ง X ให้อยู่กลาง

        // คำนวณตำแหน่งกึ่งกลางของรายละเอียด
        $detailBox = imagettfbbox(45, 0, $font, $detail);
        $detailWidth = $detailBox[2] - $detailBox[0]; // ความกว้างของข้อความ
        $detailX = (imagesx($template) - $detailWidth) / 2; // คำนวณตำแหน่ง X ให้อยู่กลาง

        // คำนวณตำแหน่ง Y ให้ข้อความอยู่ในตำแหน่งที่ต้องการ
        $nameY = 630;  // ปรับตำแหน่ง Y ของชื่อ
        $detailY = 720;  // ปรับตำแหน่ง Y ของรายละเอียด

        // วาดข้อความ
        imagettftext($template, 65, 0, $nameX, $nameY, $color, $font, $name); // วาดชื่อ ใช้ฟอนต์ 65 พิกเซล
        imagettftext($template, 50, 0, $detailX, $detailY, $color1, $font, $detail); // วาดรายละเอียด ใช้ฟอนต์ 50 พิกเซล


        // สร้างชื่อไฟล์แบบ unique
        $uniqueId = uniqid();
        $tempImage = "certificates/temp_certificate.jpg"; // ไฟล์ชั่วคราว (ใช้บีบอัดภาพเพื่อลดขนาด)
        $filename = "certificates/{$name}_certificate.pdf"; // ชื่อไฟล์ PDF ที่จะบันทึก (ใช้ชื่อของผู้รับเกียรติบัตร)

        // บันทึกภาพชั่วคราว (ใช้บีบอัดภาพเพื่อลดขนาด)
        imagejpeg($template, $tempImage, 85);
        imagedestroy($template);

        // สร้าง PDF
        $pdf = new Fpdi();
        $imageWidth = 2000; // ขนาดภาพใน PDF
        $imageHeight = 1414; // ขนาดภาพใน PDF
        $imageWidthInMM = ($imageWidth / 72) * 25.4;
        $imageHeightInMM = ($imageHeight / 72) * 25.4;

        $pdf->AddPage('L', [round($imageWidthInMM), round($imageHeightInMM)]); // เพิ่มหน้าใน PDF แบบ Landscape (แนวนอน) ขนาด A4
        $pdf->Image($tempImage, 0, 0, $imageWidthInMM, $imageHeightInMM); // แทรกรูปภาพลงใน PDF ที่ตำแหน่ง (0, 0) ขนาดเต็มหน้ากระดาษ A4
        
        // บันทึก PDF
        $pdf->Output('F', $filename); // บันทึกเป็นไฟล์ PDF

        // ลบไฟล์ชั่วคราว
        if (file_exists($tempImage)) {
            unlink($tempImage);
        }

        // ตรวจสอบว่าสร้าง PDF สำเร็จ
        if (!file_exists($filename)) {
            throw new Exception("ไม่สามารถสร้างไฟล์ PDF ได้");
        }

        // บันทึกลงฐานข้อมูล
        $stmt = $pdo->prepare("INSERT INTO certificates (name, detail, file_path) VALUES (?, ?, ?)");
        if (!$stmt->execute([$name, $detail, $filename])) {
            throw new Exception("ไม่สามารถบันทึกข้อมูลในฐานข้อมูล");
        }

        // แสดงหน้า success พร้อมปุ่มดาวน์โหลด
        echo "<!DOCTYPE html>
        <html lang='th'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>เกียรติบัตร | Certificate Generator</title>
            <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'>
            <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap'>
            <link rel='stylesheet' href='css/generate.css'>
            <link rel='icon' type='image/png' href='img/b.png'>
        </head>
        <body>
            <div class='container mt-5 text-center'>
                <div class='card shadow-lg p-5' style='max-width: 600px; margin: auto; border-radius: 15px;'>
                    <div class='success-icon mb-4'>
                        <svg width='64' height='64' viewBox='0 0 24 24' fill='none' stroke='#00b894' stroke-width='2'>
                            <path d='M22 11.08V12a10 10 0 1 1-5.93-9.14'></path>
                            <polyline points='22 4 12 14.01 9 11.01'></polyline>
                        </svg>
                    </div>
                    <h2 class='text-success mb-4'>🎉 สร้างเกียรติบัตรสำเร็จ!</h2>
                    <p class='text-muted mb-4'>คุณสามารถดาวน์โหลดเกียรติบัตรของคุณได้ที่ปุ่มด้านล่าง</p>
                    <div class='download-section'>
                        <a href='" . htmlspecialchars($filename) . "' class='btn btn-success btn-lg' download>
                            <svg width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' class='me-2'>
                                <path d='M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4'></path>
                                <polyline points='7 10 12 15 17 10'></polyline>
                                <line x1='12' y1='15' x2='12' y2='3'></line>
                            </svg>
                            ดาวน์โหลดเกียรติบัตร
                        </a>
                    </div>
                    <hr class='my-4'>
                    <div class='d-flex justify-content-center gap-3'>
                        <a href='index.php' class='btn btn-outline-primary'>
                            <svg width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' class='me-2'>
                                <line x1='19' y1='12' x2='5' y2='12'></line>
                                <polyline points='12 19 5 12 12 5'></polyline>
                            </svg>
                            กลับหน้าหลัก
                        </a>
                    </div>
                </div>
            </div>
        </body>
        </html>";

    } catch (Exception $e) {
        // แสดงข้อผิดพลาด
        echo "<div class='container mt-5 text-center'>
                <div class='card shadow-lg p-5' style='max-width: 600px; margin: auto; border-radius: 15px;'>
                    <div class='error-icon mb-4'>
                        <svg width='64' height='64' viewBox='0 0 24 24' fill='none' stroke='#ff6b6b' stroke-width='2'>
                            <circle cx='12' cy='12' r='10'></circle>
                            <line x1='12' y1='8' x2='12' y2='12'></line>
                            <line x1='12' y1='16' x2='12.01' y2='16'></line>
                        </svg>
                    </div>
                    <h2 class='text-danger mb-4'>❌ เกิดข้อผิดพลาด</h2>
                    <p class='text-muted mb-4'>" . htmlspecialchars($e->getMessage()) . "</p>
                    <a href='index.php' class='btn btn-primary'>กลับหน้าหลัก</a>
                </div>
              </div>";
    }
} else {
    // แสดงข้อความเมื่อไม่มีข้อมูล
    echo "<div class='d-flex justify-content-center align-items-center vh-100'>
        <div class='card shadow-lg p-4 border-0 animate__animated animate__fadeInDown' 
            style='max-width: 500px; border-radius: 20px; background: linear-gradient(135deg, #ff758c, #ffb199); text-align: center;'>
            <div class='card-body'>
                <h2 class='text-white fw-bold'><i class='bi bi-x-circle-fill'></i> กรุณาระบุชื่อผู้รับเกียรติบัตร</h2>
                <a href='index.php' class='btn btn-light mt-3 px-4 py-2 rounded-pill shadow-sm fw-bold hover-effect text-decoration-none'>กลับหน้าหลัก</a>
            </div>
        </div>
    </div>";

    echo "<style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
        }
        .hover-effect {
            background-color: white;
            color: #ff4d6d;
            font-size: 1.2rem;
            transition: all 0.3s ease-in-out;
            display: inline-block;
        }
        .hover-effect:hover {
            background-color: #ff4d6d !important;
            color: white !important;
            transform: scale(1.05);
        }
        </style>";

    echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'>";
}
?>