# Sistema de Gestión Escolar

Sistema completo de gestión escolar desarrollado en PHP para XAMPP, diseñado para colegios que necesitan administrar estudiantes, personal, cursos, matrículas y cobros.

## 🚀 Características

- **Autenticación por roles**: Admin, Profesor, Secretaria, Contador
- **Gestión de Estudiantes**: Registro, edición, consulta con filtros
- **Gestión de Personal**: Control de empleados y profesores
- **Administración de Cursos y Aulas**: Asignación y programación
- **Sistema de Matrículas**: Control de inscripciones por período
- **Gestión de Cobros**: Facturación, seguimiento de pagos, reportes
- **Dashboard con estadísticas**: Resumen visual del estado del sistema
- **Interfaz moderna**: Bootstrap 5 con diseño responsive

## 📋 Requisitos

- XAMPP (Apache + MySQL + PHP 7.4+)
- Navegador web moderno
- phpMyAdmin (incluido en XAMPP)

## 🛠️ Instalación

### 1. Configurar XAMPP
1. Instala XAMPP desde [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Inicia Apache y MySQL desde el panel de control de XAMPP

### 2. Configurar la Base de Datos
1. Abre phpMyAdmin en tu navegador: `http://localhost/phpmyadmin`
2. Importa el archivo `install.sql` o ejecuta el script SQL completo
3. Verifica que se haya creado la base de datos `colegio_sistema`

### 3. Configurar el Proyecto
1. Copia todos los archivos del proyecto a `C:\xampp\htdocs\colegio\`
2. Verifica la configuración de base de datos en `config/database.php`
3. Asegúrate de que las credenciales coincidan con tu instalación de MySQL

### 4. Acceder al Sistema
1. Abre tu navegador y ve a: `http://localhost/colegio/`
2. Usa las credenciales de prueba:
   - **Admin**: usuario `admin`, contraseña `admin123`
   - **Secretaria**: usuario `secretaria`, contraseña `secretaria123`
   - **Contador**: usuario `contador`, contraseña `contador123`
   - **Profesor**: usuario `profesor`, contraseña `profesor123`

## 📁 Estructura del Proyecto

```
colegio/
├── config/
│   └── database.php          # Configuración de base de datos
├── includes/
│   └── auth.php              # Sistema de autenticación
├── estudiantes/
│   ├── index.php             # Lista de estudiantes
│   ├── nuevo.php             # Registro de estudiantes
│   ├── editar.php            # Edición de estudiantes
│   └── ver.php               # Detalle de estudiante
├── personal/
│   └── [archivos de gestión de personal]
├── cursos/
│   └── [archivos de gestión de cursos]
├── aulas/
│   └── [archivos de gestión de aulas]
├── matriculas/
│   └── [archivos de gestión de matrículas]
├── cobros/
│   ├── index.php             # Lista de cobros
│   ├── nuevo.php             # Registro de cobros
│   └── [otros archivos]
├── usuarios/
│   └── [gestión de usuarios del sistema]
├── dashboard.php             # Panel principal
├── login.php                 # Página de inicio de sesión
├── logout.php                # Cerrar sesión
├── install.sql               # Script de instalación de BD
└── README.md                 # Este archivo
```

## 👥 Roles y Permisos

### Administrador
- Acceso completo a todas las funciones
- Gestión de usuarios del sistema
- Configuración general

### Secretaria
- Gestión de estudiantes y matrículas
- Registro de personal
- Consulta de información general

### Contador
- Gestión de cobros y pagos
- Reportes financieros
- Consulta de información de estudiantes

### Profesor
- Consulta de estudiantes de sus cursos
- Acceso limitado a información académica

## 🎯 Funcionalidades Principales

### Dashboard
- Estadísticas en tiempo real
- Accesos rápidos a funciones principales
- Resumen del estado del sistema

### Gestión de Estudiantes
- Registro completo con datos personales y del acudiente
- Filtros de búsqueda avanzados
- Estados: Activo, Inactivo, Graduado, Retirado

### Sistema de Cobros
- Registro de diferentes tipos de cobros
- Seguimiento de pagos y vencimientos
- Estados: Pendiente, Pagado, Vencido, Cancelado
- Conceptos predefinidos para agilizar el proceso

### Matrículas
- Inscripción de estudiantes a cursos
- Control por períodos escolares
- Diferentes modalidades y jornadas

## 🔧 Configuración Adicional

### Cambiar Credenciales de Base de Datos
Edita el archivo `config/database.php`:
```php
private $host = "localhost";
private $db_name = "colegio_sistema";
private $username = "tu_usuario";
private $password = "tu_contraseña";
```

### Agregar Nuevos Usuarios
Los usuarios se pueden agregar desde el panel de administración o directamente en la base de datos con contraseñas hasheadas.

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
1. Verifica que MySQL esté ejecutándose en XAMPP
2. Confirma las credenciales en `config/database.php`
3. Asegúrate de que la base de datos `colegio_sistema` exista

### Página en Blanco
1. Habilita la visualización de errores PHP
2. Verifica los logs de Apache en XAMPP
3. Confirma que todos los archivos estén en la ubicación correcta

### Problemas de Sesión
1. Verifica que las cookies estén habilitadas
2. Confirma que la configuración de sesiones PHP esté correcta

## 📊 Base de Datos

El sistema utiliza las siguientes tablas principales:
- `usuarios`: Autenticación y roles
- `estudiantes`: Información de estudiantes
- `personal`: Datos del personal docente y administrativo
- `cursos`: Cursos y materias
- `aulas`: Espacios físicos
- `matriculas`: Inscripciones de estudiantes
- `cobros`: Facturación y pagos

## 🤝 Contribución

Este es un proyecto académico de Big Data. Para mejoras o sugerencias:
1. Documenta claramente los cambios propuestos
2. Mantén la compatibilidad con XAMPP
3. Sigue las convenciones de código establecidas

## 📄 Licencia

Proyecto académico desarrollado para fines educativos.

---

**Desarrollado para proyecto de Big Data - Sistema de Gestión Escolar**
