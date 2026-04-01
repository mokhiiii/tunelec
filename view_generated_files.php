<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fichiers d'audit générés - Tunelec</title>
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen p-4">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-8 pb-4 border-b">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white rounded-lg shadow-md flex items-center justify-center border-2 border-blue-200 overflow-hidden p-2">
                    <img src="public/tunelec.jpg" alt="Tunelec Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Tunelec</h1>
                    <p class="text-sm text-gray-600">Fichiers d'audit générés</p>
                </div>
            </div>
            <a href="main.html" class="text-blue-600 hover:text-blue-800 transition-colors">
                Retour au formulaire d'audit
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom du fichier</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date de création</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taille</th>
                        <th class="px-6 py-3 bg-gray-50"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $files = glob('C:/Users/Administrateur.TUNELEC/Desktop/Audits_Tunelec/*.xlsx');
                    if (empty($files)) {
                        echo '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Aucun fichier généré pour le moment.</td></tr>';
                    } else {
                        foreach ($files as $file) {
                            $filename = basename($file);
                            $created = date("d/m/Y H:i", filemtime($file));
                            $size = round(filesize($file) / 1024, 2); // Convert to KB
                            
                            echo '<tr class="hover:bg-gray-50">';
                            echo '<td class="px-6 py-4 text-sm text-gray-900">' . htmlspecialchars($filename) . '</td>';
                            echo '<td class="px-6 py-4 text-sm text-gray-500">' . $created . '</td>';
                            echo '<td class="px-6 py-4 text-sm text-gray-500">' . $size . ' KB</td>';
                            echo '<td class="px-6 py-4 text-right text-sm font-medium">';
                            echo '<a href="download_file.php?file=' . urlencode($filename) . '" class="text-blue-600 hover:text-blue-900">Télécharger</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
