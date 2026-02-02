# Trip Request Manager

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Sistema completo de gerenciamento de pedidos de viagem desenvolvido com Laravel 11 (Back-end) + Vue.js 3 (Front-end) + Docker.

## 🚀 Executar em 3 minutos

### Pré-requisitos
- Docker e Docker Compose instalados
- Git

### Instalação Rápida

```bash
# 1. Clone o repositório
git clone <repo-url>
cd SobreVoar

# 2. Configure o ambiente Laravel
cd backend
cp .env.example .env
cd ..

# 3. Configure o ambiente Vue.js
cd frontend
cp .env.example .env
cd ..

# 4. Inicie os containers Docker
docker-compose up -d

# 5. Aguarde os containers iniciarem (30-60 segundos)
# Então execute as migrations e seeds
docker-compose exec laravel php artisan key:generate
docker-compose exec laravel php artisan migrate --seed

# 6. Instale as dependências do frontend
docker-compose exec frontend npm install
```

## 📱 Acessar a Aplicação

- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000/api
- **PHPMyAdmin**: http://localhost:8080

### Credenciais de Acesso

**Usuário Admin:**
- Email: `admin@example.com`
- Senha: `password`

**Usuário Regular:**
- Email: `test@example.com`
- Senha: `password`

## 🎯 Funcionalidades

### Back-end (Laravel 11)

#### Modelos & Relacionamentos
- **User**: Gerenciamento de usuários com controle de admin
- **TripRequest**: Pedidos de viagem com status (requested, approved, cancelled)
- Relacionamentos: User hasMany TripRequests | TripRequest belongsTo User

#### API Endpoints (com autenticação JWT/Sanctum)

**Autenticação:**
- `POST /api/register` - Registrar novo usuário
- `POST /api/login` - Login de usuário
- `POST /api/logout` - Logout de usuário
- `GET /api/user` - Obter dados do usuário autenticado

**Trip Requests:**
- `GET /api/trip-requests` - Listar pedidos (com filtros)
- `POST /api/trip-requests` - Criar pedido
- `GET /api/trip-requests/{id}` - Detalhes do pedido
- `PUT /api/trip-requests/{id}` - Atualizar pedido
- `DELETE /api/trip-requests/{id}` - Cancelar pedido
- `PATCH /api/trip-requests/{id}/status` - Atualizar status (admin only)

#### Lógica de Negócio
- Apenas ADMIN pode aprovar/cancelar pedidos de outros usuários
- Usuário comum só visualiza/edita SEUS próprios pedidos
- Cancelamento permitido apenas se status='requested'
- Notificações automáticas por email em mudanças de status

### Front-end (Vue 3 + Composition API)

#### Páginas/Rotas
- `/login` - Autenticação JWT com storage local
- `/dashboard` - Tabela responsiva com filtros
- `/trip-requests/create` - Formulário para novo pedido
- `/trip-requests/:id` - Detalhes do pedido

#### Componentes
- **DataTable.vue** - Tabela com filtros, paginação e ações
- **TripForm.vue** - Formulário validado com datepickers
- **StatusBadge.vue** - Badges coloridos por status
- **LoadingSpinner.vue** - Indicador de carregamento

#### Estado Global (Pinia)
- **authStore** - token, user, isAdmin
- **tripStore** - list, filters, loading, pagination

## 🛠 Tecnologias Utilizadas

### Back-end
- Laravel 11
- PHP 8.3
- MySQL 8.0
- Laravel Sanctum (Autenticação API)
- Laravel Notifications (Emails)
- PHPUnit/Pest (Testes)

### Front-end
- Vue.js 3 (Composition API)
- Vite
- Vue Router
- Pinia (State Management)
- Axios (HTTP Client)
- TailwindCSS (Styling)
- Headless UI
- Heroicons
- date-fns (Date formatting)

### DevOps
- Docker & Docker Compose
- Nginx
- Node 20

## 🧪 Testes

### Back-end (Laravel)
```bash
# Executar todos os testes
docker-compose exec laravel php artisan test

# Executar testes específicos
docker-compose exec laravel php artisan test --filter=AuthTest
docker-compose exec laravel php artisan test --filter=TripRequestTest
```

### Front-end (Vue)
```bash
# Testes unitários
docker-compose exec frontend npm run test:unit

# Testes E2E
docker-compose exec frontend npm run test:e2e
```

## 📂 Estrutura do Projeto

```
.
├── backend/                 # Laravel Application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   ├── Policies/
│   │   └── Notifications/
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   ├── routes/
│   └── tests/
│
├── frontend/                # Vue.js Application
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── stores/
│   │   ├── router/
│   │   ├── services/
│   │   └── assets/
│   └── public/
│
├── docker/                  # Docker configuration
│   └── nginx/
│
├── docker-compose.yml
└── README.md
```

## 🔧 Comandos Úteis

### Docker
```bash
# Iniciar containers
docker-compose up -d

# Parar containers
docker-compose down

# Ver logs
docker-compose logs -f

# Reconstruir containers
docker-compose up -d --build
```

### Laravel (Backend)
```bash
# Acessar container Laravel
docker-compose exec laravel bash

# Executar migrations
docker-compose exec laravel php artisan migrate

# Executar seeds
docker-compose exec laravel php artisan db:seed

# Limpar cache
docker-compose exec laravel php artisan cache:clear
docker-compose exec laravel php artisan config:clear

# Criar migration
docker-compose exec laravel php artisan make:migration create_table_name

# Criar controller
docker-compose exec laravel php artisan make:controller ControllerName
```

### Vue (Frontend)
```bash
# Acessar container frontend
docker-compose exec frontend sh

# Instalar dependências
docker-compose exec frontend npm install

# Build para produção
docker-compose exec frontend npm run build

# Lint
docker-compose exec frontend npm run lint
```

## 🔐 Segurança

- Autenticação via Laravel Sanctum
- Validação de dados em todas as requisições
- Proteção contra SQL Injection (Eloquent ORM)
- Proteção CSRF
- Passwords com hash bcrypt
- Políticas de autorização (Policies)
- Headers de segurança configurados

## 📊 Filtros e Paginação

A aplicação suporta os seguintes filtros:
- **Status**: requested, approved, cancelled
- **Destino**: Busca parcial no nome do destino
- **Data de início**: Filtro por data de partida
- **Data de fim**: Filtro por data de retorno

Paginação:
- 15 itens por página (configurável)
- Navegação entre páginas
- Informações de total de registros

## 🎨 UI/UX

- Design responsivo (mobile-first)
- Badges coloridos por status:
  - 🟡 Amarelo: Requested
  - 🟢 Verde: Approved
  - 🔴 Vermelho: Cancelled
- Skeleton loading states
- Validação de formulários em tempo real
- Mensagens de erro claras
- Confirmações para ações destrutivas

## 📝 Variáveis de Ambiente

### Backend (.env)
```env
APP_NAME="Trip Request Manager"
APP_URL=http://localhost:8000
DB_HOST=mysql
DB_DATABASE=trip_manager
DB_USERNAME=laravel
DB_PASSWORD=secret
MAIL_MAILER=log
```

### Frontend (.env)
```env
VITE_API_URL=http://localhost:8000/api
```

## 🤝 Contribuindo

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo LICENSE para mais detalhes.

## 👥 Autores

Desenvolvido como projeto de demonstração de sistema full-stack moderno.

## 🐛 Problemas Conhecidos

Se encontrar problemas:

1. **Porta já em uso**: Altere as portas no docker-compose.yml
2. **Permissões no Laravel**: Execute `docker-compose exec laravel chmod -R 777 storage bootstrap/cache`
3. **Frontend não carrega**: Certifique-se de que `npm install` foi executado

## 📞 Suporte

Para dúvidas ou problemas, abra uma issue no repositório.

---

Desenvolvido com ❤️ usando Laravel 11 + Vue.js 3 + Docker
