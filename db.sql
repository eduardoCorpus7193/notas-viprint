CREATE DATABASE viprint_notas;
USE viprint_notas;

CREATE TABLE notas_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_nota VARCHAR(50) NOT NULL,
    empresa ENUM('ViPrint', 'Imagen') NOT NULL,
    detalle_cliente VARCHAR(300) NOT NULL,
    telefono_cliente VARCHAR(20),
    observaciones TEXT,
    fecha_nota DATE NOT NULL,
    fecha_recibido DATE NOT NULL,
    fecha_concluido DATE NULL,
    estado ENUM('pendiente', 'en_proceso', 'terminado', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_numero_nota UNIQUE (numero_nota)
);

CREATE TABLE detalle_imagen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tamano ENUM('Grande IMG 3.2', 'Mediana IMG 2.7', 'Jumbo IMG 3.4', 'Otro') NOT NULL DEFAULT 'Otro',
    detalles VARCHAR(100),
    id_notas_trabajo INT NOT NULL,
    CONSTRAINT fk_detalle_imagen_nota
        FOREIGN KEY (id_notas_trabajo)
        REFERENCES notas_trabajo(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

INSERT INTO notas_trabajo (
    numero_nota,
    empresa,
    detalle_cliente,
    telefono_cliente,
    observaciones,
    fecha_nota,
    fecha_recibido,
    fecha_concluido,
    estado
) VALUES
('NT-001', 'ViPrint', 'María López', '4491234567', '500 flyers tamaño media carta a color', '2026-04-01', '2026-04-01', NULL, 'pendiente'),
('NT-002', 'Imagen', 'Carlos Ramírez', '4497654321', 'Lona de 2x1 metros con diseño incluido', '2026-04-01', '2026-04-01', NULL, 'en_proceso'),
('NT-003', 'ViPrint', 'Ana Torres', '4498881122', '100 tarjetas de presentación', '2026-03-31', '2026-03-31', '2026-04-01', 'terminado');