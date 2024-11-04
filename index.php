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
    if ($filePath) {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $columns = $sheet->getSheetDimension()->getColumns();
        return $columns;
    }
    return [];
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
        } else {
            echo "Erreur : Le fichier n'a pas pu être téléchargé.";
        }
    }

    // Étape 2: Sélectionner une colonne
    if (isset($_POST['selectColumn'])) {
        $_SESSION['selectedColumn'] = $_POST['column'];
    }

    // Étape 3 et 4: Rechercher et sélectionner des images
    if (isset($_POST['searchImages'])) {
        if (isset($_SESSION['filePath'])) {
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
        } else {
            echo "Erreur : Aucun fichier chargé.";
        }
    }

    // Étape 5: Ajouter les URLs au fichier
    if (isset($_POST['addUrls'])) {
        if (isset($_SESSION['filePath'])) {
            $spreadsheet = IOFactory::load($_SESSION['filePath']);
            $sheet = $spreadsheet->getActiveSheet();
            $column = $sheet->getHighestColumn() + 1;

            foreach ($_SESSION['images'] as $value => $urls) {
                $row = array_search($value, $_SESSION['images']);
                $sheet->setCellValueByColumnAndRow($column, $row, $urls[0]);
            }

            downloadExcelFile($spreadsheet);
        } else {
            echo "Erreur : Aucun fichier chargé pour ajouter les URLs.";
        }
    }
}
?>
