<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: main.html');
    exit;
}
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Tunelec</title>
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-md border border-blue-200">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-white rounded-lg shadow-md flex items-center justify-center border-2 border-blue-200 overflow-hidden p-2">
                <img src="public/tunelec.jpg" alt="Tunelec Logo" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tunelec</h1>
                <p class="text-sm text-gray-600">Connexion sécurisée</p>
            </div>
        </div>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="login_handler.php" method="post" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'utilisateur</label>
                <input type="text" name="username" required class="w-full border rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <input type="password" name="password" required class="w-full border rounded p-2">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition-colors">Se connecter</button>
        </form>
    </div>
</body>
</html>
