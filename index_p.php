<?php
session_start();
if (!isset($_SESSION['conn']) || $_SESSION['conn']['role'] !== 'participant') {
    header('Location: ../../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>ค้นหาเกียรติบัตร | ระบบจัดการแข่งขัน ROV</title>
   <link rel="icon" type="image/png" href="../../img/logo.jpg">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="css/index_p.css">
   <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
      /* สไตล์สำหรับการจัดกึ่งกลางของฟอร์ม (ปรับให้เข้ากับมือถือ) */
      .certificate-container {
         max-width: 600px;
         margin: 0 auto; 
         background: rgba(255, 255, 255, 0.9);
         border-radius: 20px;
         padding: 25px;
         box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
         text-align: center;
         backdrop-filter: blur(10px);
         -webkit-backdrop-filter: blur(10px);
         width: 100%;
         box-sizing: border-box;
      }
      .certificate-container h3 { margin-bottom: 15px; }
      .btn-search {
         background: linear-gradient(45deg, #4a90e2, #8e44ad);
         color: white;
         border: none;
         padding: 14px;
         border-radius: 12px;
         transition: 0.2s;
         text-decoration: none;
         display: block;
         margin-bottom: 10px; 
      }
      .btn-search:hover { opacity: 0.95; }
      
      #certificateForm {
         display: flex;
         flex-direction: column;
         align-items: center;
      }
      .mb-4 {
         width: 100%;
      }
      .form-control {
         width: 100%;
         box-sizing: border-box;
      }
      .w-100 {
         width: 100%;
      }
      .btn-list {
         background: linear-gradient(45deg, #8e44ad, #4a90e2);
      }
   </style>
</head>
<body>
   <button class="mobile-toggle" id="sidebarToggle" aria-label="Toggle navigation">
       <i class="fas fa-bars"></i>
   </button>
   
   <div class="sidebar" id="sidebar">
      <div class="sidebar-header">
         <div class="logo"><i class="fas fa-trophy"></i><span>ROV Tournament</span></div>
      </div>
      <div class="sidebar-menu">
         <ul>
            <li><a href="../participant_dashboard.php"><i class="fas fa-home"></i><span>หน้าหลัก</span></a></li>
            <li><a href="index.php" class="active"><i class="fas fa-ranking-star"></i><span>เกียรติบัตร</span></a></li>
            <li><a href="../settings.php"><i class="fas fa-cog"></i><span>ตั้งค่า</span></a></li>
            <li><a href="../participant_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i><span>ออกจากระบบ</span></a></li>
         </ul>
      </div>
   </div>

   <div class="main-content">
      <div class="dashboard-container">
         <div class="certificate-container">
            <h1>ระบบค้นหารายชื่อเกียรติบัตร</h1>
            <h2>The <?php echo date('Y') - 2020; ?><sup>th</sup> SURIN RMUTI E-SPORT <?php echo date('Y'); ?></h2>
            <form id="certificateForm">
               <div class="mb-4">
                  <label class="form-label mt-4">ชื่อผู้รับเกียรติบัตร</label><br>
                  <input type="text" name="name" class="form-control" placeholder="กรุณากรอกชื่อ-นามสกุล" required>
               </div><br>
               <button type="submit" class="btn-search w-100 h-10">ค้นหาเกียรติบัตร</button>
               <a href="list.php" class="btn-search w-95">ดูรายชื่อทั้งหมด</a>
            </form>
         </div>
      </div>

      <div class="dashboard-footer">
         <p>&copy; <?php echo date('Y'); ?> ระบบจัดการแข่งขัน ROV.</p>
      </div>
   </div>

   <script>
      const sidebar = document.getElementById('sidebar');
      const sidebarToggle = document.getElementById('sidebarToggle');
      const mainContent = document.querySelector('.main-content');
      
      // ฟังก์ชันเปิด/ปิด sidebar เมื่อคลิกที่ปุ่ม
      sidebarToggle.addEventListener('click', e => {
         e.stopPropagation();
         sidebar.classList.toggle('sidebar-active');
      });
      
      // ฟังก์ชันปิด sidebar เมื่อคลิกที่เนื้อหาหลัก (บนมือถือ)
      mainContent.addEventListener('click', () => {
         // ตรวจสอบขนาดหน้าจอเพื่อปิดเฉพาะบนมือถือ
         if (window.innerWidth <= 768 && sidebar.classList.contains('sidebar-active')) {
             sidebar.classList.remove('sidebar-active');
         }
      });
      
      // ✅ ฟังก์ชันเดิมของการค้นหา
      $('#certificateForm').submit(function(e){
         e.preventDefault();
         var name = $("input[name='name']").val().trim();
         if(!name) {
            Swal.fire({ icon: 'warning', title: 'กรุณากรอกชื่อ' });
            return;
         }
         $.post('check_certificate.php', {name: name}, function(response){
            if(response !== "not found" && response !== "empty name" && response !== "error"){
               Swal.fire({
                  icon: 'success',
                  title: 'พบข้อมูลเกียรติบัตร',
                  timer: 1500,
                  showConfirmButton: false
               }).then(()=>window.location.href="generate.php?member_id="+response);
            } else {
               Swal.fire({ icon: 'error', title: 'ไม่พบข้อมูล', text: 'ตรวจสอบชื่ออีกครั้ง' });
            }
         });
      });
   </script>
</body>
</html>