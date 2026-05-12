README - Sistema de Controle de Sinistros Veiculares

1. SOBRE O PROJETO

Este sistema foi desenvolvido em PHP puro + MySQL + HTML5 + CSS3 + JavaScript Vanilla.
Ele oferece um painel administrativo moderno para controlar sinistros veiculares, status, prazos, orçamentos e histórico.

2. ESTRUTURA DE ARQUIVOS

/sinistros/
  index.php
  dashboard.php
  cadastrar.php
  editar.php
  trocar_status.php
  salvar_orcamento.php
  historico.php
  excluir.php
  db_connect.php
  style.css
  script.js
  script.sql
  README.txt
  /assets/
    /img/
    /icons/
  /includes/
    header.php
    sidebar.php
    footer.php
  /modals/
    modal_status.php
    modal_orcamento.php

3. COMO CONFIGURAR O WAMPSERVER

- Copie a pasta "sinistros" para a pasta "www" do seu WampServer.
  Exemplo: C:\wamp64\www\sinistros
- Certifique-se de que o Apache e o MySQL estejam em execução.
- Abra o Gerenciador do WampServer e inicie os serviços.

4. IMPORTANDO O BANCO DE DADOS

- Abra o phpMyAdmin em http://localhost/phpmyadmin
- Clique em "Importar"
- Selecione o arquivo "script.sql" localizado em c:\wamp64\www\sinistros\script.sql
- Clique em "Executar"

OU execute pelo terminal MySQL:

mysql -u root < caminho\para\script.sql

5. CONFIGURAÇÃO DO MYSQL

O sistema está configurado para usar o banco padrão:
- HOST: localhost
- DATABASE: sinistros_db
- USER: root
- PASSWORD: (vazia)

Caso altere as credenciais, modifique o arquivo db_connect.php.

6. COMO ACESSAR O SISTEMA

Abra o navegador e navegue para:
http://localhost/sinistros/index.php

Para acessar o dashboard:
http://localhost/sinistros/dashboard.php

7. FUNCIONALIDADES DISPONÍVEIS

- Cadastro de veículos com validação de placa.
- Controle de status com histórico permanente.
- Orçamento para motos dentro de oficina.
- Cálculo de dias decorridos e dias restantes.
- Identificação visual de atrasos com badges e efeitos.
- Busca instantânea por placa.
- Histórico de movimentações em timeline.
- Dashboard com métricas e gráfico Donut.

8. OBSERVAÇÕES IMPORTANTES

- Todas as consultas utilizam PDO com prepared statements para segurança.
- O sistema está pronto para rodar localmente no WampServer sem frameworks.
- Use o arquivo script.sql para criar o banco e inserir dados de teste.

9. SUPORTE

Se precisar, revise o arquivo db_connect.php e confirme as credenciais do MySQL.
