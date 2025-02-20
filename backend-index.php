<!DOCTYPE html> 
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนรับเกียรติบัตร</title>
    <link rel="icon" type="image/png" href="img/b.png">
    <link rel="stylesheet" href="css/backend-index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5 form-container">
        <h2 class="text-center mb-4 page-title"> ลงทะเบียนรับเกียรติบัตร</h2>
        <form action="backend-submit.php" method="POST" class="card custom-card p-4 shadow-lg" style="max-width: 500px; margin: auto; border-radius: 15px;">
            <div class="mb-4">
                <label class="form-label"> ชื่อ-นามสกุล</label>
                <input type="text" name="name" class="form-control" required placeholder="กรุณากรอกชื่อ-นามสกุล">
            </div>
            <div class="mb-4">
                <label class="form-label">หัวข้อเกียรติบัตร</label>
                <select name="detail" class="form-control" required>
                    <option value="" disabled selected>กรุณาเลือกหัวข้อเกียรติบัตร</option>
                    <option value="รางวัลชนะเลิศ">รางวัลชนะเลิศ</option>
                    <option value="รางวัลรองชนะเลิศ อันดับที่ 1">รางวัลรองชนะเลิศ อันดับที่ 1</option>
                    <option value="รางวัลรองชนะเลิศ อันดับที่ 2">รางวัลรองชนะเลิศ อันดับที่ 2</option>
                    <option value="รางวัลรองชนะเลิศ อันดับที่ 3">รางวัลรองชนะเลิศ อันดับที่ 3</option>
                    <option value="ผู้เข้าร่วมการแข่งขัน">ผู้เข้าร่วมการแข่งขัน</option>
                    <option value="ผู้ควบคุมทีม">ผู้ควบคุมทีม</option>
                    <option value="กรรมการจัดการแข่งขัน">กรรมการจัดการแข่งขัน</option>
                    <option value="ผู้เข้าร่วมจัดการแข่งขัน">ผู้เข้าร่วมจัดการแข่งขัน</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label"> วันที่เข้าร่วม</label>
                <input type="date" name="datenew" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">เลือกรุ่นของเกียรติบัตร</label>
                <select name="model" class="form-control" required>
                    <option value="รุ่นระดับมัธยมศึกษาหรืออาชีวศึกษา">แบบที่ 1 ( รุ่นระดับมัธยมศึกษาหรืออาชีวศึกษา )</option>
                    <option value="รุ่นระดับอุดมศึกษาหรือบุคคลทั่วไป">แบบที่ 2 (รุ่นระดับอุดมศึกษาหรือบุคคลทั่วไป)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mb-2"> ลงทะเบียน</button>
        </form>
        <div class="text-center mt-4">
            <a href="backend-list.php" class="btn btn-info" style="width: 250px;"> ดูรายชื่อผู้ลงทะเบียน</a>
        </div>
    </div>
</body>
</html>
