<?php require __DIR__.'/../api/bootstrap.php';
// Esta página é pública, não requer auth()

$codigo = $_GET['codigo'] ?? '';
$doc = null;

if($codigo) {
    $q = db()->prepare('
        SELECT d.emitido_em, d.tipo, p.nome as paciente_nome, m.nome as medico_nome, m.crm, m.uf, m.especialidade
        FROM documentos d
        JOIN pacientes p ON d.paciente_id = p.id
        JOIN medicos m ON d.medico_id = m.id
        WHERE d.codigo = ?
    ');
    $q->execute([trim($codigo)]);
    $doc = $q->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Validação de Documento Médico</title>
<style>
body { font-family: system-ui, sans-serif; background: #f5f7fb; color: #172033; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
.card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 500px; width: 100%; border-top: 5px solid #155eef; }
h1 { font-size: 20px; text-align: center; margin-top: 0; }
input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; margin-bottom: 15px; font-size: 16px; text-align: center; text-transform: uppercase; }
button { width: 100%; background: #155eef; color: #fff; border: none; padding: 12px; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; }
.success { background: #ecfdf3; border: 1px solid #a6f4c5; padding: 20px; border-radius: 8px; margin-top: 20px; }
.error { background: #fee4e2; border: 1px solid #fecdca; padding: 20px; border-radius: 8px; margin-top: 20px; color: #b42318; text-align: center; }
</style>
</head>
<body>
    <div class="card">
        <h1>Validação de Documento</h1>
        <p style="text-align:center; color:#666; font-size:14px; margin-bottom:25px;">
            Informe o código de validação impresso no rodapé do documento para verificar sua autenticidade.
        </p>
        <form method="get">
            <input type="text" name="codigo" placeholder="Ex: A1B2C3D4E5" value="<?=htmlspecialchars($codigo)?>" required>
            <button type="submit">Verificar Autenticidade</button>
        </form>

        <?php if($codigo): ?>
            <?php if($doc): ?>
                <div class="success">
                    <h3 style="margin-top:0; color:#027a48; text-align:center;">✓ Documento Autêntico</h3>
                    <p style="margin:0 0 10px 0;"><strong>Tipo:</strong> <?=htmlspecialchars(ucfirst($doc['tipo']))?></p>
                    <p style="margin:0 0 10px 0;"><strong>Data de Emissão:</strong> <?=date('d/m/Y H:i', strtotime($doc['emitido_em']))?></p>
                    <p style="margin:0 0 10px 0;"><strong>Paciente:</strong> <?=htmlspecialchars($doc['paciente_nome'])?></p>
                    <hr style="border:none; border-top:1px solid #a6f4c5; margin:15px 0;">
                    <p style="margin:0 0 5px 0;"><strong>Médico Responsável:</strong> Dr(a). <?=htmlspecialchars($doc['medico_nome'])?></p>
                    <p style="margin:0;"><strong>CRM:</strong> <?=htmlspecialchars($doc['crm'])?> <?=htmlspecialchars($doc['uf']?'- '.$doc['uf']:'')?></p>
                    <p style="margin:0;"><strong>Especialidade:</strong> <?=htmlspecialchars($doc['especialidade'])?></p>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>Documento não encontrado.</strong><br>
                    Verifique se o código foi digitado corretamente.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
