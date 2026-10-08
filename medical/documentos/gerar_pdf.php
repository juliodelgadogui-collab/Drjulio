<?php require __DIR__.'/../api/bootstrap.php';
auth();
$id = $_GET['id'] ?? 0;

$q = db()->prepare('
    SELECT d.*, p.nome as paciente_nome, p.cpf as paciente_cpf,
           m.nome as medico_nome, m.crm, m.especialidade, m.assinatura_path, m.logo_path, m.uf, m.endereco
    FROM documentos d
    JOIN pacientes p ON d.paciente_id = p.id
    JOIN medicos m ON d.medico_id = m.id
    WHERE d.id=? AND d.medico_id=?
');
$q->execute([$id, uid()]);
$doc = $q->fetch(PDO::FETCH_ASSOC);

if(!$doc) die('Documento não encontrado ou sem permissão.');

require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$mpdf = new \Mpdf\Mpdf([
    'margin_left' => 20,
    'margin_right' => 20,
    'margin_top' => 40,
    'margin_bottom' => 40,
    'margin_header' => 10,
    'margin_footer' => 10
]);

$logoHtml = '';
if($doc['logo_path'] && file_exists(__DIR__.'/../'.$doc['logo_path'])) {
    // Note that we should probably serve this from storage safely, but for mpdf it reads local file
    $logoHtml = '<img src="../'.$doc['logo_path'].'" style="max-height:60px;">';
} else {
    $logoHtml = '<h2 style="margin:0;color:#155eef;">Dr(a). '.e($doc['medico_nome']).'</h2>';
}

$header = '
<table width="100%" style="border-bottom:2px solid #155eef; padding-bottom:5px;">
    <tr>
        <td width="50%">'.$logoHtml.'</td>
        <td width="50%" style="text-align:right; font-size:12px; color:#666;">
            <strong>'.e($doc['especialidade']).'</strong><br>
            CRM: '.e($doc['crm']).'
        </td>
    </tr>
</table>';

$mpdf->SetHTMLHeader($header);

$assinaturaHtml = '';
if($doc['assinatura_path'] && file_exists(__DIR__.'/../'.$doc['assinatura_path'])) {
    $assinaturaHtml = '<img src="../'.$doc['assinatura_path'].'" style="max-height:80px;"><br>';
}
$assinaturaBlock = '
<div style="text-align:center; margin-top:50px;">
    '.$assinaturaHtml.'
    ___________________________________________________<br>
    Dr(a). '.e($doc['medico_nome']).'<br>
    CRM '.e($doc['uf']).' '.e($doc['crm']).'<br>
    '.e($doc['especialidade']).'
</div>';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$validarUrl = $protocol . $host . "/medical/validar/?codigo=" . $doc['codigo'];

// Gerar QR Code
$options = new QROptions([
    'version'      => 5,
    'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
    'eccLevel'     => QRCode::ECC_L,
]);
$qrcode = new QRCode($options);
$qrImageBase64 = base64_encode($qrcode->render($validarUrl));
$qrHtml = '<img src="data:image/svg+xml;base64,'.$qrImageBase64.'" width="60" height="60">';

$footer = '
<table width="100%" style="border-top:1px solid #ccc; padding-top:5px; font-size:10px; color:#999;">
    <tr>
        <td width="20%">'.$qrHtml.'</td>
        <td width="50%" align="center">
            Valide em: <strong>'.$validarUrl.'</strong><br>
            Código: <strong>'.$doc['codigo'].'</strong><br>
            Gerado em: '.date('d/m/Y H:i', strtotime($doc['emitido_em'])).'
        </td>
        <td width="30%" style="text-align:right;">
            '.e($doc['endereco']).'<br>
            Página {PAGENO}/{nbpg}
        </td>
    </tr>
</table>';
$mpdf->SetHTMLFooter($footer);

$html = '
<div style="margin-top:20px; font-family:sans-serif; font-size:14px; line-height:1.6;">
    <h3 style="text-align:center; text-transform:uppercase;">'.e($doc['tipo']).'</h3>
    <br>
    '.nl2br(e($doc['conteudo'])).'
    <br><br><br>
    <div style="text-align:right;">Data de emissão: '.date('d/m/Y', strtotime($doc['emitido_em'])).'</div>
    '.$assinaturaBlock.'
</div>';

$mpdf->WriteHTML($html);
$mpdf->Output('Documento_'.$doc['codigo'].'.pdf', 'I');
