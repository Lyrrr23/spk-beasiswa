<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logout...</title>
    <meta http-equiv="refresh" content="1;url=login.php"> <!-- Redirect ke login.php dalam 1 detik -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }
        .message {
            text-align: center;
            animation: fadeOut 1s forwards;
        }
        @keyframes fadeOut {
            0% {opacity: 1;}
            100% {opacity: 0;}
        }
    </style>
</head>
<body>
    <div class="message">
        <h1>Anda berhasil logout...</h1>
        <p>Mengarahkan ke halaman login...</p>
    </div>
</body>
</html>
