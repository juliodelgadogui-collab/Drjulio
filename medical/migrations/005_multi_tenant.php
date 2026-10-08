<?php
// Expansão sem apagar registros clínicos existentes.
db()->exec("CREATE TABLE IF NOT EXISTS organizacoes (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, tipo TEXT NOT NULL CHECK(tipo IN ('clinica','individual')), ativo INTEGER NOT NULL DEFAULT 1, criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)");
db()->exec("CREATE TABLE IF NOT EXISTS super_administradores (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT NOT NULL, email TEXT NOT NULL UNIQUE, senha_hash TEXT NOT NULL, ativo INTEGER NOT NULL DEFAULT 1, criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP)");
db()->exec("CREATE TABLE IF NOT EXISTS organizacao_usuarios (organizacao_id INTEGER NOT NULL REFERENCES organizacoes(id), medico_id INTEGER NOT NULL REFERENCES medicos(id), papel TEXT NOT NULL CHECK(papel IN ('admin_clinica','medico')), ativo INTEGER NOT NULL DEFAULT 1, PRIMARY KEY(organizacao_id,medico_id))");
ensure_column('medicos','ativo','INTEGER NOT NULL DEFAULT 1');
ensure_column('medicos','organizacao_id','INTEGER REFERENCES organizacoes(id)');
db()->exec('CREATE INDEX IF NOT EXISTS idx_medicos_organizacao ON medicos(organizacao_id)');
// Cada médico legado recebe sua própria organização, sem compartilhar pacientes.
$legacy=db()->query('SELECT id,nome_completo FROM medicos WHERE organizacao_id IS NULL')->fetchAll();
foreach($legacy as $m){
 $i=db()->prepare("INSERT INTO organizacoes(nome,tipo) VALUES(?,'individual')");$i->execute(['Consultório de '.$m['nome_completo']]);$oid=(int)db()->lastInsertId();
 db()->prepare('UPDATE medicos SET organizacao_id=? WHERE id=?')->execute([$oid,$m['id']]);
 db()->prepare("INSERT OR IGNORE INTO organizacao_usuarios(organizacao_id,medico_id,papel) VALUES(?,?,'admin_clinica')")->execute([$oid,$m['id']]);
}
