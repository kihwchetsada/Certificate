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
   <link rel="stylesheet" href="../../css/dashboard.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
      .certificate-container {
         max-width: 600px;
         margin: 40px auto;
         background: white;
         border-radius: 10px;
         padding: 25px;
         box-shadow: 0 4px 10px rgba(0,0,0,0.1);
         text-align: center;
      }
      .certificate-container h3 { margin-bottom: 15px; }
      .btn-search {
         background-color: #3498db;
         color: white;
         border: none;
         padding: 10px 20px;
         border-radius: 6px;
         transition: 0.2s;
      }
      .btn-search:hover { background-color: #2980b9; }
   </style>
</head>
<body>
   <!-- ✅ เมนูเหมือน dashboard -->
   <div class="sidebar" id="sidebar">
      <div class="sidebar-header"><div class="logo"><i class="fas fa-trophy"></i><span>ROV Tournament</span></div></div>
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
      <div class="top-navbar">
         <button class="mobile-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
         <div class="user-menu">
            <div class="user-info">
               <span><?php echo htmlspecialchars($_SESSION['conn']['username']); ?></span>
               <div class="user-avatar"><i class="fas fa-user"></i></div>
            </div>
         </div>
      </div>

      <!-- ✅ เนื้อหาหลัก -->
      <div class="dashboard-container">
         <div class="certificate-container">
            <h3>ระบบค้นหารายชื่อเกียรติบัตร</h3>
            <h4>The <?php echo date('Y') - 2020; ?><sup>th</sup> SURIN RMUTI E-SPORT <?php echo date('Y'); ?></h4>
            <form id="certificateForm">
               <div class="mb-4">
                  <label class="form-label">ชื่อผู้รับเกียรติบัตร</label>
                  <input type="text" name="name" class="form-control" placeholder="กรุณากรอกชื่อ-นามสกุล" required>
               </div>
               <button type="submit" class="btn-search w-100">ค้นหาเกียรติบัตร</button>
               <a href="list.php" class="btn-search w-100 mt-2">ดูรายชื่อ</a>
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
      sidebarToggle.addEventListener('click', e => {
         e.stopPropagation();
         sidebar.classList.toggle('sidebar-active');
      });
      mainContent.addEventListener('click', () => {
         if (sidebar.classList.contains('sidebar-active')) sidebar.classList.remove('sidebar-active');
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
