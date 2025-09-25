<?php
// เปิดการแสดง Error ทั้งหมด เพื่อดูปัญหาที่ซ่อนอยู่
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// ฟังก์ชันสำหรับดักจับ Error สุดท้ายของ PHP
function get_last_php_error() {
    $error = error_get_last();
    return $error ? $error['message'] : 'No recent error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && isset($_POST['target'])) {
    $file = $_FILES['image'];
    $target = $_POST['target'];
    
    $assetsDir = __DIR__ . '/assets';
    $targetPath = $assetsDir . '/' . $target . '.jpg';

    // --- ส่วน Debug เพิ่มเติม ---
    $debug_info = [
        'php_version' => phpversion(),
        'script_directory' => __DIR__,
        'target_assets_directory' => $assetsDir,
        'final_target_path' => $targetPath,
        'assets_dir_exists' => is_dir($assetsDir),
        'assets_dir_is_writable' => is_writable($assetsDir),
        'parent_dir_is_writable' => is_writable(__DIR__),
        'is_valid_uploaded_file' => is_uploaded_file($file['tmp_name']),
        'php_upload_error_code' => $file['error'],
        'last_php_error_before_move' => get_last_php_error()
    ];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $debug_info['final_message'] = 'Upload error code was not UPLOAD_ERR_OK.';
        echo json_encode(['success' => false, 'message' => 'PHP Upload Error Code: ' . $file['error'], 'debug' => $debug_info]);
        exit;
    }

    if (!is_dir($assetsDir)) {
        if (!mkdir($assetsDir, 0775, true)) {
            $debug_info['final_message'] = 'Failed at mkdir() step.';
            $debug_info['mkdir_last_error'] = get_last_php_error();
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถสร้างโฟลเดอร์ assets ได้', 'debug' => $debug_info]);
            exit;
        }
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['success' => true, 'message' => "อัปโหลดสำเร็จ!"]);
    } else {
        $debug_info['final_message'] = 'Failed at move_uploaded_file() step.';
        $debug_info['move_uploaded_file_last_error'] = get_last_php_error();
        echo json_encode([
            'success' => false, 
            'message' => 'ไม่สามารถบันทึกไฟล์ได้ (move_uploaded_file failed)', 
            'debug' => $debug_info
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or missing files/target.']);
}
?>
