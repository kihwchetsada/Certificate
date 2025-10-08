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
    <link rel="icon" type="image/png" href="../../img/logo.jpg">
    <link rel="stylesheet" href="css/upload-template.css">
</head>
<body>

    <div class="main-wrapper">
        <div class="container">
            <h2><i class="fas fa-image"></i> เทมเพลตปัจจุบัน</h2>
            <div class="template-item">
                <p>ใบประกาศนียบัตรหลัก</p>
                <img src="assets/certificate_template.jpg" alt="Template Preview" onerror="this.onerror=null;this.src=''; this.outerHTML='<div class=&quot;placeholder-image&quot;><i class=&quot;fas fa-exclamation-triangle&quot;></i> ไม่พบรูปภาพ</div>';">
            </div>
        </div>
        <div class="container">
            <h2><i class="fas fa-upload"></i> อัปโหลดเพื่อแทนที่เทมเพลต</h2>
            <p style="text-align: center; color: var(--text-color); margin-top: -15px; margin-bottom: 25px;">(ไฟล์ .jpg เท่านั้น, ขนาดไม่เกิน 2MB)</p>
            <form id="uploadForm">
                
                <label for="imageInput" class="custom-file-upload">
                    <i class="fas fa-folder-open"></i> เลือกไฟล์รูปภาพ
                </label>
                <input type="file" name="image" id="imageInput" accept=".jpg,image/jpeg" required>
                <span id="file-name">ยังไม่ได้เลือกไฟล์</span>

                <div id="previewContainer">
                    <img id="preview" src="#" alt="Preview">
                </div>

                <button type="submit"><i class="fas fa-cloud-upload-alt"></i> อัปโหลดและแทนที่</button>
            </form>

            <div id="message"></div>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-alt-circle-left"></i> กลับหน้าหลัก</a>
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
            const file = fileInput.files[0];

            if (!file) {
                document.getElementById('message').innerHTML = '<p style="color:var(--danger-color);">❌ กรุณาเลือกไฟล์รูปภาพใหม่</p>';
                return;
            }

            if (file.type !== 'image/jpeg') {
                document.getElementById('message').innerHTML = '<p style="color:var(--danger-color);">❌ กรุณาเลือกไฟล์ .jpg เท่านั้น</p>';
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                document.getElementById('message').innerHTML = '<p style="color:var(--danger-color);">❌ ขนาดไฟล์ต้องไม่เกิน 2MB</p>';
                return;
            }

            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังอัปโหลด...';
            submitBtn.disabled = true;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('target', 'certificate_template');

            fetch('upload-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const msg = document.getElementById('message');
                msg.innerHTML = data.success
                    ? `<p style="color:var(--success-color);"><i class="fas fa-check-circle"></i> ${data.message}</p>`
                    : `<p style="color:var(--danger-color);"><i class="fas fa-times-circle"></i> ${data.message}</p>`;

                if (data.success) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('message').innerHTML =
                    '<p style="color:var(--danger-color);"><i class="fas fa-exclamation-triangle"></i> เกิดข้อผิดพลาดในการส่งข้อมูล</p>';
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

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
        
        function resetPreview() {
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('preview').setAttribute('src', '#');
        }
    </script>
</body>
</html>