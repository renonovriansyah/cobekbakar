<?php
// FILE: login.php

session_start();

// Jika pengguna sudah login, langsung redirect ke halaman utama
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

// Ambil pesan error dari session (jika ada)
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']); // Hapus error setelah ditampilkan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir Cobek Bakar</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* CSS Khusus Halaman Login */
        body {
            background-color: #f0f4f7; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            width: 350px;
            padding: 40px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-container h2 {
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 2em;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        #error-message {
            background-color: #f8d7da; /* Warna latar belakang error */
            color: #721c24; /* Warna teks error */
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 0.9em;
            display: <?php echo empty($login_error) ? 'none' : 'block'; ?>; /* Tampilkan jika ada error */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>LOGIN KASIR</h2>
        <div id="error-message">
            <?php echo htmlspecialchars($login_error); ?>
        </div> 
        <form action="proses_login.php" method="post" autocomplete="off">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" placeholder="Username" required autocomplete="off">
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
</body>
</html>