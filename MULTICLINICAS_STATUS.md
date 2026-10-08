# DrJulio — Multi-clínicas (desenvolvimento)
Este branch introduz a estrutura inicial de organizações e Super Administrador.
**Não está homologado para produção clínica.** Ainda faltam o isolamento por organização em todas as consultas SQL, papéis de equipe, testes de autorização, testes de integração PHP/SQLite, backups e revisão LGPD.
O Super ADM não deve ter acesso automático aos prontuários.
A migração 005 preserva registros existentes e cria consultórios individuais para médicos legados.
Nunca publique bancos SQLite reais, segredos, logs ou sessões neste repositório.
