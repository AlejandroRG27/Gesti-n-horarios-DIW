-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS InstitucionEducativa;

-- Seleccionar la base de datos
USE InstitucionEducativa;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS Usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL, -- Ajustado por posibles DNIs más largos
    passworduser VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0, -- 0 = alumno, 1 = administrador
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Ciclos Formativos
CREATE TABLE IF NOT EXISTS CiclosFormativos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cod VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    duracion ENUM('1', '2') NOT NULL -- Duración de 1 o 2 años
);

-- Tabla de Módulos
CREATE TABLE IF NOT EXISTS Modulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    ciclo_formativo_id INT NOT NULL,
    profesor VARCHAR(100) NOT NULL, -- Nombre del profesor
    creditos INT NOT NULL, -- Créditos del módulo
    FOREIGN KEY (ciclo_formativo_id) REFERENCES CiclosFormativos(id)
);

-- Tabla de Horarios
CREATE TABLE IF NOT EXISTS Horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    modulo_id INT NOT NULL,
    dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    FOREIGN KEY (modulo_id) REFERENCES Modulos(id)
);

-- Tabla para relacionar alumnos con módulos
CREATE TABLE IF NOT EXISTS AlumnosModulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id),
    FOREIGN KEY (modulo_id) REFERENCES Modulos(id)
);
