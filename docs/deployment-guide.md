# Guía de Deployment - VItal-red
## Puesta en Producción

### Pre-requisitos
- Docker y Docker Compose instalados
- Certificados SSL configurados
- Base de datos MySQL 8.0+
- Redis 7+
- Dominio configurado

### 1. Configuración de Entorno

```bash
# Clonar repositorio
git clone https://github.com/hospital/vital-red.git
cd vital-red

# Configurar variables de entorno
cp .env.example .env.production
```

### 2. Variables de Producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://vital-red.hospital.com

DB_HOST=vital-red-db
DB_DATABASE=vital_red_prod
DB_USERNAME=vital_red_user
DB_PASSWORD=secure_password

REDIS_HOST=vital-red-redis
REDIS_PASSWORD=redis_password

GEMINI_API_KEY=your_gemini_api_key
```

### 3. Deployment Blue-Green

```bash
# Construir imágenes
docker-compose -f deploy/docker-compose.prod.yml build

# Iniciar servicios
docker-compose -f deploy/docker-compose.prod.yml up -d

# Ejecutar migraciones
docker exec vital-red-blue php artisan migrate --force

# Verificar health checks
curl https://vital-red.hospital.com/health
```

### 4. Monitoreo Post-Deployment

- Verificar métricas en Prometheus: http://monitor.vital-red.com:9090
- Revisar logs centralizados
- Confirmar alertas configuradas
- Validar performance <200ms

### 5. Rollback (si es necesario)

```bash
# Ejecutar rollback automático
./deploy/blue-green-deploy.sh rollback
```