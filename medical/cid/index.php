<?php require __DIR__.'/../api/bootstrap.php';
auth();
$busca = $_GET['q'] ?? '';
$rows = [];
if ($busca) {
    // Busca por código ou descrição
    $q = db()->prepare("SELECT * FROM cid10 WHERE codigo LIKE ? OR descricao LIKE ? LIMIT 100");
    $q->execute(["$busca%", "%$busca%"]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);
}
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
        <a href="/medical/cid/" style="background:#18243a;color:#fff;">CID-10</a>
        <a href="/medical/modelos/">Modelos</a>
        <a href="/medical/ia/">IA assistiva</a>
        <a href="/medical/configuracoes/">Configurações</a>
        <a href="/medical/logout.php">Sair</a>
    </nav>
</aside>
<main>
    <h1>Consulta CID-10</h1>
    <div class="panel" style="margin-bottom:20px;">
        <form method="get" style="display:flex;gap:10px;">
            <input type="text" name="q" placeholder="Digite o código ou parte da descrição..." value="<?=e($busca)?>" style="margin:0;flex:1;">
            <button type="submit" class="btn">Pesquisar</button>
            <?php if($busca): ?>
                <a href="index.php" class="btn" style="background:var(--muted)">Limpar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel">
        <?php if($busca): ?>
            <?php if(empty($rows)): ?>
                <p style="text-align:center;color:var(--muted);padding:20px;">Nenhum CID encontrado.</p>
            <?php else: ?>
                <table style="width:100%;border-collapse:collapse;text-align:left;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--line);">
                            <th style="padding:10px;width:100px;">Código</th>
                            <th style="padding:10px;">Descrição</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rows as $r):?>
                        <tr style="border-bottom:1px solid var(--line);">
                            <td style="padding:10px;"><strong><?=e($r['codigo'])?></strong></td>
                            <td style="padding:10px;"><?=e($r['descricao'])?></td>
                        </tr>
                        <?php endforeach?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php else: ?>
            <p style="text-align:center;color:var(--muted);padding:20px;">Use o campo acima para buscar códigos CID-10.<br>A base precisa ser preenchida caso esteja vazia.</p>

            <form method="post" action="importar.php" style="text-align:center;margin-top:20px;">
                <input type="hidden" name="csrf" value="<?=csrf()?>">
                <button type="submit" class="btn" style="background:var(--primary); font-size: 14px;" onclick="return confirm('Importar dados básicos da CID-10? Isso pode demorar alguns segundos.');">Importar CIDs mais comuns (Exemplo)</button>
            </form>
        <?php endif; ?>
    </div>
</main>
</div>
</body>
</html>
