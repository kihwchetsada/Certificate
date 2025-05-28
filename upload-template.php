<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดภาพ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .upload-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            color: #555;
        }
        select, input[type="file"] {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
        }
        button[type="submit"]:hover {
            background-color: #45a049;
        }
        button[type="button"] {
            background-color: #f44336;
            color: white;
        }
        button[type="button"]:hover {
            background-color: #da190b;
        }
        #preview {
            max-width: 100%;
            height: auto;
            border: 2px solid #ddd;
            border-radius: 5px;
            padding: 5px;
            background: white;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .back-link:hover {
            background-color: #0b7dda;
        }
        #message {
            margin-top: 15px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <h2>อัปโหลดเทมเพลตใบประกาศนียบัตร (ไม่เกิน 2MB, .jpg)</h2>
        <form id="uploadForm">
            <label for="target">เลือกเทมเพลต:</label>
            <select name="target" id="target" required>
                <option value="">-- เลือกประเภท --</option>
                <option value="certificate_template">เทมเพลตใบประกาศนียบัตร 1 (certificate_template.jpg)</option>
                <option value="certificate_template1">เทมเพลตใบประกาศนียบัตร 2 (certificate_template1.jpg)</option>
            </select>

            <label for="imageInput">เลือกไฟล์ภาพ:</label>
            <input type="file" name="image" id="imageInput" accept=".jpg,image/jpeg" required>

            <div id="previewContainer" style="display: none;">
                <label>ตัวอย่างภาพ:</label>
                <img id="preview" src="#" alt="Preview">
            </div>

            <div class="button-group">
                <button type="submit">อัปโหลดและแทนที่</button>
                <button type="button" onclick="resetPreview()">รีเซต</button>
            </div>
        </form>

        <div id="message"></div>
        <a href="../organizer_dashboard.php" class="back-link">กลับหน้าหลัก</a>
    </div>

    <script>
        document.getElementById('imageInput').addEventListener('change', function () {
            previewImage(this);
        });

        document.getElementById('uploadForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const fileInput = document.getElementById('imageInput');
            const targetSelect = document.getElementById('target');
            const file = fileInput.files[0];

            // ตรวจสอบการเลือกเทมเพลต
            if (!targetSelect.value) {
                alert('กรุณาเลือกเทมเพลต');
                return;
            }

            // ตรวจสอบไฟล์
            if (!file) {
                alert('กรุณาเลือกไฟล์');
                return;
            }

            if (file.type !== 'image/jpeg') {
                alert('กรุณาเลือกไฟล์ .jpg เท่านั้น');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('❌ ขนาดไฟล์ต้องไม่เกิน 2MB');
                return;
            }

            // แสดงสถานะการอัปโหลด
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'กำลังอัปโหลด...';
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
                    ? `<p style="color:green; font-weight:bold;">✅ ${data.message}</p>`
                    : `<p style="color:red; font-weight:bold;">❌ ${data.message}</p>`;
                
                // รีเซตฟอร์มหากสำเร็จ
                if (data.success) {
                    setTimeout(() => {
                        resetPreview();
                    }, 2000);
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('message').innerHTML = 
                    '<p style="color:red; font-weight:bold;">❌ เกิดข้อผิดพลาดในการส่งข้อมูล</p>';
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });

        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('previewContainer');
            const file = input.files[0];

            if (file && file.type === 'image/jpeg') {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                resetPreview();
                if (file) {
                    alert("กรุณาเลือกไฟล์ .jpg เท่านั้น");
                }
            }
        }

        function resetPreview() {
            document.getElementById('imageInput').value = '';
            document.getElementById('target').value = '';
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('previewContainer');
            preview.src = '#';
            previewContainer.style.display = 'none';
            document.getElementById('message').innerHTML = '';
        }
    </script>
</body>
</html>