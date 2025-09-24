# 🏥 VItal-red - Sistema de Referencias Médicas con IA

## 📋 Descripción

VItal-red es un sistema avanzado de gestión de referencias médicas que utiliza Inteligencia Artificial para priorizar y clasificar automáticamente las solicitudes de referencia entre instituciones de salud.

## ✨ Características Principales

- 🤖 **Clasificación IA**: Priorización automática usando Gemini AI
- ⚡ **Tiempo Real**: Notificaciones instantáneas con WebSockets
- 🔐 **Multi-Rol**: Admin, Médico, IPS, Jefe de Urgencias
- 📊 **Analytics**: Dashboards ejecutivos y métricas en tiempo real
- 🚨 **Alertas Críticas**: Sistema de alertas para casos urgentes
- 📱 **Responsive**: Interfaz adaptable a todos los dispositivos
- 🔒 **Seguridad**: Autenticación robusta y control de permisos

## 🛠️ Tecnologías

### Backend
- **Laravel 11** - Framework PHP
- **MySQL 8.0** - Base de datos
- **Redis** - Cache y colas
- **Sanctum** - Autenticación API

### Frontend
- **React 18** - Interfaz de usuario
- **TypeScript** - Tipado estático
- **Inertia.js** - SPA sin API
- **Tailwind CSS** - Estilos
- **Shadcn/ui** - Componentes UI

### IA y Servicios
- **Google Gemini AI** - Clasificación inteligente
- **Pusher** - WebSockets tiempo real
- **Twilio** - SMS de emergencia
- **AWS S3** - Almacenamiento archivos

## 🚀 Instalación

### Prerrequisitos
- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Redis
- Composer

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/VItal-red.git
cd VItal-red
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias Node.js**
```bash
npm install
```

4. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos**
```bash
php artisan migrate
php artisan db:seed
```

6. **Compilar assets**
```bash
npm run build
```

7. **Iniciar servidor**
```bash
php artisan serve
```

## 🐳 Docker

### Desarrollo con Docker

```bash
# Construir y levantar servicios
docker-compose up -d

# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Ejecutar seeders
docker-compose exec app php artisan db:seed
```

### Servicios Docker
- **app**: Aplicación Laravel (PHP 8.2-FPM)
- **webserver**: Nginx
- **db**: MySQL 8.0
- **redis**: Redis para cache y colas

## 👥 Roles del Sistema

### 🔴 Administrador
- Gestión completa de usuarios
- Configuración del sistema
- Reportes y analytics
- Auditoría de seguridad

### 👨‍⚕️ Médico
- Evaluación de solicitudes
- Dashboard de casos críticos
- Seguimiento de pacientes
- Métricas de rendimiento

### 🏥 IPS (Institución Prestadora de Salud)
- Crear solicitudes de referencia
- Seguimiento de solicitudes
- Historial de pacientes

### 👨‍💼 Jefe de Urgencias
- Dashboard ejecutivo
- Gestión de recursos
- Métricas del sistema
- Alertas críticas

## 📊 Funcionalidades por Módulo

### 🤖 Inteligencia Artificial
- Clasificación automática de prioridades
- Análisis de patrones médicos
- Recomendaciones inteligentes
- Aprendizaje continuo

### 🔔 Notificaciones
- Tiempo real con WebSockets
- Email para casos críticos
- SMS para emergencias
- Panel de notificaciones

### 📈 Reportes y Analytics
- Reportes automáticos diarios/semanales
- Métricas de rendimiento
- Dashboards interactivos
- Exportación de datos

### 🔒 Seguridad
- Autenticación multi-factor
- Control de permisos granular
- Auditoría completa
- Rate limiting

## 🔧 Comandos Artisan

```bash
# Generar reportes
php artisan reports:generate daily
php artisan reports:generate weekly --email=admin@hospital.com

# Limpiar datos antiguos
php artisan data:cleanup --days=30 --backup

# Enviar recordatorios
php artisan reminders:send pending
php artisan reminders:send critical
```

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter=SolicitudTest

# Coverage
php artisan test --coverage
```

## 📚 API Documentation

### Endpoints Principales

#### Autenticación
```http
POST /api/v1/login
POST /api/v1/logout
```

#### Referencias
```http
GET    /api/v1/referencias
POST   /api/v1/referencias
PUT    /api/v1/referencias/{id}
DELETE /api/v1/referencias/{id}
```

#### Métricas
```http
GET /api/v1/metricas/dashboard
```

## 🔄 Flujo de Trabajo

1. **IPS** crea solicitud de referencia
2. **IA** clasifica automáticamente la prioridad
3. **Sistema** notifica a médicos disponibles
4. **Médico** evalúa y toma decisión
5. **Sistema** notifica resultado a IPS
6. **Seguimiento** continuo del caso

## 📱 Características Técnicas

### Performance
- Cache Redis para consultas frecuentes
- Lazy loading de componentes React
- Optimización de consultas SQL
- CDN para assets estáticos

### Escalabilidad
- Arquitectura modular
- Colas para procesos pesados
- Microservicios preparados
- Load balancing ready

### Monitoreo
- Logs estructurados
- Métricas de sistema
- Alertas automáticas
- Dashboard de salud

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 👨‍💻 Equipo de Desarrollo

- **Arquitecto de Software**: Sistema completo Laravel + React
- **Especialista IA**: Integración Gemini AI
- **DevOps**: Docker y despliegue
- **UI/UX**: Diseño de interfaz

## 📞 Soporte

- **Email**: soporte@vital-red.com
- **Documentación**: [docs.vital-red.com](https://docs.vital-red.com)
- **Issues**: [GitHub Issues](https://github.com/tu-usuario/VItal-red/issues)

## 🎯 Roadmap

### v2.0 (Próxima versión)
- [ ] App móvil nativa
- [ ] Integración FHIR
- [ ] Machine Learning avanzado
- [ ] Telemedicina integrada

### v1.1 (En desarrollo)
- [x] Sistema de referencias ✅
- [x] IA de clasificación ✅
- [x] Notificaciones tiempo real ✅
- [x] Multi-rol completo ✅

---

**VItal-red** - Revolucionando las referencias médicas con Inteligencia Artificial 🚀