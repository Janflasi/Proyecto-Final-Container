  // Función para crear alertas personalizadas
function mostrarAlerta(mensaje, tipo = 'info', duracion = 3000) {
    // Remover alertas existentes
    const alertasExistentes = document.querySelectorAll('.custom-alert');
    alertasExistentes.forEach(alerta => alerta.remove());
    
    // Crear el elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = `custom-alert custom-alert-${tipo}`;
    
    // Definir iconos según el tipo
    const iconos = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-triangle',
        'warning': 'fas fa-exclamation-circle',
        'info': 'fas fa-info-circle'
    };
    
    alerta.innerHTML = `
        <div class="custom-alert-content">
            <i class="${iconos[tipo] || iconos.info}"></i>
            <span>${mensaje}</span>
        </div>
        <button class="custom-alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Agregar estilos inline para que funcione sin CSS adicional
    Object.assign(alerta.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        backgroundColor: tipo === 'success' ? '#10b981' : 
                        tipo === 'error' ? '#ef4444' : 
                        tipo === 'warning' ? '#f59e0b' : '#3b82f6',
        color: 'white',
        padding: '12px 16px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '10000',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        minWidth: '300px',
        maxWidth: '400px',
        fontSize: '14px',
        fontWeight: '500',
        animation: 'slideInRight 0.3s ease-out',
        transition: 'all 0.3s ease'
    });
    
    const content = alerta.querySelector('.custom-alert-content');
    Object.assign(content.style, {
        display: 'flex',
        alignItems: 'center',
        gap: '8px'
    });
    
    const closeBtn = alerta.querySelector('.custom-alert-close');
    Object.assign(closeBtn.style, {
        background: 'none',
        border: 'none',
        color: 'white',
        cursor: 'pointer',
        padding: '4px',
        borderRadius: '4px',
        marginLeft: '12px'
    });
    
    // Agregar animación CSS
    if (!document.querySelector('#custom-alert-styles')) {
        const style = document.createElement('style');
        style.id = 'custom-alert-styles';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            .custom-alert-close:hover {
                background-color: rgba(255,255,255,0.2) !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(alerta);
    
    // Auto-remover después del tiempo especificado
    if (duracion > 0) {
        setTimeout(() => {
            if (alerta.parentElement) {
                alerta.style.transform = 'translateX(100%)';
                alerta.style.opacity = '0';
                setTimeout(() => alerta.remove(), 300);
            }
        }, duracion);
    }
}

// Función para confirmaciones personalizadas
function mostrarConfirmacion(mensaje, callback, textoConfirmar = 'Confirmar', textoCancelar = 'Cancelar') {
    // Remover confirmaciones existentes
    const confirmacionesExistentes = document.querySelectorAll('.custom-confirm');
    confirmacionesExistentes.forEach(confirmacion => confirmacion.remove());
    
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.className = 'custom-confirm';
    Object.assign(overlay.style, {
        position: 'fixed',
        top: '0',
        left: '0',
        width: '100%',
        height: '100%',
        backgroundColor: 'rgba(0,0,0,0.5)',
        zIndex: '10001',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        animation: 'fadeIn 0.2s ease-out'
    });
    
    // Crear modal
    const modal = document.createElement('div');
    Object.assign(modal.style, {
        backgroundColor: 'white',
        borderRadius: '12px',
        padding: '24px',
        minWidth: '320px',
        maxWidth: '400px',
        boxShadow: '0 20px 25px -5px rgba(0,0,0,0.1)',
        animation: 'scaleIn 0.2s ease-out'
    });
    
    modal.innerHTML = `
        <div style="text-align: center;">
            <div style="margin-bottom: 16px;">
                <i class="fas fa-question-circle" style="font-size: 48px; color: #f59e0b;"></i>
            </div>
            <h3 style="margin: 0 0 16px 0; color: #1f2937; font-size: 18px;">Confirmación</h3>
            <p style="margin: 0 0 24px 0; color: #6b7280; line-height: 1.5;">${mensaje}</p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button id="confirm-yes" style="
                    background-color: #ef4444;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                    transition: background-color 0.2s;
                ">${textoConfirmar}</button>
                <button id="confirm-no" style="
                    background-color: #6b7280;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                    transition: background-color 0.2s;
                ">${textoCancelar}</button>
            </div>
        </div>
    `;
    
    // Agregar animaciones CSS si no existen
    if (!document.querySelector('#custom-confirm-styles')) {
        const style = document.createElement('style');
        style.id = 'custom-confirm-styles';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes scaleIn {
                from { transform: scale(0.9); opacity: 0; }
                to { transform: scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Agregar event listeners
    document.getElementById('confirm-yes').onclick = () => {
        overlay.remove();
        callback(true);
    };
    
    document.getElementById('confirm-no').onclick = () => {
        overlay.remove();
        callback(false);
    };
    
    // Cerrar al hacer click en el overlay
    overlay.onclick = (e) => {
        if (e.target === overlay) {
            overlay.remove();
            callback(false);
        }
    };
}

// TODO: Asignar los valores de 'mesas', 'productos' e 'idUsuario' desde el backend o mediante una petición AJAX
let mesas = [];
let productos = [];
let mesaSeleccionada = null;
let idUsuario = null;

function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    const chevron = document.getElementById('profileChevron');
    menu.classList.toggle('active');
    chevron.style.transform = menu.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
}

function configuraciones() {
    mostrarAlerta('Función de configuraciones - Próximamente', 'info');
    toggleProfileMenu();
}

function cerrarSesion() {
    mostrarConfirmacion('¿Estás seguro de que quieres cerrar sesión?', (confirmado) => {
        if (confirmado) {
            window.location.href = 'logout.php';
        }
    }, 'Cerrar Sesión', 'Cancelar');
    toggleProfileMenu();
}

async function enviarRequest(data) {
    const response = await fetch(window.location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    return await response.json();
}

function mostrarMesas() {
    const container = document.getElementById('mesasGrid');
    container.innerHTML = '';
    
    mesas.forEach(mesa => {
        const mesaElement = document.createElement('div');
        mesaElement.className = `mesa ${mesa.disponible ? 'libre' : 'ocupada'}`;
        if (mesaSeleccionada === mesa.id_mesa) mesaElement.classList.add('seleccionada');
        
        mesaElement.innerHTML = `
            <div class="mesa-numero">${mesa.numero_mesa}</div>
            <div class="mesa-estado">${mesa.disponible ? 'libre' : 'ocupada'}</div>
        `;
        
        mesaElement.onclick = () => seleccionarMesa(mesa.id_mesa, mesa.numero_mesa);
        container.appendChild(mesaElement);
    });
}

function mostrarProductos() {
    const container = document.getElementById('productosList');
    container.innerHTML = '';
    
    const categorias = {};
    productos.forEach(producto => {
        if (!categorias[producto.nombre_categoria]) {
            categorias[producto.nombre_categoria] = [];
        }
        categorias[producto.nombre_categoria].push(producto);
    });
    
    Object.keys(categorias).forEach(categoria => {
        const categoriaDiv = document.createElement('div');
        categoriaDiv.innerHTML = `<h4 style="color: var(--accent); margin: 1rem 0 0.5rem 0; font-weight: 600;">${categoria}</h4>`;
        container.appendChild(categoriaDiv);
        
        categorias[categoria].forEach(producto => {
            const productoElement = document.createElement('div');
            productoElement.className = 'producto';
            productoElement.innerHTML = `
                <div>
                    <div class="producto-nombre">${producto.nombre_producto}</div>
                    <div class="producto-precio">$${parseFloat(producto.precio).toFixed(0)}</div>
                </div>
                <button class="btn-agregar" onclick="agregarProducto(${producto.id_producto})">
                    <i class="fas fa-plus"></i>
                </button>
            `;
            container.appendChild(productoElement);
        });
    });
}

function seleccionarMesa(id_mesa, numero_mesa) {
    mesaSeleccionada = id_mesa;
    document.getElementById('mesaActual').innerHTML = `<i class="fas fa-table"></i> Mesa ${numero_mesa}`;
    mostrarMesas();
    cargarCarrito();
    mostrarAlerta(`Mesa ${numero_mesa} seleccionada`, 'success', 2000);
}

async function agregarProducto(id_producto) {
    if (!mesaSeleccionada) {
        mostrarAlerta('Selecciona una mesa primero', 'warning');
        return;
    }
    
    const result = await enviarRequest({
        action: 'agregar_producto',
        id_mesa: mesaSeleccionada,
        id_usuario: idUsuario,
        id_producto: id_producto
    });
    
    if (result.success) {
        cargarCarrito();
        mostrarAlerta('Producto agregado al pedido', 'success', 2000);
    } else {
        mostrarAlerta('Error al agregar producto', 'error');
    }
}

async function cargarCarrito() {
    if (!mesaSeleccionada) return;
    
    try {
        const response = await fetch(`carrito.php?id_mesa=${mesaSeleccionada}&id_usuario=${idUsuario}`);
        const carrito = await response.json();
        mostrarCarrito(carrito);
    } catch (e) {
        mostrarCarrito([]);
    }
}

function mostrarCarrito(carrito) {
    const container = document.getElementById('pedidoItems');
    const totalElement = document.getElementById('totalPedido');
    
    container.innerHTML = '';
    let total = 0;
    
    carrito.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'pedido-item';
        itemElement.innerHTML = `
            <div>
                <div class="item-nombre">${item.nombre_producto}</div>
                <div class="item-precio">$${parseFloat(item.precio_unitario).toFixed(0)} c/u</div>
            </div>
            <div class="cantidad-controls">
                <button class="btn-cantidad" onclick="modificarCantidad(${item.id_producto}, ${item.cantidad - 1})">
                    <i class="fas fa-minus"></i>
                </button>
                <span class="cantidad">${item.cantidad}</span>
                <button class="btn-cantidad" onclick="modificarCantidad(${item.id_producto}, ${item.cantidad + 1})">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        `;
        container.appendChild(itemElement);
        total += item.cantidad * item.precio_unitario;
    });
    
    totalElement.textContent = total.toFixed(0);
}

async function modificarCantidad(id_producto, nueva_cantidad) {
    const result = await enviarRequest({
        action: 'modificar_cantidad',
        id_producto: id_producto,
        id_mesa: mesaSeleccionada,
        id_usuario: idUsuario,
        cantidad: nueva_cantidad
    });
    
    if (result.success) {
        cargarCarrito();
        if (nueva_cantidad <= 0) {
            mostrarAlerta('Producto eliminado del pedido', 'info', 2000);
        } else {
            mostrarAlerta('Cantidad actualizada', 'success', 1500);
        }
    } else {
        mostrarAlerta('Error al modificar cantidad', 'error');
    }
}

async function enviarPedido() {
    if (!mesaSeleccionada) {
        mostrarAlerta('Selecciona una mesa primero', 'warning');
        return;
    }
    
    const result = await enviarRequest({
        action: 'procesar_venta',
        id_mesa: mesaSeleccionada,
        id_usuario: idUsuario
    });
    
    if (result.success) {
        mostrarAlerta('Pedido enviado correctamente', 'success');
        cargarCarrito();
        cargarCuentas();
    } else {
        mostrarAlerta('Error al enviar pedido', 'error');
    }
}

async function generarCuenta() {
    await enviarPedido();
}

async function limpiarPedido() {
    if (!mesaSeleccionada) return;
    
    mostrarConfirmacion('¿Limpiar el pedido actual?', async (confirmado) => {
        if (confirmado) {
            const result = await enviarRequest({
                action: 'limpiar_carrito',
                id_mesa: mesaSeleccionada,
                id_usuario: idUsuario
            });
            
            if (result.success) {
                cargarCarrito();
                mostrarAlerta('Pedido limpiado', 'info');
            } else {
                mostrarAlerta('Error al limpiar pedido', 'error');
            }
        }
    }, 'Limpiar', 'Cancelar');
}

async function cargarCuentas() {
    try {
        const response = await fetch('cuentas.php');
        const cuentas = await response.json();
        mostrarCuentas(cuentas);
    } catch (e) {
        mostrarCuentas([]);
    }
}

function mostrarCuentas(cuentas) {
    const container = document.getElementById('cuentasList');
    container.innerHTML = '';
    
    if (cuentas.length === 0) {
        container.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 1rem;">No hay cuentas pendientes</p>';
        return;
    }
    
    cuentas.forEach(cuenta => {
        const cuentaElement = document.createElement('div');
        cuentaElement.className = 'cuenta-item';
        cuentaElement.innerHTML = `
            <div class="cuenta-header">
                <span class="cuenta-mesa">Mesa ${cuenta.numero_mesa}</span>
                <span class="cuenta-total">$${parseFloat(cuenta.total).toFixed(0)}</span>
            </div>
            <div class="botones-accion">
                <button class="btn btn-cuenta btn-small" onclick="pagarCuenta(${cuenta.id_venta})">
                    <i class="fas fa-credit-card"></i> Pagar
                </button>
            </div>
        `;
        container.appendChild(cuentaElement);
    });
}

async function pagarCuenta(id_venta) {
    mostrarConfirmacion('¿Confirmar pago de la cuenta?', async (confirmado) => {
        if (confirmado) {
            const result = await enviarRequest({
                action: 'pagar_venta',
                id_venta: id_venta
            });
            
            if (result.success) {
                mostrarAlerta('Pago procesado correctamente', 'success');
                cargarCuentas();
            } else {
                mostrarAlerta('Error al procesar pago', 'error');
            }
        }
    }, 'Confirmar Pago', 'Cancelar');
}

document.addEventListener('click', function(event) {
    const profileDropdown = document.querySelector('.profile-dropdown');
    const profileMenu = document.getElementById('profileMenu');
    
    if (!profileDropdown.contains(event.target) && profileMenu.classList.contains('active')) {
        toggleProfileMenu();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    mostrarMesas();
    mostrarProductos();
    cargarCuentas();
});

// Función actualizada para enviar requests AJAX
async function enviarRequest(data) {
    const response = await fetch('/mesero/action', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    });
    return await response.json();
}

// Función actualizada para cargar el carrito
async function cargarCarrito() {
    if (!mesaSeleccionada) return;
    
    try {
        const response = await fetch(`/mesero/carrito?id_mesa=${mesaSeleccionada}&id_usuario=${idUsuario}`);
        const carrito = await response.json();
        mostrarCarrito(carrito);
    } catch (e) {
        mostrarCarrito([]);
    }
}

// Función actualizada para cargar las cuentas
async function cargarCuentas() {
    try {
        const response = await fetch('/mesero/cuentas');
        const cuentas = await response.json();
        mostrarCuentas(cuentas);
    } catch (e) {
        mostrarCuentas([]);
    }
}