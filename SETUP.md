# Quick Setup Guide

## Instalação Rápida (3 minutos)

### 1. Preparar ambiente

```bash
# Backend
cd backend
cp .env.example .env

# Frontend
cd ../frontend
cp .env.example .env
cd ..
```

### 2. Iniciar containers

```bash
docker-compose up -d
```

Aguarde cerca de 30-60 segundos para os containers iniciarem completamente.

### 3. Configurar Laravel

```bash
# Gerar chave da aplicação
docker-compose exec laravel php artisan key:generate

# Executar migrations e seeders
docker-compose exec laravel php artisan migrate --seed
```

### 4. Instalar dependências do frontend

```bash
docker-compose exec frontend npm install
```

### 5. Acessar a aplicação

Abra seu navegador em: http://localhost:5173

**Login:**
- Admin: admin@example.com / password
- User: test@example.com / password

## Troubleshooting

### Erro de permissão no Laravel
```bash
docker-compose exec laravel chmod -R 777 storage bootstrap/cache
```

### Porta já em uso
Edite o `docker-compose.yml` e altere as portas:
```yaml
ports:
  - "8001:80"  # Backend (ao invés de 8000)
  - "5174:5173"  # Frontend (ao invés de 5173)
```

### Frontend não carrega
```bash
# Reiniciar o container do frontend
docker-compose restart frontend

# Ver logs
docker-compose logs -f frontend
```

### Recriar banco de dados
```bash
docker-compose exec laravel php artisan migrate:fresh --seed
```

## Comandos Úteis

```bash
# Ver logs de todos os containers
docker-compose logs -f

# Ver logs do Laravel
docker-compose logs -f laravel

# Ver logs do Frontend
docker-compose logs -f frontend

# Parar containers
docker-compose down

# Parar e remover volumes
docker-compose down -v

# Reconstruir containers
docker-compose up -d --build
```

## Estrutura de URLs

- Frontend: http://localhost:5173
- Backend API: http://localhost:8000/api
- PHPMyAdmin: http://localhost:8080
  - Server: mysql
  - Username: root
  - Password: root

## Próximos Passos

1. Explore o dashboard em http://localhost:5173
2. Crie um novo trip request
3. Como admin, aprove/rejeite requests
4. Teste os filtros e paginação
5. Veja as notificações por email nos logs: `docker-compose logs -f laravel | grep mail`
