-- Base de datos para Sistema de Gestión Escolar (XAMPP Compatible)
-- Ejecutar en phpMyAdmin o MySQL de XAMPP

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS colegio_sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE colegio_sistema;

-- Tabla de usuarios para autenticación
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'profesor', 'secretaria', 'contador') DEFAULT 'secretaria',
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de personal
CREATE TABLE IF NOT EXISTS personal (
    cedula VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido1 VARCHAR(100) NOT NULL,
    apellido2 VARCHAR(100),
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('Masculino', 'Femenino', 'Otro') NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    cargo VARCHAR(100) NOT NULL,
    departamento VARCHAR(100),
    fecha_ingreso DATE DEFAULT (CURRENT_DATE),
    salario DECIMAL(10,2),
    estado ENUM('Activo', 'Inactivo', 'Licencia', 'Retirado') DEFAULT 'Activo',
    titulo_profesional VARCHAR(200),
    experiencia_anos INT DEFAULT 0,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    cedula VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido1 VARCHAR(100) NOT NULL,
    apellido2 VARCHAR(100),
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('Masculino', 'Femenino', 'Otro') NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    acudiente_nombre VARCHAR(200),
    acudiente_telefono VARCHAR(20),
    acudiente_email VARCHAR(100),
    estado ENUM('Activo', 'Inactivo', 'Graduado', 'Retirado') DEFAULT 'Activo',
    fecha_ingreso DATE DEFAULT (CURRENT_DATE),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de aulas
CREATE TABLE IF NOT EXISTS aulas (
    id_aula INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) UNIQUE NOT NULL,
    nombre VARCHAR(100),
    edificio VARCHAR(50),
    piso INT,
    capacidad_estudiantes INT NOT NULL,
    tipo_aula ENUM('Aula Regular', 'Laboratorio', 'Taller', 'Auditorio', 'Biblioteca', 'Gimnasio') DEFAULT 'Aula Regular',
    equipamiento TEXT,
    estado ENUM('Disponible', 'Ocupada', 'Mantenimiento', 'Fuera de Servicio') DEFAULT 'Disponible',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de cursos
CREATE TABLE IF NOT EXISTS cursos (
    codigo VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    nivel ENUM('Preescolar', 'Primaria', 'Secundaria', 'Media') NOT NULL,
    grado VARCHAR(10) NOT NULL,
    seccion VARCHAR(10),
    horas_semanales INT DEFAULT 1,
    modalidad ENUM('Presencial', 'Virtual', 'Híbrida') DEFAULT 'Presencial',
    profesor_id VARCHAR(20),
    aula_id INT,
    estado ENUM('Activo', 'Inactivo', 'Finalizado') DEFAULT 'Activo',
    cupo_maximo INT DEFAULT 30,
    horario TEXT,
    fecha_inicio DATE,
    fecha_fin DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profesor_id) REFERENCES personal(cedula) ON DELETE SET NULL,
    FOREIGN KEY (aula_id) REFERENCES aulas(id_aula) ON DELETE SET NULL
);

-- Tabla de matrículas
CREATE TABLE IF NOT EXISTS matriculas (
    id_matricula INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id VARCHAR(20) NOT NULL,
    curso_codigo VARCHAR(20) NOT NULL,
    periodo_escolar VARCHAR(20) NOT NULL,
    jornada ENUM('Mañana', 'Tarde', 'Noche', 'Completa') DEFAULT 'Mañana',
    modalidad ENUM('Presencial', 'Virtual', 'Híbrida') DEFAULT 'Presencial',
    fecha_matricula DATE DEFAULT (CURRENT_DATE),
    observaciones TEXT,
    estado ENUM('Activa', 'Inactiva', 'Cancelada', 'Finalizada') DEFAULT 'Activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(cedula) ON DELETE CASCADE,
    FOREIGN KEY (curso_codigo) REFERENCES cursos(codigo) ON DELETE CASCADE
);

-- Tabla de cobros
CREATE TABLE IF NOT EXISTS cobros (
    id_cobro INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id VARCHAR(20) NOT NULL,
    curso_codigo VARCHAR(20),
    concepto VARCHAR(200) NOT NULL,
    descripcion TEXT,
    monto DECIMAL(10,2) NOT NULL,
    fecha_cobro DATE DEFAULT (CURRENT_DATE),
    fecha_vencimiento DATE NOT NULL,
    fecha_pago DATE NULL,
    estado ENUM('Pendiente', 'Pagado', 'Vencido', 'Cancelado') DEFAULT 'Pendiente',
    metodo_pago ENUM('Efectivo', 'Transferencia', 'Tarjeta', 'Cheque', 'Otro'),
    numero_comprobante VARCHAR(100),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(cedula) ON DELETE CASCADE,
    FOREIGN KEY (curso_codigo) REFERENCES cursos(codigo) ON DELETE SET NULL
);

-- Insertar usuarios de prueba
INSERT INTO usuarios (username, email, password_hash, rol) VALUES 
('admin', 'admin@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('secretaria', 'secretaria@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretaria'),
('contador', 'contador@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'contador'),
('profesor', 'profesor@colegio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesor');

-- Insertar datos de ejemplo
INSERT INTO aulas (numero, nombre, edificio, piso, capacidad_estudiantes, tipo_aula) VALUES
('101', 'Aula 101', 'Edificio A', 1, 30, 'Aula Regular'),
('102', 'Aula 102', 'Edificio A', 1, 25, 'Aula Regular'),
('201', 'Laboratorio de Ciencias', 'Edificio B', 2, 20, 'Laboratorio'),
('301', 'Aula de Informática', 'Edificio C', 3, 25, 'Taller');

INSERT INTO personal (cedula, nombre, apellido1, apellido2, fecha_nacimiento, genero, cargo, departamento, salario, titulo_profesional) VALUES
('12345678', 'María', 'García', 'López', '1985-03-15', 'Femenino', 'Profesora', 'Matemáticas', 2500000, 'Licenciada en Matemáticas'),
('87654321', 'Carlos', 'Rodríguez', 'Pérez', '1980-07-22', 'Masculino', 'Profesor', 'Ciencias', 2600000, 'Licenciado en Biología'),
('11223344', 'Ana', 'Martínez', 'Gómez', '1990-11-08', 'Femenino', 'Secretaria', 'Administración', 1800000, 'Técnica en Administración');

INSERT INTO cursos (codigo, nombre, nivel, grado, profesor_id, aula_id, cupo_maximo) VALUES
('MAT-6A', 'Matemáticas 6°A', 'Primaria', '6', '12345678', 1, 30),
('CIE-7B', 'Ciencias Naturales 7°B', 'Secundaria', '7', '87654321', 3, 25),
('ESP-5A', 'Español 5°A', 'Primaria', '5', '12345678', 2, 28);

INSERT INTO estudiantes (cedula, nombre, apellido1, apellido2, fecha_nacimiento, genero, acudiente_nombre, acudiente_telefono) VALUES
('1001234567', 'Juan', 'Pérez', 'González', '2010-05-12', 'Masculino', 'Pedro Pérez', '3001234567'),
('1007654321', 'María', 'López', 'Martín', '2009-08-25', 'Femenino', 'Carmen López', '3007654321'),
('1009876543', 'Carlos', 'Gómez', 'Ruiz', '2011-02-18', 'Masculino', 'Luis Gómez', '3009876543');

INSERT INTO matriculas (estudiante_id, curso_codigo, periodo_escolar, jornada) VALUES
('1001234567', 'MAT-6A', '2024', 'Mañana'),
('1007654321', 'CIE-7B', '2024', 'Mañana'),
('1009876543', 'ESP-5A', '2024', 'Mañana');

INSERT INTO cobros (estudiante_id, concepto, monto, fecha_vencimiento) VALUES
('1001234567', 'Mensualidad Enero', 150000, '2024-01-31'),
('1007654321', 'Mensualidad Enero', 150000, '2024-01-31'),
('1009876543', 'Matrícula 2024', 200000, '2024-02-15');
