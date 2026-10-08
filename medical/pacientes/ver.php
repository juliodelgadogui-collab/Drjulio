<?php require __DIR__.'/../api/bootstrap.php';
auth();
$id = $_GET['id'] ?? 0;
$q = db()->prepare('SELECT * FROM pacientes WHERE id=? AND medico_id=?');
$q->execute([$id, uid()]);
$paciente = $q->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die('Paciente não encontrado.');
}

// Buscar atendimentos
$qAt = db()->prepare('SELECT * FROM atendimentos WHERE paciente_id=? AND medico_id=? ORDER BY data DESC');
$qAt->execute([$id, uid()]);
$atendimentos = $qAt->fetchAll(PDO::FETCH_ASSOC);

// Buscar documentos
$qDoc = db()->prepare('SELECT * FROM documentos WHERE paciente_id=? AND medico_id=? ORDER BY emitido_em DESC');
$qDoc->execute([$id, uid()]);
$documentos = $qDoc->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
<style>
.timeline { border-left: 2px solid var(--primary); padding-left: 20px; margin-left: 10px; }
.timeline-item { position: relative; margin-bottom: 20px; }
.timeline-item::before {
    content: ''; position: absolute; left: -26px; top: 0; width: 10px; height: 10px;
    background: #fff; border: 2px solid var(--primary); border-radius: 50%;
}
</style>
</head>
<body>
<div class="layout">
<aside>
    <strong>DrJulio</strong>
    <nav>
        <a href="/medical/">Dashboard</a>
        <a href="/medical/pacientes/" style="background:#18243a;color:#fff;">Pacientes</a>
        <a href="/medical/agenda/">Agenda</a>
        <a href="/medical/atendimentos/">Atendimentos</a>
        <a href="/medical/documentos/">Documentos</a>
        <a href="/medical/cid/">CID-10</a>
        <a href="/medical/modelos/">Modelos</a>
        <a href="/medical/ia/">IA assistiva</a>
        <a href="/medical/configuracoes/">Configurações</a>
        <a href="/medical/logout.php">Sair</a>
    </nav>
</aside>
<main>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h1>Prontuário: <?=e($paciente['nome'])?></h1>
        <div>
            <a href="../atendimentos/novo.php?paciente_id=<?=$id?>" class="btn" style="margin-right:10px;">+ Novo Atendimento</a>
            <a href="index.php" class="btn" style="background:var(--muted)">Voltar</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
        <!-- Lado Esquerdo: Info -->
        <div>
            <div class="panel" style="margin-bottom:20px;">
                <h3>Resumo Clínico</h3>
                <p><strong>Idade:</strong> <?= $paciente['nascimento'] ? date_diff(date_create($paciente['nascimento']), date_create('today'))->y . ' anos' : 'Não informado' ?></p>
                <p><strong>Alergias:</strong><br><?= nl2br(e($paciente['alergias'] ?: 'Nenhuma relatada')) ?></p>
                <p><strong>Medicamentos:</strong><br><?= nl2br(e($paciente['medicamentos'] ?: 'Nenhum')) ?></p>
                <p><strong>Antecedentes:</strong><br><?= nl2br(e($paciente['antecedentes'] ?: '-')) ?></p>
                <div style="margin-top:15px;">
                    <a href="editar.php?id=<?=$id?>" style="color:var(--primary);text-decoration:underline;">Editar Dados</a>
                </div>
            </div>

            <div class="panel">
                <h3>Documentos Emitidos</h3>
                <?php if(empty($documentos)): ?>
                    <p style="color:var(--muted);font-size:14px;">Nenhum documento.</p>
                <?php else: ?>
                    <ul style="padding-left:20px;margin:0;font-size:14px;">
                        <?php foreach($documentos as $doc): ?>
                            <li style="margin-bottom:8px;">
                                <strong><?=e($doc['tipo'])?></strong> (<?= date('d/m/Y', strtotime($doc['emitido_em'])) ?>)<br>
                                <a href="../documentos/ver.php?id=<?=$doc['id']?>" target="_blank" style="color:var(--primary);">Ver Documento</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lado Direito: Timeline de Atendimentos -->
        <div class="panel">
            <h3>Histórico de Atendimentos</h3>
            <?php if(empty($atendimentos)): ?>
                <p style="color:var(--muted);text-align:center;padding:20px;">Nenhum atendimento registrado para este paciente.</p>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach($atendimentos as $at): ?>
                        <div class="timeline-item">
                            <h4 style="margin:0 0 5px 0;">Data: <?= date('d/m/Y H:i', strtotime($at['data'])) ?></h4>
                            <?php if($at['queixa']): ?>
                                <strong>Queixa Principal:</strong> <?= e($at['queixa']) ?><br>
                            <?php endif; ?>
                            <?php if($at['avaliacao']): ?>
                                <strong>Avaliação:</strong> <?= e($at['avaliacao']) ?><br>
                            <?php endif; ?>
                            <div style="margin-top:10px;">
                                <a href="../atendimentos/ver.php?id=<?=$at['id']?>" style="font-size:14px;color:var(--primary);">Ver Prontuário Completo →</a>
                            </div>
                            <hr style="border:none;border-top:1px solid var(--line);margin:15px 0;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</div>
</body>
</html>
