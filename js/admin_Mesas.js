
        // Datos de las mesas
        let mesas = [
            { id: 1, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 2, estado: 'ocupada', clientes: 4, total: 85.50, pedidos: ['2x Cerveza', '1x Nachos'], tiempo: new Date(Date.now() - 25 * 60000) },
            { id: 3, estado: 'ocupada', clientes: 2, total: 45.00, pedidos: ['2x Cerveza', '1x Hamburguesa', '1x Papas'], tiempo: new Date(Date.now() - 15 * 60000) },
            { id: 4, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 5, estado: 'cuenta', clientes: 3, total: 120.75, pedidos: ['3x Cerveza', '2x Pizza', '1x Ensalada'], tiempo: new Date(Date.now() - 45 * 60000) },
            { id: 6, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 7, estado: 'ocupada', clientes: 2, total: 67.25, pedidos: ['1x Mojito', '2x Alitas', '1x Nachos'], tiempo: new Date(Date.now() - 8 * 60000) },
            { id: 8, estado: 'reservada', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 9, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 10, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 11, estado: 'ocupada', clientes: 6, total: 200.00, pedidos: ['6x Cerveza', '2x Pizza', '1x Alitas'], tiempo: new Date(Date.now() - 30 * 60000) },
            { id: 12, estado: 'ocupada', clientes: 3, total: 95.80, pedidos: ['3x Cerveza', '1x Pizza'], tiempo: new Date(Date.now() - 5 * 60000) },
            { id: 13, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 14, estado: 'cuenta', clientes: 2, total: 78.50, pedidos: ['2x Mojito', '1x Hamburguesa'], tiempo: new Date(Date.now() - 60 * 60000) },
            { id: 15, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 16, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 17, estado: 'ocupada', clientes: 4, total: 156.00, pedidos: ['4x Cerveza', '2x Hamburguesa', '2x Papas'], tiempo: new Date(Date.now() - 20 * 60000) },
            { id: 18, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 19, estado: 'reservada', clientes: 0, total: 0, pedidos: [], tiempo: null },
            { id: 20, estado: 'libre', clientes: 0, total: 0, pedidos: [], tiempo: null }
        ];

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

            actualizarEstadisticas();
        }

        function calcularTiempo(tiempo) {
            const ahora = new Date();
            const diferencia = Math.floor((ahora - tiempo) / 60000); // minutos
            
            if (diferencia < 60) {
                return `${diferencia} min`;
            } else {
                const horas = Math.floor(diferencia / 60);
                const minutos = diferencia % 60;
                return `${horas}h ${minutos}m`;
            }
        }

        function actualizarEstadisticas() {
            const totalMesas = mesas.length;
            const ocupadas = mesas.filter(m => m.estado === 'ocupada').length;
            const libres = mesas.filter(m => m.estado === 'libre').length;
            const cuentas = mesas.filter(m => m.estado === 'cuenta').length;
            const reservadas = mesas.filter(m => m.estado === 'reservada').length;
            const totalVentas = mesas.reduce((sum, m) => sum + m.total, 0);

            document.getElementById('totalMesas').textContent = totalMesas;
            document.getElementById('mesasOcupadas').textContent = ocupadas + cuentas;
            document.getElementById('mesasLibres').textContent = libres;
            document.getElementById('ventasHoy').textContent = `$${totalVentas.toFixed(2)}`;
        }

        function abrirModal(idMesa) {
            const mesa = mesas.find(m => m.id === idMesa);
            mesaSeleccionada = mesa;
            
            document.getElementById('modalTitle').textContent = `Mesa ${mesa.id}`;
            
            const tiempoTexto = mesa.tiempo ? calcularTiempo(mesa.tiempo) : 'Sin tiempo';
            
            document.getElementById('modalContent').innerHTML = `
                <div class="order-detail">
                    <strong>Estado:</strong> ${mesa.estado.toUpperCase()}
                    <br><strong>Clientes:</strong> ${mesa.clientes}
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
            
            const estados = ['libre', 'ocupada', 'cuenta', 'reservada'];
            const estadoActual = mesaSeleccionada.estado;
            const siguienteIndice = (estados.indexOf(estadoActual) + 1) % estados.length;
            
            mesaSeleccionada.estado = estados[siguienteIndice];
            
            // Si cambia a libre, resetear datos
            if (mesaSeleccionada.estado === 'libre') {
                mesaSeleccionada.clientes = 0;
                mesaSeleccionada.total = 0;
                mesaSeleccionada.pedidos = [];
                mesaSeleccionada.tiempo = null;
            }
            
            renderizarMesas();
            cerrarModal();
        }

        function actualizarEstados() {
            // Simular actualización en tiempo real
            renderizarMesas();
            
            // Mostrar mensaje de confirmación
            const btn = event.target;
            const textoOriginal = btn.textContent;
            btn.textContent = '✅ Actualizada';
            btn.style.background = 'var(--accent)';
            
            setTimeout(() => {
                btn.textContent = textoOriginal;
                btn.style.background = '';
            }, 2000);
        }

        function mostrarReporte() {
            const totalVentas = mesas.reduce((sum, m) => sum + m.total, 0);
            const mesasOcupadas = mesas.filter(m => m.estado === 'ocupada' || m.estado === 'cuenta').length;
            const promedioMesa = mesasOcupadas > 0 ? totalVentas / mesasOcupadas : 0;
            
            alert(`📊 REPORTE DEL DÍA\n\n💰 Ventas totales: $${totalVentas.toFixed(2)}\n🏪 Mesas activas: ${mesasOcupadas}\n📈 Promedio por mesa: $${promedioMesa.toFixed(2)}\n⏰ Tiempo promedio: 35 min`);
        }

        function limpiarMesas() {
            if (confirm('¿Limpiar todas las mesas libres?')) {
                mesas.forEach(mesa => {
                    if (mesa.estado === 'libre') {
                        mesa.clientes = 0;
                        mesa.total = 0;
                        mesa.pedidos = [];
                        mesa.tiempo = null;
                    }
                });
                renderizarMesas();
            }
        }

        // Simulación de actualizaciones en tiempo real
        setInterval(() => {
            // Ocasionalmente cambiar algunos estados
            if (Math.random() < 0.1) {
                const mesaAleatoria = mesas[Math.floor(Math.random() * mesas.length)];
                if (mesaAleatoria.estado === 'ocupada' && Math.random() < 0.3) {
                    mesaAleatoria.estado = 'cuenta';
                    renderizarMesas();
                }
            }
        }, 10000);

        // Inicializar
        renderizarMesas();

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('tableModal');
            if (event.target === modal) {
                cerrarModal();
            }
        }
  