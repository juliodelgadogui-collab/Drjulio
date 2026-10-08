<?php
require_once __DIR__.'/../api/bootstrap.php';
function sa_current(): ?array {
 if(empty($_SESSION['superadmin_id'])) return null;
 $q=db()->prepare('SELECT id,nome,email FROM super_administradores WHERE id=? AND ativo=1');
 $q->execute([$_SESSION['superadmin_id']]); return $q->fetch() ?: null;
}
$error='';$success='';
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
 require_csrf();
 $action=(string)($_POST['action']??'');
 if($action==='login'){
  $email=strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');
  if(!rate_limit('sa:'.($_SERVER['REMOTE_ADDR']??''),8,900)){$error='Muitas tentativas. Tente mais tarde.';http_response_code(429);}
  else {
   $q=db()->prepare('SELECT * FROM super_administradores WHERE email=? AND ativo=1');$q->execute([$email]);$a=$q->fetch();
   if($a&&password_verify($password,$a['senha_hash'])){session_regenerate_id(true);unset($_SESSION['medico_id']);$_SESSION['superadmin_id']=$a['id'];header('Location: index.php');exit;}
   $error='Credenciais inválidas.';
  }
 } elseif($action==='logout'){unset($_SESSION['superadmin_id']);session_regenerate_id(true);header('Location: index.php');exit;}
 elseif(sa_current()){
  if($action==='create'){
   $tipo=(string)($_POST['tipo']??'');$nome=trim((string)($_POST['nome']??''));$medico=trim((string)($_POST['medico']??''));$email=strtolower(trim((string)($_POST['email']??'')));$crm=trim((string)($_POST['crm']??''));$uf=strtoupper(trim((string)($_POST['uf']??'')));$pass=(string)($_POST['password']??'');
   if(!in_array($tipo,['clinica','individual'],true)||mb_strlen($nome)<3||mb_strlen($medico)<3||!filter_var($email,FILTER_VALIDATE_EMAIL)||!preg_match('/^[A-Z]{2}$/',$uf)||$crm===''||strlen($pass)<12){$error='Preencha os campos corretamente. Senha mínima: 12 caracteres.';}
   else {
    try {
     db()->exec('BEGIN IMMEDIATE');
     $q=db()->prepare('INSERT INTO organizacoes(nome,tipo) VALUES(?,?)');$q->execute([$nome,$tipo]);$oid=(int)db()->lastInsertId();
     $q=db()->prepare('INSERT INTO medicos(nome_completo,email,crm,uf,senha_hash,organizacao_id,ativo) VALUES(?,?,?,?,?,?,1)');
     $q->execute([$medico,$email,$crm,$uf,password_hash($pass,PASSWORD_DEFAULT),$oid]);$mid=(int)db()->lastInsertId();
     db()->prepare("INSERT INTO organizacao_usuarios(organizacao_id,medico_id,papel) VALUES(?,?,'admin_clinica')")->execute([$oid,$mid]);
     db()->exec('COMMIT');$success='Organização e médico criados.';
    }catch(Throwable $e){if(db()->inTransaction())db()->rollBack();$error='Falha no cadastro. Verifique se o e-mail já está registrado.';}
   }
  }elseif($action==='toggle'){
   $id=filter_var($_POST['org_id']??null,FILTER_VALIDATE_INT);
   if($id){db()->prepare('UPDATE organizacoes SET ativo=CASE ativo WHEN 1 THEN 0 ELSE 1 END WHERE id=?')->execute([$id]);$success='Situação atualizada.';}
  }
 }
}
$a=sa_current();
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Super ADM | DrJulio</title><link rel="stylesheet" href="<?=h(app_url('assets/app.css'))?>"></head><body><main class="container" style="max-width:1000px;margin:3rem auto"><h1>DrJulio · Super Administrador</h1>
<?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?>
<?php if($success):?><p class="success"><?=h($success)?></p><?php endif;?>
<?php if(!$a):?>
<form method="post" class="card"><h2>Acesso administrativo</h2><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="login"><label>E-mail<input name="email" type="email" required></label><label>Senha<input name="password" type="password" required></label><button>Entrar</button></form>
<?php else:?>
<p>Administrador: <?=h($a['nome'])?></p><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="logout"><button>Sair</button></form>
<section class="card"><h2>Criar clínica ou consultório</h2><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="create"><label>Tipo<select name="tipo"><option value="individual">Médico independente</option><option value="clinica">Clínica</option></select></label><label>Nome da organização<input name="nome" required></label><label>Médico responsável<input name="medico" required></label><label>E-mail de acesso<input name="email" type="email" required></label><label>CRM<input name="crm" required></label><label>UF do CRM<input name="uf" maxlength="2" required></label><label>Senha inicial<input name="password" type="password" minlength="12" required></label><button>Criar acesso</button></form></section>
<section class="card"><h2>Organizações</h2><table><thead><tr><th>Nome</th><th>Tipo</th><th>Médicos</th><th>Situação</th><th>Ação</th></tr></thead><tbody>
<?php $orgs=db()->query('SELECT o.*, (SELECT COUNT(*) FROM medicos m WHERE m.organizacao_id=o.id) qtd FROM organizacoes o ORDER BY o.id DESC')->fetchAll();foreach($orgs as $o):?>
<tr><td><?=h($o['nome'])?></td><td><?=h($o['tipo'])?></td><td><?=h($o['qtd'])?></td><td><?=((int)$o['ativo']===1?'Ativa':'Suspensa')?></td><td><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="org_id" value="<?=h($o['id'])?>"><button><?=((int)$o['ativo']===1?'Suspender':'Reativar')?></button></form></td></tr>
<?php endforeach;?></tbody></table></section><?php endif;?></main></body></html>
