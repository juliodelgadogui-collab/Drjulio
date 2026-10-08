<?php require __DIR__.'/../api/bootstrap.php';
auth();

if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $paciente_id = !empty($_POST['paciente_id']) ? $_POST['paciente_id'] : null;
    $q=db()->prepare('INSERT INTO agenda(medico_id,paciente_id,data_hora,duracao,status,observacao) VALUES(?,?,?,?,?,?)');
    $q->execute([
        uid(),
        $paciente_id,
        $_POST['data_hora'],
        $_POST['duracao'],
        $_POST['status'],
        trim($_POST['observacao'])
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
        <h1>Novo Agendamento</h1>
        <a href="index.php" class="btn" style="background:var(--muted)">Voltar</a>
    </div>

    <div class="panel">
        <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf()?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div style="grid-column: 1 / -1;">
                    <label>Paciente</label>
                    <select name="paciente_id">
                        <option value="">-- Selecione o Paciente (Opcional para bloqueio de agenda) --</option>
                        <?php foreach($pacientes as $p): ?>
                            <option value="<?=$p['id']?>"><?=e($p['nome'])?> (<?=e($p['cpf'])?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Data e Hora *</label>
                    <input type="datetime-local" name="data_hora" required>
                </div>

                <div>
                    <label>Duração (minutos)</label>
                    <input type="number" name="duracao" value="30" required>
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="agendado">Agendado</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="retorno">Retorno</option>
                    </select>
                </div>

                <div style="grid-column: 1 / -1;">
                    <label>Observações</label>
                    <textarea name="observacao" rows="3"></textarea>
                </div>
            </div>

            <div style="margin-top:20px;">
                <button class="btn">Salvar Agendamento</button>
            </div>
        </form>
    </div>
</main>
</div>
</body>
</html>
