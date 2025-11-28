<?php
$new_password = 'PasswordSangatKuatAnda!'; // GANTI dengan password baru yang AMAN
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
echo "Hash untuk password baru Anda: " . $hashed_password;
?>