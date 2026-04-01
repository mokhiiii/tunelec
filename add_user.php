<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunelec";
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    if ($user && $pass && $full_name) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $user,
                password_hash($pass, PASSWORD_DEFAULT),
                $full_name,
                $role
            ]);
            $message = '<span style="color:green">Utilisateur ajouté avec succès !</span>';
        } catch (PDOException $e) {
            $message = '<span style="color:red">Erreur : ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
    } else {
        $message = '<span style="color:red">Veuillez remplir tous les champs.</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-md border border-blue-200">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Ajouter un utilisateur</h2>
        <?php if ($message) echo '<div class="mb-4">' . $message . '</div>'; ?>
        <form method="post" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                <input type="text" name="username" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <input type="password" name="password" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                <input type="text" name="full_name" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                <select name="role" class="w-full border rounded p-2">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors">Ajouter</button>
        </form>
    </div>
</body>
</html>
