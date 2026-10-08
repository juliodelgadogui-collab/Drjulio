<?php require __DIR__.'/../api/bootstrap.php';
auth();

$resposta_ia = '';
$texto_original = $_POST['texto'] ?? '';
$acao = $_POST['acao'] ?? 'melhorar';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Obter API Key via variável de ambiente para não expor no frontend/repo
    $apiKey = getenv('GEMINI_API_KEY');

    if(!$apiKey) {
        $resposta_ia = "ERRO: Chave da API do Gemini (GEMINI_API_KEY) não configurada no ambiente do servidor.";
    } elseif(empty(trim($texto_original))) {
        $resposta_ia = "Forneça um texto para a IA processar.";
    } else {
        $prompt = "";
        if($acao === 'melhorar') {
            $prompt = "Reescreva o texto médico abaixo de forma mais profissional e clara, corrigindo possíveis erros gramaticais, mas mantendo estritamente as informações clínicas originais:\n\n";
        } elseif($acao === 'resumir') {
            $prompt = "Faça um resumo conciso do seguinte texto clínico, destacando apenas queixas principais, diagnósticos e condutas:\n\n";
        } elseif($acao === 'estruturar') {
            $prompt = "Estruture o texto abaixo em formato padrão de prontuário (Ex: HDA, Exame Físico, Conduta), separando claramente os tópicos:\n\n";
        }

        $prompt .= $texto_original . "\n\n(Nota: Aja como um assistente de redação médica. Não adicione novos sintomas, não faça diagnósticos próprios e não prescreva medicamentos que não estejam no texto original.)";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $apiKey;

        $data = [
            "contents" => [
                ["parts" => [["text" => $prompt]]]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode == 200) {
            $json = json_decode($response, true);
            if(isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                $resposta_ia = $json['candidates'][0]['content']['parts'][0]['text'];
            } else {
                $resposta_ia = "Erro ao extrair resposta da API.";
            }
        } else {
            $resposta_ia = "Erro na comunicação com a API (HTTP $httpCode).\n\nResponse:\n" . htmlspecialchars($response);
        }
    }
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
        <a href="/medical/cid/">CID-10</a>
        <a href="/medical/modelos/">Modelos</a>
        <a href="/medical/ia/" style="background:#18243a;color:#fff;">IA assistiva</a>
        <a href="/medical/configuracoes/">Configurações</a>
        <a href="/medical/logout.php">Sair</a>
    </nav>
</aside>
<main>
    <h1>IA Assistiva (Redação Médica)</h1>
    <p style="color:var(--muted); font-size:14px; max-width:800px;">
        Esta ferramenta utiliza Inteligência Artificial para auxiliar na redação, estruturação ou resumo de textos médicos.
        <strong>A IA não faz diagnósticos nem toma decisões clínicas. A responsabilidade final pelo conteúdo é sempre do médico.</strong>
    </p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div class="panel">
            <h3 style="margin-top:0;">Texto Original</h3>
            <form method="post">
                <input type="hidden" name="csrf" value="<?=csrf()?>">
                <textarea name="texto" rows="12" required placeholder="Cole aqui anotações rápidas, laudos extensos ou textos não estruturados..."><?=e($texto_original)?></textarea>

                <div style="margin-top:15px; margin-bottom:15px;">
                    <label>Ação desejada:</label>
                    <select name="acao">
                        <option value="melhorar" <?= $acao=='melhorar'?'selected':'' ?>>Melhorar redação e corrigir erros</option>
                        <option value="resumir" <?= $acao=='resumir'?'selected':'' ?>>Resumir informações principais</option>
                        <option value="estruturar" <?= $acao=='estruturar'?'selected':'' ?>>Estruturar tópicos (HDA, Conduta...)</option>
                    </select>
                </div>

                <button type="submit" class="btn" style="width:100%;">Processar com IA</button>
            </form>
        </div>

        <div class="panel" style="background:#f0f4f8;">
            <h3 style="margin-top:0; display:flex; justify-content:space-between; align-items:center;">
                Resultado
                <?php if($resposta_ia): ?>
                <button type="button" class="btn" style="background:#18243a; font-size:12px; padding:5px 10px;" onclick="navigator.clipboard.writeText(document.getElementById('resultado-ia').innerText); alert('Copiado!');">Copiar Resultado</button>
                <?php endif; ?>
            </h3>

            <div id="resultado-ia" style="white-space:pre-wrap; font-family:system-ui; background:#fff; padding:15px; border-radius:8px; border:1px solid var(--line); min-height: 250px;">
                <?php if($resposta_ia): ?>
                    <?=e($resposta_ia)?>
                <?php else: ?>
                    <span style="color:var(--muted); font-style:italic;">O resultado processado aparecerá aqui...</span>
                <?php endif; ?>
            </div>

            <?php if(!getenv('GEMINI_API_KEY')): ?>
            <div style="margin-top:15px; font-size:12px; color:#b42318;">
                ⚠️ <strong>Aviso ao administrador:</strong> A variável de ambiente <code>GEMINI_API_KEY</code> não foi encontrada. Configure-a no servidor para que a integração funcione.
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</div>
</body>
</html>
