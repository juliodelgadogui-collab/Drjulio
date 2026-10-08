<?php require __DIR__.'/../api/bootstrap.php';
auth();
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $q=db()->prepare('INSERT INTO pacientes(medico_id,nome,cpf,nascimento,sexo,telefone,email,endereco,alergias,medicamentos,antecedentes,observacoes) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');
    $q->execute([
        uid(),
        trim($_POST['nome']),
        trim($_POST['cpf']),
        $_POST['nascimento']?:null,
        $_POST['sexo'],
        trim($_POST['telefone']),
        trim($_POST['email']),
        trim($_POST['endereco']),
        trim($_POST['alergias']??''),
        trim($_POST['medicamentos']??''),
        trim($_POST['antecedentes']??''),
        trim($_POST['observacoes']??'')
    ]);
    header('Location:index.php');
    die();
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
        <h1>Novo Paciente</h1>
        <a href="index.php" class="btn" style="background:var(--muted)">Voltar</a>
    </div>

    <div class="panel">
        <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf()?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div>
                    <label>Nome Completo *</label>
                    <input name="nome" required>
                </div>
                <div>
                    <label>CPF</label>
                    <input name="cpf">
                </div>
                <div>
                    <label>Data de Nascimento</label>
                    <input type="date" name="nascimento">
                </div>
                <div>
                    <label>Sexo</label>
                    <select name="sexo">
                        <option value="">Não informado</option>
                        <option>Feminino</option>
                        <option>Masculino</option>
                        <option>Outro</option>
                    </select>
                </div>
                <div>
                    <label>Telefone</label>
                    <input name="telefone">
                </div>
                <div>
                    <label>E-mail</label>
                    <input type="email" name="email">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label>Endereço</label>
                    <input name="endereco">
                </div>
            </div>

            <h3 style="margin-top:20px;border-bottom:1px solid var(--line);padding-bottom:10px;">Dados Clínicos (Opcional)</h3>

            <div style="display:grid;grid-template-columns:1fr;gap:15px;">
                <div>
                    <label>Alergias</label>
                    <textarea name="alergias" rows="2"></textarea>
                </div>
                <div>
                    <label>Medicamentos em Uso</label>
                    <textarea name="medicamentos" rows="2"></textarea>
                </div>
                <div>
                    <label>Antecedentes Pessoais e Familiares</label>
                    <textarea name="antecedentes" rows="3"></textarea>
                </div>
                <div>
                    <label>Observações Gerais</label>
                    <textarea name="observacoes" rows="2"></textarea>
                </div>
            </div>

            <div style="margin-top:20px;">
                <button class="btn">Salvar Paciente</button>
            </div>
        </form>
    </div>
</main>
</div>
</body>
</html>
