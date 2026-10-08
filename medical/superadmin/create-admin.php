<?php
// Executar SOMENTE pelo terminal: php create-admin.php email@dominio.com "Nome" "SenhaForte"
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require_once __DIR__.'/../api/bootstrap.php';
if($argc!==4||!filter_var($argv[1],FILTER_VALIDATE_EMAIL)||strlen($argv[3])<16){fwrite(STDERR,"Uso: php create-admin.php email nome senha-com-16+-caracteres\n");exit(1);}
if((int)db()->query('SELECT COUNT(*) FROM super_administradores')->fetchColumn()>0){fwrite(STDERR,"Super ADM já existe; cadastro adicional bloqueado.\n");exit(1);}
$s=db()->prepare('INSERT INTO super_administradores(nome,email,senha_hash) VALUES(?,?,?)');$s->execute([$argv[2],strtolower($argv[1]),password_hash($argv[3],PASSWORD_DEFAULT)]);echo "Super ADM criado. Apague ou restrinja este script após a instalação.\n";
