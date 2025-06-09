<?php
// Incluir la conexión a la base de datos
require_once 'config/conexion.php';

// Crear instancia de la base de datos
$database = new Database();
$pdo = $database->getConnection();

// Función para obtener todas las mesas con sus estados actuales
function obtenerMesas($pdo) {
    $query = "SELECT m.*, v.id_venta, v.total, v.estado as estado_venta, v.fecha_venta,
                     COUNT(dv.id_detalle) as total_items,
                     GROUP_CONCAT(CONCAT(dv.cantidad, 'x ', p.nombre_producto) SEPARATOR ', ') as pedidos
              FROM mesas m
              LEFT JOIN ventas v ON m.id_mesa = v.id_mesa AND v.estado IN ('pendiente', 'en_proceso')
              LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
              LEFT JOIN productos p ON dv.id_producto = p.id_producto
              GROUP BY m.id_mesa
              ORDER BY m.numero_mesa";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $mesas = [];
    
    foreach ($results as $row) {
        $estado = 'libre';
        if ($row['id_venta']) {
            if ($row['estado_venta'] == 'pendiente') {
                $estado = 'ocupada';
            } elseif ($row['estado_venta'] == 'en_proceso') {
                $estado = 'cuenta';
            }
        }
        
        $mesas[] = [
            'id' => $row['numero_mesa'],
            'id_mesa' => $row['id_mesa'],
            'estado' => $estado,
            'clientes' => $row['capacidad'],
            'total' => $row['total'] ? floatval($row['total']) : 0,
            'pedidos' => $row['pedidos'] ? explode(', ', $row['pedidos']) : [],
            'tiempo' => $row['fecha_venta'] ? $row['fecha_venta'] : null,
            'total_items' => intval($row['total_items'])
        ];
    }
    
    return $mesas;
}

// Función para obtener estadísticas
function obtenerEstadisticas($pdo) {
    // Total de mesas
    $stmt_total = $pdo->prepare("SELECT COUNT(*) as total FROM mesas");
    $stmt_total->execute();
    $total_mesas = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Mesas ocupadas
    $stmt_ocupadas = $pdo->prepare("SELECT COUNT(*) as ocupadas FROM mesas m 
                                   INNER JOIN ventas v ON m.id_mesa = v.id_mesa 
                                   WHERE v.estado IN ('pendiente', 'en_proceso')");
    $stmt_ocupadas->execute();
    $mesas_ocupadas = $stmt_ocupadas->fetch(PDO::FETCH_ASSOC)['ocupadas'];
    
    // Ventas del día
    $stmt_ventas = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as ventas_hoy FROM ventas 
                                 WHERE DATE(fecha_venta) = CURDATE()");
    $stmt_ventas->execute();
    $ventas_hoy = $stmt_ventas->fetch(PDO::FETCH_ASSOC)['ventas_hoy'];
    
    return [
        'total_mesas' => $total_mesas,
        'mesas_ocupadas' => $mesas_ocupadas,
        'mesas_libres' => $total_mesas - $mesas_ocupadas,
        'ventas_hoy' => floatval($ventas_hoy)
    ];
}

// Función para obtener pedidos activos
function obtenerPedidosActivos($pdo) {
    $query = "SELECT v.id_venta, m.numero_mesa, v.fecha_venta, v.total,
                     GROUP_CONCAT(CONCAT(dv.cantidad, 'x ', p.nombre_producto) SEPARATOR ', ') as items
              FROM ventas v
              INNER JOIN mesas m ON v.id_mesa = m.id_mesa
              INNER JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
              INNER JOIN productos p ON dv.id_producto = p.id_producto
              WHERE v.estado = 'pendiente'
              GROUP BY v.id_venta
              ORDER BY v.fecha_venta DESC
              LIMIT 10";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $pedidos = [];
    
    foreach ($results as $row) {
        $tiempo_transcurrido = time() - strtotime($row['fecha_venta']);
        $minutos = floor($tiempo_transcurrido / 60);
        
        $pedidos[] = [
            'mesa' => $row['numero_mesa'],
            'items' => $row['items'],
            'tiempo' => "Hace {$minutos} min",
            'total' => floatval($row['total'])
        ];
    }
    
    return $pedidos;
}

// Procesar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'obtener_datos':
            $mesas = obtenerMesas($pdo);
            $estadisticas = obtenerEstadisticas($pdo);
            $pedidos = obtenerPedidosActivos($pdo);
            
            echo json_encode([
                'success' => true,
                'mesas' => $mesas,
                'estadisticas' => $estadisticas,
                'pedidos' => $pedidos
            ]);
            exit;
            
        case 'cambiar_estado_mesa':
            $numero_mesa = intval($_POST['numero_mesa']);
            $nuevo_estado = $_POST['nuevo_estado'];
            
            // Buscar la mesa
            $stmt_mesa = $pdo->prepare("SELECT id_mesa FROM mesas WHERE numero_mesa = ?");
            $stmt_mesa->execute([$numero_mesa]);
            $mesa = $stmt_mesa->fetch(PDO::FETCH_ASSOC);
            
            if ($mesa) {
                $id_mesa = $mesa['id_mesa'];
                
                if ($nuevo_estado == 'libre') {
                    // Cerrar ventas pendientes
                    $stmt_cerrar = $pdo->prepare("UPDATE ventas SET estado = 'completada' 
                                                 WHERE id_mesa = ? AND estado IN ('pendiente', 'en_proceso')");
                    $stmt_cerrar->execute([$id_mesa]);
                } elseif ($nuevo_estado == 'ocupada') {
                    // Crear nueva venta si no existe
                    $stmt_existente = $pdo->prepare("SELECT id_venta FROM ventas 
                                                   WHERE id_mesa = ? AND estado = 'pendiente'");
                    $stmt_existente->execute([$id_mesa]);
                    $venta_existente = $stmt_existente->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$venta_existente) {
                        $stmt_nueva = $pdo->prepare("INSERT INTO ventas (id_mesa, id_usuario, total, estado) 
                                                   VALUES (?, 1, 0, 'pendiente')");
                        $stmt_nueva->execute([$id_mesa]);
                    }
                } elseif ($nuevo_estado == 'cuenta') {
                    // Cambiar estado a en_proceso
                    $stmt_cuenta = $pdo->prepare("UPDATE ventas SET estado = 'en_proceso' 
                                                WHERE id_mesa = ? AND estado = 'pendiente'");
                    $stmt_cuenta->execute([$id_mesa]);
                }
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Mesa no encontrada']);
            }
            exit;
            
        case 'obtener_reporte':
            $estadisticas = obtenerEstadisticas($pdo);
            
            // Calcular promedio por mesa
            $stmt_promedio = $pdo->prepare("SELECT AVG(total) as promedio FROM ventas 
                                          WHERE DATE(fecha_venta) = CURDATE() AND estado = 'completada'");
            $stmt_promedio->execute();
            $promedio_result = $stmt_promedio->fetch(PDO::FETCH_ASSOC);
            $promedio = $promedio_result['promedio'];
            
            echo json_encode([
                'success' => true,
                'reporte' => [
                    'ventas_totales' => $estadisticas['ventas_hoy'],
                    'mesas_activas' => $estadisticas['mesas_ocupadas'],
                    'promedio_mesa' => $promedio ? floatval($promedio) : 0,
                    'tiempo_promedio' => '35 min' // Esto podría calcularse también
                ]
            ]);
            exit;
    }
}

// Obtener datos iniciales
$mesas_iniciales = obtenerMesas($pdo);
$estadisticas_iniciales = obtenerEstadisticas($pdo);
$pedidos_iniciales = obtenerPedidosActivos($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Mesas - Bar</title>
    <link rel="stylesheet" href="/style/style11.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍺 Panel de Administración - Bar</h1>
            <p>Control en tiempo real de mesas y pedidos</p>
        </div>

        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number" id="totalMesas"><?php echo $estadisticas_iniciales['total_mesas']; ?></div>
                <div class="stat-label">Total Mesas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="mesasOcupadas"><?php echo $estadisticas_iniciales['mesas_ocupadas']; ?></div>
                <div class="stat-label">Ocupadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="mesasLibres"><?php echo $estadisticas_iniciales['mesas_libres']; ?></div>
                <div class="stat-label">Libres</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="ventasHoy">$<?php echo number_format($estadisticas_iniciales['ventas_hoy'], 2); ?></div>
                <div class="stat-label">Ventas Hoy</div>
            </div>
        </div>

        <div class="controls">
            <button class="btn btn-primary" onclick="actualizarEstados()">🔄 Actualizar</button>
            <button class="btn btn-secondary" onclick="mostrarReporte()">📊 Reporte</button>
            <button class="btn btn-primary" onclick="limpiarMesas()">🧹 Limpiar Mesas</button>
        </div>

        <div class="main-content">
            <div class="tables-section">
                <div class="tables-grid" id="tablesGrid">
                    <!-- Las mesas se generan dinámicamente -->
                </div>
            </div>

            <div class="sidebar">
                <h3>📋 Pedidos Activos</h3>
                <div id="activeOrders">
                    <?php foreach ($pedidos_iniciales as $pedido): ?>
                    <div class="order-item">
                        <div class="order-table">Mesa <?php echo $pedido['mesa']; ?></div>
                        <div class="order-items"><?php echo $pedido['items']; ?></div>
                        <div class="order-time"><?php echo $pedido['tiempo']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles de mesa -->
    <div id="tableModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h3 id="modalTitle">Detalles de Mesa</h3>
            <div id="modalContent">
                <!-- Contenido dinámico -->
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn btn-primary" onclick="cambiarEstadoMesa()">Cambiar Estado</button>
                <button class="btn btn-secondary" onclick="cerrarModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        // Datos iniciales desde PHP
        let mesas = <?php echo json_encode($mesas_iniciales); ?>;
        let mesaSeleccionada = null;

        function renderizarMesas() {
            const grid = document.getElementById('tablesGrid');
            grid.innerHTML = '';

            mesas.forEach(mesa => {
                const card = document.createElement('div');
                card.className = `table-card ${mesa.estado}`;
                card.onclick = () => abrirModal(mesa.id);

                const tiempoTexto = mesa.tiempo ? calcularTiempo(mesa.tiempo) : '';
                
                card.innerHTML = `
                    <div class="table-number">Mesa ${mesa.id}</div>
                    <div class="table-status status-${mesa.estado}">
                        ${mesa.estado === 'libre' ? '🟢 Libre' : 
                          mesa.estado === 'ocupada' ? '🔴 Ocupada' :
                          mesa.estado === 'cuenta' ? '💰 Cuenta' : '🟡 Reservada'}
                    </div>
                    <div class="table-info">
                        ${mesa.clientes > 0 ? `👥 ${mesa.clientes} personas` : ''}
                        ${tiempoTexto ? `<br>⏰ ${tiempoTexto}` : ''}
                        ${mesa.pedidos.length > 0 ? `<br>📝 ${mesa.pedidos.length} items` : ''}
                    </div>
                    ${mesa.total > 0 ? `<div class="table-total">$${mesa.total.toFixed(2)}</div>` : ''}
                `;

                grid.appendChild(card);
            });
        }

        function calcularTiempo(tiempo) {
            const ahora = new Date();
            const fechaTiempo = new Date(tiempo);
            const diferencia = Math.floor((ahora - fechaTiempo) / 60000); // minutos
            
            if (diferencia < 60) {
                return `${diferencia} min`;
            } else {
                const horas = Math.floor(diferencia / 60);
                const minutos = diferencia % 60;
                return `${horas}h ${minutos}m`;
            }
        }

        function actualizarEstadisticas(estadisticas) {
            document.getElementById('totalMesas').textContent = estadisticas.total_mesas;
            document.getElementById('mesasOcupadas').textContent = estadisticas.mesas_ocupadas;
            document.getElementById('mesasLibres').textContent = estadisticas.mesas_libres;
            document.getElementById('ventasHoy').textContent = `$${estadisticas.ventas_hoy.toFixed(2)}`;
        }

        function actualizarPedidosActivos(pedidos) {
            const container = document.getElementById('activeOrders');
            container.innerHTML = '';
            
            pedidos.forEach(pedido => {
                const orderDiv = document.createElement('div');
                orderDiv.className = 'order-item';
                orderDiv.innerHTML = `
                    <div class="order-table">Mesa ${pedido.mesa}</div>
                    <div class="order-items">${pedido.items}</div>
                    <div class="order-time">${pedido.tiempo}</div>
                `;
                container.appendChild(orderDiv);
            });
        }

        function abrirModal(numeroMesa) {
            const mesa = mesas.find(m => m.id === numeroMesa);
            mesaSeleccionada = mesa;
            
            document.getElementById('modalTitle').textContent = `Mesa ${mesa.id}`;
            
            const tiempoTexto = mesa.tiempo ? calcularTiempo(mesa.tiempo) : 'Sin tiempo';
            
            document.getElementById('modalContent').innerHTML = `
                <div class="order-detail">
                    <strong>Estado:</strong> ${mesa.estado.toUpperCase()}
                    <br><strong>Capacidad:</strong> ${mesa.clientes}
                    <br><strong>Tiempo:</strong> ${tiempoTexto}
                    <br><strong>Total:</strong> $${mesa.total.toFixed(2)}
                </div>
                <div class="order-detail">
                    <strong>Pedidos:</strong><br>
                    ${mesa.pedidos.length > 0 ? mesa.pedidos.join('<br>') : 'Sin pedidos'}
                </div>
            `;
            
            document.getElementById('tableModal').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('tableModal').style.display = 'none';
            mesaSeleccionada = null;
        }

        function cambiarEstadoMesa() {
            if (!mesaSeleccionada) return;
            
            const estados = ['libre', 'ocupada', 'cuenta'];
            const estadoActual = mesaSeleccionada.estado;
            const siguienteIndice = (estados.indexOf(estadoActual) + 1) % estados.length;
            const nuevoEstado = estados[siguienteIndice];
            
            // Enviar petición al servidor
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=cambiar_estado_mesa&numero_mesa=${mesaSeleccionada.id}&nuevo_estado=${nuevoEstado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    actualizarEstados();
                    cerrarModal();
                } else {
                    alert('Error al cambiar estado: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            });
        }

        function actualizarEstados() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=obtener_datos'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mesas = data.mesas;
                    actualizarEstadisticas(data.estadisticas);
                    actualizarPedidosActivos(data.pedidos);
                    renderizarMesas();
                    
                    // Mostrar confirmación
                    const btn = event.target;
                    const textoOriginal = btn.textContent;
                    btn.textContent = '✅ Actualizada';
                    btn.style.background = 'var(--accent)';
                    
                    setTimeout(() => {
                        btn.textContent = textoOriginal;
                        btn.style.background = '';
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar datos');
            });
        }

        function mostrarReporte() {
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=obtener_reporte'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const reporte = data.reporte;
                    alert(`📊 REPORTE DEL DÍA\n\n💰 Ventas totales: $${reporte.ventas_totales.toFixed(2)}\n🏪 Mesas activas: ${reporte.mesas_activas}\n📈 Promedio por mesa: $${reporte.promedio_mesa.toFixed(2)}\n⏰ Tiempo promedio: ${reporte.tiempo_promedio}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al obtener reporte');
            });
        }

        function limpiarMesas() {
            if (confirm('¿Limpiar todas las mesas libres?')) {
                // Esta función podría implementarse en el servidor si es necesario
                actualizarEstados();
            }
        }

        // Actualización automática cada 30 segundos
        setInterval(() => {
            actualizarEstados();
        }, 30000);

        // Inicializar
        renderizarMesas();

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('tableModal');
            if (event.target === modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>