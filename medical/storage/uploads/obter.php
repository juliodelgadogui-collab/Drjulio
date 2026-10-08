<?php require __DIR__.'/../../api/bootstrap.php';
auth(); // Apenas médicos logados

$file = $_GET['f'] ?? '';
$basename = basename($file);
$path = __DIR__ . '/' . $basename;

// Verifica se o arquivo pertence ao médico logado
$q = db()->prepare('SELECT id FROM medicos WHERE id=? AND (logo_path LIKE ? OR assinatura_path LIKE ?)');
$q->execute([uid(), "%$basename%", "%$basename%"]);
if(!$q->fetchColumn()) {
    http_response_code(403);
    die('Acesso negado.');
}

if(file_exists($path) && is_readable($path)) {
    $mime = mime_content_type($path);
    header('Content-Type: ' . $mime);
    readfile($path);
    die();
}
http_response_code(404);
die('Arquivo não encontrado.');
