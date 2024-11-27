<?php
session_unset();
session_start();


require 'vendor/autoload.php'; // Charger PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
/*
    'AIzaSyC5ohbFIOe-heuTMRYDUUe_47O0cL496Bg',
    'AIzaSyCOHhxe7ynM9i0Jm847wlbNZIv6hq9MANA',
    'AIzaSyBif-0QbsksJ7SZwRv-bJmE8IhHGUgIAcg',
    'AIzaSyDWG0qT7XvGcqrPA1aBcgfumOdMe7ItvIk'

    'AIzaSyBspjH5sydi8xdNc8E3PpAFMxZpuKVd5mU',
    'AIzaSyCGI5yxffF571JJfnEYa4nUqwv7jtc5U-8',
    'AIzaSyC1NrI5fFAzJuPappn-09ybR8zbpILtd4g',
    'AIzaSyCnuwZF4W5rat_nTKYmmTgjrfUBMMJleTQ',
    'AIzaSyDilVFvhQhGrHM9XfCbp5DU2Jj9jaTYBJM',
    'AIzaSyCgbChwWVswQyR1GzXRivurwHlJI40P4qU',
    'AIzaSyBAk9TSqOVyzBrGgtQFIk8xRsYHD5GfAsc',
    'AIzaSyB4kSawOzLTiPCqG2ze-CwMU7EJ9P5Gx_E',

    'AIzaSyBVOFY9-pfxsusK4xvthiiBy8k9UMauE7I',
    'AIzaSyDpY3eU5ZZU7xJH1pJ00KmFSl0L8ZVsVIw',
    'AIzaSyBcD2CsB8Zs9mzqCyolEnd9Oi4aldjSpBw',
    'AIzaSyAxcJZ0He21pQ7EmNdkJlUdVIvGbW_qIAY',

    'AIzaSyD3zhsG5jwkySqMf09NxPDPBOWCMBIJ0J4',
    'AIzaSyAYDLXzy-Gwor7uRq-Puo-fOQc-3DnBdwo',
    'AIzaSyAULYhT2qON9pPIp5wSaUIbrFiyBbqvWZg',
    'AIzaSyBK_GxZSES8sJt3h0nAouM_s5q5RBbTUJA',
    'AIzaSyAxB6j6kivDpcUGuwHmgsBnGBzBr0OBpi8',
*/
$apiKeys = [
    'AIzaSyC5ohbFIOe-heuTMRYDUUe_47O0cL496Bg',
    'AIzaSyCOHhxe7ynM9i0Jm847wlbNZIv6hq9MANA',
    'AIzaSyBif-0QbsksJ7SZwRv-bJmE8IhHGUgIAcg',
    'AIzaSyDWG0qT7XvGcqrPA1aBcgfumOdMe7ItvIk',

    'AIzaSyBspjH5sydi8xdNc8E3PpAFMxZpuKVd5mU',
    'AIzaSyCGI5yxffF571JJfnEYa4nUqwv7jtc5U-8',
    'AIzaSyC1NrI5fFAzJuPappn-09ybR8zbpILtd4g',
    'AIzaSyCnuwZF4W5rat_nTKYmmTgjrfUBMMJleTQ',
    'AIzaSyDilVFvhQhGrHM9XfCbp5DU2Jj9jaTYBJM',
    'AIzaSyCgbChwWVswQyR1GzXRivurwHlJI40P4qU',
    'AIzaSyBAk9TSqOVyzBrGgtQFIk8xRsYHD5GfAsc',
    'AIzaSyB4kSawOzLTiPCqG2ze-CwMU7EJ9P5Gx_E',

    'AIzaSyBVOFY9-pfxsusK4xvthiiBy8k9UMauE7I',
    'AIzaSyDpY3eU5ZZU7xJH1pJ00KmFSl0L8ZVsVIw',
    'AIzaSyBcD2CsB8Zs9mzqCyolEnd9Oi4aldjSpBw',
    'AIzaSyAxcJZ0He21pQ7EmNdkJlUdVIvGbW_qIAY',

    'AIzaSyD3zhsG5jwkySqMf09NxPDPBOWCMBIJ0J4',
    'AIzaSyC5ohbFIOe-heuTMRYDUUe_47O0cL496Bg',
    'AIzaSyCOHhxe7ynM9i0Jm847wlbNZIv6hq9MANA',
    'AIzaSyBif-0QbsksJ7SZwRv-bJmE8IhHGUgIAcg',
    'AIzaSyDWG0qT7XvGcqrPA1aBcgfumOdMe7ItvIk'
];
function getBaseUrl() {
    $ngrokUrl = getNgrokUrl();
    if ($ngrokUrl) {
        return $ngrokUrl;
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST']; // Récupère l'hôte (ex: localhost, ngrok, etc.)
    $scriptName = dirname($_SERVER['SCRIPT_NAME']); // Récupère le chemin de base (si votre script est dans un sous-dossier)
    $scriptName = rtrim($scriptName, '/'); // Évite les slashs inutiles

    return $protocol . '://' . $host . $scriptName;
}

// Initialisez le compteur de requêtes si non défini
if (!isset($_SESSION['requestCount'])) {
    $_SESSION['requestCount'] = 0;
}
function getApiKey() {
    global $apiKeys;

    // Vérifie si une liste de clés désactivées existe dans la session
    if (!isset($_SESSION['disabledKeys'])) {
        $_SESSION['disabledKeys'] = [];
    }

    $activeKeys = array_diff_key($apiKeys, array_flip($_SESSION['disabledKeys']));

    // Si toutes les clés sont désactivées, réinitialise la liste
    if (empty($activeKeys)) {
        $_SESSION['disabledKeys'] = [];
        $activeKeys = $apiKeys;
    }

    // Détermine l'index de la clé active
    $index = intdiv($_SESSION['requestCount'], 100) % count($activeKeys);
    return array_values($activeKeys)[$index];
}

function uploadCustomImage($file, $productName) {
    $customDir = __DIR__ . '/uploads/custom/';
    $proxyDir = __DIR__ . '/uploads/proxy_images/';
    $localhost = getBaseUrl();
    $projectName = basename(__DIR__);

    // Créer les répertoires si nécessaire
    if (!is_dir($customDir)) {
        mkdir($customDir, 0777, true);
    }
    if (!is_dir($proxyDir)) {
        mkdir($proxyDir, 0777, true);
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $customPath = $customDir . $fileName;

    // Déplacer l'image téléchargée dans le dossier custom
    if (move_uploaded_file($file['tmp_name'], $customPath)) {
        // Vérifier si le fichier est une image valide
        if (!getimagesize($customPath)) {
            unlink($customPath); // Supprimer le fichier invalide
            throw new Exception("Le fichier uploadé n'est pas une image valide : {$file['name']}");
        }

        // Copier l'image dans le répertoire proxy_images
        $proxyPath = $proxyDir . $fileName;
        if (!copy($customPath, $proxyPath)) {
            throw new Exception("Impossible de copier l'image vers le proxy : {$proxyPath}");
        }

        // Retourner le chemin complet pour l'Excel
        return $localhost . '/' . $projectName . '/uploads/proxy_images/' . $fileName;
    }

    throw new Exception("Échec de l'upload de l'image : {$file['name']}");
}



function uploadExcelFile() {
    if (isset($_FILES['excelFile'])) {
        $file = $_FILES['excelFile'];
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Vérification du type de fichier
        if ($fileType == 'xlsx' || $fileType == 'xls') {
            $filePath = 'uploads/' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], $filePath);
            $_SESSION['filePath'] = $filePath;
            return $filePath;
        }
    }
    return null;
}

// Charger et lire les colonnes du fichier Excel
function loadExcelColumns($filePath) {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

    $columns = [];
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $columnLetter = Coordinate::stringFromColumnIndex($col);
        $columns[] = $sheet->getCell($columnLetter . '1')->getValue(); // Récupère les valeurs des en-têtes de colonnes
    }

    return $columns;
}
function downloadImage($url, $localDir = 'uploads/proxy_images/') {
    $localhost = getBaseUrl();
    $projectName = basename(__DIR__);

    // Vérifie si l'URL correspond à l'image par défaut
    $defaultImageUrl = createDefaultImage($localDir); // Appel pour obtenir l'URL de l'image par défaut
    if ($url === $defaultImageUrl) {
        return $defaultImageUrl; // Retourne directement l'image par défaut
    }

    $filename = md5($url) . '.' . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    $filePath = $localDir . $filename;

    if (!is_dir($localDir)) {
        mkdir($localDir, 0777, true);
    }

    if (!file_exists($filePath)) {
        $contextOptions = [
            "http" => [
                "header" => "User-Agent: Mozilla/5.0\r\n",
            ],
        ];
        $context = stream_context_create($contextOptions);
        $imageData = @file_get_contents($url, false, $context);

        if (!$imageData) {
            return $defaultImageUrl; // Retourne l'image par défaut en cas d'échec
        }

        file_put_contents($filePath, $imageData);

        if (!getimagesize($filePath)) {
            unlink($filePath); // Supprime le fichier corrompu
            return $defaultImageUrl; // Retourne l'image par défaut
        }
    }

    return $localhost . '/' . $projectName . '/' . $filePath;
}





function getNgrokUrl() {
    $ngrokApiUrl = 'http://127.0.0.1:4040/api/tunnels';
    $contextOptions = [
        "http" => [
            "header" => "User-Agent: PHP-Script\r\n",
        ],
    ];
    $context = stream_context_create($contextOptions);

    $response = @file_get_contents($ngrokApiUrl, false, $context);
    if ($response) {
        $data = json_decode($response, true);
        foreach ($data['tunnels'] as $tunnel) {
            if ($tunnel['proto'] === 'https') {
                return $tunnel['public_url'];
            }
        }
    }

    return null;
}


$defaultImageUrl = null;

function createDefaultImage($localDir = 'uploads/proxy_images/', $defaultFilename = 'default.jpg') {
    global $defaultImageUrl;

    if ($defaultImageUrl) {
        return $defaultImageUrl; // Retourne l'URL si elle a déjà été générée
    }

    $localhost = getBaseUrl();
    $projectName = basename(__DIR__);
    $filePath = $localDir . $defaultFilename;

    // Créer l'image par défaut si elle n'existe pas
    if (!file_exists($filePath)) {
        $defaultImage = imagecreatetruecolor(500, 500);
        $backgroundColor = imagecolorallocate($defaultImage, 255, 255, 255); // Blanc
        $textColor = imagecolorallocate($defaultImage, 0, 0, 0); // Noir
        imagefilledrectangle($defaultImage, 0, 0, 500, 500, $backgroundColor);
        imagestring($defaultImage, 5, 150, 230, 'No Image', $textColor);
        imagejpeg($defaultImage, $filePath);
        imagedestroy($defaultImage);
    }

    $defaultImageUrl = $localhost . '/' . $projectName . '/' . $filePath;
    return $defaultImageUrl;
}






// Appel API pour la recherche d'images
function searchImages($query) {
    $_SESSION['requestCount']++;
    $maxRetries = count($GLOBALS['apiKeys']); // Nombre maximum de clés disponibles

    for ($retry = 0; $retry < $maxRetries; $retry++) {
        $apiKey = getApiKey(); // Obtenez une clé active
        $searchEngineId = 'e4aa36cca289940cf';
        $url = "https://www.googleapis.com/customsearch/v1?key=$apiKey&cx=$searchEngineId&q=" . urlencode($query) . "&searchType=image&num=9";

        $contextOptions = [
            "http" => [
                "header" => "User-Agent: PHP-Script\r\n",
            ],
        ];
        $context = stream_context_create($contextOptions);

        $response = @file_get_contents($url, false, $context);

        if ($response !== false) {
            $data = json_decode($response, true);

            if (isset($data['error']['code']) && $data['error']['code'] == 429) {
                $_SESSION['disabledKeys'][] = array_search($apiKey, $GLOBALS['apiKeys']);
                continue;
            }

            // Conservez uniquement les URLs sans téléchargement
            $imageUrls = [];
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $imageUrls[] = $item['link'];
                }
            }

            return $imageUrls;
        } else {
            $_SESSION['disabledKeys'][] = array_search($apiKey, $GLOBALS['apiKeys']);
        }
    }

    return [createDefaultImage()]; // Retourne une liste vide si aucune image n'est trouvée
}








// Télécharger le fichier Excel modifié
function downloadExcelFile($spreadsheet) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="updated_file.xls"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Xls');
    $writer->save('php://output');
    exit;
}

// Traitement des images personnalisées avec redimensionnement
if (isset($_FILES['customImage'])) {
    foreach ($_FILES['customImage']['tmp_name'] as $productName => $tmpFilePath) {
        if (is_uploaded_file($tmpFilePath)) {
            if (isset($_FILES['customImage'][$productName])) {
                $imageUrl = uploadCustomImage($_FILES['customImage'][$productName], $productName);
                if ($imageUrl) {
                    // Enregistrer l'URL dans la session pour usage ultérieur
                    $_SESSION['customImages'][$productName] = $imageUrl;
                }
            } else {
                // Gestion du cas où l'image n'est pas présente
                error_log("Aucune image uploadée pour le produit : $productName");
            }


        }
    }
}


// Ajouter les URLs dans le fichier Excel
if (isset($_POST['addUrls'])) {
    $spreadsheet = IOFactory::load($_SESSION['filePath']);
    $sheet = $spreadsheet->getActiveSheet();

    // Déterminez la nouvelle colonne pour les URLs
    $highestColumn = $sheet->getHighestColumn();
    $newColumnIndex = Coordinate::columnIndexFromString($highestColumn) + 1;
    $newColumnLetter = Coordinate::stringFromColumnIndex($newColumnIndex);

    // Ajoutez le titre de colonne pour les URLs
    $sheet->setCellValue("{$newColumnLetter}1", "Image URL");

    $selectedColumnLetter = Coordinate::stringFromColumnIndex($_SESSION['selectedColumn']);
    $rowCount = $sheet->getHighestRow();

    foreach ($_POST['selectedImage'] as $value => $imageUrl) {
        for ($row = 2; $row <= $rowCount; $row++) {
            $cellReference = $selectedColumnLetter . $row;
            $cellValue = $sheet->getCell($cellReference)->getValue();

            if ($cellValue === $value) {
                $newCellReference = "{$newColumnLetter}{$row}";

                // Téléchargez l'image au moment de l'ajout dans l'Excel
                try {
                    $localPath = downloadImage($imageUrl);
                    $sheet->setCellValue($newCellReference, $localPath);
                } catch (Exception $e) {
                }
                break;
            }
        }
    }

    downloadExcelFile($spreadsheet);
}






// Traitement des différentes étapes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Étape 1: Importer le fichier
    if (isset($_POST['upload'])) {
        $filePath = uploadExcelFile();
        if ($filePath) {
            $_SESSION['columns'] = loadExcelColumns($filePath);
        }
    }

    // Étape 2: Sélectionner une colonne
    if (isset($_POST['selectColumn'])) {
        $_SESSION['selectedColumn'] = $_POST['column'];
    }

    // Étape 3 et 4: Rechercher et sélectionner des images
    if (isset($_POST['searchImages'])) {
        $filePath = $_SESSION['filePath'];
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $columnIndex = $_SESSION['selectedColumn']; // Index de la colonne sélectionnée

        $rowCount = $sheet->getHighestRow();
        for ($row = 2; $row <= $rowCount; $row++) {
            // Convertit l'index de colonne en lettre de colonne
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            // Récupère la valeur de la cellule (par exemple, A2, B2, etc.)
            $value = $sheet->getCell($columnLetter . $row)->getValue();

            if ($value === null) {
                break; // Sort de la boucle si aucune image n'est trouvée
            }
            // Recherche les images
            $images = searchImages($value);

            // Si aucune image n'est trouvée, on arrête le processus
            if ($images === null) {
                break; // Sort de la boucle si aucune image n'est trouvée
            }

            // Enregistre les images dans la session si elles existent
            $_SESSION['images'][$value] = $images;
        }
    }

// Traitement des images personnalisées
    if (isset($_FILES['customImage'])) {
        foreach ($_FILES['customImage']['tmp_name'] as $productName => $tmpFilePath) {
            // Vérifie si un fichier a bien été téléversé pour ce produit
            if (is_uploaded_file($tmpFilePath)) {
                // Chemin absolu pour le stockage du fichier
                $uploadDir = __DIR__ . '/uploads/custom/';
                $fileName = uniqid() . '_' . basename($_FILES['customImage']['name'][$productName]);
                $filePath = $uploadDir . $fileName;

                // URL publique pour accéder à l'image
                $imageUrl = '/uploads/custom/' . $fileName;

                // Crée le dossier de téléversement s'il n'existe pas
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Déplace l'image téléchargée vers le dossier de téléversement
                if (move_uploaded_file($tmpFilePath, $filePath)) {
                    // Enregistre l'URL de l'image dans la session
                    $_SESSION['customImages'][$productName] = $imageUrl;
                }
            }
        }
    }



    // Étape 5: Ajouter les URLs au fichier
    if (isset($_POST['addUrls'])) {
        $spreadsheet = IOFactory::load($_SESSION['filePath']);
        $sheet = $spreadsheet->getActiveSheet();

        // Determine the new column letter for URLs
        $highestColumn = $sheet->getHighestColumn();
        $newColumnIndex = Coordinate::columnIndexFromString($highestColumn) + 1;
        $newColumnLetter = Coordinate::stringFromColumnIndex($newColumnIndex);

        // Add "Image URL" as the header for the new column
        $sheet->setCellValue("{$newColumnLetter}1", "Image URL");

        // Loop through rows to add selected URLs
        $selectedColumnLetter = Coordinate::stringFromColumnIndex($_SESSION['selectedColumn']);
        $rowCount = $sheet->getHighestRow();

        foreach ($_POST['selectedImage'] as $value => $selectedUrl) {
            for ($row = 2; $row <= $rowCount; $row++) {
                $cellReference = $selectedColumnLetter . $row;
                $cellValue = $sheet->getCell($cellReference)->getValue();

                // If the cell value matches, add the selected image URL in the new column
                if ($cellValue === $value) {
                    $newCellReference = "{$newColumnLetter}{$row}";
                    $sheet->setCellValue($newCellReference, $selectedUrl);
                    break;
                }
            }
        }

        downloadExcelFile($spreadsheet);
    }


}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion d'Images Excel</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-5">Gestion d'Images pour Fichier Excel</h1>

    <!-- Formulaire d'importation -->
    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="form-group">
            <label for="excelFile">Importer un fichier Excel:</label>
            <input type="file" name="excelFile" id="excelFile" accept=".xlsx,.xls" class="form-control-file">
        </div>
        <button type="submit" name="upload" class="btn btn-primary">Importer</button>
    </form>

    <?php if (isset($_SESSION['columns'])): ?>
        <!-- Sélection de colonne -->
        <form method="POST" class="mb-4">
            <div class="form-group">
                <label for="column">Sélectionner une colonne:</label>
                <select name="column" id="column" class="form-control">
                    <?php foreach ($_SESSION['columns'] as $index => $colName): ?>
                        <option value="<?= $index + 1 ?>"><?= htmlspecialchars($colName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="selectColumn" class="btn btn-success">Choisir</button>
        </form>
    <?php endif; ?>

    <?php if (isset($_SESSION['selectedColumn'])): ?>
        <!-- Recherche d'images -->
        <form method="POST" class="mb-4">
            <button type="submit" name="searchImages" class="btn btn-info">Rechercher les images</button>
        </form>
    <?php endif; ?>

    <?php if (isset($_SESSION['images'])): ?>
        <!-- Formulaire pour sélectionner les images et télécharger le fichier Excel -->
        <form method="POST" enctype="multipart/form-data">
            <?php foreach ($_SESSION['images'] as $value => $images): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><?= htmlspecialchars($value) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Afficher les 3 premières images seulement -->
                            <?php
                            if (empty($images)) {
                                $images = [createDefaultImage()];
                            }
                            foreach (array_slice($images, 0, 3) as $index => $url): ?>
                                <div class="col-4 text-center mb-3">
                                    <input type="radio" name="selectedImage[<?= htmlspecialchars($value) ?>]" value="<?= htmlspecialchars($url) ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                    <img src="<?= htmlspecialchars($url) ?>" width="100" class="img-thumbnail">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Conteneur pour les 10 images supplémentaires (masqué par défaut) -->
                        <div class="row additional-images" style="display: none;">
                            <?php foreach (array_slice($images, 3, 10) as $url): ?>
                                <div class="col-4 text-center mb-3">
                                    <input type="radio" name="selectedImage[<?= htmlspecialchars($value) ?>]" value="<?= htmlspecialchars($url) ?>">
                                    <img src="<?= htmlspecialchars($url) ?>" width="100" class="img-thumbnail">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Bouton pour afficher les 10 images supplémentaires -->
                        <button type="button" class="btn btn-secondary show-more" onclick="showMoreImages(this)">Voir plus</button>

                        <!-- Field for adding a custom image -->
                        <div class="mt-3">
                            <label for="customImage_<?= htmlspecialchars($value) ?>">Ajouter une image personnalisée :</label>
                            <input
                                    type="file"
                                    name="customImage[<?= htmlspecialchars($value) ?>]"
                                    id="customImage_<?= htmlspecialchars($value) ?>"
                                    accept="image/*"
                                    class="form-control-file"
                                    onchange="previewCustomImage(this, '<?= htmlspecialchars($value) ?>')"
                            >
                        </div>

                        <!-- Container for the custom image preview -->
                        <div class="custom-image-preview mt-3" id="customPreview_<?= htmlspecialchars($value) ?>" style="display: none;">
                            <div class="col-4 text-center mb-3">
                                <input
                                        type="radio"
                                        name="selectedImage[<?= htmlspecialchars($value) ?>]"
                                        id="customRadio_<?= htmlspecialchars($value) ?>"
                                        value=""
                                >
                                <img
                                        src=""
                                        id="customImagePreview_<?= htmlspecialchars($value) ?>"
                                        width="100"
                                        class="img-thumbnail"
                                >
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit" name="addUrls" class="btn btn-danger">Télécharger le fichier Excel modifié</button>
        </form>
    <?php endif; ?>

    <script>
        // Fonction pour afficher les images supplémentaires
        function showMoreImages(button) {
            const additionalImages = button.parentElement.querySelector('.additional-images');
            additionalImages.style.display = 'flex';
            button.style.display = 'none';
        }

        // Fonction pour prévisualiser l'image personnalisée
        function previewCustomImage(input, productId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const customPreview = document.getElementById('customPreview_' + productId);
                        const customImagePreview = document.getElementById('customImagePreview_' + productId);
                        const customRadio = document.getElementById('customRadio_' + productId);

                        customImagePreview.src = e.target.result;
                        customRadio.value = `/uploads/proxy_images/${file.name}`; // Chemin prévu pour proxy_images
                        customPreview.style.display = 'block';

                        // Envoyer l'image au serveur pour la copier dans proxy_images
                        const formData = new FormData();
                        formData.append('customImage', file);
                        formData.append('productName', productId);

                        fetch('upload_proxy_image.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    console.log('Image copiée dans proxy_images avec succès.');
                                    customRadio.value = data.imageUrl; // Mettre à jour la valeur du bouton radio
                                } else {
                                    console.error('Erreur lors de la copie de l\'image:', data.error);
                                }
                            })
                            .catch(error => console.error('Erreur AJAX:', error));
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }






    </script>



</div>

<!-- Lien vers Bootstrap JS et dépendances -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
