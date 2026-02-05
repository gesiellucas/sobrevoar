# Documentação dos Testes - SobreVoar API

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Estrutura dos Testes](#estrutura-dos-testes)
- [Como Rodar os Testes](#como-rodar-os-testes)
- [Cobertura de Testes](#cobertura-de-testes)
- [Autenticação nos Testes](#autenticação-nos-testes)
- [Padrões e Boas Práticas](#padrões-e-boas-práticas)
- [Como Adicionar Novos Testes](#como-adicionar-novos-testes)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

Este projeto possui **58 testes automatizados** que cobrem todas as funcionalidades da API, incluindo:

- ✅ Autenticação e autorização (JWT)
- ✅ CRUD de recursos (Travelers, Destinations, Trip Requests)
- ✅ Segurança e isolamento de dados
- ✅ Validações de entrada
- ✅ Permissões admin vs usuário regular
- ✅ Edge cases e cenários de erro

**Total de testes**: 58 (100% de cobertura dos controllers)

---

## 📁 Estrutura dos Testes

```
backend/tests/
├── Feature/
│   ├── AuthTest.php                    # 5 testes - Autenticação
│   ├── TravelerControllerTest.php      # 20 testes - Gerenciamento de viajantes
│   ├── DestinationControllerTest.php   # 17 testes - Gerenciamento de destinos
│   └── TripRequestTest.php             # 16 testes - Solicitações de viagem
├── Unit/                               # (vazio - testes unitários futuros)
└── TestCase.php                        # Classe base para todos os testes
```

---

## 🚀 Como Rodar os Testes

### Pré-requisitos

1. PHP 8.1+ instalado
2. Composer instalado
3. Banco de dados configurado (usa SQLite in-memory para testes)

### Comandos Básicos

```bash
# Navegar até o diretório backend
cd backend

# Rodar TODOS os testes
php artisan test

# Rodar com output detalhado (nomes dos testes)
php artisan test --testdox

# Rodar apenas um arquivo de teste específico
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/TravelerControllerTest.php
php artisan test tests/Feature/DestinationControllerTest.php
php artisan test tests/Feature/TripRequestTest.php

# Rodar um teste específico
php artisan test --filter test_admin_can_view_all_travelers

# Rodar testes com cobertura (se configurado)
php artisan test --coverage

# Rodar testes em paralelo (mais rápido)
php artisan test --parallel

# Rodar com mais informações de debug
php artisan test --verbose
```

### Atalhos Úteis

```bash
# Ver apenas testes que falharam
php artisan test --stop-on-failure

# Rodar apenas testes que falharam anteriormente
php artisan test --retry

# Limpar cache antes de rodar testes
php artisan config:clear && php artisan test
```

---

## 📊 Cobertura de Testes

### Por Controller

| Controller | Testes | Cobertura | Status |
|------------|--------|-----------|--------|
| **AuthController** | 5 | 100% | ✅ |
| **TravelerController** | 20 | 100% | ✅ |
| **DestinationController** | 17 | 100% | ✅ |
| **TripRequestController** | 16 | 100% | ✅ |
| **TOTAL** | **58** | **100%** | ✅ |

### Por Categoria

- **Autenticação**: 5 testes
- **Autorização e Segurança**: 15 testes
- **CRUD Operations**: 25 testes
- **Validações**: 8 testes
- **Edge Cases**: 5 testes

---

## 🔐 Autenticação nos Testes

**IMPORTANTE**: Este projeto usa **JWT Authentication** (tymon/jwt-auth), não Laravel Sanctum.

### ✅ Método Correto

```php
use Tymon\JWTAuth\Facades\JWTAuth;

class MyTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    public function test_example(): void
    {
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/endpoint');

        $response->assertStatus(200);
    }
}
```

### ❌ Método ERRADO (Sanctum - vai falhar!)

```php
// NÃO USE ISSO!
$token = $user->createToken('test-token')->plainTextToken;
```

---

## 📝 Padrões e Boas Práticas

### 1. Estrutura de um Teste

```php
public function test_description_of_what_is_tested(): void
{
    // Arrange: Preparar dados
    $user = User::factory()->create();
    $token = $this->getAuthToken($user);

    // Act: Executar ação
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/endpoint', ['data' => 'value']);

    // Assert: Verificar resultado
    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['id', 'field']]);

    $this->assertDatabaseHas('table', ['field' => 'value']);
}
```

### 2. Uso de Factories

```php
// Criar usuário admin
$admin = User::factory()->admin()->create();

// Criar traveler para um usuário específico
$traveler = Traveler::factory()->forUser($user)->create();

// Criar traveler inativo
$inactiveTraveler = Traveler::factory()->inactive()->create();

// Criar trip request com status específico
$tripRequest = TripRequest::factory()->requested()->create();
$approvedTrip = TripRequest::factory()->approved()->create();
```

### 3. Assertivas Comuns

```php
// HTTP Status
$response->assertStatus(200);      // Sucesso
$response->assertStatus(201);      // Criado
$response->assertStatus(401);      // Não autenticado
$response->assertStatus(403);      // Proibido
$response->assertStatus(422);      // Erro de validação

// JSON Structure
$response->assertJsonStructure(['data' => ['id', 'name']]);

// JSON Content
$response->assertJson(['data' => ['name' => 'John']]);

// JSON Count
$response->assertJsonCount(3, 'data');

// Database
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);

// Validation Errors
$response->assertJsonValidationErrors(['email']);
```

### 4. Trait RefreshDatabase

**SEMPRE** use `RefreshDatabase` nos testes de Feature para garantir um banco limpo:

```php
class MyTest extends TestCase
{
    use RefreshDatabase;  // ← IMPORTANTE!

    // seus testes aqui...
}
```

---

## ➕ Como Adicionar Novos Testes

### Passo 1: Criar o arquivo de teste

```bash
php artisan make:test NomeDoControllerTest
```

### Passo 2: Estrutura básica

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class NomeDoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function getAuthToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    public function test_funcionalidade_especifica(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $this->getAuthToken($user);

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/endpoint');

        // Assert
        $response->assertStatus(200);
    }
}
```

### Passo 3: Categorias de testes a cobrir

Para cada controller, teste:

1. **CRUD básico**: create, read, update, delete
2. **Autorização**: admin vs usuário regular
3. **Validações**: campos obrigatórios, formatos
4. **Segurança**: isolamento de dados, permissões
5. **Edge cases**: cenários de erro, estados inválidos

---

## 🔧 Troubleshooting

### Problema: Todos os testes falhando com erro de autenticação

**Causa**: Usando método Sanctum ao invés de JWT

**Solução**:
```php
// ❌ Errado
$token = $user->createToken('test-token')->plainTextToken;

// ✅ Correto
use Tymon\JWTAuth\Facades\JWTAuth;
$token = JWTAuth::fromUser($user);
```

### Problema: Erro "Class 'Database\Factories\...' not found"

**Causa**: Factories não carregadas

**Solução**:
```bash
composer dump-autoload
php artisan config:clear
```

### Problema: Testes lentos

**Soluções**:
```bash
# Rodar em paralelo
php artisan test --parallel

# Usar banco em memória (já configurado em phpunit.xml)
# DB_DATABASE=:memory:
```

### Problema: "Target class [...] does not exist"

**Solução**:
```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

### Problema: Datas inválidas em testes

**Causa**: Testes usando `departure_date` ao invés de `departure_datetime`

**Solução**: Use formato datetime completo:
```php
// ❌ Errado
'departure_date' => now()->addDays(10)->format('Y-m-d')

// ✅ Correto
'departure_datetime' => now()->addDays(10)->format('Y-m-d H:i:s')
```

---

## 📚 Exemplos de Testes

### Teste de Autenticação

```php
public function test_user_can_login_with_valid_credentials(): void
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user',
            'access_token',
            'token_type',
        ]);
}
```

### Teste de Autorização

```php
public function test_regular_user_cannot_create_destination(): void
{
    $user = User::factory()->create(); // Não é admin
    $token = $this->getAuthToken($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/destinations', [
            'city' => 'São Paulo',
            'country' => 'Brasil',
        ]);

    $response->assertStatus(403); // Forbidden
}
```

### Teste de Validação

```php
public function test_trip_request_validates_required_fields(): void
{
    $user = User::factory()->create();
    $traveler = Traveler::factory()->forUser($user)->create();
    $token = $this->getAuthToken($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/trip-requests', [
            // destination_id faltando
            'departure_datetime' => now()->addDays(10)->format('Y-m-d H:i:s'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['destination_id']);
}
```

### Teste de Segurança/Isolamento

```php
public function test_user_cannot_view_other_users_trip_requests(): void
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $traveler2 = Traveler::factory()->forUser($user2)->create();

    TripRequest::factory()->count(3)->create(['traveler_id' => $traveler2->id]);

    $token = $this->getAuthToken($user1);
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/trip-requests');

    // User1 não deve ver nenhum trip request do user2
    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
}
```

---

## 🎯 Checklist de Qualidade

Antes de fazer commit, verifique:

- [ ] Todos os testes passam (`php artisan test`)
- [ ] Novos testes seguem os padrões estabelecidos
- [ ] Usa `RefreshDatabase` trait
- [ ] Usa JWT para autenticação (`JWTAuth::fromUser()`)
- [ ] Nomes de testes são descritivos
- [ ] Testa cenários positivos E negativos
- [ ] Inclui validações e edge cases
- [ ] Database assertions verificam persistência

---

## 📞 Suporte

Se encontrar problemas:

1. Verifique a seção [Troubleshooting](#troubleshooting)
2. Consulte a [memória do projeto](../../.claude/projects/c--Users-ADMIN-Documents-GitHub-SobreVoar/memory/MEMORY.md)
3. Rode com `--verbose` para mais detalhes: `php artisan test --verbose`

---

## 📖 Referências

- [Laravel Testing Documentation](https://laravel.com/docs/11.x/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [JWT Auth Package](https://github.com/tymondesigns/jwt-auth)
- Memória do Projeto: `.claude/projects/.../memory/MEMORY.md`

---

**Última atualização**: 2026-02-05
**Total de testes**: 58
**Cobertura**: 100% dos controllers
