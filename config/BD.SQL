/* -- Base de Datos Container Bar
CREATE DATABASE container_bar;
use container_bar;


-- Tabla de Roles
CREATE TABLE roles (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(100)
);

-- Tabla de Usuarios (Meseros y Administrador)
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(15),
    id_rol INT NOT NULL,
    fecha_ingreso DATE DEFAULT (CURRENT_DATE),
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla de Mesas
CREATE TABLE mesas (
    id_mesa INT PRIMARY KEY AUTO_INCREMENT,
    numero_mesa INT NOT NULL UNIQUE,
    capacidad INT NOT NULL,
    disponible BOOLEAN DEFAULT TRUE
);

-- Tabla de Categorías
CREATE TABLE categorias (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(200)
);

-- Tabla de Productos
CREATE TABLE productos (
    id_producto INT PRIMARY KEY AUTO_INCREMENT,
    nombre_producto VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    id_categoria INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla de Ventas
CREATE TABLE ventas (
    id_venta INT PRIMARY KEY AUTO_INCREMENT,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL, -- Antes era empleado
    total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente',
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Detalle de Ventas
CREATE TABLE detalle_ventas (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Facturas
CREATE TABLE facturas (
    id_factura INT PRIMARY KEY AUTO_INCREMENT,
    numero_factura VARCHAR(20) NOT NULL UNIQUE,
    id_venta INT NOT NULL,
    fecha_factura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10,2) NOT NULL,
    impuestos DECIMAL(10,2) DEFAULT 0,
    total_factura DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Pagos a Usuarios (Meseros)
CREATE TABLE pagos_usuarios (
    id_pago INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    fecha_pago DATE NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    concepto VARCHAR(100) DEFAULT 'Salario',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Gastos
CREATE TABLE gastos (
    id_gasto INT PRIMARY KEY AUTO_INCREMENT,
    fecha_gasto DATE NOT NULL,
    concepto VARCHAR(200) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    categoria_gasto VARCHAR(100) NOT NULL,
    descripcion TEXT,
    comprobante VARCHAR(100)
);
select*from usuarios;

-- Carrito
CREATE TABLE carrito (
    id_carrito INT PRIMARY KEY AUTO_INCREMENT,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE ON UPDATE CASCADE
);
-- Roles
INSERT INTO roles (nombre_rol, descripcion) VALUES 
('Administrador', 'Acceso total al sistema'),
('Mesero', 'Gestión de pedidos y ventas');

-- Categoría
INSERT INTO categorias (nombre_categoria, descripcion) VALUES 
('Bebidas', 'Bebidas alcohólicas y no alcohólicas');

-- Mesas
INSERT INTO mesas (numero_mesa, capacidad) VALUES 
(1, 4), (2, 2), (3, 6), (4, 4), (5, 8);

-- Gastos
INSERT INTO gastos (fecha_gasto, concepto, monto, categoria_gasto, descripcion) VALUES 
('2024-01-15', 'Compra de cerveza', 250000.00, 'Inventario', 'Reposición stock cervezas'),
('2024-01-10', 'Pago servicios públicos', 180000.00, 'Servicios', 'Luz y agua del mes'),
('2024-01-05', 'Mantenimiento equipos', 120000.00, 'Mantenimiento', 'Reparación nevera principal');
-- Índices
CREATE INDEX idx_ventas_fecha ON ventas(fecha_venta);
CREATE INDEX idx_productos_categoria ON productos(id_categoria);
CREATE INDEX idx_usuarios_rol ON usuarios(id_rol);
CREATE INDEX idx_ventas_usuario ON ventas(id_usuario);
CREATE INDEX idx_gastos_fecha ON gastos(fecha_gasto);
CREATE INDEX idx_gastos_categoria ON gastos(categoria_gasto);
CREATE INDEX idx_carrito_mesa ON carrito(id_mesa);
CREATE INDEX idx_carrito_usuario ON carrito(id_usuario);

-- Trigger
DELIMITER //
CREATE TRIGGER actualizar_stock_venta
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE productos 
    SET stock = stock - NEW.cantidad 
    WHERE id_producto = NEW.id_producto;
END//
DELIMITER ;

-- Vista de Ventas
CREATE VIEW vista_ventas AS
SELECT 
    v.id_venta,
    v.fecha_venta,
    m.numero_mesa,
    u.nombre AS usuario,
    v.total,
    v.estado
FROM ventas v
JOIN mesas m ON v.id_mesa = m.id_mesa
JOIN usuarios u ON v.id_usuario = u.id_usuario;

-- Vista de Productos
CREATE VIEW vista_productos AS
SELECT 
    p.id_producto,
    p.nombre_producto,
    p.precio,
    p.stock,
    c.nombre_categoria
FROM productos p
JOIN categorias c ON p.id_categoria = c.id_categoria
WHERE p.activo = TRUE;

-- Vista de Gastos por categoría
CREATE VIEW vista_gastos_categoria AS
SELECT 
    categoria_gasto,
    COUNT(*) AS total_gastos,
    SUM(monto) AS total_monto,
    AVG(monto) AS promedio_gasto
FROM gastos
GROUP BY categoria_gasto;

-- Vista del carrito
CREATE VIEW vista_carrito AS
SELECT 
    c.id_carrito,
    m.numero_mesa,
    u.nombre AS usuario,
    p.nombre_producto,
    c.cantidad,
    c.precio_unitario,
    (c.cantidad * c.precio_unitario) AS subtotal,
    c.fecha_agregado
FROM carrito c
JOIN mesas m ON c.id_mesa = m.id_mesa
JOIN usuarios u ON c.id_usuario = u.id_usuario
JOIN productos p ON c.id_producto = p.id_producto;
 */