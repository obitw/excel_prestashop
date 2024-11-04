<?php
session_start();
require 'vendor/autoload.php'; // Charger PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
    $columns = $sheet->getSheetDimension()->getColumns();
    return $columns;
}

// Appel API pour la recherche d'images
function searchImages($query) {
    $apiKey = 'VOTRE_API_KEY';
    $searchEngineId = 'VOTRE_SEARCH_ENGINE_ID';
    $url = "https://www.googleapis.com/customsearch/v1?key=$apiKey&cx=$searchEngineId&q=" . urlencode($query) . "&searchType=image&num=3";

    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return array_column($data['items'], 'link'); // Récupère les URLs des images
}

// Télécharger le fichier Excel modifié
function downloadExcelFile($spreadsheet) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="updated_file.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit;
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
        $column = $_SESSION['selectedColumn'];

        $rowCount = $sheet->getHighestRow();
        for ($row = 2; $row <= $rowCount; $row++) {
            $value = $sheet->getCellByColumnAndRow($column, $row)->getValue();
            $images = searchImages($value);
            $_SESSION['images'][$value] = $images;
        }
    }

    // Étape 5: Ajouter les URLs au fichier
    if (isset($_POST['addUrls'])) {
        $spreadsheet = IOFactory::load($_SESSION['filePath']);
        $sheet = $spreadsheet->getActiveSheet();
        $column = $sheet->getHighestColumn() + 1;

        foreach ($_SESSION['images'] as $value => $urls) {
            $row = array_search($value, $_SESSION['images']);
            $sheet->setCellValueByColumnAndRow($column, $row, $urls[0]);
        }

        downloadExcelFile($spreadsheet);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion d'Images Excel</title>
</head>
<body>

<h1>Gestion d'Images pour Fichier Excel</h1>

<!-- Formulaire d'importation -->
<form method="POST" enctype="multipart/form-data">
    <label>Importer un fichier Excel:</label>
    <input type="file" name="excelFile" accept=".xlsx,.xls">
    <button type="submit" name="upload">Télécharger</button>
</form>

<?php if (isset($_SESSION['columns'])): ?>
    <!-- Sélection de colonne -->
    <form method="POST">
        <label>Sélectionner une colonne:</label>
        <select name="column">
            <?php foreach ($_SESSION['columns'] as $colName): ?>
                <option value="<?= $colName ?>"><?= $colName ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="selectColumn">Choisir</button>
    </form>
<?php endif; ?>

<?php if (isset($_SESSION['selectedColumn'])): ?>
    <!-- Recherche d'images -->
    <form method="POST">
        <button type="submit" name="searchImages">Rechercher les images</button>
    </form>
<?php endif; ?>

<?php if (isset($_SESSION['images'])): ?>
    <!-- Sélection des images et téléchargement -->
    <form method="POST">
        <?php foreach ($_SESSION['images'] as $value => $images): ?>
            <h3><?= htmlspecialchars($value) ?></h3>
            <?php foreach ($images as $url): ?>
                <input type="radio" name="selectedImage[<?= htmlspecialchars($value) ?>]" value="<?= htmlspecialchars($url) ?>">
                <img src="<?= htmlspecialchars($url) ?>" width="100">
            <?php endforeach; ?>
        <?php endforeach; ?>
        <button type="submit" name="addUrls">Télécharger le fichier Excel modifié</button>
    </form>
<?php endif; ?>

</body>
</html>
