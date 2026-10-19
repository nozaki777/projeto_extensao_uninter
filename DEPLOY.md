# Instruções de Deploy - SGBD

## Pré-requisitos
- Servidor Web com suporte a PHP 7.4 ou superior (Apache, Nginx, LiteSpeed).
- Servidor de Banco de Dados MySQL ou MariaDB.
- Extensão PDO ativada no PHP.

## Passos para Implantação

1. **Upload dos Arquivos:**
   Faça o upload de todos os arquivos e pastas da raiz do projeto para o diretório público do seu servidor web (ex: `public_html`, `www`, `htdocs`).
   Certifique-se de que o arquivo `.htaccess` foi enviado. Ele é oculto na maioria dos gerenciadores de arquivos e sistemas operacionais, mas é crucial para o roteamento amigável das URLs (esconder as extensões `.php`).

2. **Criação do Banco de Dados:**
   Acesse o painel do seu banco de dados (ex: phpMyAdmin) no servidor de hospedagem.
   - Crie um novo banco de dados (ex: `meu_banco`).
   - Importe o script SQL gerado no arquivo `/src/safra.sql`. Ele criará todas as tabelas necessárias com seus índices.

3. **Configuração da Conexão:**
   Abra o arquivo `/src/db.php` e edite as credenciais com os dados do seu banco de dados em produção:
   ```php
   $host = 'localhost'; // Geralmente localhost
   $db   = 'meu_banco'; // Nome do banco que você criou
   $user = 'meu_usuario'; // Usuário do banco
   $pass = 'minha_senha_secreta'; // Senha do banco
   ```

4. **Usuário Administrador:**
   O script `safra.sql` já insere um usuário administrador com as seguintes credenciais:
   - **Email:** `bruno@bancodedados.org`
   - **Senha:** `abc123`

   **Recomendação:** Faça login logo após o deploy e altere esta senha.

## Observações PWA e Segurança
- Para que o PWA (manifest, service worker) e funcionalidades modernas funcionem perfeitamente em mobile, seu site precisa estar servido em `HTTPS`.
- Você deve ajustar o `start_url` no arquivo `manifest.json` e os caches no `service-worker.js` caso seu sistema não fique na raiz (como `/sgbd/`). Se você colocar na raiz de um domínio, remova `/sgbd/` dos arquivos `manifest.json` e `service-worker.js`.
