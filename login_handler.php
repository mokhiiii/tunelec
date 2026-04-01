<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunelec";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            header('Location: main.html');
            exit;
        } else {
            header('Location: login.php?error=Identifiants invalides');
            exit;
        }
    } catch (Exception $e) {
        header('Location: login.php?error=Erreur serveur');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
