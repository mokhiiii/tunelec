<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Question Images</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen p-8">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold mb-8 text-center text-gray-800">Upload Question Images</h1>
        
        <?php
        // Display any messages from the URL parameters
        if (isset($_GET['success'])) {
            echo '<div class="mb-4 p-4 bg-green-100 text-green-700 rounded">' . htmlspecialchars($_GET['success']) . '</div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="mb-4 p-4 bg-red-100 text-red-700 rounded">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>
        
        <form action="upload_question_image.php" method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Question:</label>
                <select name="question_id" required class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select a question...</option>
                    <option value="1">Question 1: Les risques de sécurité sont identifiés</option>
                    <option value="2">Question 2: La matrice d'utilisation des EPI</option>
                    <option value="3">Question 3: Les instructions de travail</option>
                    <option value="4">Question 4: Les fiches suiveuses</option>
                    <option value="5">Question 5: Les instruction d'Ok démarrage</option>
                    <option value="6">Question 6: Le document d'Ok démarrage</option>
                    <option value="7">Question 7: Le tableau QCDM</option>
                    <option value="8">Question 8: Ouverture d'un plan d'action</option>
                    <option value="9">Question 9: Disponibilité de bac rouge</option>
                    <option value="10">Question 10: Tous piéces NC est identifié</option>
                    <option value="11">Question 11: Chaque NC fait l'objet d'un QRQC</option>
                    <option value="12">Question 12: La déclaration des rebuts</option>
                    <option value="13">Question 13: Travail pièce par pièce</option>
                    <option value="14">Question 14: Zonning de la zone est respecté</option>
                    <option value="15">Question 15: Pas de mélange de pièce</option>
                    <option value="16">Question 16: Le document Grille de Polyvalence</option>
                    <option value="17">Question 17: Les fiches d'habilitation</option>
                    <option value="18">Question 18: Aucun opérateur non habilité</option>
                    <option value="19">Question 19: Etiquette Maint Niv 02</option>
                    <option value="20">Question 20: Fiche intervention maintenance</option>
                    <option value="21">Question 21: Pas de problème maintenance</option>
                    <option value="22">Question 22: Pas de fuite d'huile</option>
                    <option value="23">Question 23: Les encours sont stockés</option>
                    <option value="24">Question 24: Existance d'étiquettes d'identification</option>
                    <option value="25">Question 25: A la fin de ligne, tous les cartons PF</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Image File:</label>
                <input type="file" name="image" accept="image/*" required class="w-full p-2 border rounded">
            </div>
            
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition duration-200">
                Upload Image
            </button>
        </form>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Uploaded Images:</h2>
            <div class="grid grid-cols-1 gap-4">
                <?php
                try {
                    $conn = new PDO("mysql:host=localhost;dbname=tunelec", "root", "");
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $stmt = $conn->query("SELECT question_id FROM question_images ORDER BY question_id");
                    $found = false;
                    
                    while ($row = $stmt->fetch()) {
                        $found = true;
                        echo "<div class='p-4 bg-gray-100 rounded flex justify-between items-center hover:bg-gray-200 transition duration-200'>";
                        echo "<div>";
                        $questionId = htmlspecialchars($row['question_id']);
                        echo "<span class='font-medium'>Question " . $questionId . "</span>";
                        echo " - <a href='get_question_image.php?question_id=" . $questionId . "' target='_blank' class='text-blue-500 hover:text-blue-700'>View Image</a>";
                        echo "</div>";
                        echo "<form action='delete_question_image.php' method='post' class='inline' onsubmit='return confirm(\"Êtes-vous sûr de vouloir supprimer cette image ?\");'>";
                        echo "<input type='hidden' name='question_id' value='" . $questionId . "'>";
                        echo "<button type='submit' class='bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-200 flex items-center gap-2'>";
                        echo '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';
                        echo "Delete</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                    
                    if (!$found) {
                        echo "<p class='text-gray-500 text-center p-4'>No images have been uploaded yet.</p>";
                    }
                    
                } catch(PDOException $e) {
                    echo "<p class='text-red-500'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <a href="main.html" class="inline-block bg-gray-500 text-white py-2 px-6 rounded hover:bg-gray-600 transition duration-200">
                Return to Audit Form
            </a>
        </div>
    </div>
</body>
</html>
