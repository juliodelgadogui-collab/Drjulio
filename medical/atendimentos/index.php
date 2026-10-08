<?php require __DIR__.'/../api/bootstrap.php';
auth();
$q = db()->prepare('
    SELECT a.*, p.nome as paciente_nome
    FROM atendimentos a
    JOIN pacientes p ON a.paciente_id = p.id
    WHERE a.medico_id=?
    ORDER BY a.data DESC LIMIT 50
');
$q->execute([uid()]);
$atendimentos = $q->fetchAll(PDO::FETCH_ASSOC);
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
        <h1>Atendimentos Recentes</h1>
        <a class="btn" href="novo.php">+ Iniciar Atendimento</a>
    </div>

    <div class="panel">
        <?php if(empty($atendimentos)): ?>
            <p style="text-align:center;color:var(--muted);padding:20px;">Nenhum atendimento registrado.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid var(--line);">
                        <th style="padding:10px;">Data/Hora</th>
                        <th style="padding:10px;">Paciente</th>
                        <th style="padding:10px;">Queixa Principal</th>
                        <th style="padding:10px;text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($atendimentos as $a):?>
                    <tr style="border-bottom:1px solid var(--line);">
                        <td style="padding:10px;"><strong><?= date('d/m/Y H:i', strtotime($a['data'])) ?></strong></td>
                        <td style="padding:10px;"><a href="../pacientes/ver.php?id=<?=$a['paciente_id']?>" style="color:var(--primary);"><?=e($a['paciente_nome'])?></a></td>
                        <td style="padding:10px;"><?=e(mb_strimwidth($a['queixa']?:'-', 0, 50, '...'))?></td>
                        <td style="padding:10px;text-align:right;">
                            <a href="ver.php?id=<?=$a['id']?>" style="color:var(--primary);">Ver Prontuário</a>
                        </td>
                    </tr>
                    <?php endforeach?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>
</div>
</body>
</html>
