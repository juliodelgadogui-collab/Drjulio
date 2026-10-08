<?php require __DIR__.'/../api/bootstrap.php';
auth();
$id = $_GET['id'] ?? 0;

$qA = db()->prepare('SELECT * FROM agenda WHERE id=? AND medico_id=?');
$qA->execute([$id, uid()]);
$agendamento = $qA->fetch(PDO::FETCH_ASSOC);

if(!$agendamento) die('Agendamento não encontrado.');

if($_SERVER['REQUEST_METHOD']==='POST'){
    if (isset($_POST['excluir'])) {
        verify_csrf();
        $del = db()->prepare('DELETE FROM agenda WHERE id=? AND medico_id=?');
        $del->execute([$id, uid()]);
        header('Location:index.php');
        die();
    }

    verify_csrf();
    $paciente_id = !empty($_POST['paciente_id']) ? $_POST['paciente_id'] : null;
    $upd = db()->prepare('UPDATE agenda SET paciente_id=?, data_hora=?, duracao=?, status=?, observacao=? WHERE id=? AND medico_id=?');
    $upd->execute([
        $paciente_id,
        $_POST['data_hora'],
        $_POST['duracao'],
        $_POST['status'],
        trim($_POST['observacao']),
        $id,
        uid()
    ]);
    header('Location:index.php');
    die();
}

$qP = db()->prepare('SELECT id, nome, cpf FROM pacientes WHERE medico_id=? ORDER BY nome');
$qP->execute([uid()]);
$pacientes = $qP->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
</head>
<body>
<div class="layout">
<aside>
    <strong>DrJulio</strong>
    <nav>
        <a href="/medical/">Dashboard</a>
        <a href="/medical/pacientes/">Pacientes</a>
        <a href="/medical/agenda/" style="background:#18243a;color:#fff;">Agenda</a>
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
        <h1>Editar Agendamento</h1>
        <a href="index.php" class="btn" style="background:var(--muted)">Voltar</a>
    </div>

    <div class="panel">
        <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf()?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div style="grid-column: 1 / -1;">
                    <label>Paciente</label>
                    <select name="paciente_id">
                        <option value="">-- Selecione o Paciente (Opcional) --</option>
                        <?php foreach($pacientes as $p): ?>
                            <option value="<?=$p['id']?>" <?= $agendamento['paciente_id'] == $p['id'] ? 'selected' : '' ?>>
                                <?=e($p['nome'])?> (<?=e($p['cpf'])?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Data e Hora *</label>
                    <input type="datetime-local" name="data_hora" value="<?= date('Y-m-d\TH:i', strtotime($agendamento['data_hora'])) ?>" required>
                </div>

                <div>
                    <label>Duração (minutos)</label>
                    <input type="number" name="duracao" value="<?=$agendamento['duracao']?>" required>
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="agendado" <?= $agendamento['status']=='agendado'?'selected':'' ?>>Agendado</option>
                        <option value="confirmado" <?= $agendamento['status']=='confirmado'?'selected':'' ?>>Confirmado</option>
                        <option value="retorno" <?= $agendamento['status']=='retorno'?'selected':'' ?>>Retorno</option>
                        <option value="concluido" <?= $agendamento['status']=='concluido'?'selected':'' ?>>Concluído</option>
                        <option value="cancelado" <?= $agendamento['status']=='cancelado'?'selected':'' ?>>Cancelado</option>
                    </select>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label>Observações</label>
                    <textarea name="observacao" rows="3"><?=e($agendamento['observacao']??'')?></textarea>
                </div>
            </div>

            <div style="margin-top:20px; display:flex; justify-content:space-between;">
                <button type="submit" class="btn">Salvar Alterações</button>
                <button type="submit" name="excluir" class="btn" style="background:#d92d20;" onclick="return confirm('Excluir este agendamento?');">Excluir</button>
            </div>
        </form>
    </div>
</main>
</div>
</body>
</html>
