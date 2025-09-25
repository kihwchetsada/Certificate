<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเทมเพลตเกียรติบัตร</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #eef2f6;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .main-wrapper {
            max-width: 900px;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .container:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.12);
        }
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 1.8em;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-weight: 500;
            color: #34495e;
            font-size: 1.05em;
            margin-bottom: 5px;
        }
        select, input[type="file"], input[type="text"] {
            padding: 12px;
            border: 1px solid #c8d6e5;
            border-radius: 8px;
            font-size: 16px;
            color: #34495e;
            background-color: #fcfdff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        select:focus, input[type="file"]:focus, input[type="text"]:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        input[type="file"] {
            display: none;
        }
        .custom-file-upload {
            border: 1px solid #c8d6e5;
            display: inline-block;
            padding: 12px 15px;
            cursor: pointer;
            border-radius: 8px;
            background-color: #fcfdff;
            color: #34495e;
            font-size: 16px;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .custom-file-upload:hover {
            background-color: #eef2f6;
            border-color: #aebfd4;
        }
        .custom-file-upload i {
            margin-right: 8px;
        }
        #file-name {
            margin-left: 10px;
            color: #555;
            font-style: italic;
        }
        button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        button[type="submit"] {
            background-color: #2ecc71;
            color: white;
        }
        button[type="submit"]:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }
        button[type="button"] {
            background-color: #e74c3c;
            color: white;
        }
        button[type="button"]:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
        }
        button:disabled {
            background-color: #bdc3c7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        #preview {
            max-width: 100%;
            height: auto;
            border: 1px solid #c8d6e5;
            border-radius: 8px;
            padding: 8px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        #previewContainer {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }
        .back-link {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }
        .back-link:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        #message {
            margin-top: 20px;
            font-size: 17px;
            text-align: center;
            font-weight: 500;
        }
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .template-item {
            border: 1px solid #e0e6ed;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background-color: #fcfdff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .template-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .template-item img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            border: 1px solid #eee;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .template-item p {
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: 600;
            color: #34495e;
            font-size: 1.1em;
        }
        .placeholder-image {
            width: 100%;
            height: 200px;
            background-color: #f0f2f5;
            color: #aebfd4;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 1.2em;
            margin-top: 15px;
            border: 1px dashed #c8d6e5;
        }
        .placeholder-image i {
            margin-right: 10px;
        }
        @media (max-width: 768px) {
            .template-grid {
                grid-template-columns: 1fr;
            }
            .main-wrapper {
                padding: 15px;
            }
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 1.5em;
            }
            .button-group {
                flex-direction: column;
            }
            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <div class="container">
            <h2><i class="fas fa-image"></i> เทมเพลตปัจจุบัน</h2>
            <div class="template-grid">
                <div class="template-item">
                    <p>ใบประกาศนียบัตร 1</p>
                    <img src="assets/certificate_template.jpg" alt="Template 1 Preview" onerror="this.onerror=null;this.src=''; this.outerHTML='<div class=&quot;placeholder-image&quot;><i class=&quot;fas fa-exclamation-triangle&quot;></i> ไม่พบรูปภาพ</div>';">
                </div>
                <div class="template-item">
                    <p>ใบประกาศนียบัตร 2</p>
                     <img src="assets/certificate_template1.jpg" alt="Template 2 Preview" onerror="this.onerror=null;this.src=''; this.outerHTML='<div class=&quot;placeholder-image&quot;><i class=&quot;fas fa-exclamation-triangle&quot;></i> ไม่พบรูปภาพ</div>';">
                </div>
            </div>
        </div>
        <div class="container">
            <h2><i class="fas fa-upload"></i> อัปโหลดเพื่อแทนที่เทมเพลต</h2>
            <p style="text-align: center; color: #555; margin-top: -15px; margin-bottom: 25px;">(ไฟล์ .jpg เท่านั้น, ขนาดไม่เกิน 5MB)</p>
            <form id="uploadForm">
                <label for="target">เลือกเทมเพลตที่ต้องการแทนที่:</label>
                <select name="target" id="target" required>
                    <option value="">-- กรุณาเลือกเทมเพลต --</option>
                    <option value="certificate_template">ใบประกาศนียบัตร 1</option>
                    <option value="certificate_template1">ใบประกาศนียบัตร 2</option>
                </select>

                <label for="imageInput">เลือกไฟล์ภาพใหม่:</label>
                <label for="imageInput" class="custom-file-upload">
                    <i class="fas fa-folder-open"></i> เลือกไฟล์รูปภาพ
                </label>
                <input type="file" name="image" id="imageInput" accept=".jpg,image/jpeg" required>
                <span id="file-name">ยังไม่ได้เลือกไฟล์</span>

                <div id="previewContainer" style="display: none;">
                    <label>ตัวอย่างไฟล์ที่เลือก:</label>
                    <img id="preview" src="#" alt="Preview">
                </div>

                <div class="button-group">
                    <button type="submit"><i class="fas fa-cloud-upload-alt"></i> อัปโหลดและแทนที่</button>
                    <button type="button" onclick="resetForm()"><i class="fas fa-redo-alt"></i> รีเซ็ต</button>
                </div>
            </form>

            <div id="message"></div>
            <a href="../organizer_dashboard.php" class="back-link"><i class="fas fa-arrow-alt-circle-left"></i> กลับหน้าหลัก</a>
        </div>
    </div>

    <script>
        document.getElementById('imageInput').addEventListener('change', function () {
            const fileNameSpan = document.getElementById('file-name');
            if (this.files.length > 0) {
                fileNameSpan.textContent = this.files[0].name;
                previewImage(this);
            } else {
                fileNameSpan.textContent = 'ยังไม่ได้เลือกไฟล์';
                resetPreview();
            }
        });

        document.getElementById('uploadForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const fileInput = document.getElementById('imageInput');
            const targetSelect = document.getElementById('target');
            const file = fileInput.files[0];

            if (!targetSelect.value) {
                document.getElementById('message').innerHTML = '<p style="color:red;">❌ กรุณาเลือกเทมเพลตที่ต้องการแทนที่</p>';
                return;
            }

            if (!file) {
                document.getElementById('message').innerHTML = '<p style="color:red;">❌ กรุณาเลือกไฟล์รูปภาพใหม่</p>';
                return;
            }

            // ▼▼▼ (แก้ไข) เปลี่ยนเงื่อนไขการตรวจสอบไฟล์ ▼▼▼
            if (file.type !== 'image/jpeg' && file.type !== 'image/jpg') {
                document.getElementById('message').innerHTML = '<p style="color:red;">❌ กรุณาเลือกไฟล์ .jpg เท่านั้น</p>';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                document.getElementById('message').innerHTML = '<p style="color:red;">❌ ขนาดไฟล์ต้องไม่เกิน 5MB</p>';
                return;
            }

            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังอัปโหลด...';
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('target', targetSelect.value);

            fetch('upload-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const msg = document.getElementById('message');
                msg.innerHTML = data.success
                    ? `<p style="color:#27ae60; font-weight:bold;"><i class="fas fa-check-circle"></i> ${data.message}</p>`
                    : `<p style="color:#c0392b; font-weight:bold;"><i class="fas fa-times-circle"></i> ${data.message}</p>`;

                if (data.success) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('message').innerHTML =
                    '<p style="color:#c0392b; font-weight:bold;"><i class="fas fa-times-circle"></i> เกิดข้อผิดพลาดในการส่งข้อมูล</p>';
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('previewContainer');
            const file = input.files[0];

            if (file && (file.type === 'image/jpeg' || file.type === 'image/jpg')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            } else {
                resetPreview();
            }
        }

        function resetForm() {
            document.getElementById('uploadForm').reset();
            document.getElementById('file-name').textContent = 'ยังไม่ได้เลือกไฟล์';
            resetPreview();
            document.getElementById('message').innerHTML = '';
        }

        function resetPreview() {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('previewContainer');
            preview.src = '#';
            previewContainer.style.display = 'none';
        }

        document.querySelectorAll('.template-item img').forEach(img => {
            img.addEventListener('error', function() {
            });
        });
    </script>
</body>
</html>