<?php
header('Content-Type: application/json');
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
$proxyDir = __DIR__ . '/uploads/proxy_images/';
$localhost = getBaseUrl();
$projectName = basename(__DIR__);

if (!is_dir($proxyDir)) {
    mkdir($proxyDir, 0777, true);
}

if (isset($_FILES['customImage']) && isset($_POST['productName'])) {
    $file = $_FILES['customImage'];
    $fileName = basename($file['name']);
    $proxyPath = $proxyDir . $fileName;

    // Déplacer le fichier vers le répertoire proxy_images
    if (move_uploaded_file($file['tmp_name'], $proxyPath)) {
        $imageUrl = $localhost . '/' . $projectName . '/uploads/proxy_images/' . $fileName;
        echo json_encode(['success' => true, 'imageUrl' => $imageUrl]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Échec de la copie de l\'image.']);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Aucun fichier reçu ou données manquantes.']);
exit;
?>
