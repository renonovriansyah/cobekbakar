<?php
// FILE: proses_login.php (Revisi untuk Plaintext Password)

session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Ambil data pengguna, kini mengambil kolom 'password' sebagai plaintext
    $sql = "SELECT id_user, username, password, nama_lengkap, role FROM user WHERE username = ?";

    if ($stmt = $conn->prepare($sql)) {
        
        $stmt->bind_param("s", $param_username);
        $param_username = $username;

        if ($stmt->execute()) {
            
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                
                $user = $result->fetch_assoc();
                
                $stored_password = $user['password'];

                // --- PERUBAHAN KRUSIAL DI SINI: Perbandingan String Langsung ---
                if ($password === $stored_password) { 
                    
                    // Password benar, mulai session
                    session_regenerate_id(true); 
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id_user"] = $user['id_user'];
                    $_SESSION["username"] = $user['username'];
                    $_SESSION["nama_lengkap"] = $user['nama_lengkap'];
                    $_SESSION["role"] = $user['role'];

                    header("location: index.php"); 
                    exit;
                } else {
                    // Password salah
                    $_SESSION['login_error'] = "Password yang Anda masukkan salah.";
                    header("location: login.php");
                    exit;
                }
            } else {
                // Username tidak ditemukan
                $_SESSION['login_error'] = "Username tidak ditemukan.";
                header("location: login.php");
                exit;
            }
        } else {
            // Kesalahan eksekusi query
            $_SESSION['login_error'] = "Terjadi kesalahan sistem saat mencari pengguna.";
            header("location: login.php");
            exit;
        }

        $stmt->close();
    }
}

$conn->close();
?>