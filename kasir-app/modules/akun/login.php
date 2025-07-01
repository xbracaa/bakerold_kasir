<?php
session_start();
include("../../config/db.php");

$error = "";

// Ini buat proses login pas form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Ini buat validasi username & password diisi
    if (empty($username) || empty($password)) {
        $error = "Isi semua dong!";
    } else {
        $stmt = $koneksi->prepare("SELECT id_kasir, nama_kasir FROM kasir WHERE username = ? AND password = ?");

        if (!$stmt) {
            $error = "Query gagal disiapin: " . $koneksi->error;
        } else {
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                // Kalo login berhasil
                $data = $result->fetch_assoc();
                $_SESSION['id_kasir'] = $data['id_kasir'];
                $_SESSION['nama_kasir'] = $data['nama_kasir'];
                header("Location: ../home.php");
                exit;
            } else {
                // Kalo login gagal
                $error = "Username atau password salah nih!";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir - Baker Old</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Tampilan */
        :root {
            --color-primary-brown: #5A3F2B;
            --color-secondary-brown: #8B6F5A;
            --color-light-brown: #D4B29A;
            --color-cream: #FFF8E1;
            --color-yellow-accent: #FFD54F;
            --color-dark-yellow-accent: #FFA000;
            --color-text-dark: #333;
            --color-text-light: #fff;
            --color-error-red: #EF5350;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--color-cream);
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-box {
            background-color: var(--color-text-light);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 380px;
            text-align: center;
            border: 2px solid var(--color-light-brown);
        }

        h2 {
            color: var(--color-primary-brown);
            font-size: 2.2em;
            margin-bottom: 25px;
            font-family: 'Georgia', serif;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--color-secondary-brown);
            font-size: 0.95em;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--color-light-brown);
            border-radius: 8px;
            font-size: 1em;
            color: var(--color-text-dark);
            background-color: var(--color-cream);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--color-secondary-brown);
            box-shadow: 0 0 0 3px rgba(139, 111, 90, 0.2);
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: var(--color-yellow-accent);
            color: var(--color-primary-brown);
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(255, 213, 79, 0.4);
        }

        button[type="submit"]:hover {
            background-color: var(--color-dark-yellow-accent);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 213, 79, 0.6);
        }

        .error {
            color: var(--color-error-red);
            background-color: rgba(239, 83, 80, 0.1);
            border: 1px solid var(--color-error-red);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <i class="fas fa-cash-register text-primary-brown" style="font-size: 3.5em; margin-bottom: 15px;"></i>
        <h2>Login Kasir</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="username"><i class="fas fa-user mr-2"></i>Username:</label>
                <input type="text" id="username" name="username" required autocomplete="username" />
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock mr-2"></i>Password:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" />
            </div>
            <button type="submit">Masuk</button>
        </form>
    </div>

</body>
</html>