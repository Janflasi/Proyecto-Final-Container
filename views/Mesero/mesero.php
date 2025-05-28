<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Container - Mesero</title>
    <style>
        :root {
            --primary-dark: #E55A2B;
            --secondary: #FFD23F;
            --accent: #4ECDC4;
            --dark: #1A1A1A;
            --dark-light: #2A2A2A;
            --gray: #666666;
            --gray-light: #CCCCCC;
            --white: #FFFFFF;
            --gradient-main: linear-gradient(135deg, #FF6B35 0%, #F7931E 50%, #FFD23F 100%);
            --gradient-dark: linear-gradient(135deg, #1A1A1A 0%, #2A2A2A 50%, #3A3A3A 100%);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-light: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: var(--gradient-dark);
            color: var(--white);
            min-height: 100vh;
        }

        .header {
            background: var(--gradient-main);
            padding: 1rem;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .mesero-info {
            font-size: 1rem;
            opacity: 0.9;
        }

        .container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .mesas-section {
            background: var(--dark-light);
            border-radius: 10px;
            padding: 1.5rem;
        }

        .mesas-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .control-input {
            background: var(--dark);
            color: var(--white);
            border: 1px solid var(--gray);
            padding: 0.5rem;
            border-radius: 5px;
            width: 60px;
            text-align: center;
        }

        .btn-control {
            background: var(--accent);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .btn-control:hover {
            background: #3DA89C;
        }

        .btn-danger {
            background: var(--primary-dark);
        }

        .btn-danger:hover {
            background: #C44A1F;
        }

        .section-title {
            color: var(--secondary);
            margin-bottom: 1rem;
            font-size: 1.2rem;
            text-align: center;
        }

        .mesas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .mesa {
            background: var(--dark);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .mesa:hover {
            transform: translateY(-3px);
        }

        .mesa.libre {
            border-color: var(--accent);
        }

        .mesa.ocupada {
            border-color: var(--primary-dark);
            background: rgba(229, 90, 43, 0.2);
        }

        .mesa.seleccionada {
            border-color: var(--secondary);
            background: rgba(255, 210, 63, 0.2);
        }

        .mesa-numero {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .mesa-estado {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .pedido-section {
            background: var(--dark-light);
            border-radius: 10px;
            padding: 1.5rem;
            position: sticky;
            top: 1rem;
            max-height: calc(100vh - 2rem);
            overflow-y: auto;
        }

        .mesa-actual {
            background: var(--gradient-main);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .productos-list {
            margin-bottom: 1rem;
        }

        .producto {
            background: var(--dark);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .producto-info {
            flex: 1;
        }

        .producto-nombre {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .producto-precio {
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .btn-agregar {
            background: var(--accent);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-agregar:hover {
            background: #3DA89C;
        }

        .pedido-actual {
            border-top: 2px solid var(--gray);
            padding-top: 1rem;
        }

        .pedido-item {
            background: var(--dark);
            padding: 0.8rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-info {
            flex: 1;
        }

        .item-nombre {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .item-precio {
            color: var(--secondary);
            font-size: 0.8rem;
        }

        .cantidad-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-cantidad {
            background: var(--primary-dark);
            color: var(--white);
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
        }

        .cantidad {
            min-width: 20px;
            text-align: center;
            font-weight: bold;
        }

        .total-pedido {
            background: var(--gradient-main);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            margin: 1rem 0;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .botones-accion {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-enviar {
            background: var(--accent);
            color: var(--white);
        }

        .btn-cuenta {
            background: var(--secondary);
            color: var(--dark);
        }

        .btn-limpiar {
            background: var(--primary-dark);
            color: var(--white);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-light);
        }

        .cuentas-activas {
            margin-top: 2rem;
            border-top: 2px solid var(--gray);
            padding-top: 1rem;
        }

        .cuenta-item {
            background: var(--dark);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            border-left: 4px solid var(--accent);
        }

        .cuenta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .cuenta-mesa {
            font-weight: bold;
            color: var(--secondary);
        }

        .cuenta-total {
            font-weight: bold;
            color: var(--accent);
        }

        .cuenta-botones {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .pedido-section {
                position: static;
                max-height: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🍺 BAR CONTAINER</h1>
        <div class="mesero-info">Mesero: Juan Pérez</div>
    </div>

    <div class="container">
        <div class="mesas-section">
            <h2 class="section-title">Gestión de Mesas</h2>
            
            <div class="mesas-controls">
                <div class="control-group">
                    <label>Total de mesas:</label>
                    <input type="number" id="totalMesas" class="control-input" min="1" max="50" value="15">
                    <button class="btn-control" onclick="actualizarMesas()">Actualizar</button>
                </div>
                <div class="control-group">
                    <label>Quitar mesa:</label>
                    <input type="number" id="mesaQuitar" class="control-input" min="1" placeholder="Nº">
                    <button class="btn-control btn-danger" onclick="quitarMesa()">Quitar</button>
                </div>
            </div>
            
            <div class="mesas-grid" id="mesasGrid"></div>
            
            <div class="productos-list">
                <h3 class="section-title">Productos Disponibles</h3>
                <div id="productosList"></div>
            </div>
        </div>

        <div class="pedido-section">
            <div class="mesa-actual" id="mesaActual">
                Selecciona una mesa
            </div>

            <div class="pedido-actual">
                <h3 style="color: var(--secondary); margin-bottom: 1rem;">Pedido Actual</h3>
                <div id="pedidoItems"></div>
                
                <div class="total-pedido">
                    Total: $<span id="totalPedido">0.00</span>
                </div>

                <div class="botones-accion">
                    <button class="btn btn-enviar" onclick="enviarPedido()">Enviar Pedido</button>
                    <button class="btn btn-cuenta" onclick="generarCuenta()">Generar Cuenta</button>
                    <button class="btn btn-limpiar" onclick="limpiarPedido()">Limpiar Todo</button>
                </div>
            </div>

            <div class="cuentas-activas">
                <h3 style="color: var(--secondary); margin-bottom: 1rem;">Cuentas Activas</h3>
                <div id="cuentasList"></div>
            </div>
        </div>
    </div>

    <script>
        // Datos del sistema
        let mesas = [];
        let productos = [];
        let cuentas = [];
        let pedidoActual = [];
        let mesaSeleccionada = null;
        let idEmpleado = 1; // ID del mesero logueado

        // Inicializar datos
        function inicializarDatos() {
            // Crear mesas iniciales (1-15)
            actualizarListaMesas(15);

            // Productos del bar
            productos = [
                {id_producto: 1, nombre_producto: 'Cerveza Corona', precio: 45.00, stock: 50, activo: true},
                {id_producto: 2, nombre_producto: 'Cerveza Modelo', precio: 40.00, stock: 45, activo: true},
                {id_producto: 3, nombre_producto: 'Tequila Shot', precio: 35.00, stock: 30, activo: true},
                {id_producto: 4, nombre_producto: 'Margarita', precio: 85.00, stock: 25, activo: true},
                {id_producto: 5, nombre_producto: 'Mojito', precio: 75.00, stock: 20, activo: true},
                {id_producto: 6, nombre_producto: 'Whiskey', precio: 120.00, stock: 15, activo: true},
                {id_producto: 7, nombre_producto: 'Nachos', precio: 65.00, stock: 40, activo: true},
                {id_producto: 8, nombre_producto: 'Alitas', precio: 95.00, stock: 30, activo: true},
                {id_producto: 9, nombre_producto: 'Papas', precio: 55.00, stock: 35, activo: true}
            ];

            // Cuentas ejemplo
            cuentas = [
                {
                    id_venta: 1,
                    id_mesa: 3,
                    total: 245.00,
                    estado: 'pendiente',
                    fecha_venta: new Date()
                },
                {
                    id_venta: 2,
                    id_mesa: 7,
                    total: 180.00,
                    estado: 'pendiente',
                    fecha_venta: new Date()
                }
            ];
        }

        // Actualizar lista de mesas
        function actualizarListaMesas(cantidad) {
            mesas = [];
            for (let i = 1; i <= cantidad; i++) {
                mesas.push({
                    id_mesa: i,
                    numero: i,
                    estado: 'libre'
                });
            }
        }

        // Actualizar cantidad de mesas
        function actualizarMesas() {
            const total = parseInt(document.getElementById('totalMesas').value);
            if (total < 1 || total > 50) {
                alert('El número de mesas debe estar entre 1 y 50');
                return;
            }

            // Si hay pedido activo, preguntar
            if (pedidoActual.length > 0) {
                if (!confirm('Hay un pedido activo. ¿Continuar? Se perderá el pedido actual.')) {
                    return;
                }
                limpiarPedido();
            }

            actualizarListaMesas(total);
            renderizarMesas();
            alert(`Mesas actualizadas a ${total} mesas`);
        }

        // Quitar mesa específica
        function quitarMesa() {
            const numeroMesa = parseInt(document.getElementById('mesaQuitar').value);
            if (!numeroMesa) {
                alert('Ingresa el número de mesa a quitar');
                return;
            }

            const mesaIndex = mesas.findIndex(m => m.numero === numeroMesa);
            if (mesaIndex === -1) {
                alert('Mesa no encontrada');
                return;
            }

            const mesa = mesas[mesaIndex];
            
            // Verificar si tiene cuenta activa
            const cuentaActiva = cuentas.find(c => c.id_mesa === mesa.id_mesa);
            if (cuentaActiva) {
                alert('No se puede quitar la mesa. Tiene una cuenta activa.');
                return;
            }

            // Verificar si es la mesa seleccionada
            if (mesaSeleccionada === mesa.id_mesa) {
                limpiarPedido();
            }

            // Quitar mesa
            mesas.splice(mesaIndex, 1);
            
            // Reordenar números de mesa
            mesas.forEach((mesa, index) => {
                mesa.numero = index + 1;
                mesa.id_mesa = index + 1;
            });

            // Actualizar input de total
            document.getElementById('totalMesas').value = mesas.length;
            document.getElementById('mesaQuitar').value = '';
            
            renderizarMesas();
            alert(`Mesa ${numeroMesa} eliminada`);
        }

        // Renderizar mesas
        function renderizarMesas() {
            const grid = document.getElementById('mesasGrid');
            grid.innerHTML = '';
            
            mesas.forEach(mesa => {
                const div = document.createElement('div');
                div.className = `mesa ${mesa.estado}`;
                if (mesaSeleccionada === mesa.id_mesa) {
                    div.classList.add('seleccionada');
                }
                
                div.innerHTML = `
                    <div class="mesa-numero">Mesa ${mesa.numero}</div>
                    <div class="mesa-estado">${mesa.estado}</div>
                `;
                
                div.onclick = () => seleccionarMesa(mesa.id_mesa);
                grid.appendChild(div);
            });
        }

        // Renderizar productos
        function renderizarProductos() {
            const lista = document.getElementById('productosList');
            lista.innerHTML = '';
            
            productos.filter(p => p.activo && p.stock > 0).forEach(producto => {
                const div = document.createElement('div');
                div.className = 'producto';
                div.innerHTML = `
                    <div class="producto-info">
                        <div class="producto-nombre">${producto.nombre_producto}</div>
                        <div class="producto-precio">$${producto.precio.toFixed(2)}</div>
                    </div>
                    <button class="btn-agregar" onclick="agregarProducto(${producto.id_producto})">
                        Agregar
                    </button>
                `;
                lista.appendChild(div);
            });
        }

        // Renderizar cuentas
        function renderizarCuentas() {
            const lista = document.getElementById('cuentasList');
            lista.innerHTML = '';
            
            cuentas.forEach(cuenta => {
                const div = document.createElement('div');
                div.className = 'cuenta-item';
                div.innerHTML = `
                    <div class="cuenta-header">
                        <div class="cuenta-mesa">Mesa ${cuenta.id_mesa}</div>
                        <div class="cuenta-total">$${cuenta.total.toFixed(2)}</div>
                    </div>
                    <div class="cuenta-botones">
                        <button class="btn btn-cuenta btn-small" onclick="cobrarCuenta(${cuenta.id_venta})">
                            Cobrar
                        </button>
                    </div>
                `;
                lista.appendChild(div);
            });
        }

        // Seleccionar mesa
        function seleccionarMesa(idMesa) {
            mesaSeleccionada = idMesa;
            document.getElementById('mesaActual').textContent = `Mesa ${idMesa}`;
            renderizarMesas();
        }

        // Agregar producto al pedido
        function agregarProducto(idProducto) {
            if (!mesaSeleccionada) {
                alert('Selecciona una mesa primero');
                return;
            }

            const producto = productos.find(p => p.id_producto === idProducto);
            const itemExistente = pedidoActual.find(item => item.id_producto === idProducto);

            if (itemExistente) {
                itemExistente.cantidad++;
                itemExistente.subtotal = itemExistente.cantidad * itemExistente.precio_unitario;
            } else {
                pedidoActual.push({
                    id_producto: idProducto,
                    nombre_producto: producto.nombre_producto,
                    cantidad: 1,
                    precio_unitario: producto.precio,
                    subtotal: producto.precio
                });
            }

            renderizarPedido();
        }

        // Renderizar pedido actual
        function renderizarPedido() {
            const lista = document.getElementById('pedidoItems');
            lista.innerHTML = '';
            
            let total = 0;
            pedidoActual.forEach(item => {
                total += item.subtotal;
                
                const div = document.createElement('div');
                div.className = 'pedido-item';
                div.innerHTML = `
                    <div class="item-info">
                        <div class="item-nombre">${item.nombre_producto}</div>
                        <div class="item-precio">$${item.precio_unitario.toFixed(2)}</div>
                    </div>
                    <div class="cantidad-controls">
                        <button class="btn-cantidad" onclick="cambiarCantidad(${item.id_producto}, -1)">-</button>
                        <span class="cantidad">${item.cantidad}</span>
                        <button class="btn-cantidad" onclick="cambiarCantidad(${item.id_producto}, 1)">+</button>
                    </div>
                `;
                lista.appendChild(div);
            });
            
            document.getElementById('totalPedido').textContent = total.toFixed(2);
        }

        // Cambiar cantidad
        function cambiarCantidad(idProducto, cambio) {
            const item = pedidoActual.find(i => i.id_producto === idProducto);
            if (item) {
                item.cantidad += cambio;
                if (item.cantidad <= 0) {
                    pedidoActual = pedidoActual.filter(i => i.id_producto !== idProducto);
                } else {
                    item.subtotal = item.cantidad * item.precio_unitario;
                }
                renderizarPedido();
            }
        }

        // Enviar pedido (solo actualiza estado)
        function enviarPedido() {
            if (!mesaSeleccionada || pedidoActual.length === 0) {
                alert('Selecciona una mesa y agrega productos');
                return;
            }

            // Marcar mesa como ocupada
            const mesa = mesas.find(m => m.id_mesa === mesaSeleccionada);
            if (mesa) mesa.estado = 'ocupada';

            alert(`Pedido enviado a Mesa ${mesaSeleccionada}`);
            renderizarMesas();
        }

        // Generar cuenta
        function generarCuenta() {
            if (!mesaSeleccionada || pedidoActual.length === 0) {
                alert('Selecciona una mesa y agrega productos');
                return;
            }

            const total = pedidoActual.reduce((sum, item) => sum + item.subtotal, 0);
            
            // Crear nueva venta
            const nuevaCuenta = {
                id_venta: cuentas.length + 1,
                id_mesa: mesaSeleccionada,
                id_empleado: idEmpleado,
                total: total,
                estado: 'pendiente',
                fecha_venta: new Date(),
                detalle: [...pedidoActual]
            };

            cuentas.push(nuevaCuenta);
            
            alert(`Cuenta generada para Mesa ${mesaSeleccionada}\nTotal: $${total.toFixed(2)}`);
            
            limpiarPedido();
            renderizarCuentas();
        }

        // Cobrar cuenta
        function cobrarCuenta(idVenta) {
            if (confirm('¿Confirmar pago de esta cuenta?')) {
                const cuenta = cuentas.find(c => c.id_venta === idVenta);
                if (cuenta) {
                    // Liberar mesa
                    const mesa = mesas.find(m => m.id_mesa === cuenta.id_mesa);
                    if (mesa) mesa.estado = 'libre';
                    
                    // Eliminar cuenta
                    cuentas = cuentas.filter(c => c.id_venta !== idVenta);
                    
                    alert(`Cuenta pagada - Mesa ${cuenta.id_mesa} liberada`);
                    renderizarCuentas();
                    renderizarMesas();
                }
            }
        }

        // Limpiar pedido
        function limpiarPedido() {
            pedidoActual = [];
            mesaSeleccionada = null;
            document.getElementById('mesaActual').textContent = 'Selecciona una mesa';
            renderizarPedido();
            renderizarMesas();
        }

        // Inicializar aplicación
        document.addEventListener('DOMContentLoaded', function() {
            inicializarDatos();
            renderizarMesas();
            renderizarProductos();
            renderizarCuentas();
        });
    </script>
</body>
</html>