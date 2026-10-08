<?php require __DIR__.'/../api/bootstrap.php';
auth();
$q = db()->prepare('
    SELECT d.*, p.nome as paciente_nome
    FROM documentos d
    JOIN pacientes p ON d.paciente_id = p.id
    WHERE d.medico_id=?
    ORDER BY d.emitido_em DESC LIMIT 50
');
$q->execute([uid()]);
$documentos = $q->fetchAll(PDO::FETCH_ASSOC);
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
        <h1>Documentos Emitidos</h1>
        <a class="btn" href="novo.php">+ Emitir Documento</a>
    </div>

    <div class="panel">
        <?php if(empty($documentos)): ?>
            <p style="text-align:center;color:var(--muted);padding:20px;">Nenhum documento emitido.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid var(--line);">
                        <th style="padding:10px;">Data de Emissão</th>
                        <th style="padding:10px;">Paciente</th>
                        <th style="padding:10px;">Tipo</th>
                        <th style="padding:10px;">Código (Validação)</th>
                        <th style="padding:10px;text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($documentos as $d):?>
                    <tr style="border-bottom:1px solid var(--line);">
                        <td style="padding:10px;"><strong><?= date('d/m/Y H:i', strtotime($d['emitido_em'])) ?></strong></td>
                        <td style="padding:10px;"><a href="../pacientes/ver.php?id=<?=$d['paciente_id']?>" style="color:var(--primary);"><?=e($d['paciente_nome'])?></a></td>
                        <td style="padding:10px;"><?=e(ucfirst($d['tipo']))?></td>
                        <td style="padding:10px;font-family:monospace;"><?=e($d['codigo'])?></td>
                        <td style="padding:10px;text-align:right;">
                            <a href="gerar_pdf.php?id=<?=$d['id']?>" target="_blank" style="color:var(--primary);margin-right:10px;">Gerar PDF</a>
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
