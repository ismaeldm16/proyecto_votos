-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS votos;
USE votos;

-- Tabla Usuarios
CREATE TABLE usuarios (
    usuario VARCHAR(20) PRIMARY KEY,
    contrasena VARCHAR(255) NOT NULL -- Usar hash para contraseñas
);

-- Tabla Productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    voto DECIMAL(3, 2) DEFAULT 0
);

-- Tabla Votos
CREATE TABLE votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cantidad INT DEFAULT 0, -- Valor de la votación
    idPr INT NOT NULL, -- ID del producto
    idUs VARCHAR(20) NOT NULL, -- Usuario que votó
    CONSTRAINT fk_votos_usu FOREIGN KEY (idUs) REFERENCES usuarios(usuario) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    CONSTRAINT fk_votos_pro FOREIGN KEY (idPr) REFERENCES productos(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

-- Insertar datos de prueba
-- Usuarios
INSERT INTO usuarios (usuario, contrasena) VALUES
('admin', ('1234')), 
('ana', ('ana123'));

-- Productos
INSERT INTO productos (nombre, descripcion) VALUES
('Producto A', 'Descripción del Producto A'),
('Producto B', 'Descripción del Producto B'),
('Producto C', 'Descripción del Producto C');


