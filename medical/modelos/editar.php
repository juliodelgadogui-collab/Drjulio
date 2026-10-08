<?php require __DIR__.'/../api/bootstrap.php';
auth();
$id = $_GET['id'] ?? 0;
$q = db()->prepare('SELECT * FROM modelos WHERE id=? AND medico_id=?');
$q->execute([$id, uid()]);
$modelo = $q->fetch(PDO::FETCH_ASSOC);

if(!$modelo) die('Modelo não encontrado.');

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $upd = db()->prepare('UPDATE modelos SET tipo=?, nome=?, conteudo=?, ativo=?, updated_at=CURRENT_TIMESTAMP WHERE id=? AND medico_id=?');
    $upd->execute([
        trim($_POST['tipo']),
        trim($_POST['nome']),
        trim($_POST['conteudo']),
        isset($_POST['ativo']) ? 1 : 0,
        $id,
        uid()
    ]);
    header('Location:index.php');
    die();
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
<style>
.vars-box { background: #f0f4f8; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; margin-bottom: 20px; }
.vars-box span { display: inline-block; background: #fff; padding: 3px 6px; border: 1px solid #ccc; border-radius: 4px; margin: 3px; cursor: help; }
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
        <a href="/medical/modelos/" style="background:#18243a;color:#fff;">Modelos</a>
        <a href="/medical/ia/">IA assistiva</a>
        <a href="/medical/configuracoes/">Configurações</a>
        <a href="/medical/logout.php">Sair</a>
    </nav>
</aside>
<main>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h1>Editar Modelo</h1>
        <a href="index.php" class="btn" style="background:var(--muted)">Voltar</a>
    </div>

    <div class="vars-box">
        <strong>Variáveis disponíveis:</strong><br>
        <span>{{PACIENTE}}</span> <span>{{CPF}}</span> <span>{{DATA_NASCIMENTO}}</span>
        <span>{{MEDICO}}</span> <span>{{CRM}}</span> <span>{{ESPECIALIDADE}}</span> <span>{{CRM_UF}}</span>
        <span>{{CID}}</span> <span>{{CID_DESCRICAO}}</span> <span>{{DATA}}</span>
        <span>{{DIAS}}</span> <span>{{INICIO}}</span> <span>{{FIM}}</span>
    </div>

    <div class="panel">
        <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf()?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div>
                    <label>Tipo de Documento *</label>
                    <select name="tipo" required>
                        <option value="atestado" <?= $modelo['tipo']=='atestado'?'selected':'' ?>>Atestado</option>
                        <option value="laudo" <?= $modelo['tipo']=='laudo'?'selected':'' ?>>Laudo / Relatório</option>
                        <option value="receita" <?= $modelo['tipo']=='receita'?'selected':'' ?>>Receituário</option>
                        <option value="declaracao" <?= $modelo['tipo']=='declaracao'?'selected':'' ?>>Declaração</option>
                        <option value="outro" <?= $modelo['tipo']=='outro'?'selected':'' ?>>Outro</option>
                    </select>
                </div>
                <div>
                    <label>Nome do Modelo (Identificação interna) *</label>
                    <input name="nome" value="<?=e($modelo['nome'])?>" required>
                </div>
            </div>

            <div style="margin-top:15px;">
                <label>Conteúdo (Use HTML ou texto simples e as variáveis acima) *</label>
                <textarea name="conteudo" rows="15" required style="font-family:monospace;"><?=e($modelo['conteudo'])?></textarea>
            </div>

            <div style="margin-top:15px;">
                <label>
                    <input type="checkbox" name="ativo" value="1" <?= $modelo['ativo'] ? 'checked' : '' ?> style="width:auto;margin-right:10px;">
                    Modelo Ativo
                </label>
            </div>

            <div style="margin-top:20px;">
                <button class="btn">Salvar Alterações</button>
            </div>
        </form>
    </div>
</main>
</div>
</body>
</html>
