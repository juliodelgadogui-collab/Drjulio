<?php
// Executar exclusivamente por CLI. A senha e lida de STDIN, nao dos argumentos.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../api/bootstrap.php';
if ($argc !== 3 || !filter_var($argv[1], FILTER_VALIDATE_EMAIL) || trim($argv[2]) === '') {
    fwrite(STDERR, "Uso: php create-admin.php email@dominio.com 'Nome Completo'\n");
    exit(1);
}
if ((int)db()->query('SELECT COUNT(*) FROM super_administradores')->fetchColumn() > 0) {
    fwrite(STDERR, "Super ADM ja cadastrado.\n"); exit(1);
}
fwrite(STDERR, "Informe a senha inicial (minimo 16 caracteres) e pressione Enter: ");
$password = rtrim((string)fgets(STDIN), "\r\n");
if (strlen($password) < 16) { fwrite(STDERR, "Senha insuficiente.\n"); exit(1); }
$s = db()->prepare('INSERT INTO super_administradores(nome,email,senha_hash) VALUES(?,?,?)');
$s->execute([trim($argv[2]), strtolower($argv[1]), password_hash($password, PASSWORD_DEFAULT)]);
unset($password);
echo "Super ADM cadastrado.\n";
