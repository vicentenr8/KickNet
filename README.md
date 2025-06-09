# ⚽ Kicknet

Kicknet es una red social centrada exclusivamente en el fútbol, que permite a los usuarios compartir publicaciones, interactuar con otros fanáticos y hacer pronósticos de partidos (1X2) en tiempo real. La plataforma ofrece una experiencia moderna, intuitiva y personalizable con soporte para modo claro y oscuro.

## 🚀 Características

- 🗣️ **Publicaciones** estilo "feed", como X (antes Twitter).
- 📊 **Sistema de pronósticos** en tiempo real (1X2) con estadísticas de participación.
- 🌐 **Interfaz moderna** adaptada a escritorio y móvil.
- 📅 Listado de partidos del día con información relevante.
- 👤 Avatar generado por iniciales.
- 🔐 Autenticación y verificación de usuarios.
- 🧾 Contador de caracteres al publicar.

## 🛠️ Tecnologías utilizadas

### Frontend
- **Angular** + **TypeScript**
- **TailwindCSS**
- **HTML5**

### Backend
- **Symfony** (PHP)
- **PostgresSQL**
- API RESTful para autenticación, gestión de usuarios, publicaciones, comentarios y pronósticos.

## 📦 Instalación y ejecución

### Backend (Symfony)

# Clonar el repositorio
git clone https://github.com/tuusuario/kicknet-backend.git
cd kicknet-backend

# Instalar dependencias
composer install

# Configurar archivo .env con la conexión a base de datos

# Ejecutar servidor
symfony server:start

# FrontEnd (Angular)
# Clonar el repositorio
git clone https://github.com/tuusuario/kicknet-frontend.git
cd kicknet-frontend

# Instalar dependencias
npm install

# Ejecutar aplicación
ng serve

# Funcionalidades Futuras

- 👤 Seguidores y seguidos

- 🔔 Sistema de notificaciones.

- 💬 Chat entre usuarios.

- 🏆 Preddiciones de usuarios (1, X, 2)

#Autor
Desarrollado por Vicente Navas Rojas
