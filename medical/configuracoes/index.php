<?php require __DIR__.'/../api/bootstrap.php';
auth();

$q = db()->prepare('SELECT * FROM medicos WHERE id=?');
$q->execute([uid()]);
$medico = $q->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();

    // Processar uploads se existirem
    $logo_path = $medico['logo_path'];
    $assinatura_path = $medico['assinatura_path'];
    $uploadDir = __DIR__ . '/../storage/uploads/';
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if(!empty($_FILES['logo']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg','jpeg','png'])) {
            $name = 'logo_' . uid() . '_' . time() . '.' . $ext;
            if(move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $name)) {
                $logo_path = 'storage/uploads/' . $name;
            }
        }
    }

    if(!empty($_FILES['assinatura']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['assinatura']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg','jpeg','png'])) {
            $name = 'ass_' . uid() . '_' . time() . '.' . $ext;
            if(move_uploaded_file($_FILES['assinatura']['tmp_name'], $uploadDir . $name)) {
                $assinatura_path = 'storage/uploads/' . $name;
            }
        }
    }

    $upd = db()->prepare('UPDATE medicos SET nome=?, crm=?, especialidade=?, telefone=?, email=?, uf=?, endereco=?, logo_path=?, assinatura_path=? WHERE id=?');
    $upd->execute([
        trim($_POST['nome']),
        trim($_POST['crm']),
        trim($_POST['especialidade']),
        trim($_POST['telefone']),
        trim($_POST['email']),
        trim($_POST['uf']),
        trim($_POST['endereco']),
        $logo_path,
        $assinatura_path,
        uid()
    ]);

    // Atualizar senha se preenchida
    if(!empty($_POST['senha']) && strlen($_POST['senha']) >= 8) {
        $updSenha = db()->prepare('UPDATE medicos SET senha_hash=? WHERE id=?');
        $updSenha->execute([password_hash($_POST['senha'], PASSWORD_DEFAULT), uid()]);
    }

    header('Location:index.php?msg=sucesso');
    die();
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
<style>
.preview-img { max-height: 100px; border: 1px solid #ccc; padding: 5px; background: #fff; border-radius: 4px; margin-top: 10px; }
</style>
</head>
<body>
<div class="layout">
<aside>
    <strong>DrJulio</strong>
    <nav>
        <a href="/medical/">Dashboard</a>
        <a href="/medical/pacientes/">Pacientes</a>
        <a href="/medical/agenda/">Agenda</a>
        <a href="/medical/atendimentos/">Atendimentos</a>
        <a href="/medical/documentos/">Documentos</a>
        <a href="/medical/cid/">CID-10</a>
        <a href="/medical/modelos/">Modelos</a>
        <a href="/medical/ia/">IA assistiva</a>
        <a href="/medical/configuracoes/" style="background:#18243a;color:#fff;">Configurações</a>
        <a href="/medical/logout.php">Sair</a>
    </nav>
</aside>
<main>
    <h1>Configurações do Médico</h1>

    <?php if(isset($_GET['msg']) && $_GET['msg']=='sucesso'): ?>
        <div style="background:#ecfdf3; color:#027a48; padding:15px; border-radius:8px; margin-bottom:20px;">Configurações atualizadas com sucesso!</div>
    <?php endif; ?>

    <div class="panel">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?=csrf()?>">

            <h3 style="border-bottom:1px solid var(--line); padding-bottom:10px;">Dados Profissionais</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div>
                    <label>Nome Completo *</label>
                    <input name="nome" value="<?=e($medico['nome'])?>" required>
                </div>
                <div>
                    <label>Especialidade</label>
                    <input name="especialidade" value="<?=e($medico['especialidade'])?>">
                </div>
                <div>
                    <label>CRM *</label>
                    <input name="crm" value="<?=e($medico['crm'])?>" required>
                </div>
                <div>
                    <label>UF do CRM</label>
                    <input name="uf" value="<?=e($medico['uf']??'')?>">
                </div>
                <div>
                    <label>Telefone</label>
                    <input name="telefone" value="<?=e($medico['telefone']??'')?>">
                </div>
                <div>
                    <label>E-mail de Contato</label>
                    <input type="email" name="email" value="<?=e($medico['email'])?>">
                </div>
                <div style="grid-column:1/-1;">
                    <label>Endereço do Consultório (Sairá no rodapé dos documentos)</label>
                    <input name="endereco" value="<?=e($medico['endereco']??'')?>">
                </div>
            </div>

            <h3 style="border-bottom:1px solid var(--line); padding-bottom:10px; margin-top:30px;">Identidade Visual (Documentos)</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <label>Logo da Clínica (JPG/PNG)</label>
                    <input type="file" name="logo" accept=".jpg,.jpeg,.png">
                    <?php if($medico['logo_path']): ?>
                        <br><img src="../<?=e($medico['logo_path'])?>" class="preview-img" alt="Logo atual">
                    <?php endif; ?>
                </div>
                <div>
                    <label>Sua Assinatura e Carimbo (Fundo transparente / JPG/PNG)</label>
                    <input type="file" name="assinatura" accept=".jpg,.jpeg,.png">
                    <?php if($medico['assinatura_path']): ?>
                        <br><img src="../<?=e($medico['assinatura_path'])?>" class="preview-img" alt="Assinatura atual">
                    <?php endif; ?>
                </div>
            </div>

            <h3 style="border-bottom:1px solid var(--line); padding-bottom:10px; margin-top:30px;">Segurança</h3>
            <div>
                <label>Nova Senha (deixe em branco para manter a atual, mín 8 caracteres)</label>
                <input type="password" name="senha" minlength="8">
            </div>

            <div style="margin-top:30px;">
                <button class="btn" style="padding:15px 30px; font-size:16px;">Salvar Configurações</button>
            </div>
        </form>
    </div>
</main>
</div>
</body>
</html>
