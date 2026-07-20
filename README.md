# 📚 SGBV - Sistema de Gestión de Bibliotecas Virtuales

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Architecture](https://img.shields.io/badge/Arquitectura-MVC%20Nativo-FF2D20?style=for-the-badge)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Status](https://img.shields.io/badge/Estado-Activo%20%26%20Estable-00C853?style=for-the-badge)

<p align="center">
  <strong>Una plataforma web moderna, segura y modular orientada a la gestión y renta de recursos literarios digitales (Libros, Audiolibros y Artículos), desarrollada bajo el patrón arquitectónico MVC nativo en PHP 8+.</strong>
</p>

</div>

--- LOCAL --- XAMPP

LINK: http://localhost:8080/SGBV/

LINK: http://localhost/SGBV/

--- Servidor ---

Proximamente...

---
---

## 📖 Índice de Contenidos

- [🏛️ Arquitectura del Sistema](#-arquitectura-del-sistema)
- [📁 Estructura de Carpetas](#-estructura-de-carpetas)
- [🚀 Instalación y Puesta en Marcha](#-instalación-y-puesta-en-marcha)
- [🔑 Credenciales de Prueba (Seeders)](#-credenciales-de-prueba-seeders)
- [🛠️ Stack Tecnológico](#️-stack-tecnológico)

---


## 🏛️ Arquitectura del Sistema

El proyecto está diseñado bajo el patrón **Modelo-Vista-Controlador (MVC)** 


---

## 📁 Estructura de Carpetas

```text
SGVB/
├── .git/                      # Control de versiones
├── config/                    # Configuración centralizada
│   ├── db.php                 # Conexión estática PDO (Patrón Singleton)
│   └── CryptoHelper.php       # Utilerías de cifrado y seguridad
├── controllers/               # Controladores de la aplicación (MVC)
│   ├── AuthController.php     # Autenticación, registro y cierre de sesión
│   ├── PrestamosController.php# Carrito, visor, alquileres y librería del lector
│   ├── RecursosController.php # Catálogo, búsqueda, páginas legales y API JSON
│   ├── UsuarioController.php  # Ajustes de cuenta y perfil de usuario
│   ├── admin/                 # Controladores de la zona de administración
│   └── estandar/              # Controladores auxiliares para el usuario estándar
├── models/                    # Clases de entidades y lógica con base de datos
│   ├── Categoria.php          # Gestión de categorías literarias
│   ├── Permiso.php            # RBAC - Permisos del sistema
│   ├── Prestamo.php           # Motor de préstamos y rentas digitales
│   ├── Recurso.php            # Catálogo de libros, audiolibros y artículos
│   ├── Rol.php                # RBAC - Roles de usuario
│   ├── Suscripcion.php        # Gestión de membresías
│   └── Usuario.php            # Gestión de usuarios, saldos y autenticación
├── views/                     # Plantillas e interfaces gráficas
│   ├── admin/                 # Vistas del dashboard administrativo
│   ├── auth/                  # Vistas de login y registro
│   ├── estandar/              # Vistas del visor, panel y librería del lector
│   └── ...                    # Vistas del catálogo y páginas legales
├── layouts/                   # Estructuras comunes (Header, Footer, Navbar)
├── public/                    # Activos públicos del cliente
│   ├── css/style.css          # Estilos Vanilla CSS y tokens de diseño moderno
│   └── js/app.js              # Lógica del cliente, modales e interacciones
├── routes/                    # Definición del enrutador modular
│   └── web.php                # Mapeo de rutas (GET/POST) y clase Router
├── SgbvDB.sql                 # Dump de la base de datos y Seeders iniciales
├── .htaccess                  # Reescribir URLs amigables en Apache
└── index.php                  # Front Controller principal
```

---

## 🚀 Instalación y Puesta en Marcha

Sigue estos pasos para ejecutar el sistema en un entorno de desarrollo local **XAMPP**:

### Requisitos Previos
- **PHP 8.0** o superior (con extensiones `PDO` y `pdo_mysql` habilitadas).
- **Servidor Apache** (con el módulo `mod_rewrite` activo para URLs amigables).
- **MySQL 5.7+** o **MariaDB 10.3+**.

### Paso 1: Clonar o Ubicar en el Servidor Web
Coloca los archivos del proyecto en la carpeta raíz de tu servidor web. En el caso de XAMPP, el directorio predeterminado es:
```bash
C:\xampp\htdocs\SGVB
```

### Paso 2: Crear la Base de Datos e Importar Seeders
1. Abre **phpMyAdmin** en `http://localhost/phpmyadmin`, `http://localhost:8080/phpmyadmin` o utiliza la terminal de MySQL.
2. El archivo `SgbvDB.sql` se encargará de crear automáticamente la base de datos `sgbv_db`, todas las tablas con sus llaves foráneas y datos iniciales de prueba (seeders).
3. Si lo haces desde consola:
```bash
mysql -u root -p < C:/xampp/htdocs/SGVB/SgbvDB.sql
```

### Paso 3: Configurar la Conexión a la Base de Datos
Abre el archivo [config/db.php](file:///c:/xampp/htdocs/SGVB/config/db.php) y verifica o actualiza las constantes de conexión si tu entorno usa credenciales distintas:
```php
private const DB_HOST = 'localhost';
private const DB_NAME = 'sgbv_db';
private const DB_USER = 'root';     // Cambiar por tu usuario de MySQL en producción
private const DB_PASS = '';         // Cambiar por tu contraseña si aplica
```

### Paso 4: ¡Acceder a la Aplicación!
Abre tu navegador y entra a:
👉 **`http://localhost/SGVB/`**, **`http://localhost:8080/SGVB/`**, *(O en su defecto, `http://localhost/SGVB/home`, `http://localhost:8080/SGVB/home`)*

---

## 🔑 Credenciales de Prueba (Seeders)

El archivo `SgbvDB.sql` incluye usuarios preconfigurados y listos para probar ambas interfaces del sistema:

| Rol | Correo Electrónico | Contraseña |
| :--- | :--- | :--- | :---: | :--- |
| **Administrador** | `admin@sgbv.com` | **`admin123`** |
| **Lector Estándar** | `lector@sgbv.com` | **`lector123`** |
| **Lector de Prueba 2** | `ana@sgbv.com` | **`lector123`** |
| **Lector de Prueba 3** | `carlos@sgbv.com` | **`lector123`** |

---


## 🛠️ Stack Tecnológico

- **Backend:** PHP 8+ (Orientado a Objetos + Funciones nativas de tipado estricto ).
- **Base de Datos:** MySQL (Motor relacional InnoDB con soporte de llaves foráneas).
- **Capa de Datos:** PHP Data Objects (`PDO`) con *prepared statements* para prevenir inyecciones SQL.
- **Frontend / Styling:** Vanilla CSS moderno con variables CSS personalizadas, Glassmorphism, Bootstrap 5.3 y Bootstrap Icons (`bi`).
- **Control de Rutas:** Enrutamiento nativo a través de un *Front Controller* (`index.php`) y `.htaccess`.

---
## 👥 Equipo de Desarrollo 

* **Ricardo Peña García**
* **Eddual Rafael Corniel**
