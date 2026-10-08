<?php require __DIR__.'/../api/bootstrap.php';
auth();
$id = $_GET['id'] ?? 0;

$q = db()->prepare('
    SELECT a.*, p.nome as paciente_nome, p.cpf as paciente_cpf, p.nascimento
    FROM atendimentos a
    JOIN pacientes p ON a.paciente_id = p.id
    WHERE a.id=? AND a.medico_id=?
');
$q->execute([$id, uid()]);
$atendimento = $q->fetch(PDO::FETCH_ASSOC);

if(!$atendimento) die('Atendimento não encontrado.');

$idade = $atendimento['nascimento'] ? date_diff(date_create($atendimento['nascimento']), date_create('today'))->y . ' anos' : 'Idade não informada';
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
<style>
.section-title { font-size:1.1em; color: var(--primary); border-bottom: 1px solid var(--line); margin-top: 25px; margin-bottom: 10px; padding-bottom: 5px; font-weight:bold; }
.content-box { background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid var(--line); min-height: 50px; white-space: pre-wrap;}
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
        <a href="/medical/atendimentos/" style="background:#18243a;color:#fff;">Atendimentos</a>
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
        <h1>Prontuário Médico</h1>
        <div>
            <a href="../documentos/novo.php?paciente_id=<?=$atendimento['paciente_id']?>&atendimento_id=<?=$id?>" class="btn" style="margin-right:10px;">Emitir Documento</a>
            <a href="index.php" class="btn" style="background:var(--muted)">Voltar</a>
        </div>
    </div>

    <div class="panel">
        <div style="display:flex; justify-content:space-between; border-bottom:2px solid var(--primary); padding-bottom:15px; margin-bottom:20px;">
            <div>
                <h2 style="margin:0;"><a href="../pacientes/ver.php?id=<?=$atendimento['paciente_id']?>" style="color:inherit;text-decoration:underline;"><?=e($atendimento['paciente_nome'])?></a></h2>
                <span style="color:var(--muted);"><?=e($atendimento['paciente_cpf'])?> | <?= $idade ?></span>
            </div>
            <div style="text-align:right;">
                <strong>Data do Atendimento:</strong><br>
                <?= date('d/m/Y H:i', strtotime($atendimento['data'])) ?>
            </div>
        </div>

        <?php if($atendimento['queixa']): ?>
            <div class="section-title">Queixa Principal</div>
            <div class="content-box"><?=e($atendimento['queixa'])?></div>
        <?php endif; ?>

        <?php if($atendimento['historico']): ?>
            <div class="section-title">História da Doença Atual (HDA)</div>
            <div class="content-box"><?=e($atendimento['historico'])?></div>
        <?php endif; ?>

        <?php if($atendimento['exame_fisico']): ?>
            <div class="section-title">Exame Físico</div>
            <div class="content-box"><?=e($atendimento['exame_fisico'])?></div>
        <?php endif; ?>

        <?php if($atendimento['avaliacao']): ?>
            <div class="section-title">Avaliação / Diagnóstico</div>
            <div class="content-box"><?=e($atendimento['avaliacao'])?></div>
        <?php endif; ?>

        <?php if($atendimento['conduta']): ?>
            <div class="section-title">Conduta</div>
            <div class="content-box"><?=e($atendimento['conduta'])?></div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <?php if($atendimento['exames']): ?>
                <div>
                    <div class="section-title">Exames Solicitados</div>
                    <div class="content-box"><?=e($atendimento['exames'])?></div>
                </div>
            <?php endif; ?>
            <?php if($atendimento['resultados']): ?>
                <div>
                    <div class="section-title">Resultados Trazidos</div>
                    <div class="content-box"><?=e($atendimento['resultados'])?></div>
                </div>
            <?php endif; ?>
        </div>

        <?php if($atendimento['evolucao']): ?>
            <div class="section-title">Evolução / Observações</div>
            <div class="content-box"><?=e($atendimento['evolucao'])?></div>
        <?php endif; ?>
    </div>
</main>
</div>
</body>
</html>
