<?php require __DIR__.'/../api/bootstrap.php';
auth();
$q = db()->prepare('SELECT * FROM modelos WHERE medico_id=? ORDER BY tipo, nome');
$q->execute([uid()]);
$modelos = $q->fetchAll(PDO::FETCH_ASSOC);
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
        <h1>Modelos de Documentos</h1>
        <a class="btn" href="novo.php">+ Novo Modelo</a>
    </div>

    <div class="panel">
        <?php if(empty($modelos)): ?>
            <p style="text-align:center;color:var(--muted);padding:20px;">Nenhum modelo cadastrado.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid var(--line);">
                        <th style="padding:10px;">Tipo</th>
                        <th style="padding:10px;">Nome do Modelo</th>
                        <th style="padding:10px;">Status</th>
                        <th style="padding:10px;text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modelos as $m):?>
                    <tr style="border-bottom:1px solid var(--line);">
                        <td style="padding:10px;"><strong><?=e(ucfirst($m['tipo']))?></strong></td>
                        <td style="padding:10px;"><?=e($m['nome'])?></td>
                        <td style="padding:10px;"><?= $m['ativo'] ? '<span style="color:green;">Ativo</span>' : '<span style="color:red;">Inativo</span>' ?></td>
                        <td style="padding:10px;text-align:right;">
                            <a href="editar.php?id=<?=$m['id']?>" style="color:var(--primary);margin-right:10px;">Editar</a>
                            <a href="excluir.php?id=<?=$m['id']?>" style="color:red;" onclick="return confirm('Excluir este modelo?');">Excluir</a>
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
