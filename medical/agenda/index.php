<?php require __DIR__.'/../api/bootstrap.php';
auth();
// Busca agendamentos do mês atual (simplificado, pega todos futuros/recentes por enquanto)
$q = db()->prepare('SELECT a.*, p.nome as paciente_nome FROM agenda a LEFT JOIN pacientes p ON a.paciente_id = p.id WHERE a.medico_id=? ORDER BY a.data_hora ASC');
$q->execute([uid()]);
$agendamentos = $q->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por data (YYYY-MM-DD)
$agrupados = [];
foreach($agendamentos as $a) {
    $dataStr = substr($a['data_hora'], 0, 10);
    $agrupados[$dataStr][] = $a;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<?php require __DIR__.'/../partials/head.php';?>
<style>
.agenda-dia { margin-bottom: 30px; }
.agenda-dia h3 { border-bottom: 2px solid var(--primary); padding-bottom: 5px; margin-bottom: 15px; }
.agenda-item { background: #f9fafb; border: 1px solid var(--line); border-radius: 8px; padding: 15px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
.agenda-item.status-cancelado { opacity: 0.6; text-decoration: line-through; }
.agenda-item.status-concluido { border-left: 4px solid #12b76a; }
.agenda-time { font-weight: bold; font-size: 1.2em; color: var(--ink); width: 80px; }
.agenda-info { flex: 1; padding: 0 15px; }
.badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.badge-agendado { background: #e0f2fe; color: #026aa7; }
.badge-confirmado { background: #d1fadf; color: #039855; }
.badge-cancelado { background: #fee4e2; color: #d92d20; }
.badge-concluido { background: #ecfdf3; color: #027a48; }
</style>
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
        <h1>Agenda de Consultas</h1>
        <a class="btn" href="novo.php">+ Novo Agendamento</a>
    </div>

    <div class="panel">
        <?php if(empty($agrupados)): ?>
            <p style="text-align:center;color:var(--muted);padding:20px;">Nenhum agendamento encontrado.</p>
        <?php else: ?>
            <?php foreach($agrupados as $data => $itens): ?>
                <div class="agenda-dia">
                    <h3><?= date('d/m/Y', strtotime($data)) ?></h3>
                    <?php foreach($itens as $item):
                        $statusClass = 'status-' . strtolower($item['status']);
                        $badgeClass = 'badge-' . strtolower($item['status']);
                    ?>
                        <div class="agenda-item <?= $statusClass ?>">
                            <div class="agenda-time">
                                <?= date('H:i', strtotime($item['data_hora'])) ?>
                            </div>
                            <div class="agenda-info">
                                <strong><?= $item['paciente_nome'] ? e($item['paciente_nome']) : 'Paciente não registrado (avulso)' ?></strong><br>
                                <span style="font-size:13px;color:var(--muted);">
                                    Duração: <?= $item['duracao'] ?> min |
                                    Obs: <?= e($item['observacao'] ?: '-') ?>
                                </span>
                            </div>
                            <div style="text-align:right;">
                                <span class="badge <?= $badgeClass ?>" style="margin-bottom:8px;"><?= ucfirst($item['status']) ?></span><br>
                                <a href="editar.php?id=<?=$item['id']?>" style="font-size:14px;color:var(--primary);margin-right:10px;">Editar</a>
                                <?php if($item['paciente_id']): ?>
                                    <a href="../atendimentos/novo.php?paciente_id=<?=$item['paciente_id']?>&agenda_id=<?=$item['id']?>" style="font-size:14px;color:var(--primary);">Atender →</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</div>
</body>
</html>
