# 🚀 ViralNest Community Platform v1.0.0

Sistema completo de comunidade digital viral com gamificação, ciclos de entrada por convite, cursos, planos de assinatura e integrações de pagamento.

---

## 📋 Requisitos

| Requisito | Versão mínima |
|-----------|--------------|
| PHP | 7.4+ |
| MySQL | 5.7+ / MariaDB 10.3+ |
| Apache | 2.4+ (mod_rewrite ativado) |
| Extensões PHP | pdo_mysql, curl, openssl, json |

---

## ⚡ Instalação Rápida

### 1. Fazer upload do sistema
Extraia o conteúdo de `viralnest.zip` na raiz do seu domínio (ou subpasta).

```
/public_html/          ← domínio raiz
/public_html/viralnest/ ← subpasta
```

### 2. Criar banco de dados
No cPanel ou phpMyAdmin, crie um banco MySQL e anote:
- Host (geralmente `localhost`)
- Nome do banco
- Usuário
- Senha

### 3. Rodar o instalador
Acesse no navegador:
```
https://seudominio.com/install/
```
ou
```
https://seudominio.com/viralnest/install/
```

O wizard de 5 etapas irá:
1. Verificar requisitos do servidor
2. Testar conexão com o banco
3. Importar todas as tabelas automaticamente
4. Configurar nome do site, URL e admin
5. Gerar o arquivo `config/config.php`

### 4. Após instalar — IMPORTANTE
**Delete ou proteja a pasta `/install/`** para evitar reinstalação acidental:
```bash
rm -rf install/
# ou via cPanel: remova a pasta install
```

---

## 🔐 Acesso inicial

| Área | URL | Login padrão |
|------|-----|-------------|
| Site | `seudominio.com/` | — |
| Admin | `seudominio.com/admin/login` | admin@viralnest.com / (senha definida no instalador) |

---

## ⚙️ Configurações do Sistema

Todas as regras da plataforma são editáveis em **Admin → Configurações**.

### Categorias de configurações

| Categoria | Exemplos |
|-----------|---------|
| **Geral** | Nome do site, tagline, logo, cores, URL |
| **Cadastro** | Permitir registro, exigir convite |
| **Pontos** | Pontos por convite, cadastro, aula concluída |
| **Níveis** | Pontos mínimos para cada nível |
| **Grupos** | Máx. grupos por usuário, pontos para criar |
| **Ranking** | Quantidade de usuários exibidos |
| **WhatsApp** | Token Whatsell, mensagens personalizadas |
| **Social** | Links de redes sociais |

### Níveis de gamificação (padrão)

| Nível | Pontos mínimos | Benefícios |
|-------|---------------|------------|
| 🧭 Explorer | 0 | Acesso básico |
| 📚 Mentor | 200 | +5% desconto em cursos |
| 🛡️ Guardian | 1.000 | +10% desconto, criar grupos |
| ⚡ Master | 3.000 | +20% desconto |
| 👑 Legend | 7.000 | +30% desconto, benefícios máximos |

---

## 💳 Gateways de Pagamento

Configure em **Admin → Gateways**.

### Mercado Pago
1. Acesse [mercadopago.com.br/developers](https://www.mercadopago.com.br/developers)
2. Crie uma aplicação
3. Copie o **Access Token** e **Public Key**
4. Configure o Webhook: `https://seudominio.com/webhook/mercadopago`

### Asaas
1. Acesse [asaas.com](https://www.asaas.com)
2. Configurações → Integrações → API Key
3. Configure o Webhook: `https://seudominio.com/webhook/asaas`

### EfiBank
1. Crie aplicação no [Portal EfiBank](https://dev.gerencianet.com.br) com escopo `pix.write`
2. Faça download do certificado `.pem` e suba para o servidor
3. Informe o caminho absoluto do certificado no admin
4. Webhook: `https://seudominio.com/webhook/efibank`

### Banco Inter
1. Crie aplicação OAuth2 no portal Developer do Inter
2. Faça download dos certificados mTLS (`.crt` e `.key`)
3. Suba para o servidor e informe os caminhos
4. Webhook: `https://seudominio.com/webhook/inter`

---

## 💬 WhatsApp (Whatsell)

Configure em **Admin → WhatsApp** ou **Admin → Configurações → WhatsApp**.

1. Obtenha seu token Bearer em [whatsell.online](https://api.whatsell.online)
2. Cole o token no campo **Token Whatsell**
3. Ative o toggle **Ativar notificações WhatsApp**
4. Personalize as mensagens usando as variáveis:

| Variável | Descrição |
|----------|-----------|
| `{name}` | Nome do usuário |
| `{level}` | Nível do usuário |
| `{points}` | Pontos ganhos |
| `{invited}` | Nome do usuário convidado |
| `{product}` | Nome do produto/curso |
| `{site_name}` | Nome da plataforma |

5. Use **Testar conexão** para verificar antes de ativar

---

## 🎓 Gerenciar Cursos

### Estrutura: Curso → Módulos → Aulas

1. **Admin → Cursos → Novo Curso**
   - Defina título, descrição, preço, preço em pontos, nível mínimo
   
2. **Admin → Cursos → Módulos**
   - Organize o conteúdo em módulos

3. **Admin → Módulos → Aulas**
   - Adicione aulas com URL de vídeo
   - O sistema **detecta automaticamente** o tipo:
     - URL do YouTube → embed YouTube
     - URL do Google Drive → embed Drive (use link de compartilhamento)
     - URL do Vimeo → embed Vimeo
     - Código `<iframe>` → exibido diretamente

#### Formatos de URL aceitos

```
YouTube:      https://www.youtube.com/watch?v=VIDEO_ID
              https://youtu.be/VIDEO_ID

Google Drive: https://drive.google.com/file/d/FILE_ID/view
              https://drive.google.com/open?id=FILE_ID

Vimeo:        https://vimeo.com/VIDEO_ID

iframe:       <iframe src="..." ...></iframe>
```

---

## 💎 Planos de Assinatura

Configure em **Admin → Planos**.

- Crie planos com preços, ciclos de cobrança e benefícios
- Cada plano pode ter:
  - **Desconto em cursos** (%)
  - **Multiplicador de pontos** (ex: 2x, 3x)
  - **Máximo de grupos** simultâneos
  - **Lista de funcionalidades** (exibida na página de planos)

---

## 🏆 Sistema de Gamificação

### Como os pontos funcionam
- Usuário se cadastra → recebe `points_register` pontos
- Usa um convite → quem convidou recebe `points_invite` pontos
- Conclui uma aula → recebe os pontos configurados na aula
- Conclui um curso → recebe `points_complete_course` pontos
- Troca por curso → pontos são debitados

### Multiplicador de plano
Se o usuário tem um plano ativo, os pontos ganhos são multiplicados:
```
Pontos ganhos = pontos_base × multiplicador_do_plano
```

### Desconto automático por nível
Os descontos em cursos são aplicados automaticamente pelo nível ou plano (o maior prevalece).

---

## 🔄 Ciclos de Entrada

Configure em **Admin → Ciclos**.

- **Ciclo ativo**: permite entradas sem convite até atingir o limite de vagas
- **Após esgotamento**: novos usuários precisam de convite (se `require_invite_after_cycle = true`)
- Múltiplos ciclos podem ser criados em sequência

---

## 📁 Estrutura de Pastas

```
/viralnest/
├── admin/              ← Painel administrativo
│   ├── AdminController.php
│   ├── index.php       ← Router do admin
│   └── views/          ← Views do admin
├── assets/
│   ├── css/            ← main.css + admin.css
│   ├── js/             ← main.js
│   └── img/uploads/    ← Avatares enviados (gravável)
├── config/
│   └── config.php      ← Gerado pelo instalador
├── controllers/        ← Lógica de cada rota
├── core/               ← Database, Auth, Services
├── database/
│   └── schema.sql      ← SQL completo para importação manual
├── install/            ← Wizard de instalação (remover após instalar)
├── models/             ← User, Course, Setting
├── views/              ← Templates das páginas
├── .htaccess           ← Rewrite rules Apache
└── index.php           ← Front controller
```

---

## 🛠️ Solução de Problemas

### "Página não encontrada" em todas as rotas
Verifique se o `mod_rewrite` está ativo no Apache:
```bash
a2enmod rewrite
service apache2 restart
```
E se o `.htaccess` tem permissão (`AllowOverride All` no VirtualHost).

### Erro de conexão com banco
- Verifique usuário/senha/host em `config/config.php`
- Em hospedagem compartilhada, o host pode ser `127.0.0.1` em vez de `localhost`

### Uploads de avatar não funcionam
Verifique permissões da pasta:
```bash
chmod 755 assets/img/uploads/
```

### WhatsApp não envia
1. Confirme que o token está correto em Configurações → WhatsApp
2. Use o botão "Testar conexão" no admin
3. Verifique os logs em Admin → WhatsApp

---

## 🔒 Segurança

- Todas as senhas são hasheadas com `password_hash(PHP_PASSWORD_DEFAULT)`
- Todas as queries usam Prepared Statements (PDO)
- Credenciais dos gateways são criptografadas com AES-256-CBC
- Tokens CSRF em todos os formulários
- Headers de segurança configurados no `.htaccess`
- Sessões com `httponly` e `strict_mode`

---

## 📞 Suporte

Sistema desenvolvido com ViralNest v1.0.0  
Documentação gerada automaticamente.
