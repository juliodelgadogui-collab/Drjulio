<?php require __DIR__.'/../api/bootstrap.php';
auth();
$id = $_GET['id'] ?? 0;
$q = db()->prepare('DELETE FROM modelos WHERE id=? AND medico_id=?');
$q->execute([$id, uid()]);
header('Location:index.php');
die();
