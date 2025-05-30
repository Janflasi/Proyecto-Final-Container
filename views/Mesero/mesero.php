<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bar Container - Mesero</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style/style9.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
       <div class="logo-section">
    <div class="logo-icon">
        <img src="/assets/unnamed.png" alt="Logo del bar" style="width: 50px; height: 50px;">
    </div>
    <div class="logo-text">
        <h1>BAR CONTAINER</h1>
        <p>Sistema de Gestión</p>
    </div>
</div>


        <div class="profile-dropdown">
            <div class="profile-trigger" onclick="toggleProfileMenu()">
                <div class="profile-avatar">JP</div>
                <div class="profile-info">
                    <h3>Juan Pérez</h3>
                    <p>Mesero</p>
                </div>
                <i class="fas fa-chevron-down" style="color: var(--text-muted); transition: transform 0.3s ease;" id="profileChevron"></i>
            </div>
            
            <div class="profile-menu" id="profileMenu">
                
                <div class="profile-menu-item" onclick="configuraciones()">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </div>
                
                <div class="profile-menu-divider"></div>
                <div class="profile-menu-item" onclick="cerrarSesion()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Container principal -->
    <div class="container">
        <!-- Sección de mesas -->
        <div class="mesas-section">
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-table"></i>
                    Gestión de Mesas
                </h2>
                
                <div class="mesas-controls">
                    <div class="control-group">
                        <label>Total de mesas:</label>
                        <input type="number" id="totalMesas" class="control-input" min="1" max="50" value="15">
                        <button class="btn-control" onclick="actualizarMesas()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                    <div class="control-group">
                        <label>Quitar mesa:</label>
                        <input type="number" id="mesaQuitar" class="control-input" min="1" placeholder="Nº mesa">
                        <button class="btn-control btn-danger" onclick="quitarMesa()">
                            <i class="fas fa-trash"></i> Quitar
                        </button>
                    </div>
                </div>
                
                <div class="mesas-grid" id="mesasGrid"></div>
            </div>
            
            <div class="productos-list">
                <div class="section">
                    <h3 class="section-title">
                        <i class="fas fa-glass-cheers"></i>
                        Productos Disponibles
                    </h3>
                    <div id="productosList"></div>
                </div>
            </div>
        </div>

        <!-- Sección de pedidos -->
        <div class="pedido-section">
            <div class="mesa-actual" id="mesaActual">
                <i class="fas fa-mouse-pointer"></i> Selecciona una mesa
            </div>

            <div class="pedido-actual">
                <h3 style="color: var(--secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-shopping-cart"></i>
                    Pedido Actual
                </h3>
                <div id="pedidoItems"></div>
                
                <div class="total-pedido">
                    <i class="fas fa-calculator"></i>
                    Total: $<span id="totalPedido">0.00</span>
                </div>

                <div class="botones-accion">
                    <button class="btn btn-enviar" onclick="enviarPedido()">
                        <i class="fas fa-paper-plane"></i> Enviar
                    </button>
                    <button class="btn btn-cuenta" onclick="generarCuenta()">
                        <i class="fas fa-receipt"></i> Cuenta
                    </button>
                    <button class="btn btn-limpiar" onclick="limpiarPedido()">
                        <i class="fas fa-broom"></i> Limpiar
                    </button>
                </div>
            </div>

            <div class="cuentas-activas">
                <h3 style="color: var(--secondary); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Cuentas Activas
                </h3>
                <div id="cuentasList"></div>
            </div>
        </div>
    </div>

    <!-- Modal de perfil -->
    <div class="modal" id="profileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-user-edit"></i>
                    Editar Perfil
                </h3>
                <button class="btn-close" onclick="cerrarModal()">&times;</button>
            </div>
            
            <form id="profileForm">
                <div class="form-group">
                    <label>Nombre completo:</label>
                    <input type="text" id="nombreCompleto" value="Juan Pérez">
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="email" value="juan.perez@barcontainer.com">
                </div>
                
                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="tel" id="telefono" value="+52 555 123 4567">
                </div>
                
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" id="password" placeholder="Dejar en blanco para no cambiar">
                </div>
                
                <div class="botones-accion">
                    <button type="button" class="btn btn-cuenta" onclick="guardarPerfil()">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-limpiar" onclick="cerrarModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Datos del sistema (mantiene la funcionalidad original)
        let mesas = [];
        let productos = [];
        let cuentas = [];
        let pedidoActual = [];
        let mesaSeleccionada = null;
        let idEmpleado = 1;

        // Funciones del menú de perfil
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            const chevron = document.getElementById('profileChevron');
            
            menu.classList.toggle('active');
            chevron.style.transform = menu.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
        }

        function editarPerfil() {
            document.getElementById('profileModal').classList.add('active');
            toggleProfileMenu();
        }

        function configuraciones() {
            alert('Función de configuraciones - Próximamente');
            toggleProfileMenu();
        }
function estadisticas() {
            alert('Función de estadísticas - Próximamente');
            toggleProfileMenu();
        }

        function cerrarSesion() {
            if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
                alert('Cerrando sesión...');
                // Aquí iría la lógica para cerrar sesión
            }
            toggleProfileMenu();
        }

        function cerrarModal() {
            document.getElementById('profileModal').classList.remove('active');
        }

        function guardarPerfil() {
            const nombre = document.getElementById('nombreCompleto').value;
            const email = document.getElementById('email').value;
            const telefono = document.getElementById('telefono').value;
            
            // Actualizar la información en el header
            document.querySelector('.profile-info h3').textContent = nombre;
            
            alert('Perfil actualizado correctamente');
            cerrarModal();
        }

        // Cerrar menú de perfil al hacer clic fuera
        document.addEventListener('click', function(event) {
            const profileDropdown = document.querySelector('.profile-dropdown');
            const profileMenu = document.getElementById('profileMenu');
            
            if (!profileDropdown.contains(event.target) && profileMenu.classList.contains('active')) {
                toggleProfileMenu();
            }
        });

        // Cerrar modal al hacer clic fuera
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('profileModal');
            const modalContent = document.querySelector('.modal-content');
            
            if (event.target === modal) {
                cerrarModal();
            }
        });

        // Inicialización del sistema
        function inicializarSistema() {
            // Productos del bar
            productos = [
                { id: 1, nombre: 'Cerveza Corona', precio: 45.00, categoria: 'Cerveza' },
                { id: 2, nombre: 'Cerveza Modelo', precio: 42.00, categoria: 'Cerveza' },
                { id: 3, nombre: 'Tequila Blanco', precio: 65.00, categoria: 'Destilados' },
                { id: 4, nombre: 'Whisky JW Red', precio: 85.00, categoria: 'Destilados' },
                { id: 5, nombre: 'Vodka Absolut', precio: 75.00, categoria: 'Destilados' },
                { id: 6, nombre: 'Mojito', precio: 120.00, categoria: 'Cocteles' },
                { id: 7, nombre: 'Margarita', precio: 110.00, categoria: 'Cocteles' },
                { id: 8, nombre: 'Piña Colada', precio: 130.00, categoria: 'Cocteles' },
                { id: 9, nombre: 'Nachos', precio: 95.00, categoria: 'Botanas' },
                { id: 10, nombre: 'Alitas Buffalo', precio: 125.00, categoria: 'Botanas' },
                { id: 11, nombre: 'Quesadillas', precio: 85.00, categoria: 'Botanas' },
                { id: 12, nombre: 'Agua Mineral', precio: 25.00, categoria: 'Sin Alcohol' },
                { id: 13, nombre: 'Refresco', precio: 30.00, categoria: 'Sin Alcohol' },
                { id: 14, nombre: 'Jugo Natural', precio: 35.00, categoria: 'Sin Alcohol' }
            ];

            // Inicializar mesas
            const totalMesas = parseInt(document.getElementById('totalMesas').value) || 15;
            generarMesas(totalMesas);
            
            // Mostrar productos
            mostrarProductos();
            
            // Actualizar interfaz
            actualizarPedido();
            actualizarCuentas();
        }

        function generarMesas(cantidad) {
            mesas = [];
            for (let i = 1; i <= cantidad; i++) {
                mesas.push({
                    numero: i,
                    estado: 'libre', // libre, ocupada
                    pedido: [],
                    cuenta: null
                });
            }
            mostrarMesas();
        }

        function mostrarMesas() {
            const container = document.getElementById('mesasGrid');
            container.innerHTML = '';
            
            mesas.forEach(mesa => {
                const mesaElement = document.createElement('div');
                mesaElement.className = `mesa ${mesa.estado}`;
                if (mesaSeleccionada === mesa.numero) {
                    mesaElement.classList.add('seleccionada');
                }
                
                mesaElement.innerHTML = `
                    <div class="mesa-numero">${mesa.numero}</div>
                    <div class="mesa-estado">${mesa.estado}</div>
                `;
                
                mesaElement.onclick = () => seleccionarMesa(mesa.numero);
                container.appendChild(mesaElement);
            });
        }

        function mostrarProductos() {
            const container = document.getElementById('productosList');
            container.innerHTML = '';
            
            // Agrupar productos por categoría
            const categorias = {};
            productos.forEach(producto => {
                if (!categorias[producto.categoria]) {
                    categorias[producto.categoria] = [];
                }
                categorias[producto.categoria].push(producto);
            });
            
            // Mostrar productos por categoría
            Object.keys(categorias).forEach(categoria => {
                const categoriaDiv = document.createElement('div');
                categoriaDiv.innerHTML = `<h4 style="color: var(--accent); margin: 1rem 0 0.5rem 0; font-weight: 600;">${categoria}</h4>`;
                container.appendChild(categoriaDiv);
                
                categorias[categoria].forEach(producto => {
                    const productoElement = document.createElement('div');
                    productoElement.className = 'producto';
                    productoElement.innerHTML = `
                        <div>
                            <div class="producto-nombre">${producto.nombre}</div>
                            <div class="producto-precio">$${producto.precio.toFixed(2)}</div>
                        </div>
                        <button class="btn-agregar" onclick="agregarProducto(${producto.id})">
                            <i class="fas fa-plus"></i>
                        </button>
                    `;
                    container.appendChild(productoElement);
                });
            });
        }

        function seleccionarMesa(numero) {
            mesaSeleccionada = numero;
            const mesa = mesas.find(m => m.numero === numero);
            
            // Actualizar interfaz
            document.getElementById('mesaActual').innerHTML = `
                <i class="fas fa-table"></i> Mesa ${numero} - ${mesa.estado.toUpperCase()}
            `;
            
            // Cargar pedido existente si la mesa está ocupada
            if (mesa.estado === 'ocupada' && mesa.pedido) {
                pedidoActual = [...mesa.pedido];
            } else {
                pedidoActual = [];
            }
            
            mostrarMesas();
            actualizarPedido();
        }

        function agregarProducto(productoId) {
            if (!mesaSeleccionada) {
                alert('Por favor selecciona una mesa primero');
                return;
            }
            
            const producto = productos.find(p => p.id === productoId);
            const itemExistente = pedidoActual.find(item => item.id === productoId);
            
            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                pedidoActual.push({
                    id: producto.id,
                    nombre: producto.nombre,
                    precio: producto.precio,
                    cantidad: 1
                });
            }
            
            actualizarPedido();
        }

        function actualizarPedido() {
            const container = document.getElementById('pedidoItems');
            const totalElement = document.getElementById('totalPedido');
            
            container.innerHTML = '';
            let total = 0;
            
            pedidoActual.forEach(item => {
                const itemElement = document.createElement('div');
                itemElement.className = 'pedido-item';
                itemElement.innerHTML = `
                    <div>
                        <div class="item-nombre">${item.nombre}</div>
                        <div class="item-precio">$${item.precio.toFixed(2)} c/u</div>
                    </div>
                    <div class="cantidad-controls">
                        <button class="btn-cantidad" onclick="modificarCantidad(${item.id}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="cantidad">${item.cantidad}</span>
                        <button class="btn-cantidad" onclick="modificarCantidad(${item.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                `;
                container.appendChild(itemElement);
                total += item.precio * item.cantidad;
            });
            
            totalElement.textContent = total.toFixed(2);
        }

        function modificarCantidad(productoId, cambio) {
            const item = pedidoActual.find(item => item.id === productoId);
            if (!item) return;
            
            item.cantidad += cambio;
            
            if (item.cantidad <= 0) {
                const index = pedidoActual.indexOf(item);
                pedidoActual.splice(index, 1);
            }
            
            actualizarPedido();
        }

        function enviarPedido() {
            if (!mesaSeleccionada || pedidoActual.length === 0) {
                alert('Selecciona una mesa y agrega productos al pedido');
                return;
            }
            
            const mesa = mesas.find(m => m.numero === mesaSeleccionada);
            mesa.estado = 'ocupada';
            mesa.pedido = [...pedidoActual];
            
            alert(`Pedido enviado a la cocina para la Mesa ${mesaSeleccionada}`);
            
            // Limpiar pedido actual pero mantener mesa seleccionada
            pedidoActual = [];
            mostrarMesas();
            actualizarPedido();
        }

        function generarCuenta() {
            if (!mesaSeleccionada) {
                alert('Selecciona una mesa primero');
                return;
            }
            
            const mesa = mesas.find(m => m.numero === mesaSeleccionada);
            
            if (mesa.estado !== 'ocupada' || !mesa.pedido || mesa.pedido.length === 0) {
                alert('Esta mesa no tiene pedidos para generar cuenta');
                return;
            }
            
            let total = 0;
            mesa.pedido.forEach(item => {
                total += item.precio * item.cantidad;
            });
            
            const cuenta = {
                id: Date.now(),
                mesa: mesaSeleccionada,
                items: [...mesa.pedido],
                total: total,
                fecha: new Date(),
                empleado: idEmpleado,
                estado: 'pendiente'
            };
            
            cuentas.push(cuenta);
            mesa.cuenta = cuenta.id;
            
            alert(`Cuenta generada para Mesa ${mesaSeleccionada}\nTotal: $${total.toFixed(2)}`);
            
            actualizarCuentas();
        }

        function actualizarCuentas() {
            const container = document.getElementById('cuentasList');
            container.innerHTML = '';
            
            const cuentasPendientes = cuentas.filter(c => c.estado === 'pendiente');
            
            if (cuentasPendientes.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 1rem;">No hay cuentas pendientes</p>';
                return;
            }
            
            cuentasPendientes.forEach(cuenta => {
                const cuentaElement = document.createElement('div');
                cuentaElement.className = 'cuenta-item';
                cuentaElement.innerHTML = `
                    <div class="cuenta-header">
                        <span class="cuenta-mesa">Mesa ${cuenta.mesa}</span>
                        <span class="cuenta-total">$${cuenta.total.toFixed(2)}</span>
                    </div>
                    <div class="botones-accion">
                        <button class="btn btn-cuenta btn-small" onclick="pagarCuenta(${cuenta.id})">
                            <i class="fas fa-credit-card"></i> Pagar
                        </button>
                        <button class="btn btn-enviar btn-small" onclick="verCuenta(${cuenta.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                    </div>
                `;
                container.appendChild(cuentaElement);
            });
        }

        function pagarCuenta(cuentaId) {
            const cuenta = cuentas.find(c => c.id === cuentaId);
            if (!cuenta) return;
            
            const mesa = mesas.find(m => m.numero === cuenta.mesa);
            
            if (confirm(`¿Confirmar pago de $${cuenta.total.toFixed(2)} para Mesa ${cuenta.mesa}?`)) {
                cuenta.estado = 'pagada';
                mesa.estado = 'libre';
                mesa.pedido = [];
                mesa.cuenta = null;
                
                alert('Pago procesado correctamente');
                
                // Si era la mesa seleccionada, limpiar pedido
                if (mesaSeleccionada === cuenta.mesa) {
                    pedidoActual = [];
                    actualizarPedido();
                }
                
                mostrarMesas();
                actualizarCuentas();
            }
        }

        function verCuenta(cuentaId) {
            const cuenta = cuentas.find(c => c.id === cuentaId);
            if (!cuenta) return;
            
            let detalle = `CUENTA - MESA ${cuenta.mesa}\n`;
            detalle += `Fecha: ${cuenta.fecha.toLocaleString()}\n`;
            detalle += `----------------------------------------\n`;
            
            cuenta.items.forEach(item => {
                detalle += `${item.nombre} x${item.cantidad} - $${(item.precio * item.cantidad).toFixed(2)}\n`;
            });
            
            detalle += `----------------------------------------\n`;
            detalle += `TOTAL: $${cuenta.total.toFixed(2)}`;
            
            alert(detalle);
        }

        function limpiarPedido() {
            if (pedidoActual.length === 0) return;
            
            if (confirm('¿Estás seguro de que quieres limpiar el pedido actual?')) {
                pedidoActual = [];
                actualizarPedido();
            }
        }

        function actualizarMesas() {
            const totalMesas = parseInt(document.getElementById('totalMesas').value);
            if (totalMesas < 1 || totalMesas > 50) {
                alert('El número de mesas debe estar entre 1 y 50');
                return;
            }
            
            generarMesas(totalMesas);
            mesaSeleccionada = null;
            pedidoActual = [];
            document.getElementById('mesaActual').innerHTML = '<i class="fas fa-mouse-pointer"></i> Selecciona una mesa';
            actualizarPedido();
        }

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
            if (mesa.estado === 'ocupada') {
                alert('No se puede quitar una mesa ocupada');
                return;
            }
            
            if (confirm(`¿Estás seguro de que quieres quitar la Mesa ${numeroMesa}?`)) {
                mesas.splice(mesaIndex, 1);
                
                // Renumerar mesas
                mesas.forEach((mesa, index) => {
                    mesa.numero = index + 1;
                });
                
                // Actualizar input
                document.getElementById('totalMesas').value = mesas.length;
                document.getElementById('mesaQuitar').value = '';
                
                // Limpiar selección si era la mesa quitada
                if (mesaSeleccionada === numeroMesa) {
                    mesaSeleccionada = null;
                    pedidoActual = [];
                    document.getElementById('mesaActual').innerHTML = '<i class="fas fa-mouse-pointer"></i> Selecciona una mesa';
                    actualizarPedido();
                }
                
                mostrarMesas();
            }
        }

        // Inicializar sistema al cargar la página
        document.addEventListener('DOMContentLoaded', inicializarSistema);
    </script>
</body>
</html>