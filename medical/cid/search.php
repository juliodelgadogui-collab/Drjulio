<?php require __DIR__.'/../api/bootstrap.php';
auth();
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
if(strlen($q) < 2) {
    echo json_encode([]);
    die();
}

$stmt = db()->prepare("SELECT codigo, descricao FROM cid10 WHERE codigo LIKE ? OR descricao LIKE ? LIMIT 20");
$stmt->execute(["$q%", "%$q%"]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
die();
