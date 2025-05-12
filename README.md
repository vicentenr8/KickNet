# 🍽️ Fray Melitón – Renovación Web y Sistema de Gestión

Proyecto de rediseño completo para la web del restaurante **Fray Melitón**, con el objetivo de ofrecer una experiencia moderna, funcional e intuitiva tanto para los clientes como para el equipo de gestión.

## Objetivo

Transformar el sitio actual (WordPress) en una plataforma personalizada donde:

- Los usuarios puedan:
  - Consultar la carta actualizada sin necesidad de registrarse.
  - Hacer reservas online de forma sencilla.
  - Obtener información general del restaurante desde cualquier dispositivo.

- Los administradores puedan:
  - Gestionar reservas y disponibilidad.
  - Editar la carta en tiempo real.
  - Controlar todo desde un único panel de administración.

- La carta esté siempre accesible mediante código QR desde las mesas o redes sociales.

---

## ⚙️ Funcionalidades Principales

### 1. Landing Page Mejorada

Inspirada en la actual página: [fraymeliton.es](https://fraymeliton.es)

Secciones clave:

- Quiénes somos / historia  
- Carta dividida por categorías (entrantes, principales, postres, bebidas)  
- Reservas online  
- Horarios y contacto  
- Mapa de ubicación  
- Reseñas o testimonios  
- Página dedicada a la carta (para uso con código QR)

---

### 2. Gestión de Reservas

#### Para clientes (sin login)

- Reserva rápida con: nombre, email, teléfono, fecha, hora, nº de personas, comentarios.  
- Confirmación automática por email.

#### Para usuarios registrados

- Ver historial de reservas.  
- Editar o cancelar (hasta X días antes).  
- Solicitud de soporte en caso de incidencias.

#### Para administradores

- Ver y gestionar todas las reservas desde el panel.  
- Filtros por fecha, estado y nº de personas.  
- Crear/modificar/cancelar reservas (incluso hechas en persona).

---

### 3. Gestión de Disponibilidad

- Configurar horarios y franjas disponibles.  
- Limitar nº de mesas o comensales por franja.  
- Bloquear fechas especiales (festivos, vacaciones, eventos).

---

### 4. Carta Dinámica

Uno de los puntos clave del sistema:

- Carta cargada desde base de datos.  
- Gestión total desde el panel: añadir, editar, ocultar platos.  
- Opción de marcar platos como “no disponible”.  
- Categorías personalizables (con menús especiales o por alérgenos).  
- Siempre actualizada y visible para todos los usuarios.

---

### 5. Código QR para Carta

- Generación automática de un QR que enlaza a la carta online.  
- Escaneable desde mesas o redes sociales.  
- Acceso directo a versión móvil sin descarga.

---

## 👥 Roles y Permisos

| Rol                | Acciones disponibles                                              |
|--------------------|-------------------------------------------------------------------|
| Visitante          | Ver carta, reservar sin cuenta                                    |
| Usuario registrado | Ver historial, editar y cancelar reservas                         |
| Administrador      | Gestionar carta, reservas, disponibilidad, horarios, etc.         |

---


## ✅ Requisitos Funcionales

### Para usuarios no registrados

- Acceso libre a la carta.  
- Realizar reservas sin crear cuenta.  
- Consultar ubicación y contacto.

### Para usuarios registrados

- Gestionar reservas pasadas y futuras.

### Para administradores

- Modificar la carta en tiempo real.  
- Gestionar reservas (todas).  
- Configurar horarios y disponibilidad.

---

## 🔮 Mejoras Futuras

- Notificaciones automáticas por email (ej: recordatorios de reserva).  
- Estadísticas de demanda por plato y gestión de stock anticipada.  
- Integración con Google Calendar.  
- Chat de atención con rol de gestor de reservas.

---

## 🐳 Clonar y ejecutar con Docker

### 📦 Clonar el Repositorio

```bash
git clone git@github.com:CodeArts-Solutions/elephants-B-Fray-Meliton.git
```

### Levantar los contenedores de Docker

```bash
docker compose up -d
```
