# Sistema de Sinistros Veiculares

Interface web para controle de sinistros veiculares, com cadastro de veiculos, historico de movimentacoes, controle de status, acompanhamento de prazos e dashboard administrativo.

![Interface do sistema](docs/interface-sinistros.svg)

## Destaques

- Interface responsiva inspirada em aplicativo mobile.
- Cadastro e edicao de veiculos com validacao de placa.
- Busca instantanea por placa, proprietario, condutor, cidade, processo ou status.
- Controle de status com historico permanente.
- Dashboard com indicadores e grafico de distribuicao.
- Identificacao visual de prazos e registros em atraso.
- Fluxo de orcamento para motos em oficina.
- Backend em PHP puro com PDO e MySQL.

## Tecnologias

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript Vanilla
- Chart.js
- Font Awesome
- WampServer para ambiente local

## Estrutura

```text
sinistros/
├── assets/
├── docs/
│   └── interface-sinistros.svg
├── includes/
│   ├── footer.php
│   ├── header.php
│   └── sidebar.php
├── modals/
│   ├── modal_orcamento.php
│   └── modal_status.php
├── cadastrar.php
├── dashboard.php
├── db_connect.php
├── editar.php
├── excluir.php
├── historico.php
├── index.php
├── salvar_orcamento.php
├── script.js
├── script.sql
├── style.css
└── trocar_status.php
```

## Como Rodar Localmente

1. Copie a pasta do projeto para:

```text
C:\wamp64\www\sinistros
```

2. Inicie Apache e MySQL no WampServer.

3. Importe o banco no phpMyAdmin:

```text
http://localhost/phpmyadmin
```

4. Use o arquivo `script.sql` ou uma das migracoes disponiveis no projeto.

5. Acesse no navegador:

```text
http://localhost/sinistros/index.php
```

## Banco de Dados

Configuracao padrao em `db_connect.php`:

```text
Host: localhost
Database: sinistros_db
User: root
Password: vazio
```

Para deploy na Hostinger, ajuste `db_connect.php` com as credenciais do banco de producao.

## Paginas Principais

- `index.php`: lista de veiculos e busca.
- `dashboard.php`: resumo administrativo.
- `cadastrar.php`: cadastro de novo veiculo.
- `editar.php`: edicao de registro.
- `historico.php`: linha do tempo de movimentacoes.

## Deploy

Antes de enviar para a hospedagem:

- Confirme as credenciais do banco em `db_connect.php`.
- Importe a estrutura SQL no banco da Hostinger.
- Nao envie arquivos locais sensiveis, como `.vscode/sftp.json`.
- Teste o fluxo de cadastro, troca de status, historico e exclusao.

## Licenca

Projeto privado. Todos os direitos reservados.
