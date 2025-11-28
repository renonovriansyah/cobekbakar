<?php
// FILE: login.php

session_start();

// Jika pengguna sudah login, langsung arahkan ke halaman utama
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// Ambil pesan error dari session (jika ada kegagalan login)
$login_error = $_SESSION['login_error'] ?? '';

// Hapus pesan error dari session agar tidak muncul lagi setelah refresh
if (isset($_SESSION['login_error'])) {
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir | Cobek Bakar</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        :root {
            --primary-color: #e74c3c; /* Merah */
            --secondary-color: #2c3e50; /* Biru gelap */
        }
        
        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        .login-container h2 {
            color: var(--secondary-color);
            margin-bottom: 30px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 2; 
            font-size: 1em;
            transition: color 0.1s;
        }

        .input-group i:not(.toggle-password) {
            left: 15px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 45px; /* Padding kiri disesuaikan untuk ikon */
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .toggle-password {
            right: 15px;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1em;
            transition: color 0.1s;
            z-index: 3; /* Pastikan ikon mata di atas semua elemen lain */
        }

        /* Tambahkan padding kanan pada input agar teks tidak menutupi ikon mata */
        .password-group input {
            padding-right: 40px !important; 
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }
        /* -------------------------------------- */
        
        .primary-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .primary-btn:hover {
            background-color: #c0392b;
        }
        
        #error-message {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>LOGIN KASIR</h2>
        <div id="error-message">
            <?php echo htmlspecialchars($login_error); ?>
        </div> 
        
        <form action="proses_login.php" method="post" autocomplete="off" id="login-form">
            
            <input type="text" style="display:none" autocomplete="username"> 
            <input type="password" style="display:none"> 
            
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" placeholder="Username" required autocomplete="new-username">
            </div>
            
            <div class="input-group password-group"> 
                <i class="fas fa-lock"></i> 
                
                <input type="password" id="password" name="password" placeholder="Password" required autocomplete="new-password">
                
                <i class="fas fa-eye toggle-password" id="togglePassword"></i> 
            </div>

            <button type="submit" class="primary-btn" style="width: 100%;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            // Cek apakah kedua elemen (ikon dan input) ditemukan di halaman
            if (togglePassword && passwordInput) {
                // Pasang event listener pada ikon mata
                togglePassword.addEventListener('click', function() {
                    
                    // 1. Tentukan tipe input saat ini
                    const currentType = passwordInput.getAttribute('type');
                    
                    // 2. Ganti tipe input: 'password' menjadi 'text', atau sebaliknya
                    const newType = currentType === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', newType);
                    
                    // 3. Ganti ikon: fa-eye (terbuka) menjadi fa-eye-slash (tertutup)
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>

    </body>
</html>