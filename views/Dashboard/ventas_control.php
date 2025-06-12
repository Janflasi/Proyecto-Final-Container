<?php
// Configuración de errores - solo para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiado a 0 para AJAX
ini_set('log_errors', 1);

function sendJsonResponse($data, $httpCode = 200) {
    // Limpiar cualquier buffer de salida
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Access-Control-Allow-Origin: *');
    
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        $json = json_encode(['success' => false, 'mensaje' => 'Error de codificación JSON: ' . json_last_error_msg()]);
    }
    
    echo $json;
    exit;
}

// Procesar AJAX con debugging mejorado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar buffer de salida para capturar errores
        ob_start();
        
        if (!isset($_POST['accion'])) {
            sendJsonResponse(['success' => false, 'mensaje' => 'Acción no especificada'], 400);
        }
        
        // Verificar si existe el archivo de conexión
        if (!file_exists('config/Conexion.php')) {
            sendJsonResponse(['success' => false, 'mensaje' => 'Archivo de configuración no encontrado'], 500);
        }
        
        require_once 'config/Conexion.php';
        
        // Verificar si la clase Database existe
        if (!class_exists('Database')) {
            sendJsonResponse(['success' => false, 'mensaje' => 'Clase Database no encontrada'], 500);
        }
        
        $historial = new HistorialVentas();
        
        switch ($_POST['accion']) {
            case 'obtenerVentas':
                $params = ['fechaDesde', 'fechaHasta', 'estado', 'busqueda'];
                $filters = array_filter(array_intersect_key($_POST, array_flip($params)), fn($v) => !empty($v));
                
                $ventas = $historial->obtenerVentas(...array_pad(array_values($filters), 4, null));
                $estadisticas = $historial->obtenerEstadisticas(...array_pad(array_values($filters), 4, null));
                
                sendJsonResponse(['ventas' => $ventas, 'estadisticas' => $estadisticas, 'success' => true]);
                break;
                
            case 'obtenerDetalle':
                $idVenta = (int)($_POST['idVenta'] ?? 0);
                if (!$idVenta) sendJsonResponse(['success' => false, 'mensaje' => 'ID de venta requerido'], 400);
                
                sendJsonResponse(['detalle' => $historial->obtenerDetalleVenta($idVenta), 'success' => true]);
                break;
                
            case 'actualizarEstado':
                $idVenta = (int)($_POST['idVenta'] ?? 0);
                $nuevoEstado = $_POST['nuevoEstado'] ?? '';
                
                if (!$idVenta || !$nuevoEstado) {
                    sendJsonResponse(['success' => false, 'mensaje' => 'Datos incompletos'], 400);
                }
                
                if (!in_array($nuevoEstado, ['pendiente', 'completada', 'cancelada'])) {
                    sendJsonResponse(['success' => false, 'mensaje' => 'Estado no válido'], 400);
                }
                
                $resultado = $historial->actualizarEstadoVenta($idVenta, $nuevoEstado);
                sendJsonResponse([
                    'success' => $resultado,
                    'mensaje' => $resultado ? 'Estado actualizado correctamente' : 'Error al actualizar estado'
                ]);
                break;
                
            default:
                sendJsonResponse(['success' => false, 'mensaje' => 'Acción no válida'], 400);
        }
    } catch (Exception $e) {
        // Capturar cualquier salida no deseada
        $output = ob_get_clean();
        
        error_log("Error AJAX: " . $e->getMessage());
        error_log("Output capturado: " . $output);
        
        sendJsonResponse([
            'success' => false, 
            'mensaje' => 'Error interno: ' . $e->getMessage(),
            'debug' => $output // Solo para desarrollo
        ], 500);
    }
}

// Conexión solo para GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    require_once 'config/Conexion.php';
}

class HistorialVentas {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        if (!$this->conn) throw new Exception("Error de conexión a BD");
    }
    
    public function obtenerVentas($fechaDesde = null, $fechaHasta = null, $estado = null, $busqueda = null) {
        try {
            $sql = "SELECT v.id_venta, v.fecha_venta, m.numero_mesa, u.nombre as mesero, v.total, v.estado
                    FROM ventas v 
                    JOIN mesas m ON v.id_mesa = m.id_mesa 
                    JOIN usuarios u ON v.id_usuario = u.id_usuario 
                    WHERE 1=1";
            
            $params = [];
            $conditions = [
                'fechaDesde' => ['DATE(v.fecha_venta) >= :fechaDesde', $fechaDesde],
                'fechaHasta' => ['DATE(v.fecha_venta) <= :fechaHasta', $fechaHasta],
                'estado' => ['v.estado = :estado', $estado],
                'busqueda' => ['(m.numero_mesa LIKE :busqueda OR u.nombre LIKE :busqueda OR v.total LIKE :busqueda OR v.id_venta LIKE :busqueda)', "%$busqueda%"]
            ];
            
            foreach ($conditions as $key => $condition) {
                if (!empty($condition[1])) {
                    $sql .= " AND " . $condition[0];
                    $params[":$key"] = $condition[1];
                }
            }
            
            $sql .= " ORDER BY v.fecha_venta DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obtenerVentas: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDetalleVenta($idVenta) {
        try {
            $sql = "SELECT v.id_venta, v.fecha_venta, m.numero_mesa, u.nombre as mesero, v.total, v.estado,
                           dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre_producto
                    FROM ventas v 
                    JOIN mesas m ON v.id_mesa = m.id_mesa 
                    JOIN usuarios u ON v.id_usuario = u.id_usuario 
                    JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
                    JOIN productos p ON dv.id_producto = p.id_producto
                    WHERE v.id_venta = :idVenta";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obtenerDetalleVenta: " . $e->getMessage());
            return [];
        }
    }
    
    public function actualizarEstadoVenta($idVenta, $nuevoEstado) {
        try {
            $stmt = $this->conn->prepare("UPDATE ventas SET estado = :estado WHERE id_venta = :idVenta");
            $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
            $stmt->bindParam(':idVenta', $idVenta, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error actualizarEstadoVenta: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerEstadisticas($fechaDesde = null, $fechaHasta = null, $estado = null, $busqueda = null) {
        $ventas = $this->obtenerVentas($fechaDesde, $fechaHasta, $estado, $busqueda);
        $estadosCounts = array_count_values(array_column($ventas, 'estado'));
        
        return [
            'totalVentas' => array_sum(array_column($ventas, 'total')),
            'totalTransacciones' => count($ventas),
            'pendientes' => $estadosCounts['pendiente'] ?? 0,
            'completadas' => $estadosCounts['completada'] ?? 0
        ];
    }
}

// Datos iniciales
$ventasIniciales = [];
$estadisticasIniciales = ['totalVentas' => 0, 'totalTransacciones' => 0, 'pendientes' => 0, 'completadas' => 0];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    try {
        $historial = new HistorialVentas();
        $ventasIniciales = $historial->obtenerVentas();
        $estadisticasIniciales = $historial->obtenerEstadisticas();
    } catch (Exception $e) {
        error_log("Error datos iniciales: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - Container Bar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style/style8.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-shipping-fast"></i> Container Bar</h1>
            <p>Historial de Ventas - Sistema de Inventario Contable</p>
        </div>

        <div class="controls">
            <?php 
            $controls = [
                ['dateFrom', 'date', 'Fecha Desde:', '2024-05-01'],
                ['dateTo', 'date', 'Fecha Hasta:', '2024-12-31'],
                ['statusFilter', 'select', 'Estado:', '', [
                    '' => 'Todos los estados',
                    'pendiente' => 'Pendiente',
                    'completada' => 'Completada',
                    'cancelada' => 'Cancelada'
                ]],
                ['searchInput', 'text', 'Buscar:', '', [], 'Mesa, Usuario, Total...']
            ];
            
            foreach ($controls as $control) {
                echo '<div class="control-group">';
                echo "<label for='{$control[0]}'>{$control[2]}</label>";
                
                if ($control[1] === 'select') {
                    echo "<select id='{$control[0]}'>";
                    foreach ($control[4] as $value => $text) {
                        echo "<option value='$value'>$text</option>";
                    }
                    echo "</select>";
                } else {
                    $placeholder = isset($control[5]) ? "placeholder='{$control[5]}'" : '';
                    $value = isset($control[3]) ? "value='{$control[3]}'" : '';
                    echo "<input type='{$control[1]}' id='{$control[0]}' $value $placeholder>";
                }
                echo '</div>';
            }
            ?>
            <div class="control-group">
                <label>&nbsp;</label>
                <button class="btn btn-primary" onclick="filterSales()">
                    <i class="fas fa-search"></i> Filtrar
                </button>
            </div>
        </div>

        <div class="stats-grid">
            <?php 
            $stats = [
                ['totalSales', 'fas fa-dollar-sign', '$' . number_format($estadisticasIniciales['totalVentas'], 0, ',', '.'), 'Total Ventas'],
                ['totalTransactions', 'fas fa-chart-line', $estadisticasIniciales['totalTransacciones'], 'Transacciones'],
                ['pendingSales', 'fas fa-clock', $estadisticasIniciales['pendientes'], 'Pendientes'],
                ['completedSales', 'fas fa-check-circle', $estadisticasIniciales['completadas'], 'Completadas']
            ];
            
            foreach ($stats as $stat) {
                echo "<div class='stat-card'>
                        <div class='icon'><i class='{$stat[1]}'></i></div>
                        <div class='value' id='{$stat[0]}'>{$stat[2]}</div>
                        <div class='label'>{$stat[3]}</div>
                      </div>";
            }
            ?>
        </div>

        <div class="sales-table">
            <div class="table-header">
                <i class="fas fa-receipt"></i>
                <span>Historial de Ventas</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <?php 
                        $headers = ['ID Venta', 'Fecha', 'Mesa', 'Mesero', 'Total', 'Estado', 'Acciones'];
                        foreach ($headers as $header) {
                            echo "<th>$header</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody id="salesTableBody"></tbody>
            </table>
        </div>

        <div class="pagination" id="pagination"></div>
    </div>

    <!-- Modal -->
    <div id="saleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Detalles de Venta</h3>
                <button class="close-btn" onclick="closeSaleModal()">&times;</button>
            </div>
            <div id="saleDetails"></div>
        </div>
    </div>

    <div id="alertContainer"></div>

    <script>
        let salesData = <?php echo json_encode($ventasIniciales, JSON_UNESCAPED_UNICODE); ?>;
        let filteredSales = [...salesData];
        let currentPage = 1;
        const itemsPerPage = 10;

        const showAlert = (message, type = 'info') => {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert ${type}`;
            
            const icons = {success: 'fas fa-check-circle', error: 'fas fa-exclamation-triangle', info: 'fas fa-info-circle'};
            alert.innerHTML = `<i class="${icons[type]}"></i><span>${message}</span>`;
            
            alertContainer.appendChild(alert);
            setTimeout(() => alert.classList.add('show'), 100);
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alertContainer.contains(alert) && alertContainer.removeChild(alert), 400);
            }, 4000);
        };

        const formatCurrency = amount => new Intl.NumberFormat('es-CO', {
            style: 'currency', currency: 'COP', minimumFractionDigits: 0
        }).format(amount);

        const formatDate = dateString => {
            try {
                return new Date(dateString).toLocaleString('es-CO', {
                    year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
                });
            } catch (e) { return dateString; }
        };

        const updateStats = stats => {
            try {
                document.getElementById('totalSales').textContent = formatCurrency(stats.totalVentas || 0);
                document.getElementById('totalTransactions').textContent = stats.totalTransacciones || 0;
                document.getElementById('pendingSales').textContent = stats.pendientes || 0;
                document.getElementById('completedSales').textContent = stats.completadas || 0;
            } catch (e) { console.error('Error actualizando estadísticas:', e); }
        };

        const renderSalesTable = () => {
            const tbody = document.getElementById('salesTableBody');
            
            if (!filteredSales?.length) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No se encontraron ventas</td></tr>';
                return;
            }
            
            const startIndex = (currentPage - 1) * itemsPerPage;
            const paginatedSales = filteredSales.slice(startIndex, startIndex + itemsPerPage);

            tbody.innerHTML = paginatedSales.map(sale => `
                <tr>
                    <td>#${sale.id_venta || 'N/A'}</td>
                    <td>${formatDate(sale.fecha_venta || '')}</td>
                    <td>Mesa ${sale.numero_mesa || 'N/A'}</td>
                    <td>${sale.mesero || 'N/A'}</td>
                    <td>${formatCurrency(sale.total || 0)}</td>
                    <td><span class="status ${sale.estado || 'pendiente'}">${sale.estado || 'pendiente'}</span></td>
                    <td>
                        <button class="btn btn-primary action-btn" onclick="viewSaleDetails(${sale.id_venta})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        ${sale.estado === 'pendiente' ? `
                            <button class="btn btn-primary action-btn" onclick="updateSaleStatus(${sale.id_venta}, 'completada')">
                                <i class="fas fa-check"></i> Completar
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `).join('');
        };

        const renderPagination = () => {
            const totalPages = Math.ceil(filteredSales.length / itemsPerPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let html = '';
            if (currentPage > 1) {
                html += `<button class="page-btn" onclick="changePage(${currentPage - 1})"><i class="fas fa-chevron-left"></i></button>`;
            }
            
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage || i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += `<span class="page-btn">...</span>`;
                }
            }
            
            if (currentPage < totalPages) {
                html += `<button class="page-btn" onclick="changePage(${currentPage + 1})"><i class="fas fa-chevron-right"></i></button>`;
            }
            
            pagination.innerHTML = html;
        };

        const changePage = page => {
            currentPage = page;
            renderSalesTable();
            renderPagination();
        };

        const fetchData = (action, data, callback) => {
            const formData = new FormData();
            formData.append('accion', action);
            Object.entries(data || {}).forEach(([key, value]) => value && formData.append(key, value));

            fetch(window.location.href, { method: 'POST', body: formData })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers.get('content-type'));
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    // Obtener el texto de la respuesta primero
                    return response.text();
                })
                .then(text => {
                    console.log('Response text:', text.substring(0, 500)); // Log primeros 500 caracteres
                    
                    try {
                        const data = JSON.parse(text);
                        callback(data);
                    } catch (parseError) {
                        console.error('JSON Parse Error:', parseError);
                        console.error('Response text:', text);
                        throw new Error('La respuesta no es JSON válido. Respuesta: ' + text.substring(0, 200));
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    showAlert('Error de conexión: ' + error.message, 'error');
                });
        };

        const filterSales = () => {
            const data = {
                fechaDesde: document.getElementById('dateFrom').value,
                fechaHasta: document.getElementById('dateTo').value,
                estado: document.getElementById('statusFilter').value,
                busqueda: document.getElementById('searchInput').value
            };

            fetchData('obtenerVentas', data, response => {
                if (response.success) {
                    salesData = response.ventas || [];
                    filteredSales = [...salesData];
                    currentPage = 1;
                    updateStats(response.estadisticas || {});
                    renderSalesTable();
                    renderPagination();
                    showAlert(`Se encontraron ${filteredSales.length} ventas`, 'success');
                } else {
                    showAlert(response.mensaje || 'Error al filtrar ventas', 'error');
                }
            });
        };

        const viewSaleDetails = saleId => {
            if (!saleId) {
                showAlert('ID de venta no válido', 'error');
                return;
            }

            fetchData('obtenerDetalle', { idVenta: saleId }, response => {
                if (response.success && response.detalle?.length) {
                    const sale = response.detalle[0];
                    const modal = document.getElementById('saleModal');
                    const details = document.getElementById('saleDetails');
                    
                    const productsHTML = response.detalle.map(product => `
                        <div class="detail-row">
                            <span>${product.nombre_producto || 'Producto'} x${product.cantidad || 0}</span>
                            <span>${formatCurrency(product.subtotal || 0)}</span>
                        </div>
                    `).join('');

                    details.innerHTML = `
                        <div class="detail-row"><span><strong>ID Venta:</strong></span><span>#${sale.id_venta || 'N/A'}</span></div>
                        <div class="detail-row"><span><strong>Fecha:</strong></span><span>${formatDate(sale.fecha_venta || '')}</span></div>
                        <div class="detail-row"><span><strong>Mesa:</strong></span><span>${sale.numero_mesa || 'N/A'}</span></div>
                        <div class="detail-row"><span><strong>Mesero:</strong></span><span>${sale.mesero || 'N/A'}</span></div>
                        <div class="detail-row"><span><strong>Estado:</strong></span><span class="status ${sale.estado || 'pendiente'}">${sale.estado || 'pendiente'}</span></div>
                        <div style="margin: 20px 0; padding: 15px 0; border-top: 2px solid var(--gray); border-bottom: 2px solid var(--gray);">
                            <h4 style="color: var(--secondary); margin-bottom: 15px;">Productos:</h4>
                            ${productsHTML}
                        </div>
                        <div class="detail-row"><span><strong>TOTAL:</strong></span><span><strong>${formatCurrency(sale.total || 0)}</strong></span></div>
                    `;
                    
                    modal.style.display = 'block';
                } else {
                    showAlert(response.mensaje || 'Error al obtener detalles de la venta', 'error');
                }
            });
        };

        const closeSaleModal = () => document.getElementById('saleModal').style.display = 'none';

        const updateSaleStatus = (saleId, newStatus) => {
            if (!saleId || !newStatus) {
                showAlert('Datos incompletos', 'error');
                return;
            }

            fetchData('actualizarEstado', { idVenta: saleId, nuevoEstado: newStatus }, response => {
                if (response.success) {
                    showAlert(response.mensaje || 'Estado actualizado correctamente', 'success');
                    filterSales();
                } else {
                    showAlert(response.mensaje || 'Error al actualizar estado', 'error');
                }
            });
        };

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            renderSalesTable();
            renderPagination();
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeSaleModal();
        });

        // Cerrar modal al hacer clic fuera
        window.onclick = e => {
            if (e.target === document.getElementById('saleModal')) closeSaleModal();
        };
    </script>
</body>
</html>