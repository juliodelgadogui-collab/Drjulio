<?php require __DIR__.'/../api/bootstrap.php';
auth();

$paciente_id = $_GET['paciente_id'] ?? null;
$agenda_id = $_GET['agenda_id'] ?? null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $pid = $_POST['paciente_id'];

    // Iniciar transação
    db()->beginTransaction();
    try {
        $q=db()->prepare('INSERT INTO atendimentos(medico_id,paciente_id,data,queixa,historico,exame_fisico,avaliacao,conduta,exames,resultados,observacoes,evolucao) VALUES(?,?,datetime("now","localtime"),?,?,?,?,?,?,?,?,?)');
        $q->execute([
            uid(),
            $pid,
            trim($_POST['queixa']??''),
            trim($_POST['historico']??''),
            trim($_POST['exame_fisico']??''),
            trim($_POST['avaliacao']??''),
            trim($_POST['conduta']??''),
            trim($_POST['exames']??''),
            trim($_POST['resultados']??''),
            trim($_POST['observacoes']??''),
            trim($_POST['evolucao']??'')
        ]);
        $atendimento_id = db()->lastInsertId();

        // Update agenda se veio de um agendamento
        if(!empty($_POST['agenda_id'])) {
            $upd = db()->prepare("UPDATE agenda SET status='concluido' WHERE id=? AND medico_id=?");
            $upd->execute([$_POST['agenda_id'], uid()]);
        }

        db()->commit();
        header('Location: ver.php?id='.$atendimento_id);
        die();
    } catch(Exception $e) {
        db()->rollBack();
        die("Erro ao salvar atendimento: " . $e->getMessage());
    }
}

$qP = db()->prepare('SELECT id, nome, cpf FROM pacientes WHERE medico_id=? ORDER BY nome');
$qP->execute([uid()]);
$pacientes = $qP->fetchAll(PDO::FETCH_ASSOC);

// Se tiver paciente selecionado, busca dados rápidos dele
$paciente_info = null;
if ($paciente_id) {
    $qp2 = db()->prepare('SELECT * FROM pacientes WHERE id=? AND medico_id=?');
    $qp2->execute([$paciente_id, uid()]);
    $paciente_info = $qp2->fetch(PDO::FETCH_ASSOC);
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
<style>
.section-title { background: #f0fdf4; padding: 10px; border-left: 4px solid #16a34a; margin-top: 20px; margin-bottom: 15px; font-weight: bold; }
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
        <h1>Novo Atendimento Médico</h1>
        <a href="index.php" class="btn" style="background:var(--muted)">Cancelar</a>
    </div>

    <form method="post">
        <input type="hidden" name="csrf" value="<?=csrf()?>">
        <input type="hidden" name="agenda_id" value="<?=e($agenda_id)?>">

        <div class="panel" style="margin-bottom:20px;border:2px solid var(--primary);">
            <label style="font-weight:bold;font-size:16px;">Selecionar Paciente *</label>
            <select name="paciente_id" required style="font-size:16px;padding:12px;margin-bottom:0;" onchange="if(this.value) window.location.href='?paciente_id='+this.value;">
                <option value="">-- Selecione o Paciente --</option>
                <?php foreach($pacientes as $p): ?>
                    <option value="<?=$p['id']?>" <?= $paciente_id == $p['id'] ? 'selected' : '' ?>>
                        <?=e($p['nome'])?> (<?=e($p['cpf'])?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if($paciente_info): ?>
        <div class="panel" style="margin-bottom:20px;background:#f9fafb;">
            <h4 style="margin-top:0;">Resumo Clínico - <?=e($paciente_info['nome'])?></h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;font-size:14px;">
                <div><strong>Alergias:</strong> <?=e($paciente_info['alergias']?:'Nenhuma')?></div>
                <div><strong>Medicamentos:</strong> <?=e($paciente_info['medicamentos']?:'Nenhum')?></div>
                <div style="grid-column: 1/-1;"><strong>Antecedentes:</strong> <?=e($paciente_info['antecedentes']?:'-')?></div>
            </div>
        </div>

        <div class="panel">
            <div class="section-title">Anamnese</div>
            <label>Queixa Principal (QP)</label>
            <textarea name="queixa" rows="2" placeholder="Motivo da consulta..."></textarea>

            <label>História da Doença Atual (HDA)</label>
            <textarea name="historico" rows="4"></textarea>

            <div class="section-title">Exame Físico</div>
            <textarea name="exame_fisico" rows="3" placeholder="Sinais vitais, exame geral e segmentar..."></textarea>

            <div class="section-title">Hipótese Diagnóstica / Avaliação</div>
            <textarea name="avaliacao" rows="3"></textarea>

            <div class="section-title">Conduta / Plano Terapêutico</div>
            <textarea name="conduta" rows="4" placeholder="Prescrições, orientações..."></textarea>

            <div class="section-title">Exames (Solicitados / Trazidos)</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div>
                    <label>Exames Solicitados</label>
                    <textarea name="exames" rows="2"></textarea>
                </div>
                <div>
                    <label>Resultados de Exames Trazidos</label>
                    <textarea name="resultados" rows="2"></textarea>
                </div>
            </div>

            <div class="section-title">Evolução / Observações Internas</div>
            <textarea name="evolucao" rows="3"></textarea>

            <div style="margin-top:30px;">
                <button type="submit" class="btn" style="font-size:18px;padding:15px 30px;">Salvar Atendimento</button>
            </div>
        </div>
        <?php endif; ?>
    </form>
</main>
</div>
</body>
</html>
