<?php
require_once 'config/Conexion.php';
session_start();

class StockController {
    private $conexion;
    
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->getConnection();
    }
    
    public function obtenerProductos($busqueda = '') {
        try {
            $sql = "SELECT p.*, c.nombre_categoria FROM productos p 
                    INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
                    WHERE p.activo = 1";
            
            if (!empty($busqueda)) {
                $sql .= " AND (p.nombre_producto LIKE :busqueda OR p.id_producto LIKE :busqueda)";
            }
            
            $stmt = $this->conexion->prepare($sql);
            if (!empty($busqueda)) {
                $stmt->bindValue(':busqueda', '%' . $busqueda . '%');
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN stock > 20 THEN 1 END) as disponible,
                        COUNT(CASE WHEN stock BETWEEN 10 AND 20 THEN 1 END) as bajo,
                        COUNT(CASE WHEN stock < 10 THEN 1 END) as critico
                    FROM productos WHERE activo = 1";
            
            $stmt = $this->conexion->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['total' => 0, 'disponible' => 0, 'bajo' => 0, 'critico' => 0];
        }
    }
    
    public function ajustarStock($idProducto, $nuevoStock, $motivo = 'Ajuste manual') {
        try {
            $this->conexion->beginTransaction();
            
            $sql = "SELECT stock, nombre_producto FROM productos WHERE id_producto = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id', $idProducto, PDO::PARAM_INT);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                $this->conexion->rollback();
                return false;
            }
            
            $sql = "UPDATE productos SET stock = :stock WHERE id_producto = :id";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':stock', $nuevoStock, PDO::PARAM_INT);
            $stmt->bindParam(':id', $idProducto, PDO::PARAM_INT);
            $stmt->execute();
            
            $diferencia = $nuevoStock - $producto['stock'];
            $concepto = $motivo . " - " . $producto['nombre_producto'] . " (" . ($diferencia > 0 ? "+" : "") . $diferencia . ")";
            
            $sql = "INSERT INTO gastos (fecha_gasto, concepto, monto, categoria_gasto, descripcion) 
                    VALUES (NOW(), :concepto, 0, 'Ajuste Stock', :descripcion)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':concepto', $concepto);
            $stmt->bindParam(':descripcion', $motivo);
            $stmt->execute();
            
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollback();
            return false;
        }
    }
    
    public function obtenerAlertas() {
        try {
            $sql = "SELECT nombre_producto, stock, 
                    CASE 
                        WHEN stock < 5 THEN 'critico'
                        WHEN stock < 15 THEN 'bajo'
                        ELSE 'normal'
                    END as nivel
                    FROM productos 
                    WHERE activo = 1 AND stock < 15 
                    ORDER BY stock ASC";
            
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function obtenerMovimientos() {
        try {
            $sql = "SELECT concepto, fecha_gasto, descripcion 
                    FROM gastos 
                    WHERE categoria_gasto = 'Ajuste Stock' 
                    ORDER BY fecha_gasto DESC, id_gasto DESC 
                    LIMIT 10";
            
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

$stockController = new StockController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'obtener_productos':
            echo json_encode($stockController->obtenerProductos($_POST['busqueda'] ?? ''));
            exit;
        case 'obtener_estadisticas':
            echo json_encode($stockController->obtenerEstadisticas());
            exit;
        case 'ajustar_stock':
            $resultado = $stockController->ajustarStock(
                $_POST['id_producto'] ?? 0,
                $_POST['nuevo_stock'] ?? 0,
                $_POST['motivo'] ?? 'Ajuste manual'
            );
            echo json_encode(['success' => $resultado]);
            exit;
        case 'obtener_alertas':
            echo json_encode($stockController->obtenerAlertas());
            exit;
        case 'obtener_movimientos':
            echo json_encode($stockController->obtenerMovimientos());
            exit;
    }
}

$productos = $stockController->obtenerProductos();
$estadisticas = $stockController->obtenerEstadisticas();
$alertas = $stockController->obtenerAlertas();
$movimientos = $stockController->obtenerMovimientos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Stock</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style/style4.css">
    <style>
        .stock-input {
            width: 60px;
            padding: 4px 8px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            text-align: center;
            font-weight: 500;
        }
        .stock-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .save-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin-left: 4px;
        }
        .save-btn:hover {
            background: #059669;
        }
        .save-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .stock-controls {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        }
        .notification-success { background: #10b981; }
        .notification-error { background: #ef4444; }
        .notification-info { background: #3b82f6; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                    <i class="fas fa-chevron-right"></i>
                    <span>Control de Stock</span>
                </div>
                <h1>Control de Stock</h1>
            </div>
            <button class="refresh-btn" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i>
                Actualizar
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                </div>
                <div class="stat-value" id="totalProducts"><?= $estadisticas['total'] ?></div>
                <div class="stat-label">Total de Productos</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Productos activos</span>
                </div>
            </div>

            <div class="stat-card available">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                </div>
                <div class="stat-value" id="availableProducts"><?= $estadisticas['disponible'] ?></div>
                <div class="stat-label">En Stock Disponible</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>Stock suficiente</span>
                </div>
            </div>

            <div class="stat-card low">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
                <div class="stat-value" id="lowStockProducts"><?= $estadisticas['bajo'] ?></div>
                <div class="stat-label">Stock Bajo</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-up"></i>
                    <span>Requiere atención</span>
                </div>
            </div>

            <div class="stat-card critical">
                <div class="stat-header">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                </div>
                <div class="stat-value" id="criticalProducts"><?= $estadisticas['critico'] ?></div>
                <div class="stat-label">Stock Crítico</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-up"></i>
                    <span>Urgente</span>
                </div>
            </div>
        </div>

        <div class="main-grid">
            <div class="stock-section">
                <div class="section-header">
                    <h2 class="section-title">Inventario Actual</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar productos..." id="stockSearch">
                    </div>
                </div>

                <table class="stock-table" id="stockTable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody">
                        <?php foreach ($productos as $producto): ?>
                        <tr data-id="<?= $producto['id_producto'] ?>">
                            <td>
                                <div class="product-info">
                                    <?php 
                                    $iconos = [
                                        'Bebidas' => '<i class="fas fa-glass-whiskey"></i>',
                                        'Comidas' => '<i class="fas fa-utensils"></i>',
                                        'Postres' => '<i class="fas fa-ice-cream"></i>'
                                    ];
                                    echo $iconos[$producto['nombre_categoria']] ?? '<i class="fas fa-cube"></i>';
                                    ?>
                                    <span><?= htmlspecialchars($producto['nombre_producto']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="product-category category-<?= strtolower($producto['nombre_categoria']) ?>">
                                    <?= htmlspecialchars($producto['nombre_categoria']) ?>
                                </span>
                            </td>
                            <td class="stock-controls">
                                <input type="number" 
                                       class="stock-input" 
                                       value="<?= $producto['stock'] ?>" 
                                       min="0"
                                       data-original="<?= $producto['stock'] ?>"
                                       onchange="enableSave(this)"
                                       onkeyup="enableSave(this)">
                                <button class="save-btn" 
                                        onclick="saveStock(this, <?= $producto['id_producto'] ?>, '<?= htmlspecialchars($producto['nombre_producto']) ?>')"
                                        disabled>
                                    <i class="fas fa-save"></i>
                                </button>
                            </td>
                            <td>$<?= number_format($producto['precio'], 0) ?></td>
                            <td>
                                <div class="stock-level">
                                    <?php 
                                    $stock = $producto['stock'];
                                    $clase = $stock < 5 ? 'critical' : ($stock < 15 ? 'low' : 'available');
                                    $estado = $stock < 5 ? 'Crítico' : ($stock < 15 ? 'Bajo' : 'Disponible');
                                    ?>
                                    <span class="stock-indicator <?= $clase ?>"></span>
                                    <span><?= $estado ?></span>
                                </div>
                            </td>
                            <td>
                                <button class="action-btn" onclick="quickAdjust(<?= $producto['id_producto'] ?>, '<?= htmlspecialchars($producto['nombre_producto']) ?>', <?= $producto['stock'] ?>)">
                                    <i class="fas fa-edit"></i> Ajustar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="alerts-panel">
                <div class="section-header" style="margin-bottom: 1rem; padding-bottom: 1rem;">
                    <h2 class="section-title">Alertas de Stock</h2>
                </div>
                <div id="alertsList">
                    <?php foreach ($alertas as $alerta): ?>
                    <div class="alert-item alert-<?= $alerta['nivel'] ?>">
                        <div class="alert-icon">
                            <i class="fas fa-<?= $alerta['nivel'] == 'critico' ? 'exclamation-triangle' : 'exclamation-circle' ?>"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title"><?= $alerta['nivel'] == 'critico' ? 'Stock Crítico' : 'Stock Bajo' ?></div>
                            <div class="alert-message"><?= htmlspecialchars($alerta['nombre_producto']) ?>: <?= $alerta['stock'] ?> unidades</div>
                            <div class="alert-time">Actualizado</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="movements-section">
            <div class="section-header">
                <h2 class="section-title">Movimientos Recientes</h2>
                <button class="action-btn" onclick="showNotification('Vista completa de movimientos en desarrollo', 'info')">
                    Ver Todos
                </button>
            </div>
            <div id="movementsList">
                <?php foreach (array_slice($movimientos, 0, 5) as $movimiento): ?>
                <div class="movement-item fade-in">
                    <div class="movement-info">
                        <div class="movement-type movement-<?= strpos($movimiento['concepto'], '+') !== false ? 'in' : 'out' ?>">
                            <i class="fas fa-arrow-<?= strpos($movimiento['concepto'], '+') !== false ? 'up' : 'down' ?>"></i>
                        </div>
                        <div class="movement-details">
                            <div class="movement-product"><?= htmlspecialchars($movimiento['concepto']) ?></div>
                            <div class="movement-description"><?= htmlspecialchars($movimiento['descripcion']) ?></div>
                        </div>
                    </div>
                    <div class="movement-quantity">
                        <div class="quantity-value"><?= date('d/m', strtotime($movimiento['fecha_gasto'])) ?></div>
                        <div class="movement-time"><?= date('H:i', strtotime($movimiento['fecha_gasto'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="quick-actions">
            <a href="#" class="quick-btn" onclick="showNotification('Selecciona un producto específico en la tabla', 'info')">
                <i class="fas fa-edit"></i>
                <span>Ajustar Stock</span>
            </a>
            <a href="#" class="quick-btn" onclick="generateReport()">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes</span>
            </a>
            <a href="#" class="quick-btn" onclick="scheduleRestock()">
                <i class="fas fa-truck"></i>
                <span>Reabastecer</span>
            </a>
            <a href="#" class="quick-btn" onclick="exportData()">
                <i class="fas fa-download"></i>
                <span>Exportar</span>
            </a>
        </div>
    </div>

    <script>
       // JavaScript completo para control_stock.php
// Reemplaza el script actual con este código

// Variables globales
let stockData = <?= json_encode($productos) ?>;
let alertContainer = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeAlertSystem();
    setupSearch();
    loadInitialData();
});

// Sistema de alertas bonitas
function initializeAlertSystem() {
    // Crear contenedor de alertas si no existe
    if (!document.getElementById('alertContainer')) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alertContainer';
        alertContainer.className = 'alert-container';
        document.body.appendChild(alertContainer);
    } else {
        alertContainer = document.getElementById('alertContainer');
    }
    
    // Agregar estilos CSS para las alertas
    if (!document.getElementById('alertStyles')) {
        const styleSheet = document.createElement('style');
        styleSheet.id = 'alertStyles';
        styleSheet.textContent = `
            .alert-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
            }
            
            .custom-alert {
                padding: 16px 20px;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                display: flex;
                align-items: center;
                gap: 12px;
                transform: translateX(420px);
                transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                font-size: 14px;
                font-weight: 500;
                position: relative;
                overflow: hidden;
            }
            
            .custom-alert.show {
                transform: translateX(0);
            }
            
            .custom-alert.success {
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
            }
            
            .custom-alert.error {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
            }
            
            .custom-alert.warning {
                background: linear-gradient(135deg, #f59e0b, #d97706);
                color: white;
            }
            
            .custom-alert.info {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
            }
            
            .alert-icon {
                font-size: 18px;
                min-width: 18px;
            }
            
            .alert-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            
            .alert-title {
                font-weight: 600;
                font-size: 15px;
            }
            
            .alert-message {
                opacity: 0.9;
                font-size: 13px;
                line-height: 1.4;
            }
            
            .alert-close {
                background: none;
                border: none;
                color: inherit;
                font-size: 16px;
                cursor: pointer;
                opacity: 0.7;
                transition: opacity 0.2s;
                padding: 4px;
                border-radius: 4px;
            }
            
            .alert-close:hover {
                opacity: 1;
                background: rgba(255, 255, 255, 0.1);
            }
            
            .alert-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(255, 255, 255, 0.3);
                transition: width linear;
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            
            .custom-alert.shake {
                animation: shake 0.5s ease-in-out;
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            .custom-alert.pulse {
                animation: pulse 0.6s ease-in-out;
            }
        `;
        document.head.appendChild(styleSheet);
    }
}

// Función principal para mostrar alertas bonitas
function showNotification(message, type = 'info', title = null, duration = 4000, options = {}) {
    const alertTypes = {
        success: {
            icon: 'fas fa-check-circle',
            title: title || 'Éxito',
            bgClass: 'success'
        },
        error: {
            icon: 'fas fa-exclamation-circle',
            title: title || 'Error',
            bgClass: 'error'
        },
        warning: {
            icon: 'fas fa-exclamation-triangle',
            title: title || 'Advertencia',
            bgClass: 'warning'
        },
        info: {
            icon: 'fas fa-info-circle',
            title: title || 'Información',
            bgClass: 'info'
        }
    };
    
    const config = alertTypes[type] || alertTypes.info;
    
    const alertElement = document.createElement('div');
    alertElement.className = `custom-alert ${config.bgClass}`;
    
    alertElement.innerHTML = `
        <i class="${config.icon} alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">${config.title}</div>
            <div class="alert-message">${message}</div>
        </div>
        <button class="alert-close" onclick="closeAlert(this)">
            <i class="fas fa-times"></i>
        </button>
        <div class="alert-progress"></div>
    `;
    
    alertContainer.appendChild(alertElement);
    
    // Animación de entrada
    setTimeout(() => {
        alertElement.classList.add('show');
        if (options.pulse) alertElement.classList.add('pulse');
        if (options.shake) alertElement.classList.add('shake');
    }, 100);
    
    // Barra de progreso
    const progressBar = alertElement.querySelector('.alert-progress');
    if (duration > 0) {
        progressBar.style.width = '100%';
        progressBar.style.transitionDuration = `${duration}ms`;
        setTimeout(() => {
            progressBar.style.width = '0%';
        }, 100);
        
        // Auto-cerrar
        setTimeout(() => {
            closeAlert(alertElement.querySelector('.alert-close'));
        }, duration);
    }
    
    return alertElement;
}

// Cerrar alerta individual
function closeAlert(closeBtn) {
    const alert = closeBtn.closest('.custom-alert');
    alert.classList.remove('show');
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 400);
}

// Limpiar todas las alertas
function clearAllAlerts() {
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(alert => {
        alert.classList.remove('show');
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 400);
    });
}

function setupSearch() {
    document.getElementById('stockSearch').addEventListener('input', function() {
        const searchTerm = this.value;
        fetch('/admin/stock', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=obtener_productos&busqueda=${encodeURIComponent(searchTerm)}`
        })
        .then(response => response.json())
        .then(data => renderStockTable(data))
        .catch(error => {
            console.error('Error en búsqueda:', error);
            showNotification('No se pudo realizar la búsqueda. Inténtalo nuevamente.', 'error', 'Error de Búsqueda', 5000, {shake: true});
        });
    });
}

function renderStockTable(data) {
    const tbody = document.getElementById('stockTableBody');
    tbody.innerHTML = data.map(item => {
        const stockLevel = getStockLevel(item.stock);
        const categoryIcon = getCategoryIcon(item.nombre_categoria);
        
        return `
            <tr data-id="${item.id_producto}">
                <td>
                    <div class="product-info">
                        ${categoryIcon}
                        <span>${item.nombre_producto}</span>
                    </div>
                </td>
                <td>
                    <span class="product-category category-${item.nombre_categoria.toLowerCase()}">
                        ${item.nombre_categoria}
                    </span>
                </td>
                <td class="stock-controls">
                    <input type="number" 
                           class="stock-input" 
                           value="${item.stock}" 
                           min="0"
                           data-original="${item.stock}"
                           onchange="enableSave(this)"
                           onkeyup="enableSave(this)">
                    <button class="save-btn" 
                            onclick="saveStock(this, ${item.id_producto}, '${item.nombre_producto}')"
                            disabled>
                        <i class="fas fa-save"></i>
                    </button>
                </td>
                <td>$${parseInt(item.precio).toLocaleString()}</td>
                <td>
                    <div class="stock-level">
                        <span class="stock-indicator ${stockLevel.class}"></span>
                        <span>${stockLevel.label}</span>
                    </div>
                </td>
                <td>
                    <button class="action-btn" onclick="quickAdjust(${item.id_producto}, '${item.nombre_producto}', ${item.stock})">
                        <i class="fas fa-edit"></i> Ajustar
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function enableSave(input) {
    const saveBtn = input.parentNode.querySelector('.save-btn');
    const original = parseInt(input.dataset.original);
    const current = parseInt(input.value);
    saveBtn.disabled = (original === current || isNaN(current) || current < 0);
}

function saveStock(btn, productId, productName) {
    const input = btn.parentNode.querySelector('.stock-input');
    const newStock = parseInt(input.value);
    
    if (isNaN(newStock) || newStock < 0) {
        showNotification('La cantidad debe ser un número válido mayor o igual a 0', 'error', 'Cantidad Inválida', 4000, {shake: true});
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('/admin/stock', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=ajustar_stock&id_producto=${productId}&nuevo_stock=${newStock}&motivo=Ajuste directo`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.dataset.original = newStock;
            btn.innerHTML = '<i class="fas fa-save"></i>';
            updateRowStatus(btn.closest('tr'), newStock);
            
            // Alerta bonita de éxito con detalles
            const stockStatus = getStockLevel(newStock);
            let alertMessage = `Stock actualizado correctamente para ${productName}`;
            let alertType = 'success';
            let alertOptions = {pulse: true};
            
            if (stockStatus.class === 'critical') {
                alertMessage += `\n⚠️ Atención: Stock crítico (${newStock} unidades)`;
                alertType = 'warning';
                alertOptions.shake = true;
            } else if (stockStatus.class === 'low') {
                alertMessage += `\n📦 Stock bajo (${newStock} unidades)`;
            }
            
            showNotification(alertMessage, alertType, 'Stock Actualizado', 5000, alertOptions);
            refreshStats();
        } else {
            btn.innerHTML = '<i class="fas fa-save"></i>';
            showNotification('No se pudo actualizar el stock. Verifica la conexión e inténtalo nuevamente.', 'error', 'Error de Actualización', 6000, {shake: true});
        }
        btn.disabled = true;
    })
    .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = '<i class="fas fa-save"></i>';
        btn.disabled = true;
        showNotification('Error de conexión con el servidor. Revisa tu conexión a internet.', 'error', 'Error de Conexión', 6000, {shake: true});
    });
}

function updateRowStatus(row, stock) {
    const statusCell = row.querySelector('.stock-level');
    const level = getStockLevel(stock);
    statusCell.innerHTML = `
        <span class="stock-indicator ${level.class}"></span>
        <span>${level.label}</span>
    `;
}

function quickAdjust(productId, productName, currentStock) {
    // Crear modal personalizado para ajuste rápido
    const modal = document.createElement('div');
    modal.className = 'quick-adjust-modal';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="closeQuickAdjust()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Ajustar Stock</h3>
                <button onclick="closeQuickAdjust()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="product-info-modal">
                    <strong>${productName}</strong>
                    <span>Stock actual: ${currentStock} unidades</span>
                </div>
                <div class="input-group">
                    <label>Nuevo stock:</label>
                    <input type="number" id="newStockInput" value="${currentStock}" min="0" class="modal-input">
                </div>
                <div class="input-group">
                    <label>Motivo del ajuste:</label>
                    <select id="motivoSelect" class="modal-input">
                        <option value="Ajuste rápido">Ajuste rápido</option>
                        <option value="Inventario físico">Inventario físico</option>
                        <option value="Producto dañado">Producto dañado</option>
                        <option value="Venta directa">Venta directa</option>
                        <option value="Devolución">Devolución</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="quick-buttons">
                    <button onclick="setQuickValue(0)" class="quick-btn">0</button>
                    <button onclick="setQuickValue(5)" class="quick-btn">5</button>
                    <button onclick="setQuickValue(10)" class="quick-btn">10</button>
                    <button onclick="setQuickValue(25)" class="quick-btn">25</button>
                    <button onclick="setQuickValue(50)" class="quick-btn">50</button>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeQuickAdjust()" class="btn-cancel">Cancelar</button>
                <button onclick="confirmQuickAdjust(${productId}, '${productName}')" class="btn-confirm">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    `;
    
    // Agregar estilos del modal si no existen
    if (!document.getElementById('modalStyles')) {
        const modalStyles = document.createElement('style');
        modalStyles.id = 'modalStyles';
        modalStyles.textContent = `
            .quick-adjust-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(5px);
            }
            
            .modal-content {
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                max-width: 500px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                position: relative;
                transform: scale(0.8);
                animation: modalShow 0.3s ease-out forwards;
            }
            
            @keyframes modalShow {
                to {
                    transform: scale(1);
                }
            }
            
            .modal-header {
                padding: 20px 24px 16px;
                border-bottom: 1px solid #e5e7eb;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .modal-header h3 {
                margin: 0;
                color: #1f2937;
                font-size: 18px;
                font-weight: 600;
            }
            
            .modal-close {
                background: none;
                border: none;
                font-size: 18px;
                color: #6b7280;
                cursor: pointer;
                padding: 8px;
                border-radius: 8px;
                transition: all 0.2s;
            }
            
            .modal-close:hover {
                background: #f3f4f6;
                color: #374151;
            }
            
            .modal-body {
                padding: 20px 24px;
            }
            
            .product-info-modal {
                background: #f8fafc;
                padding: 16px;
                border-radius: 12px;
                margin-bottom: 20px;
            }
            
            .product-info-modal strong {
                display: block;
                font-size: 16px;
                color: #1f2937;
                margin-bottom: 4px;
            }
            
            .product-info-modal span {
                color: #6b7280;
                font-size: 14px;
            }
            
            .input-group {
                margin-bottom: 16px;
            }
            
            .input-group label {
                display: block;
                margin-bottom: 6px;
                font-weight: 500;
                color: #374151;
            }
            
            .modal-input {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 14px;
                transition: border-color 0.2s;
            }
            
            .modal-input:focus {
                outline: none;
                border-color: #3b82f6;
            }
            
            .quick-buttons {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                margin-top: 12px;
            }
            
            .quick-btn {
                padding: 8px 16px;
                background: #f3f4f6;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .quick-btn:hover {
                background: #e5e7eb;
                border-color: #9ca3af;
            }
            
            .modal-footer {
                padding: 16px 24px 20px;
                border-top: 1px solid #e5e7eb;
                display: flex;
                gap: 12px;
                justify-content: flex-end;
            }
            
            .btn-cancel, .btn-confirm {
                padding: 10px 20px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
            }
            
            .btn-cancel {
                background: #f3f4f6;
                color: #374151;
            }
            
            .btn-cancel:hover {
                background: #e5e7eb;
            }
            
            .btn-confirm {
                background: #3b82f6;
                color: white;
            }
            
            .btn-confirm:hover {
                background: #2563eb;
            }
        `;
        document.head.appendChild(modalStyles);
    }
    
    document.body.appendChild(modal);
    document.getElementById('newStockInput').focus();
    
    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickAdjust();
        }
    });
}

function setQuickValue(value) {
    document.getElementById('newStockInput').value = value;
}

function closeQuickAdjust() {
    const modal = document.querySelector('.quick-adjust-modal');
    if (modal) {
        modal.remove();
    }
}

function confirmQuickAdjust(productId, productName) {
    const newStock = parseInt(document.getElementById('newStockInput').value);
    const motivo = document.getElementById('motivoSelect').value;
    
    if (isNaN(newStock) || newStock < 0) {
        showNotification('La cantidad debe ser un número válido mayor o igual a 0', 'error', 'Cantidad Inválida', 4000, {shake: true});
        return;
    }
    
    closeQuickAdjust();
    
    // Mostrar alerta de procesamiento
    const processingAlert = showNotification('Procesando ajuste de stock...', 'info', 'Actualizando', 0);
    
    fetch('/admin/stock', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=ajustar_stock&id_producto=${productId}&nuevo_stock=${newStock}&motivo=${encodeURIComponent(motivo)}`
    })
    .then(response => response.json())
    .then(data => {
        closeAlert(processingAlert.querySelector('.alert-close'));
        
        if (data.success) {
            const row = document.querySelector(`tr[data-id="${productId}"]`);
            const input = row.querySelector('.stock-input');
            input.value = newStock;
            input.dataset.original = newStock;
            updateRowStatus(row, parseInt(newStock));
            
            // Determinar tipo de alerta según el nuevo stock
            const stockStatus = getStockLevel(newStock);
            let alertType = 'success';
            let alertMessage = `Stock ajustado exitosamente para ${productName}`;
            let alertOptions = {pulse: true};
            
            if (stockStatus.class === 'critical') {
                alertType = 'warning';
                alertMessage += `\n⚠️ Atención: Stock crítico (${newStock} unidades)`;
                alertOptions.shake = true;
            } else if (stockStatus.class === 'low') {
                alertMessage += `\n📦 Stock bajo (${newStock} unidades)`;
            }
            
            alertMessage += `\nMotivo: ${motivo}`;
            
            showNotification(alertMessage, alertType, 'Ajuste Completado', 6000, alertOptions);
            refreshStats();
        } else {
            showNotification('Error al procesar el ajuste de stock. Inténtalo nuevamente.', 'error', 'Error de Procesamiento', 5000, {shake: true});
        }
    })
    .catch(error => {
        closeAlert(processingAlert.querySelector('.alert-close'));
        console.error('Error:', error);
        showNotification('Error de conexión. Verifica tu conexión a internet e inténtalo nuevamente.', 'error', 'Error de Conexión', 6000, {shake: true});
    });
}

function getStockLevel(stock) {
    if (stock < 5) return { class: 'critical', label: 'Crítico' };
    if (stock < 15) return { class: 'low', label: 'Bajo' };
    return { class: 'available', label: 'Disponible' };
}

function getCategoryIcon(category) {
    const icons = {
        'Bebidas': '<i class="fas fa-glass-whiskey"></i>',
        'Comidas': '<i class="fas fa-utensils"></i>',
        'Postres': '<i class="fas fa-ice-cream"></i>'
    };
    return icons[category] || '<i class="fas fa-cube"></i>';
}

function refreshStats() {
    fetch('/admin/stock', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=obtener_estadisticas'
    })
    .then(response => response.json())
    .then(stats => {
        document.getElementById('totalProducts').textContent = stats.total;
        document.getElementById('availableProducts').textContent = stats.disponible;
        document.getElementById('lowStockProducts').textContent = stats.bajo;
        document.getElementById('criticalProducts').textContent = stats.critico;
        
        // Mostrar alertas si hay productos con stock crítico o bajo
        if (stats.critico > 0) {
            showNotification(`${stats.critico} producto(s) con stock crítico requieren atención inmediata`, 'warning', 'Stock Crítico', 8000, {shake: true});
        }
    })
    .catch(error => {
        console.error('Error al actualizar estadísticas:', error);
        showNotification('Error al actualizar las estadísticas del sistema', 'error', 'Error de Estadísticas', 4000);
    });
}

function loadInitialData() {
    showNotification('Cargando datos del sistema...', 'info', 'Inicializando', 2000);
    refreshStats();
}

function refreshData() {
    const refreshBtn = document.querySelector('.refresh-btn');
    const icon = refreshBtn.querySelector('i');
    icon.style.animation = 'spin 1s linear infinite';
    
    // Mostrar alerta de actualización
    const refreshingAlert = showNotification('Actualizando todos los datos del sistema...', 'info', 'Actualizando', 0);
    
    Promise.all([
        fetch('/admin/stock', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=obtener_estadisticas'}),
        fetch('/admin/stock', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=obtener_productos'}),
        fetch('/admin/stock', {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'action=obtener_alertas'})
    ])
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(([stats, productos, alertas]) => {
        // Actualizar estadísticas
        document.getElementById('totalProducts').textContent = stats.total;
        document.getElementById('availableProducts').textContent = stats.disponible;
        document.getElementById('lowStockProducts').textContent = stats.bajo;
        document.getElementById('criticalProducts').textContent = stats.critico;
        
        // Actualizar tabla
        renderStockTable(productos);
        
        // Cerrar alerta de actualización
        closeAlert(refreshingAlert.querySelector('.alert-close'));
        
        // Mostrar resultado
        icon.style.animation = '';
        showNotification('Todos los datos han sido actualizados correctamente', 'success', 'Actualización Completa', 4000, {pulse: true});
        
        // Mostrar alertas críticas si las hay
        if (stats.critico > 0) {
            setTimeout(() => {
                showNotification(`Atención: ${stats.critico} producto(s) tienen stock crítico`, 'warning', 'Revisión Necesaria', 8000, {shake: true});
            }, 1000);
        }
        
        // Procesar alertas adicionales si vienen del servidor
        if (alertas && alertas.length > 0) {
            alertas.forEach((alerta, index) => {
                setTimeout(() => {
                    showNotification(alerta.mensaje, alerta.tipo || 'info', alerta.titulo || 'Alerta', 6000);
                }, 2000 + (index * 500));
            });
        }
    })
    .catch(error => {
        closeAlert(refreshingAlert.querySelector('.alert-close'));
        console.error('Error al actualizar datos:', error);
        icon.style.animation = '';
        showNotification('Error al actualizar los datos. Algunos elementos pueden no estar actualizados.', 'error', 'Error de Actualización', 6000, {shake: true});
    });
}

// Función para mostrar alertas de stock bajo automáticamente
function checkStockAlerts() {
    const rows = document.querySelectorAll('#stockTableBody tr');
    let criticalCount = 0;
    let lowCount = 0;
    
    rows.forEach(row => {
        const stockInput = row.querySelector('.stock-input');
        const stock = parseInt(stockInput.value);
        const productName = row.querySelector('.product-info span').textContent;
        
        if (stock < 5) {
            criticalCount++;
        } else if (stock < 15) {
            lowCount++;
        }
    });
    
    // Mostrar alertas de resumen si hay productos con stock bajo
    if (criticalCount > 0 || lowCount > 0) {
        let message = '';
        let type = 'warning';
        
        if (criticalCount > 0) {
            message = `${criticalCount} producto(s) con stock crítico`;
            if (lowCount > 0) {
                message += ` y ${lowCount} con stock bajo`;
            }
        } else {
            message = `${lowCount} producto(s) con stock bajo`;
            type = 'info';
        }
        
        showNotification(message + '. Considera restock pronto.', type, 'Revisión de Inventario', 8000);
    }
}

// Función para exportar datos (bonus)
function exportStockData() {
    const exportBtn = document.querySelector('.export-btn');
    if (exportBtn) {
        const icon = exportBtn.querySelector('i');
        icon.className = 'fas fa-spinner fa-spin';
        
        fetch('/admin/stock', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=exportar_stock'
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `stock_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            icon.className = 'fas fa-download';
            showNotification('Datos de stock exportados exitosamente', 'success', 'Exportación Completa', 4000, {pulse: true});
        })
        .catch(error => {
            console.error('Error al exportar:', error);
            icon.className = 'fas fa-download';
            showNotification('Error al exportar los datos. Inténtalo nuevamente.', 'error', 'Error de Exportación', 5000, {shake: true});
        });
    }
}

// Función para importar datos (bonus)
function importStockData(fileInput) {
    const file = fileInput.files[0];
    if (!file) return;
    
    if (!file.name.endsWith('.csv')) {
        showNotification('Solo se permiten archivos CSV para la importación', 'error', 'Formato Incorrecto', 5000, {shake: true});
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('action', 'importar_stock');
    
    const importingAlert = showNotification('Importando datos desde archivo CSV...', 'info', 'Procesando Importación', 0);
    
    fetch('/admin/stock', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        closeAlert(importingAlert.querySelector('.alert-close'));
        
        if (data.success) {
            showNotification(`Importación completada: ${data.updated} productos actualizados`, 'success', 'Importación Exitosa', 6000, {pulse: true});
            refreshData();
        } else {
            showNotification(data.message || 'Error al procesar el archivo de importación', 'error', 'Error de Importación', 6000, {shake: true});
        }
    })
    .catch(error => {
        closeAlert(importingAlert.querySelector('.alert-close'));
        console.error('Error en importación:', error);
        showNotification('Error al procesar la importación. Verifica el formato del archivo.', 'error', 'Error de Importación', 6000, {shake: true});
    })
    .finally(() => {
        fileInput.value = ''; // Limpiar input
    });
}

// Función para mostrar alertas de productos próximos a vencer (si aplica)
function showExpirationAlerts() {
    fetch('/admin/stock', {
        method: 'POST', 
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=obtener_productos_proximos_vencer'
    })
    .then(response => response.json())
    .then(data => {
        if (data.length > 0) {
            data.forEach((producto, index) => {
                setTimeout(() => {
                    const diasRestantes = producto.dias_restantes;
                    let type = 'warning';
                    let title = 'Producto Próximo a Vencer';
                    
                    if (diasRestantes <= 1) {
                        type = 'error';
                        title = 'Producto Venciendo Hoy';
                    } else if (diasRestantes <= 3) {
                        type = 'warning';
                        title = 'Vencimiento Inminente';
                    }
                    
                    showNotification(
                        `${producto.nombre} vence en ${diasRestantes} día(s). Stock: ${producto.stock}`,
                        type,
                        title,
                        8000,
                        {shake: diasRestantes <= 1}
                    );
                }, index * 1000);
            });
        }
    })
    .catch(error => {
        console.error('Error al verificar vencimientos:', error);
    });
}

// Función para mostrar confirmación antes de acciones críticas
function confirmCriticalAction(message, callback) {
    const confirmed = confirm(`⚠️ ACCIÓN CRÍTICA ⚠️\n\n${message}\n\n¿Estás seguro de que deseas continuar?`);
    if (confirmed) {
        callback();
    } else {
        showNotification('Acción cancelada por el usuario', 'info', 'Operación Cancelada', 3000);
    }
}

// Función para ajuste masivo de stock
function massStockAdjustment() {
    const selectedProducts = document.querySelectorAll('input[name="selected_products"]:checked');
    if (selectedProducts.length === 0) {
        showNotification('Selecciona al menos un producto para el ajuste masivo', 'warning', 'Sin Selección', 4000, {shake: true});
        return;
    }
    
    const adjustment = prompt('Ingresa el ajuste a aplicar (usar + o - para incrementar/decrementar):\nEjemplo: +10, -5, =20');
    if (!adjustment) return;
    
    const adjustmentType = adjustment.charAt(0);
    const adjustmentValue = parseInt(adjustment.slice(1));
    
    if (isNaN(adjustmentValue) || !['+', '-', '='].includes(adjustmentType)) {
        showNotification('Formato inválido. Usa +10, -5 o =20', 'error', 'Formato Incorrecto', 5000, {shake: true});
        return;
    }
    
    const productIds = Array.from(selectedProducts).map(cb => cb.value);
    const processingAlert = showNotification(`Aplicando ajuste masivo a ${productIds.length} productos...`, 'info', 'Procesando Ajuste Masivo', 0);
    
    fetch('/admin/stock', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=ajuste_masivo&productos=${encodeURIComponent(JSON.stringify(productIds))}&ajuste=${encodeURIComponent(adjustment)}&motivo=Ajuste masivo`
    })
    .then(response => response.json())
    .then(data => {
        closeAlert(processingAlert.querySelector('.alert-close'));
        
        if (data.success) {
            showNotification(`Ajuste masivo completado: ${data.affected} productos actualizados`, 'success', 'Ajuste Masivo Exitoso', 6000, {pulse: true});
            refreshData();
        } else {
            showNotification('Error en el ajuste masivo: ' + (data.message || 'Error desconocido'), 'error', 'Error de Ajuste Masivo', 6000, {shake: true});
        }
    })
    .catch(error => {
        closeAlert(processingAlert.querySelector('.alert-close'));
        console.error('Error en ajuste masivo:', error);
        showNotification('Error de conexión durante el ajuste masivo', 'error', 'Error de Conexión', 6000, {shake: true});
    });
}

// Sistema de notificaciones push (si el navegador lo soporta)
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showNotification('Notificaciones activadas para alertas de stock crítico', 'success', 'Notificaciones Habilitadas', 4000);
            }
        });
    }
}

function showDesktopNotification(title, message, type = 'info') {
    if ('Notification' in window && Notification.permission === 'granted') {
        const notification = new Notification(title, {
            body: message,
            icon: type === 'error' ? '/assets/icons/error.png' : '/assets/icons/info.png',
            tag: 'stock-alert',
            requireInteraction: type === 'error'
        });
        
        notification.onclick = function() {
            window.focus();
            notification.close();
        };
        
        setTimeout(() => notification.close(), 5000);
    }
}

// Verificación periódica de stock crítico
let stockCheckInterval;

function startStockMonitoring() {
    // Verificar cada 5 minutos
    stockCheckInterval = setInterval(() => {
        fetch('/admin/stock', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=verificar_stock_critico'
        })
        .then(response => response.json())
        .then(data => {
            if (data.criticos && data.criticos.length > 0) {
                data.criticos.forEach(producto => {
                    showNotification(
                        `${producto.nombre} tiene stock crítico: ${producto.stock} unidades`,
                        'error',
                        'Alerta de Stock Crítico',
                        10000,
                        {shake: true}
                    );
                    
                    // Notificación del navegador también
                    showDesktopNotification(
                        'Stock Crítico Detectado',
                        `${producto.nombre}: ${producto.stock} unidades restantes`,
                        'error'
                    );
                });
            }
        })
        .catch(error => {
            console.error('Error en monitoreo de stock:', error);
        });
    }, 300000); // 5 minutos
}

function stopStockMonitoring() {
    if (stockCheckInterval) {
        clearInterval(stockCheckInterval);
        stockCheckInterval = null;
    }
}

// Inicializar monitoreo cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Solicitar permisos de notificación
    setTimeout(requestNotificationPermission, 2000);
    
    // Iniciar monitoreo de stock
    setTimeout(startStockMonitoring, 5000);
    
    // Verificar alertas de vencimiento si está habilitado
    setTimeout(showExpirationAlerts, 3000);
    
    // Verificar alertas de stock al cargar
    setTimeout(checkStockAlerts, 1000);
});

// Limpiar al salir de la página
window.addEventListener('beforeunload', function() {
    stopStockMonitoring();
});

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    // Ctrl+R para refrescar datos
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        refreshData();
    }
    
    // Ctrl+F para enfocar búsqueda
    if (e.ctrlKey && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.getElementById('stockSearch');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }
    
    // Escape para cerrar todas las alertas
    if (e.key === 'Escape') {
        clearAllAlerts();
    }
});

// Función para generar reportes de stock
function generateStockReport() {
    const reportBtn = document.querySelector('.report-btn');
    if (reportBtn) {
        const icon = reportBtn.querySelector('i');
        icon.className = 'fas fa-spinner fa-spin';
        
        showNotification('Generando reporte de stock completo...', 'info', 'Generando Reporte', 0);
        
        fetch('/admin/stock', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=generar_reporte_stock'
        })
        .then(response => response.json())
        .then(data => {
            icon.className = 'fas fa-chart-bar';
            
            if (data.success) {
                // Mostrar resumen del reporte
                const reportSummary = `
                    📊 Reporte generado exitosamente:
                    • Total productos: ${data.total}
                    • Stock disponible: ${data.disponible}
                    • Stock bajo: ${data.bajo}
                    • Stock crítico: ${data.critico}
                    • Valor total inventario: ${data.valor_total?.toLocaleString()}
                `;
                
                showNotification(reportSummary, 'success', 'Reporte Completado', 8000, {pulse: true});
                
                // Descargar reporte si está disponible
                if (data.download_url) {
                    setTimeout(() => {
                        const a = document.createElement('a');
                        a.href = data.download_url;
                        a.download = `reporte_stock_${new Date().toISOString().split('T')[0]}.pdf`;
                        a.click();
                    }, 1000);
                }
            } else {
                showNotification('Error al generar el reporte de stock', 'error', 'Error de Reporte', 5000, {shake: true});
            }
        })
        .catch(error => {
            console.error('Error al generar reporte:', error);
            icon.className = 'fas fa-chart-bar';
            showNotification('Error de conexión al generar el reporte', 'error', 'Error de Conexión', 5000, {shake: true});
        });
    }
}

// Función de ayuda para mostrar tips al usuario
function showHelpTips() {
    const tips = [
        {
            title: 'Ajuste Rápido',
            message: 'Haz clic en "Ajustar" para cambiar el stock de un producto específico con opciones rápidas'
        },
        {
            title: 'Atajos de Teclado',
            message: 'Usa Ctrl+R para refrescar, Ctrl+F para buscar, y Escape para cerrar alertas'
        },
        {
            title: 'Códigos de Color',
            message: 'Rojo = Stock crítico (<5), Amarillo = Stock bajo (<15), Verde = Stock disponible'
        },
        {
            title: 'Búsqueda Inteligente',
            message: 'La búsqueda funciona en tiempo real y busca tanto en nombres como en categorías'
        }
    ];
    
    tips.forEach((tip, index) => {
        setTimeout(() => {
            showNotification(tip.message, 'info', tip.title, 6000);
        }, index * 2000);
    });
}

// Función para validar datos antes de guardar
function validateStockData(stock, productName) {
    const errors = [];
    
    if (isNaN(stock) || stock < 0) {
        errors.push('El stock debe ser un número válido mayor o igual a 0');
    }
    
    if (stock > 9999) {
        errors.push('El stock no puede ser mayor a 9999 unidades');
    }
    
    if (!productName || productName.trim() === '') {
        errors.push('El nombre del producto es requerido');
    }
    
    return errors;
}

// Mejorar la función saveStock con validación
const originalSaveStock = saveStock;
saveStock = function(btn, productId, productName) {
    const input = btn.parentNode.querySelector('.stock-input');
    const newStock = parseInt(input.value);
    
    const validationErrors = validateStockData(newStock, productName);
    if (validationErrors.length > 0) {
        showNotification(validationErrors.join('\n'), 'error', 'Error de Validación', 5000, {shake: true});
        return;
    }
    
    // Confirmar si es un cambio drástico
    const originalStock = parseInt(input.dataset.original);
    const percentageChange = Math.abs((newStock - originalStock) / originalStock) * 100;
    
    if (percentageChange > 50 && originalStock > 0) {
        confirmCriticalAction(
            `Estás cambiando el stock de ${originalStock} a ${newStock} unidades (${percentageChange.toFixed(1)}% de cambio).\n\nEsto es un cambio significativo para: ${productName}`,
            () => originalSaveStock(btn, productId, productName)
        );
    } else {
        originalSaveStock(btn, productId, productName);
    }
};

console.log('Sistema de control de stock inicializado con alertas avanzadas ✅');
    </script>
</body>
</html>