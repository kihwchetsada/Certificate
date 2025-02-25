<!DOCTYPE html>
<html lang="th">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>ค้นหาเกียรติบัตร</title>
   <link rel="icon" type="image/png" href="img/b.png">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
   <link rel="stylesheet" href="css/index.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
   <div class="container">
      <h3 class="title-heading">ระบบค้นหารายชื่อเกียรติบัตร</h3>
      <h2 class="title-body">The 5<sup>th</sup> SURIN RMUTI E-SPORT 2025</h2><br>
      <div class="card shadow p-4">
         <form id="certificateForm">
            <div class="mb-4">
               <label class="form-label">ชื่อผู้รับเกียรติบัตร</label>
               <input type="text" name="name" class="form-control" placeholder="กรุณากรอกชื่อ-นามสกุล" required>
            </div>
            <button type="submit" class="btn btn-search w-100">
               <i class="fas fa-search me-2"></i>ค้นหาเกียรติบัตร
            </button>
            <a href="list.php" class="btn btn-search w-100 mt-2">
            <i class="fas fa-list me-2"></i>ดูรายชื่อ
            </a>
         </form>
      </div>
   </div>

   <script>
      $(document).ready(function() {
    $('#certificateForm').submit(function(event) {
        event.preventDefault();

        var name = $("input[name='name']").val().trim();

        // ตรวจสอบว่ากรอกชื่อหรือไม่
        if (!name) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกชื่อ',
                text: 'โปรดระบุชื่อ-นามสกุลเพื่อค้นหาเกียรติบัตร',
                confirmButtonText: 'เข้าใจแล้ว',
                confirmButtonColor: '#4a90e2'
            });
            return;
        }

        $('button[type="submit"]').addClass('loading').prop('disabled', true);

        // ส่งข้อมูลไปยัง check_certificate.php
        $.ajax({
            url: 'check_certificate.php',
            type: 'POST',
            data: { name: name },  // ส่งชื่อไปเป็นข้อมูล
            success: function(response) {
                if (response === "found") {
                    Swal.fire({
                        icon: 'success',
                        title: 'พบข้อมูลเกียรติบัตร',
                        text: 'กำลังจัดเตรียมเอกสารของคุณ...',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = "generate.php?name=" + encodeURIComponent(name);
                    });
                } else if (response === "not found") {
                    Swal.fire({
                        icon: 'error',
                        title: 'ไม่พบข้อมูล',
                        text: 'ไม่พบรายชื่อในระบบ กรุณาตรวจสอบการสะกดชื่อ-นามสกุลอีกครั้ง',
                        confirmButtonText: 'ลองใหม่',
                        confirmButtonColor: '#4a90e2'
                    });
                } else if (response === "empty name") {
                    Swal.fire({
                        icon: 'error',
                        title: 'กรุณากรอกชื่อ',
                        text: 'ชื่อไม่สามารถเป็นค่าว่างได้',
                        confirmButtonText: 'ปิด',
                        confirmButtonColor: '#4a90e2'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่ภายหลัง',
                        confirmButtonText: 'ปิด',
                        confirmButtonColor: '#4a90e2'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่ภายหลัง',
                    confirmButtonText: 'ปิด',
                    confirmButtonColor: '#4a90e2'
                });
            },
            complete: function() {
                $('button[type="submit"]').removeClass('loading').prop('disabled', false);
            }
        });
    });
});

   </script>
</body>
</html>
