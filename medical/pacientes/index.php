<?php require __DIR__.'/../api/bootstrap.php';
auth();
$busca = $_GET['q'] ?? '';
if ($busca) {
    $q = db()->prepare("SELECT * FROM pacientes WHERE medico_id=? AND (nome LIKE ? OR cpf LIKE ?) ORDER BY nome");
    $q->execute([uid(), "%$busca%", "%$busca%"]);
} else {
    $q = db()->prepare('SELECT * FROM pacientes WHERE medico_id=? ORDER BY nome');
    $q->execute([uid()]);
}
$rows = $q->fetchAll(PDO::FETCH_ASSOC);
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
        <h1>Pacientes</h1>
        <a class="btn" href="novo.php">+ Novo paciente</a>
    </div>

    <div class="panel" style="margin-bottom:20px;">
        <form method="get" style="display:flex;gap:10px;">
            <input type="text" name="q" placeholder="Buscar por nome ou CPF..." value="<?=e($busca)?>" style="margin:0;flex:1;">
            <button type="submit" class="btn">Pesquisar</button>
            <?php if($busca): ?>
                <a href="index.php" class="btn" style="background:var(--muted)">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel">
        <?php if(empty($rows)): ?>
            <p style="text-align:center;color:var(--muted);padding:20px;">Nenhum paciente encontrado.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid var(--line);">
                        <th style="padding:10px;">Nome</th>
                        <th style="padding:10px;">CPF</th>
                        <th style="padding:10px;">Telefone</th>
                        <th style="padding:10px;text-align:right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rows as $r):?>
                    <tr style="border-bottom:1px solid var(--line);">
                        <td style="padding:10px;"><strong><?=e($r['nome'])?></strong></td>
                        <td style="padding:10px;"><?=e($r['cpf'] ?: '-')?></td>
                        <td style="padding:10px;"><?=e($r['telefone'] ?: '-')?></td>
                        <td style="padding:10px;text-align:right;">
                            <a href="ver.php?id=<?=$r['id']?>" style="color:var(--primary);margin-right:10px;">Prontuário</a>
                            <a href="editar.php?id=<?=$r['id']?>" style="color:var(--muted);margin-right:10px;">Editar</a>
                            <a href="excluir.php?id=<?=$r['id']?>" style="color:red;" onclick="return confirm('Tem certeza?');">Excluir</a>
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
