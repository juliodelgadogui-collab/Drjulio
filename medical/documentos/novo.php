<?php require __DIR__.'/../api/bootstrap.php';
auth();

$paciente_id = $_GET['paciente_id'] ?? null;
$modelo_id = $_GET['modelo_id'] ?? null;
$atendimento_id = $_GET['atendimento_id'] ?? null;

// Busca dados básicos do médico para substituição
$qM = db()->prepare('SELECT * FROM medicos WHERE id=?');
$qM->execute([uid()]);
$medico = $qM->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $pid = $_POST['paciente_id'];
    $tipo = $_POST['tipo'];
    $conteudo = $_POST['conteudo_final'];

    // Gerar código único para validação
    $codigo_validacao = strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));

    $q=db()->prepare('INSERT INTO documentos(medico_id,paciente_id,tipo,conteudo,codigo,emitido_em) VALUES(?,?,?,?,?,datetime("now","localtime"))');
    $q->execute([
        uid(),
        $pid,
        $tipo,
        $conteudo,
        $codigo_validacao
    ]);
    $doc_id = db()->lastInsertId();
    header("Location: gerar_pdf.php?id=$doc_id");
    die();
}

$qP = db()->prepare('SELECT id, nome, cpf FROM pacientes WHERE medico_id=? ORDER BY nome');
$qP->execute([uid()]);
$pacientes = $qP->fetchAll(PDO::FETCH_ASSOC);

$qMod = db()->prepare('SELECT id, nome, tipo FROM modelos WHERE medico_id=? AND ativo=1 ORDER BY tipo, nome');
$qMod->execute([uid()]);
$modelos = $qMod->fetchAll(PDO::FETCH_ASSOC);

// Se paciente e modelo foram selecionados, prepara o conteúdo
$conteudo_preenchido = '';
$tipo_modelo = 'outro';

if ($paciente_id && $modelo_id) {
    // Busca paciente
    $qp2 = db()->prepare('SELECT * FROM pacientes WHERE id=? AND medico_id=?');
    $qp2->execute([$paciente_id, uid()]);
    $pac = $qp2->fetch(PDO::FETCH_ASSOC);

    // Busca modelo
    $qm2 = db()->prepare('SELECT * FROM modelos WHERE id=? AND medico_id=?');
    $qm2->execute([$modelo_id, uid()]);
    $mod = $qm2->fetch(PDO::FETCH_ASSOC);

    if ($pac && $mod) {
        $tipo_modelo = $mod['tipo'];
        $txt = $mod['conteudo'];

        // Substituições básicas
        $txt = str_replace('{{PACIENTE}}', $pac['nome'], $txt);
        $txt = str_replace('{{CPF}}', $pac['cpf'] ?: 'Não informado', $txt);
        $nascimento = $pac['nascimento'] ? date('d/m/Y', strtotime($pac['nascimento'])) : 'Não informado';
        $txt = str_replace('{{DATA_NASCIMENTO}}', $nascimento, $txt);

        $txt = str_replace('{{MEDICO}}', $medico['nome'], $txt);
        $txt = str_replace('{{CRM}}', $medico['crm'], $txt);
        $txt = str_replace('{{ESPECIALIDADE}}', $medico['especialidade'], $txt);
        $txt = str_replace('{{CRM_UF}}', $medico['uf'] ?? '', $txt);

        $txt = str_replace('{{DATA}}', date('d/m/Y'), $txt);

        $conteudo_preenchido = $txt;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
<script>
async function fillCid(btn) {
    const cidInput = document.getElementById('search_cid');
    const q = cidInput.value;
    if(q.length < 2) return;
    const res = await fetch('../cid/search.php?q=' + encodeURIComponent(q));
    const json = await res.json();
    if(json.length > 0) {
        let text = document.getElementById('conteudo_final').value;
        text = text.replace('{{CID}}', json[0].codigo);
        text = text.replace('{{CID_DESCRICAO}}', json[0].descricao);
        document.getElementById('conteudo_final').value = text;
        alert('CID ' + json[0].codigo + ' aplicado ao texto!');
    } else {
        alert('CID não encontrado.');
    }
}
function fillDays() {
    const d = document.getElementById('dias_afastamento').value;
    if(d) {
        let text = document.getElementById('conteudo_final').value;
        text = text.replace('{{DIAS}}', d);
        document.getElementById('conteudo_final').value = text;
    }
}
</script>
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
        <a href="/medical/documentos/" style="background:#18243a;color:#fff;">Documentos</a>
        <a href="/medical/cid/">CID-10</a>
        <a href="/medical/modelos/">Modelos</a>
        <a href="/medical/ia/">IA assistiva</a>
        <a href="/medical/configuracoes/">Configurações</a>
        <a href="/medical/logout.php">Sair</a>
    </nav>
</aside>
<main>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h1>Emitir Documento</h1>
        <a href="index.php" class="btn" style="background:var(--muted)">Cancelar</a>
    </div>

    <div class="panel">
        <form method="get" style="margin-bottom:20px; border-bottom:1px solid var(--line); padding-bottom:20px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div>
                    <label>Selecionar Paciente *</label>
                    <select name="paciente_id" required onchange="this.form.submit()">
                        <option value="">-- Selecione --</option>
                        <?php foreach($pacientes as $p): ?>
                            <option value="<?=$p['id']?>" <?= $paciente_id == $p['id'] ? 'selected' : '' ?>>
                                <?=e($p['nome'])?> (<?=e($p['cpf'])?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Selecionar Modelo *</label>
                    <select name="modelo_id" required onchange="this.form.submit()">
                        <option value="">-- Selecione --</option>
                        <?php foreach($modelos as $m): ?>
                            <option value="<?=$m['id']?>" <?= $modelo_id == $m['id'] ? 'selected' : '' ?>>
                                [<?=e(ucfirst($m['tipo']))?>] <?=e($m['nome'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <?php if($paciente_id && $modelo_id): ?>

        <!-- Ferramentas para preenchimento de variáveis -->
        <div style="background:#f0f4f8; padding:15px; border-radius:8px; margin-bottom:20px; display:flex; gap:20px; align-items:center;">
            <div>
                <label style="font-size:12px;display:block;">Substituir {{CID}}</label>
                <input type="text" id="search_cid" placeholder="Ex: J00" style="width:100px; display:inline-block; margin:0;">
                <button type="button" class="btn" style="padding:10px;" onclick="fillCid(this)">Aplicar CID</button>
            </div>
            <div>
                <label style="font-size:12px;display:block;">Substituir {{DIAS}}</label>
                <input type="number" id="dias_afastamento" style="width:80px; display:inline-block; margin:0;">
                <button type="button" class="btn" style="padding:10px;" onclick="fillDays()">Aplicar Dias</button>
            </div>
        </div>

        <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf()?>">
            <input type="hidden" name="paciente_id" value="<?=e($paciente_id)?>">
            <input type="hidden" name="tipo" value="<?=e($tipo_modelo)?>">

            <label>Conteúdo do Documento (Ajuste o texto final antes de gerar)</label>
            <textarea name="conteudo_final" id="conteudo_final" rows="20" required style="font-family:monospace;"><?=e($conteudo_preenchido)?></textarea>

            <div style="margin-top:20px;">
                <button class="btn" style="font-size:18px;padding:15px 30px;" onclick="return confirm('Confirmar a emissão deste documento? Ele será salvo permanentemente.');">Salvar e Gerar PDF</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>
</div>
</body>
</html>
