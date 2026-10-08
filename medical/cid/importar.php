<?php require __DIR__.'/../api/bootstrap.php';
auth();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    // Lista de CIDs comuns para demonstração e uso imediato
    $cids = [
        ['J00', 'Rinofaringite aguda [resfriado comum]'],
        ['J01', 'Sinusite aguda'],
        ['J02', 'Faringite aguda'],
        ['J03', 'Amigdalite aguda'],
        ['J04', 'Laringite e traqueíte agudas'],
        ['J06', 'Infecções agudas das vias aéreas superiores de localizações múltiplas e não especificadas'],
        ['J11', 'Influenza [gripe], vírus não identificado'],
        ['A09', 'Diarréia e gastroenterite de origem presumivelmente infecciosa'],
        ['I10', 'Hipertensão essencial (primária)'],
        ['E11', 'Diabetes mellitus não-insulino-dependente'],
        ['E78', 'Distúrbios do metabolismo de lipoproteínas e outras lipidemias'],
        ['M54', 'Dorsalgia'],
        ['H65', 'Otite média não-supurativa'],
        ['H10', 'Conjuntivite'],
        ['N39.0', 'Infecção do trato urinário, de local não especificado'],
        ['R50', 'Febre de origem desconhecida e de outras origens'],
        ['R51', 'Cefaléia']
    ];

    db()->beginTransaction();
    $stmt = db()->prepare("INSERT OR IGNORE INTO cid10 (codigo, descricao) VALUES (?, ?)");
    foreach($cids as $c) {
        $stmt->execute([$c[0], $c[1]]);
    }
    db()->commit();

    header('Location: index.php?q=J0');
    die();
}
