<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Stock</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style/style4.css">
   
</head>
<body>
    <div class="container">
        <!-- Header -->
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

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
                <div class="stat-value" id="totalProducts">248</div>
                <div class="stat-label">Total de Productos</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>+12 este mes</span>
                </div>
            </div>

            <div class="stat-card available">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-value" id="availableProducts">198</div>
                <div class="stat-label">En Stock Disponible</div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>+5% vs semana pasada</span>
                </div>
            </div>

            <div class="stat-card low">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-value" id="lowStockProducts">28</div>
                <div class="stat-label">Stock Bajo</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-up"></i>
                    <span>+3 productos</span>
                </div>
            </div>

            <div class="stat-card critical">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <div class="stat-value" id="criticalProducts">8</div>
                <div class="stat-label">Stock Crítico</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-up"></i>
                    <span>Requiere atención</span>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Stock Table -->
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
                            <th>Stock Mínimo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody">
                        <!-- Los productos se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>

            <!-- Alerts Panel -->
            <div class="alerts-panel">
                <div class="section-header" style="margin-bottom: 1rem; padding-bottom: 1rem;">
                    <h2 class="section-title">Alertas de Stock</h2>
                </div>

                <div id="alertsList">
                    <div class="alert-item alert-critical">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Stock Crítico</div>
                            <div class="alert-message">Servilletas: Solo quedan 5 unidades</div>
                            <div class="alert-time">Hace 15 minutos</div>
                        </div>
                    </div>

                    <div class="alert-item alert-warning">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Stock Bajo</div>
                            <div class="alert-message">Pizza Margherita: 8 unidades restantes</div>
                            <div class="alert-time">Hace 1 hora</div>
                        </div>
                    </div>

                    <div class="alert-item alert-warning">
                        <div class="alert-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-title">Reabastecimiento Programado</div>
                            <div class="alert-message">Coca Cola 350ml: Llegada mañana</div>
                            <div class="alert-time">Programado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Movements -->
        <div class="movements-section">
            <div class="section-header">
                <h2 class="section-title">Movimientos Recientes</h2>
                <button class="action-btn" onclick="showAllMovements()">
                    Ver Todos
                </button>
            </div>

            <div id="movementsList">
                <div class="movement-item fade-in">
                    <div class="movement-info">
                        <div class="movement-type movement-out">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="movement-details">
                            <div class="movement-product">Coca Cola 350ml</div>
                            <div class="movement-description">Venta - Mesa #12</div>
                        </div>
                    </div>
                    <div class="movement-quantity">
                        <div class="quantity-value quantity-out">-6</div>
                        <div class="movement-time">Hace 5 min</div>
                    </div>
                </div>

                <div class="movement-item fade-in">
                    <div class="movement-info">
                        <div class="movement-type movement-in">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="movement-details">
                            <div class="movement-product">Agua Mineral 500ml</div>
                            <div class="movement-description">Reabastecimiento</div>
                        </div>
                    </div>
                    <div class="movement-quantity">
                        <div class="quantity-value quantity-in">+24</div>
                        <div class="movement-time">Hace 2 horas</div>
                    </div>
                </div>

                <div class="movement-item fade-in">
                    <div class="movement-info">
                        <div class="movement-type movement-out">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="movement-details">
                            <div class="movement-product">Hamburguesa Clásica</div>
                            <div class="movement-description">Venta - Delivery #892</div>
                        </div>
                    </div>
                    <div class="movement-quantity">
                        <div class="quantity-value quantity-out">-3</div>
                        <div class="movement-time">Hace 3 horas</div>
                    </div>
                </div>

                <div class="movement-item fade-in">
                    <div class="movement-info">
                        <div class="movement-type movement-out">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="movement-details">
                            <div class="movement-product">Servilletas</div>
                            <div class="movement-description">Uso interno</div>
                        </div>
                    </div>
                    <div class="movement-quantity">
                        <div class="quantity-value quantity-out">-10</div>
                        <div class="movement-time">Hace 4 horas</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="#" class="quick-btn" onclick="openAdjustmentModal()">
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
        // Datos de ejemplo para el stock
        let stockData = [
            {
                id: 1,
                name: "Coca Cola 350ml",
                category: "bebidas",
                currentStock: 48,
                minStock: 20,
                code: "COC001"
            },
            {
                id: 2,
                name: "Hamburguesa Clásica",
                category: "alimentos",
                currentStock: 15,
                minStock: 10,
                code: "HAM001"
            },
            {
                id: 3,
                name: "Servilletas",
                category: "insumos",
                currentStock: 5,
                minStock: 15,
                code: "SER001"
            },
            {
                id: 4,
                name: "Agua Mineral 500ml",
                category: "bebidas",
                currentStock: 32,
                minStock: 25,
                code: "AGU001"
            },
            {
                id: 5,
                name: "Pizza Margherita",
                category: "alimentos",
                currentStock: 8,
                minStock: 5,
                code: "PIZ001"
            },
            {
                id: 6,
                name: "Vasos Desechables",
                category: "insumos",
                currentStock: 2,
                minStock: 50,
                code: "VAS001"
            }
        ];

        // Inicializar la aplicación
        document.addEventListener('DOMContentLoaded', function() {
            renderStockTable();
            updateStats();
            setupSearch();
        });

        // Renderizar tabla de stock
        function renderStockTable() {
            const tbody = document.getElementById('stockTableBody');
            const searchTerm = document.getElementById('stockSearch').value.toLowerCase();
            
            const filteredData = stockData.filter(item => 
                item.name.toLowerCase().includes(searchTerm) ||
                item.code.toLowerCase().includes(searchTerm)
            );

            tbody.innerHTML = filteredData.map(item => {
                const stockLevel = getStockLevel(item.currentStock, item.minStock);
                const categoryClass = `category-${item.category}`;
                        const categoryIcon = getCategoryIcon(item.category);
        
                        return `
                            <tr>
                                <td>
                                    <div class="product-info">
                                        ${categoryIcon}
                                        <span>${item.name}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="product-category ${categoryClass}">
                                        ${item.category.charAt(0).toUpperCase() + item.category.slice(1)}
                                    </span>
                                </td>
                                <td>${item.currentStock}</td>
                                <td>${item.minStock}</td>
                                <td>
                                    <div class="stock-level">
                                        <span class="stock-indicator ${stockLevel.class}"></span>
                                        <span>${stockLevel.label}</span>
                                    </div>
                                </td>
                                <td>
                                    <button class="action-btn" onclick="openAdjustmentModal(${item.id})">
                                        <i class="fas fa-edit"></i> Ajustar
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }