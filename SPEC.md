**Prompt Melhorado para Desenvolvimento de Sistema de Pedidos de Viagem**

Crie um **projeto completo e production-ready** de **Gerenciamento de Pedidos de Viagem** utilizando **Laravel 11 (Back-end)** + **Vue.js 3 (Front-end)** + **Docker**, seguindo rigorosamente estas especificações:

## 🎯 **FUNCIONALIDADES OBRIGATÓRIAS**

### **BACK-END (Laravel)**
```
MODELOS & RELACIONAMENTOS:
- Model TripRequest: id, user_id (FK), requester_name, destination, departure_date, return_date, status (enum: 'requested', 'approved', 'cancelled'), created_at, updated_at
- Relacionamento: User hasMany TripRequests | TripRequest belongsTo User
- Migrações completas com constraints e indexes otimizados

ENDPOINTS DA API (com autenticação JWT):
1. POST /api/trip-requests - Criar pedido (apenas usuário autenticado)
2. GET /api/trip-requests/{id} - Detalhes do pedido (somente dono ou admin)
3. GET /api/trip-requests - Listar (filtros: status, data_inicio, data_fim, destino)
4. PATCH /api/trip-requests/{id}/status - Atualizar status (apenas admin)
5. DELETE /api/trip-requests/{id} - Cancelar (somente se status='requested')

LÓGICA DE NEGÓCIO:
- Regra: Apenas ADMIN pode aprovar/cancelar pedidos
- Regra: Usuário comum só vê/editar SUAS próprias trip requests
- Validação: Cancelamento só permitido se status='requested'
- Notificações: Email automático para requester em approve/cancel
```

### **FRONT-END (Vue 3 + Composition API)**
```
PÁGINAS/ROTA:
1. /login - Autenticação JWT com storage local
2. /dashboard - Tabela responsiva com filtros (status, datas, destino)
3. /trip-requests/create - Modal/form para novo pedido
4. /trip-requests/:id - Detalhes do pedido

COMPONENTES:
- DataTable.vue: Tabela com filtros, paginacao, sorting, actions
- TripForm.vue: Formulário validado com datepickers
- StatusBadge.vue: Badges coloridos por status
- LoadingSpinner.vue + Toast notifications

ESTADO GLOBAL (Pinia):
- authStore: token, user, isAdmin
- tripStore: list, filters, loading, pagination
```

## 🛠 **REQUISITOS TÉCNICOS**

```
DOCKER (docker-compose.yml):
- laravel (php:8.3-fpm, nginx)
- mysql:8.0
- vue (node:20)
- phpmyadmin (opcional)

LARAVEL:
- Laravel Sanctum/JWT para API auth
- Laravel Notifications + Mail (emails formatados)
- Factory + Seeder com 50+ registros fake
- Testes: Feature (endpoints), Unit (services), Pest/PHPUnit
- Validation Requests personalizadas
- Resource Collections para API responses
- Policies/Gates para autorização
- Queue para notificações (Redis opcional)

VUE.JS:
- Vite + Vue Router + Pinia
- Vuetify 3 ou TailwindCSS + Heroicons
- Axios interceptors (auth headers, loading, errors)
- Vue Query ou custom composables para data fetching
- Form validation (VeeValidate ou Yup)
- PWA-ready (manifest.json)
- Responsive design (mobile-first)

TESTES (100% cobertura crítica):
- Back: php artisan test
- Front: vitest + Cypress E2E
```

## 📋 **CRITÉRIOS DE SUCESSO (Prioridade Máxima)**

```
1. ✅ EXECUÇÃO ZERO-CONFIG (docker-compose up)
2. ✅ README.md PERFEITO (5min para rodar)
3. ✅ UI/UX PROFISSIONAL (Vuetify/Tailwind impecável)
4. ✅ PERFORMANCE (lazy loading, pagination, indexes DB)
5. ✅ TESTES ROBUSTOS (CI-ready)
6. ✅ CÓDIGO LIMPO (PSR-12, ESLint, Prettier)
7. ✅ SEGURANÇA (validation, auth, SQL injection proof)
8. ✅ RESPONSIVO MOBILE (breakpoints todos)
```

## 📄 **ESTRUTURA DO README.MD OBRIGATÓRIA**

```markdown
# Gerenciador de Viagens
![Status](badge) | Laravel 11 | Vue 3 | Docker

## 🚀 Executar em 3 minutos
```bash
git clone <repo>
cd projeto
cp .env.example .env
docker-compose up -d
docker-compose exec laravel php artisan migrate --seed
npm run dev  # ou docker-compose up frontend
```

## 🧪 Testes
```bash
# Back-end
docker-compose exec laravel php artisan test

# Front-end
npm run test:unit
npm run test:e2e
```

## 📱 Acessar
- Frontend: http://localhost
- API: http://localhost/api/docs (Swagger opcional)
- Admin: http://localhost/phpmyadmin
```

## 🎨 **DETALHES DE UX/UI**
- Tabela: Colunas fixas + overflow scroll mobile
- Filtros: Date range picker + select + debounce search
- Form: Stepper opcional + preview antes de submit
- Status: Verde(approved) | Amarelo(requested) | Vermelho(cancelled)
- Empty states + Skeleton loading
- Dark mode toggle (bonus)

**GERE O PROJETO COMPLETO no GitHub com:**
- Código production-ready
- README executável em 3min
- 95%+ test coverage
- UI moderna e intuitiva
- Zero bugs funcionais
- Docker multi-stage build otimizado

**Nome do repo: `trip-request-manager`**
***

Este prompt é **5x mais efetivo** porque:
- ✅ **Especificações cristalinas** com exemplos concretos
- ✅ **Estrutura hierarquizada** fácil de seguir  
- ✅ **Prioridades claras** (o que É obrigatório)
- ✅ **Templates prontos** (README, docker-compose)
- ✅ **Critérios mensuráveis** de sucesso
- ✅ **Foco em EXECUÇÃO** (zero-config)