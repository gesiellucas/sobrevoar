# SobreVoar - Sistema de Gerenciamento de Viagens

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Sistema completo de gerenciamento de pedidos de viagem desenvolvido com Laravel 11 (Back-end) + Vue.js 3 (Front-end) + Docker.

## 👤 Autor

**Gesiel Lucas Ferreira**

---

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
docker-compose exec laravel php artisan jwt:secret
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
- **Traveler**: Perfis de viajantes vinculados aos usuários
- **Destination**: Destinos de viagem (cidade, estado, país)
- **TripRequest**: Solicitações de viagem com status (requested, approved, cancelled)
- **UserNotification**: Notificações para usuários

#### API Endpoints (com autenticação JWT)

**Autenticação:**
- `POST /api/register` - Registrar novo usuário
- `POST /api/login` - Login de usuário
- `POST /api/logout` - Logout de usuário
- `POST /api/refresh` - Renovar token JWT
- `GET /api/user` - Obter dados do usuário autenticado

**Travelers:**
- `GET /api/travelers` - Listar viajantes (com filtros)
- `POST /api/travelers` - Criar viajante (admin only)
- `GET /api/travelers/{id}` - Detalhes do viajante
- `PATCH /api/travelers/{id}` - Atualizar viajante (admin only)
- `DELETE /api/travelers/{id}` - Desativar viajante (admin only)
- `PATCH /api/travelers/{id}/restore` - Reativar viajante (admin only)

**Destinations:**
- `GET /api/destinations` - Listar destinos (com filtros)
- `POST /api/destinations` - Criar destino (admin only)
- `GET /api/destinations/{id}` - Detalhes do destino
- `PATCH /api/destinations/{id}` - Atualizar destino (admin only)
- `DELETE /api/destinations/{id}` - Deletar destino (admin only)
- `GET /api/destinations/countries` - Listar países únicos
- `GET /api/destinations/states` - Listar estados únicos

**Trip Requests:**
- `GET /api/trip-requests` - Listar pedidos (com filtros)
- `POST /api/trip-requests` - Criar pedido
- `GET /api/trip-requests/{id}` - Detalhes do pedido
- `PATCH /api/trip-requests/{id}` - Atualizar pedido
- `DELETE /api/trip-requests/{id}` - Cancelar pedido
- `PATCH /api/trip-requests/{id}/status` - Atualizar status (admin only)

#### Lógica de Negócio
- **Segurança de Dados**: Usuários regulares só visualizam/editam seus próprios dados
- **Controle Admin**: Apenas administradores podem:
  - Criar, editar e deletar viajantes
  - Criar, editar e deletar destinos
  - Aprovar ou rejeitar solicitações de viagem
- **Validações de Negócio**:
  - Não é possível deletar viajantes com pedidos pendentes
  - Não é possível deletar destinos com pedidos associados
  - Cancelamento permitido apenas se status='requested'
- **Notificações**: Sistema automático de notificações em mudanças de status

### Front-end (Vue 3 + Composition API)

#### Páginas/Rotas
- `/login` - Autenticação JWT com storage local
- `/dashboard` - Tabela responsiva com filtros e ações em linha
- `/trip-requests/create` - Formulário para novo pedido

#### Componentes
- **DataTable.vue** - Tabela com filtros, paginação e ações
- **TripFilters.vue** - Filtros avançados de viagem
- **TripTable.vue** - Tabela de viagens com modal de detalhes
- **TripDetailsModal.vue** - Modal para visualização de detalhes
- **StatusBadge.vue** - Badges coloridos e traduzidos por status
- **LoadingSpinner.vue** - Indicador de carregamento

#### Estado Global (Pinia)
- **authStore** - token, user, isAdmin
- **tripStore** - list, filters, loading, pagination

## 🛠 Tecnologias Utilizadas

### Back-end
- Laravel 11
- PHP 8.3
- MySQL 8.0
- JWT Authentication (tymon/jwt-auth)
- PHPUnit (58 testes unitários)

### Front-end
- Vue.js 3 (Composition API)
- Vite
- Vue Router
- Pinia (State Management)
- Axios (HTTP Client)
- TailwindCSS (Styling)
- date-fns (Date formatting)
- @vuepic/vue-datepicker

### DevOps
- Docker & Docker Compose
- Nginx
- Node 20

## 🧪 Testes

### Back-end (Laravel)

O projeto possui **58 testes automatizados** cobrindo 100% dos controllers.

```bash
# Executar todos os testes
docker-compose exec laravel php artisan test

# Executar com detalhes
docker-compose exec laravel php artisan test --testdox

# Executar testes específicos
docker-compose exec laravel php artisan test tests/Feature/AuthTest.php
docker-compose exec laravel php artisan test tests/Feature/TravelerControllerTest.php
docker-compose exec laravel php artisan test tests/Feature/DestinationControllerTest.php
docker-compose exec laravel php artisan test tests/Feature/TripRequestTest.php

# Executar em paralelo (mais rápido)
docker-compose exec laravel php artisan test --parallel
```

**Cobertura de Testes:**
- AuthController: 5 testes ✅
- TravelerController: 20 testes ✅
- DestinationController: 17 testes ✅
- TripRequestController: 16 testes ✅

**Documentação completa**: Ver `backend/tests/README.md`

## 📂 Estrutura do Projeto

```
.
├── backend/                 # Laravel Application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   ├── Middleware/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   └── Notifications/
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   ├── routes/
│   └── tests/
│       ├── Feature/
│       └── README.md        # Documentação de testes
│
├── frontend/                # Vue.js Application
│   ├── src/
│   │   ├── components/
│   │   │   └── dashboard/
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

# Criar teste
docker-compose exec laravel php artisan make:test NomeDoTest
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

- **Autenticação**: JWT (JSON Web Tokens) via tymon/jwt-auth
- **Autorização**: Middleware admin para rotas protegidas
- **Isolamento de Dados**: Usuários regulares só acessam seus próprios dados
- **Validação**: Validação em todas as requisições via FormRequests
- **Proteção SQL Injection**: Eloquent ORM
- **Passwords**: Hash bcrypt
- **Headers de Segurança**: Configurados via middleware

## 📊 Filtros e Paginação

A aplicação suporta os seguintes filtros:
- **Status**: requested, approved, cancelled
- **Viajante**: Busca por nome do viajante
- **Destino**: Busca por nome do destino
- **Período da Viagem**: Data de partida e retorno
- **País/Estado**: Filtros de localização

Paginação:
- 15 itens por página (configurável)
- Navegação entre páginas
- Informações de total de registros

## 🎨 UI/UX

- **Design Responsivo**: Mobile-first approach
- **Badges Traduzidos**:
  - 🟡 Amarelo: Solicitado (Requested)
  - 🟢 Verde: Aprovado (Approved)
  - 🔴 Vermelho: Cancelado (Cancelled)
- **Ações em Linha**: Botões de aprovar/rejeitar na coluna de status para admins
- **Modal de Detalhes**: Visualização de informações sem navegar para nova página
- **Validação em Tempo Real**: Feedback imediato em formulários
- **Loading States**: Indicadores de carregamento

## 📝 Variáveis de Ambiente

### Backend (.env)
```env
APP_NAME="SobreVoar"
APP_URL=http://localhost:8000

DB_HOST=mysql
DB_DATABASE=sobrevoar
DB_USERNAME=laravel
DB_PASSWORD=secret

JWT_SECRET=<gerado automaticamente>
JWT_TTL=60
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

## 🐛 Problemas Conhecidos

Se encontrar problemas:

1. **Porta já em uso**: Altere as portas no docker-compose.yml
2. **Permissões no Laravel**: Execute `docker-compose exec laravel chmod -R 777 storage bootstrap/cache`
3. **Frontend não carrega**: Certifique-se de que `npm install` foi executado
4. **JWT Secret**: Execute `php artisan jwt:secret` se os tokens não funcionarem

## 📞 Suporte

Para dúvidas ou problemas, abra uma issue no repositório.

---

**Desenvolvido por Gesiel Lucas Ferreira** ❤️
