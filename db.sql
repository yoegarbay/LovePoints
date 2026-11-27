CREATE DATABASE IF NOT EXISTS db_inter2;
USE db_inter2;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    rol VARCHAR(50) DEFAULT 'usuario',
    saldo_inicial DECIMAL(10, 2) NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE parejas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_1_id INT NOT NULL UNIQUE,
    usuario_2_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_1_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_2_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT chk_diferentes_usuarios CHECK (usuario_1_id <> usuario_2_id)
);

CREATE TABLE predefinidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    coste DECIMAL(10, 2) NOT NULL,
    efecto TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT uq_usuario_nombre UNIQUE (usuario_id, nombre)
);

CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    registrado_por_id INT NOT NULL,
    predefinido_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    fecha DATETIME NOT NULL,
    coste DECIMAL(10, 2) NOT NULL,
    efecto TEXT,
    anulado BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (predefinido_id) REFERENCES predefinidos(id) ON DELETE SET NULL
);

CREATE TABLE recargas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    importe DECIMAL(10, 2) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE configuracion (
    clave VARCHAR(100) PRIMARY KEY,
    valor TEXT NOT NULL
);

INSERT INTO usuarios (nombre, email, password_hash, codigo, rol, saldo_inicial, activo) VALUES
('Ana Pérez', 'ana.perez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER-001', 'usuario', 50.00, TRUE),
('Carlos Gómez', 'carlos.gomez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER-002', 'usuario', 100.00, TRUE),
('Elena Ruiz', 'elena.ruiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER-003', 'usuario', NULL, TRUE),
('David Ortolá', 'david.ortola@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER-004', 'usuario', 20.50, TRUE);

-- Usuario superadmin de prueba (password: Admin1234)
INSERT INTO usuarios (nombre, email, password_hash, codigo, rol, saldo_inicial, activo) VALUES
('Super Admin', 'admin@familycoins.local', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdB5p1n7Fk9bXy2Uu', 'ADM0001', 'superadmin', 0.00, TRUE);

INSERT INTO parejas (usuario_1_id, usuario_2_id) VALUES
(1, 2),
(3, 4);

INSERT INTO predefinidos (usuario_id, nombre, coste, efecto) VALUES
(1, 'Lavar los platos', 5.00, 'resta'),
(1, 'Sacar al perro', 3.50, 'resta'),
(2, 'Hacer la compra', 10.00, 'resta'),
(3, 'Preparar la cena', 7.50, 'suma');

INSERT INTO eventos (usuario_id, registrado_por_id, predefinido_id, nombre, fecha, coste, efecto, anulado) VALUES
(2, 1, 1, 'Lavar los platos', '2026-06-01 14:30:00', 5.00, 'resta', FALSE),
(1, 2, 3, 'Hacer la compra', '2026-06-02 10:00:00', 10.00, 'resta', FALSE),
(4, 3, 4, 'Preparar la cena', '2026-06-02 21:00:00', 7.50, 'suma', TRUE);

INSERT INTO recargas (usuario_id, importe, fecha, motivo) VALUES
(1, 25.00, '2026-06-01 09:00:00', 'Bono mensual inicial'),
(2, 50.00, '2026-06-01 09:10:00', 'Recompensa por tareas'),
(3, 15.00, '2026-06-02 11:30:00', 'Ajuste de saldo');

INSERT INTO configuracion (clave, valor) VALUES
('moneda_nombre', 'Coins'),
('sistema_activo', 'true'),
('version_app', '1.0.0');