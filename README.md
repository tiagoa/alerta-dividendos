# Alerta de dividendos

Script que faz o login no portal do CEI, busca os dividendos a receber, extrai os dividendos da data atual, soma todos os valores e envia via e-mail.

# Como usar
- Clone o repositório:
  ```bash
  git clone https://github.com/tiagoa/alerta-dividendos.git
  ```
- Insira suas credenciais do CEI e e-mail.
- Instale as dependências:
  ```bash
  composer install
  ```
- Registre na cron, por exemplo todo dia 9h da manhã:
  ```
  0 9 * * * /usr/bin/php /path/absoluto/alerta-dividendos/index.php
  ```
- Receba um e-mail com os dividendos a receber.