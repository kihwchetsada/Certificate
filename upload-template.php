<h2>อัปโหลดภาพ (ไม่เกิน 2MB, .jpg)</h2>
<form id="uploadForm">
    <label>เลือกประเภท:</label>
    <select name="target" id="target" required>
        <option value="logo">โลโก้ (logo.jpg)</option>
        <option value="banner">แบนเนอร์ (banner.jpg)</option>
        <option value="background">พื้นหลัง (background.jpg)</option>
    </select><br><br>

    <input type="file" name="image" id="imageInput" accept=".jpg" required><br><br>

    <img id="preview" src="#" alt="Preview" style="max-width: 400px; display: none; border: 1px solid #ccc; padding: 5px;"><br><br>

    <button type="submit">อัปโหลดและแทนที่</button>
    <button type="button" onclick="resetPreview()">รีเซต</button>
</form>

<div id="message"></div>
<a href="../organizer_dashboard.php">กลับหน้าหลัก</a>

<script>
document.getElementById('imageInput').addEventListener('change', function () {
    previewImage(this);
});

document.getElementById('uploadForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const fileInput = document.getElementById('imageInput');
    const file = fileInput.files[0];

    if (!file || file.type !== 'image/jpeg') {
        alert('กรุณาเลือกไฟล์ .jpg เท่านั้น');
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        alert('❌ ขนาดไฟล์ต้องไม่เกิน 2MB');
        return;
    }

    const formData = new FormData();
    formData.append('image', file);
    formData.append('target', document.getElementById('target').value);

    fetch('upload-handler.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById('message');
        msg.innerHTML = data.success 
            ? `<p style="color:green;">✅ ${data.message}</p>`
            : `<p style="color:red;">❌ ${data.message}</p>`;
    })
    .catch(err => {
        console.error(err);
        alert("เกิดข้อผิดพลาดในการส่งข้อมูล");
    });
});

function previewImage(input) {
    const preview = document.getElementById('preview');
    const file = input.files[0];

    if (file && file.type === 'image/jpeg') {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        resetPreview();
        alert("กรุณาเลือกไฟล์ .jpg เท่านั้น");
    }
}

function resetPreview() {
    document.getElementById('imageInput').value = '';
    const preview = document.getElementById('preview');
    preview.src = '#';
    preview.style.display = 'none';
    document.getElementById('message').innerHTML = '';
}
</script>
