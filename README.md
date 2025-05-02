# Sistema de Enquetes em Tempo Real

Um sistema de votação em enquetes desenvolvido com Laravel, que apresenta atualização em tempo real dos resultados usando Laravel Reverb e WebSockets.

## 🚀 Recursos

-   Criação e gerenciamento de enquetes
-   Sistema de votação com atualização em tempo real
-   Interface responsiva e intuitiva
-   Status de enquetes (não iniciada, ativa, encerrada)
-   Animações visuais para feedback de novos votos

## 📋 Requisitos

-   PHP 8.2 ou superior
-   Composer
-   Node.js e npm
-   MySQL 5.7+ ou MariaDB 10.3+

## 🔧 Instalação

1. Clone o repositório:

```bash
git clone <url-do-repositorio>
cd signo-tech-test
```

2. Instale as dependências PHP:

```bash
composer install
```

3. Instale as dependências JavaScript:

```bash
npm install
```

4. Crie o arquivo de ambiente:

```bash
cp .env.example .env
```

5. Gere a chave da aplicação:

```bash
php artisan key:generate
```

6. Configure o banco de dados MySQL:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=signo_polls
DB_USERNAME=root
DB_PASSWORD=suasenhaaqui
```

7. Execute as migrações

```bash
php artisan migrate
```

## ⚙️ Configuração do Broadcasting

O sistema utiliza Laravel Reverb para broadcasting em tempo real:

1. Configure as variáveis de ambiente no arquivo .env:

```bash
BROADCAST_CONNECTION=reverb
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## 🖥️ Executando o projeto

Execute o comando de desenvolvimento que iniciará todos os serviços necessários:

```bash
composer dev
```

Este comando executa simultaneamente:

-   Servidor Laravel (php artisan serve)
-   Processamento de filas (php artisan queue:work)
-   Log em tempo real (php artisan pail)
-   Servidor WebSocket Reverb (php artisan reverb:start)
-   Servidor de desenvolvimento Vite (npm run dev)

Acesse a aplicação em: http://localhost:8000

## 🧩 Estrutura do Projeto

### Backend

-   app/Http/Controllers/API/PollController.php - Controlador para gerenciar enquetes
-   app/Http/Controllers/API/PollOptionsController.php - Controlador para gerenciar opções de enquete e votos
-   app/Events/PollOptionVoted.php - Evento disparado quando um voto é registrado
-   routes/channels.php - Configuração dos canais de broadcasting
-   routes/api.php - Configuração das rotas de API para enquetes e votação

### Frontend

-   resources/views/main.blade.php - Interface principal da aplicação
-   resources/views/create.blade.php - Interface de criação de enquetes
-   resources/views/edit.blade.php - Interface de edição e deleção de enquetes
-   resources/js/echo.js - Configuração do cliente Laravel Echo para WebSockets

## 🛠️ Tecnologias Utilizadas

-   Laravel 11: Framework PHP principal
-   Laravel Reverb: Para broadcasting via WebSockets
-   Laravel Echo: Cliente JavaScript para WebSockets
-   MySQL: Banco de dados relacional
-   Tailwind CSS: Framework CSS
-   Vite: Bundler para assets frontend
