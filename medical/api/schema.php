<?php require __DIR__.'/bootstrap.php';
db()->exec("
CREATE TABLE IF NOT EXISTS medicos(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    crm TEXT NOT NULL,
    especialidade TEXT,
    email TEXT,
    senha_hash TEXT NOT NULL,
    assinatura_path TEXT,
    logo_path TEXT,
    telefone TEXT,
    uf TEXT,
    endereco TEXT,
    config_pdf TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pacientes(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    medico_id INTEGER NOT NULL,
    nome TEXT NOT NULL,
    cpf TEXT,
    nascimento TEXT,
    sexo TEXT,
    telefone TEXT,
    email TEXT,
    endereco TEXT,
    alergias TEXT,
    medicamentos TEXT,
    antecedentes TEXT,
    observacoes TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(medico_id) REFERENCES medicos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS atendimentos(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    medico_id INTEGER NOT NULL,
    paciente_id INTEGER NOT NULL,
    data TEXT NOT NULL,
    queixa TEXT,
    historico TEXT,
    exame_fisico TEXT,
    avaliacao TEXT,
    conduta TEXT,
    exames TEXT,
    resultados TEXT,
    observacoes TEXT,
    evolucao TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    FOREIGN KEY(paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cid10(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    codigo TEXT UNIQUE NOT NULL,
    descricao TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS documentos(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    medico_id INTEGER NOT NULL,
    paciente_id INTEGER NOT NULL,
    tipo TEXT NOT NULL,
    conteudo TEXT NOT NULL,
    codigo TEXT UNIQUE NOT NULL,
    assinatura_path TEXT,
    emitido_em TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    FOREIGN KEY(paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS modelos(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    medico_id INTEGER NOT NULL,
    tipo TEXT NOT NULL,
    nome TEXT NOT NULL,
    conteudo TEXT NOT NULL,
    ativo INTEGER DEFAULT 1,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(medico_id) REFERENCES medicos(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS agenda(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    medico_id INTEGER NOT NULL,
    paciente_id INTEGER,
    data_hora TEXT NOT NULL,
    duracao INTEGER DEFAULT 30,
    status TEXT DEFAULT 'agendado',
    observacao TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(medico_id) REFERENCES medicos(id) ON DELETE CASCADE,
    FOREIGN KEY(paciente_id) REFERENCES pacientes(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS atendimento_cid(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    atendimento_id INTEGER NOT NULL,
    cid_codigo TEXT NOT NULL,
    FOREIGN KEY(atendimento_id) REFERENCES atendimentos(id) ON DELETE CASCADE
);
");
// Migrations para adicionar colunas se não existirem
try { db()->exec("ALTER TABLE medicos ADD COLUMN telefone TEXT;"); } catch(Exception $e) {}
try { db()->exec("ALTER TABLE medicos ADD COLUMN uf TEXT;"); } catch(Exception $e) {}
try { db()->exec("ALTER TABLE medicos ADD COLUMN endereco TEXT;"); } catch(Exception $e) {}
try { db()->exec("ALTER TABLE medicos ADD COLUMN config_pdf TEXT;"); } catch(Exception $e) {}

try { db()->exec("ALTER TABLE pacientes ADD COLUMN alergias TEXT;"); } catch(Exception $e) {}
try { db()->exec("ALTER TABLE pacientes ADD COLUMN medicamentos TEXT;"); } catch(Exception $e) {}
try { db()->exec("ALTER TABLE pacientes ADD COLUMN antecedentes TEXT;"); } catch(Exception $e) {}

try { db()->exec("ALTER TABLE atendimentos ADD COLUMN exame_fisico TEXT;"); } catch(Exception $e) {}
try { db()->exec("ALTER TABLE atendimentos ADD COLUMN resultados TEXT;"); } catch(Exception $e) {}
try { db()->exec("ALTER TABLE atendimentos ADD COLUMN evolucao TEXT;"); } catch(Exception $e) {}

try { db()->exec("ALTER TABLE documentos ADD COLUMN assinatura_path TEXT;"); } catch(Exception $e) {}

echo 'Banco inicializado/atualizado.';
