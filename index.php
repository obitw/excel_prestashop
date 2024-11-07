<?php
session_start();
require 'vendor/autoload.php'; // Charger PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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
    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

    $columns = [];
    for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $columnLetter = Coordinate::stringFromColumnIndex($col);
        $columns[] = $sheet->getCell($columnLetter . '1')->getValue(); // Récupère les valeurs des en-têtes de colonnes
    }

    return $columns;
}

// Appel API pour la recherche d'images
function searchImages($query) {
    $apiKey = 'AIzaSyBspjH5sydi8xdNc8E3PpAFMxZpuKVd5mU';//AIzaSyCGI5yxffF571JJfnEYa4nUqwv7jtc5U-8//AIzaSyC1NrI5fFAzJuPappn-09ybR8zbpILtd4g // AIzaSyCnuwZF4W5rat_nTKYmmTgjrfUBMMJleTQ // AIzaSyDilVFvhQhGrHM9XfCbp5DU2Jj9jaTYBJM
    $searchEngineId = 'e4aa36cca289940cf';
    $url = "https://www.googleapis.com/customsearch/v1?key=$apiKey&cx=$searchEngineId&q=" . urlencode($query) . "&searchType=image&num=3";

    $response = file_get_contents($url);
    $data = json_decode($response, true);
// Vérifie si des images ont été trouvées
    if (!empty($data['items'])) {
        return array_column($data['items'], 'link'); // Retourne les URLs des images
    } else {
        // Retourne l'image par défaut si aucune image n'est trouvée
        return ['data:image/png;base64,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'];
    }
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

            $images = searchImages($value);
            $_SESSION['images'][$value] = $images;
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
        session_unset();
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
    <button type="submit" name="upload">Importer</button>
</form>

<?php if (isset($_SESSION['columns'])): ?>
    <!-- Sélection de colonne -->
    <form method="POST">
        <label>Sélectionner une colonne:</label>
        <select name="column">
            <?php foreach ($_SESSION['columns'] as $index => $colName): ?>
                <option value="<?= $index + 1 ?>"><?= htmlspecialchars($colName) ?></option>
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
