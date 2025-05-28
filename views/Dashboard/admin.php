<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Bar Container</title>
    <link rel="stylesheet" href="/style/style3.css">
    
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="logo">
            <h1>🍺 Bar Container</h1>
        </div>
        
        <div class="nav-menu">
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin" class="nav-link active" onclick="showSection('dashboard')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v4H8V5z"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Inventario</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin/productos" class="nav-link" onclick="showSection('productos')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                            Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/stock" class="nav-link" onclick="showSection('stock')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Control Stock
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Ventas</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin/ventas" class="nav-link" onclick="showSection('ventas')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/views/Dashboard/reportes_control.php" class="nav-link" onclick="showSection('reportes')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Reportes
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Personal</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin/empleados" class="nav-link" onclick="showSection('empleados')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            Empleados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/pagos" class="nav-link" onclick="showSection('pagos')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Pagos
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Sistema</div>
                <ul>
                    <li class="nav-item">
                        <a href="#configuracion" class="nav-link" onclick="showSection('configuracion')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Configuración
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#seguridad" class="nav-link" onclick="showSection('seguridad')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Seguridad
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Section -->
        <section id="dashboard" class="section active">
            <div class="header">
                <h1 class="page-title">Dashboard</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary">📊 Exportar</button>
                    <button class="btn btn-primary">➕ Nueva Venta</button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Ventas Hoy</div>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-value">$2,345,000</div>
                    <div class="stat-change positive">
                        ↗️ +12.5% vs ayer
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Órdenes Hoy</div>
                        <div class="stat-icon">📋</div>
                    </div>
                    <div class="stat-value">87</div>
                    <div class="stat-change positive">
                        ↗️ +8.2% vs ayer
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Productos en Stock</div>
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-value">234</div>
                    <div class="stat-change negative">
                        ↘️ -5 productos críticos
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Empleados Activos</div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-value">12</div>
                    <div class="stat-change positive">
                        ✅ Todos presentes
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div class="alert alert-warning">
                ⚠️ <strong>Stock Bajo:</strong> Cerveza Corona (5 unidades restantes)
            </div>

            <div class="alert alert-danger">
                🚨 <strong>Crítico:</strong> Ron Medellín agotado - Contactar proveedor
            </div>

            <!-- Recent Activity -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Actividad Reciente</h2>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>14:32</td>
                                <td>Venta</td>
                                <td>2x Cerveza Corona, 1x Aguardiente</td>
                                <td>Carlos Méndez</td>
                                <td><span class="btn btn-sm btn-success">Completada</span></td>
                            </tr>
                            <tr>
                                <td>14:28</td>
                                <td>Stock</td>
                                <td>Actualización inventario - Whisky</td>
                                <td>Admin</td>
                                <td><span class="btn btn-sm btn-warning">Pendiente</span></td>
                            </tr>
                            <tr>
                                <td>14:15</td>
                                <td>Pago</td>
                                <td>Pago empleado - María González</td>
                                <td>Admin</td>
                                <td><span class="btn btn-sm btn-success">Procesado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Productos Section -->
        <section id="productos" class="section">
            <div class="header">
                <h1 class="page-title">Gestión de Productos</h1>
                <div class="header-actions">
                    <button class="btn btn-secondary">📥 Importar</button>
                    <button class="btn btn-primary">➕ Nuevo Producto</button>
                </div>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Agregar Producto</h2>
                </div>
                
                <form class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-input" placeholder="Ej: Cerveza Corona">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select class="form-select">
                            <option>Cervezas</option>
                            <option>Licores</option>
                            <option>Aguardientes</option>
                            <option>Snacks</option>
                            <option>Cigarrillos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio de Venta (COP)</label>
                        <input type="number" class="form-input" placeholder="5000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" class="form-input" placeholder="50">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-input" placeholder="10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proveedor</label>
                        <input type="text" class="form-input" placeholder="Distribuidora XYZ">
                    </div>
                </form>
                
                <button class="btn btn-primary">💾 Guardar Producto</button>
            </div>

            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Lista de Productos</h2>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Cerveza Corona</strong></td>
                                <td>Cervezas</td>
                                <td>$5,000</td>
                                <td>5</td>
                                <td><span class="btn btn-sm btn-danger">Stock Bajo</span></td>
                                <td>
                                    <button class="btn btn-sm btn-warning">✏️</button>
                                    <button class="btn btn-sm btn-danger">🗑️