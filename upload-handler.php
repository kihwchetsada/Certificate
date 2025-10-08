<?php
// upload-handler.php

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.'
];

// 1. Basic Security Checks
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    $response['message'] = 'Invalid request.';
    echo json_encode($response);
    exit();
}

if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'File upload error. Code: ' . $_FILES['image']['error'];
    echo json_encode($response);
    exit();
}

// 2. Define the single, hardcoded destination path
$destination_path = __DIR__ . '/assets/certificate_template.jpg';

// 3. File Validation (Server-side)
$file_tmp_path = $_FILES['image']['tmp_name'];
$file_size = $_FILES['image']['size'];

if ($file_size > 2 * 1024 * 1024) { // Max 2MB
    $response['message'] = 'File is too large. Maximum size is 2MB.';
    echo json_encode($response);
    exit();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_tmp_path);
finfo_close($finfo);

if ($mime_type !== 'image/jpeg') {
    $response['message'] = 'Invalid file type. Only JPG/JPEG is allowed.';
    echo json_encode($response);
    exit();
}

// 4. Process the Upload
$assets_dir = dirname($destination_path);
if (!is_dir($assets_dir)) {
    mkdir($assets_dir, 0775, true);
}

if (!is_writable($assets_dir)) {
     $response['message'] = 'Error: The assets directory is not writable. Please check server permissions.';
     echo json_encode($response);
     exit();
}

if (move_uploaded_file($file_tmp_path, $destination_path)) {
    $response['success'] = true;
    $response['message'] = 'Template replaced successfully! The page will now reload.';
} else {
    $response['message'] = 'Failed to save the uploaded file.';
}

// 5. Send the final JSON response
echo json_encode($response);
?>