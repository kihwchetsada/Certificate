# 🎓 ระบบจัดการและออกใบประกาศนียบัตร

> ระบบสำหรับสร้างและจัดการใบประกาศนียบัตรแบบอัตโนมัติ พร้อมระบบตรวจสอบความถูกต้อง

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-green)
![License](https://img.shields.io/badge/license-MIT-orange)

## 📋 ความต้องการของระบบ

| รายการ | เวอร์ชั่นขั้นต่ำ |
|--------|----------------|
| PHP | 7.4 หรือสูงกว่า |
| Database | MySQL 5.7+ / MariaDB 10+ |
| Web Server | Apache 2.4+ / Nginx 1.18+ |
| Composer | 2.0+ |

## ⚡ Quick Start

```bash
# Clone โปรเจค
git clone [URL_repository](https://github.com/kihwchetsada/Certificate.git)

# ติดตั้ง Dependencies
composer install

# ตั้งค่าสิทธิ์การเข้าถึง
chmod 755 -R assets/
chmod 755 -R certificates/
```

## 🛠️ การติดตั้ง

### 1. การตั้งค่าฐานข้อมูล

สร้างไฟล์ `.env` จาก template และกำหนดค่าการเชื่อมต่อ:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=certificate_db
```

### 2. โครงสร้างโปรเจค

```
📦 certificate-system
 ┣ 📂 assets/
 ┃ ┣ 📂 fonts/
 ┃ ┣ 📂 templates/
 ┃ ┗ 📂 css/
 ┣ 📂 certificates/
 ┣ 📂 vendor/
 ┣ 📜 backend-*.php
 ┣ 📜 generate.php
 ┗ 📜 README.md
```

## 💻 วิธีการใช้งาน

### การจัดการข้อมูล

| หน้าที่ | ไฟล์ | คำอธิบาย |
|--------|------|----------|
| หน้าหลัก | `backend-index.php` | แสดงรายการทั้งหมด |
| เพิ่มข้อมูล | `backend-submit.php` | เพิ่มผู้รับใบประกาศใหม่ |
| แก้ไข | `backend-edit.php` | แก้ไขข้อมูลผู้รับใบประกาศ |
| ลบ | `backend-delete.php` | ลบรายการ |

### 🎨 การสร้างใบประกาศ

1. เข้าสู่หน้า `generate.php`
2. เลือกรายการที่ต้องการ ▶️ กดสร้างใบประกาศ
3. ระบบจะสร้างไฟล์ PDF ในโฟลเดอร์ `certificates/`

### 🔍 การตรวจสอบใบประกาศ

```php
https://your-domain.com/check_certificate.php?code=XXXXX
```

## ⚙️ การปรับแต่ง

### การแก้ไข Template

```bash
# 1. เตรียมไฟล์ JPG
assets/
  └── templates/
      └── certificate_template.jpg

# 2. แก้ไขการอ้างอิงใน generate.php
$template = 'assets/templates/certificate_template.jpg';
```

### การเพิ่มฟอนต์

```bash
# 1. วางไฟล์ฟอนต์
assets/
  └── fonts/
      └── THSarabun.ttf

# 2. แก้ไขการอ้างอิงใน generate.php
$font = 'assets/fonts/THSarabun.ttf';
```

## ❗ การแก้ไขปัญหา

### สร้างใบประกาศไม่ได้

- ✅ ตรวจสอบสิทธิ์โฟลเดอร์ `certificates/`
- ✅ ตรวจสอบการติดตั้ง PHP GD Library
- ✅ ตรวจสอบ error_log

### อัพโหลดไฟล์ไม่ได้

```ini
; แก้ไขใน php.ini
upload_max_filesize = 10M
post_max_size = 10M
```


---
Made with ❤️ by chetsadaphon wongwiwong
