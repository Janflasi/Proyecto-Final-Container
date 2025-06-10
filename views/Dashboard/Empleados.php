<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Container Bar - Gestión de Empleados</title>
    <link rel="stylesheet" href="/style/style5.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="/assets/unnamed.png" alt="Container Bar" class="header-logo">
            <h1>CONTAINER BAR</h1>
            <p>Sistema de Gestión de Empleados</p>
            <div style="margin-top: 10px;">
                <span id="currentUser">👤 Admin: Juan Perez</span>
               
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Empleados</h3>
                <div class="number" id="totalEmployees">0</div>
                <p>Personal registrado</p>
            </div>
            <div class="stat-card">
                <h3>Administradores</h3>
                <div class="number" id="totalAdmins">0</div>
                <p>Con acceso total</p>
            </div>
            <div class="stat-card">
                <h3>Meseros</h3>
                <div class="number" id="totalWaiters">0</div>
                <p>Personal de servicio</p>
            </div>
            <div class="stat-card">
                <h3>Activos</h3>
                <div class="number" id="activeEmployees">0</div>
                <p>En servicio</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Employees Section -->
            <div class="employees-section">
                <div class="section-header">
                    <h2 class="section-title">👥 Gestión de Usuario</h2>
                    <button class="btn" onclick="openModal('addEmployeeModal')">+ Nuevo Usuario</button>
                </div>

                <input type="text" class="search-bar" placeholder="🔍 Buscar por nombre, email o teléfono..." id="searchInput" onkeyup="filterEmployees()">

                <div id="employeesList">
                    <!-- Los empleados se cargarán dinámicamente aquí -->
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Quick Add Form -->
                <div class="add-employee-form">
                    <h3 class="section-title">⚡ Acceso Rápido</h3>
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" id="quickName" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="quickEmail" placeholder="email@containerbar.com">
                    </div>
                    <div class="form-group">
                        <label>Rol</label>
                        <select id="quickRole">
                            <option value="">Seleccionar...</option>
                            <option value="1">👔 Administrador</option>
                            <option value="2">🍽️ Mesero</option>
                        </select>
                    </div>
                    <button class="btn" onclick="quickAddEmployee()">➕ Agregar Rápido</button>
                </div>

                <!-- Stats Info -->
                <div class="schedule-info">
                    <h3 class="section-title">📊 Estadísticas</h3>
                    <div class="schedule-item">
                        <span>Usuarios Registrados</span>
                        <span class="shift-time" id="statsTotal">0</span>
                    </div>
                    <div class="schedule-item">
                        <span>Activos Hoy</span>
                        <span class="shift-time" id="statsActive">0</span>
                    </div>
                    <div class="schedule-item">
                        <span>Nuevos Este Mes</span>
                        <span class="shift-time" id="statsNewMonth">0</span>
                    </div>
                    <button class="btn btn-secondary" style="margin-top: 15px; width: 100%;" onclick="generateReport()">📈 Generar Reporte</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar empleado -->
    <div id="addEmployeeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addEmployeeModal')">&times;</span>
            <h2 class="section-title" id="modalTitle">➕ Agregar Nuevo Usuario</h2>
            
            <form id="employeeForm">
                <input type="hidden" id="modalUserId" value="">
                
                <div class="form-group">
                    <label>Nombre Completo *</label>
                    <input type="text" id="modalName" placeholder="Nombre completo" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="modalEmail" placeholder="email@containerbar.com" required>
                </div>
                
                <div class="form-group">
                    <label>Contraseña *</label>
                    <input type="password" id="modalPassword" placeholder="Contraseña segura" required>
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" id="modalPhone" placeholder="+57 300 123 4567">
                </div>
                
                <div class="form-group">
                    <label>Rol *</label>
                    <select id="modalRole" required>
                        <option value="">Seleccionar rol...</option>
                        <option value="1">👔 Administrador</option>
                        <option value="2">🍽️ Mesero</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Estado</label>
                    <select id="modalStatus">
                        <option value="1">✅ Activo</option>
                        <option value="0">❌ Inactivo</option>
                    </select>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">
                    <span id="submitButtonText">✅ Crear Usuario</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            <h2 class="section-title">⚠️ Confirmar Eliminación</h2>
            <p id="deleteMessage">¿Estás seguro de que deseas eliminar este usuario?</p>
            <div style="margin-top: 20px; text-align: center;">
                <button class="btn btn-danger" onclick="confirmDelete()">🗑️ Eliminar</button>
                <button class="btn btn-secondary" onclick="closeModal('deleteModal')" style="margin-left: 10px;">❌ Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let employees = [];
        let currentUser = { id: 1, nombre: 'Juan Perez', rol: 1 }; // Simulando usuario logueado
        let userToDelete = null;

        // Inicializar la aplicación
        document.addEventListener('DOMContentLoaded', function() {
            loadEmployees();
            updateStats();
        });

        // Simular carga de empleados desde la base de datos
        function loadEmployees() {
            // Simulando datos desde la base de datos usuarios
            employees = [
                {
                    id_usuario: 1,
                    nombre: 'Juan Perez',
                    email: 'juan.perez@example.com',
                    telefono: '3001234567',
                    id_rol: 1,
                    nombre_rol: 'Administrador',
                    fecha_ingreso: '2024-05-01',
                    activo: 1
                },
                {
                    id_usuario: 2,
                    nombre: 'Maria Lopez',
                    email: 'maria.lopez@example.com',
                    telefono: '3009876543',
                    id_rol: 2,
                    nombre_rol: 'Mesero',
                    fecha_ingreso: '2024-05-05',
                    activo: 1
                },
                {
                    id_usuario: 3,
                    nombre: 'Carlos Gomez',
                    email: 'carlos.gomez@example.com',
                    telefono: '3001122334',
                    id_rol: 1,
                    nombre_rol: 'Administrador',
                    fecha_ingreso: '2024-05-10',
                    activo: 1
                },
                {
                    id_usuario: 4,
                    nombre: 'Juan Torres',
                    email: 'juan.torres@example.com',
                    telefono: '3015551234',
                    id_rol: 2,
                    nombre_rol: 'Mesero',
                    fecha_ingreso: '2024-06-10',
                    activo: 1
                }
            ];
            
            renderEmployees();
            updateStats();
        }

        // Renderizar lista de empleados
        function renderEmployees() {
            const container = document.getElementById('employeesList');
            container.innerHTML = '';

            employees.forEach(employee => {
                const roleIcon = employee.id_rol == 1 ? '👔' : '🍽️';
                const statusClass = employee.activo ? 'status-active' : 'status-inactive';
                const statusText = employee.activo ? 'Activo' : 'Inactivo';

                const employeeCard = `
                    <div class="employee-card" data-name="${employee.nombre.toLowerCase()}" data-email="${employee.email.toLowerCase()}" data-phone="${employee.telefono}">
                        <div class="employee-info">
                            <div class="employee-details">
                                <h4>${employee.nombre}</h4>
                                <p>${roleIcon} ${employee.nombre_rol}</p>
                                <p>📧 ${employee.email}</p>
                                <p>📱 ${employee.telefono || 'No registrado'}</p>
                                <p>📅 Ingreso: ${formatDate(employee.fecha_ingreso)}</p>
                            </div>
                            <span class="status-badge ${statusClass}">${statusText}</span>
                        </div>
                        <div class="employee-actions">
                            <button class="btn btn-secondary btn-small" onclick="editEmployee(${employee.id_usuario})">✏️ Editar</button>
                            <button class="btn btn-small" onclick="viewEmployee(${employee.id_usuario})">👁️ Ver</button>
                            <button class="btn btn-danger btn-small" onclick="deleteEmployee(${employee.id_usuario})">🗑️ Eliminar</button>
                        </div>
                    </div>
                `;
                container.innerHTML += employeeCard;
            });
        }

        // Actualizar estadísticas
        function updateStats() {
            const total = employees.length;
            const admins = employees.filter(emp => emp.id_rol == 1).length;
            const waiters = employees.filter(emp => emp.id_rol == 2).length;
            const active = employees.filter(emp => emp.activo == 1).length;

            document.getElementById('totalEmployees').textContent = total;
            document.getElementById('totalAdmins').textContent = admins;
            document.getElementById('totalWaiters').textContent = waiters;
            document.getElementById('activeEmployees').textContent = active;

            // Sidebar stats
            document.getElementById('statsTotal').textContent = total;
            document.getElementById('statsActive').textContent = active;
            
            // Nuevos este mes (simulado)
            const currentMonth = new Date().getMonth();
            const newThisMonth = employees.filter(emp => {
                const empDate = new Date(emp.fecha_ingreso);
                return empDate.getMonth() === currentMonth;
            }).length;
            document.getElementById('statsNewMonth').textContent = newThisMonth;
        }

        // Funciones del modal
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'addEmployeeModal') {
                clearForm();
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }

        // Limpiar formulario
        function clearForm() {
            document.getElementById('employeeForm').reset();
            document.getElementById('modalUserId').value = '';
            document.getElementById('modalTitle').textContent = '➕ Agregar Nuevo Usuario';
            document.getElementById('submitButtonText').textContent = '✅ Crear Usuario';
            document.getElementById('modalPassword').required = true;
        }

        // Agregar empleado rápido
        function quickAddEmployee() {
            const name = document.getElementById('quickName').value.trim();
            const email = document.getElementById('quickEmail').value.trim();
            const role = document.getElementById('quickRole').value;
            
            if (!name || !email || !role) {
                alert('⚠️ Por favor completa todos los campos');
                return;
            }

            // Verificar email único
            if (employees.some(emp => emp.email.toLowerCase() === email.toLowerCase())) {
                alert('⚠️ El email ya está registrado');
                return;
            }

            const newEmployee = {
                id_usuario: Date.now(), // Simular ID único
                nombre: name,
                email: email,
                telefono: '',
                id_rol: parseInt(role),
                nombre_rol: role == 1 ? 'Administrador' : 'Mesero',
                fecha_ingreso: new Date().toISOString().split('T')[0],
                activo: 1
            };

            employees.push(newEmployee);
            
            // Limpiar formulario rápido
            document.getElementById('quickName').value = '';
            document.getElementById('quickEmail').value = '';
            document.getElementById('quickRole').value = '';
            
            renderEmployees();
            updateStats();
            
            alert(`✅ Usuario ${name} agregado exitosamente`);
        }

        // Manejar envío del formulario principal
        document.getElementById('employeeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('modalUserId').value;
            const name = document.getElementById('modalName').value.trim();
            const email = document.getElementById('modalEmail').value.trim();
            const password = document.getElementById('modalPassword').value;
            const phone = document.getElementById('modalPhone').value.trim();
            const role = parseInt(document.getElementById('modalRole').value);
            const status = parseInt(document.getElementById('modalStatus').value);

            // Validaciones
            if (!name || !email || (!password && !userId) || !role) {
                alert('⚠️ Por favor completa los campos obligatorios');
                return;
            }

            // Verificar email único (excepto para edición del mismo usuario)
            const emailExists = employees.some(emp => 
                emp.email.toLowerCase() === email.toLowerCase() && 
                emp.id_usuario != userId
            );
            
            if (emailExists) {
                alert('⚠️ El email ya está registrado');
                return;
            }

            if (userId) {
                // Editar usuario existente
                const employeeIndex = employees.findIndex(emp => emp.id_usuario == userId);
                if (employeeIndex !== -1) {
                    employees[employeeIndex] = {
                        ...employees[employeeIndex],
                        nombre: name,
                        email: email,
                        telefono: phone,
                        id_rol: role,
                        nombre_rol: role == 1 ? 'Administrador' : 'Mesero',
                        activo: status
                    };
                    alert(`✅ Usuario ${name} actualizado exitosamente`);
                }
            } else {
                // Crear nuevo usuario
                const newEmployee = {
                    id_usuario: Date.now(),
                    nombre: name,
                    email: email,
                    telefono: phone,
                    id_rol: role,
                    nombre_rol: role == 1 ? 'Administrador' : 'Mesero',
                    fecha_ingreso: new Date().toISOString().split('T')[0],
                    activo: status
                };
                employees.push(newEmployee);
                alert(`✅ Usuario ${name} creado exitosamente`);
            }

            closeModal('addEmployeeModal');
            renderEmployees();
            updateStats();
        });

        // Editar empleado
        function editEmployee(userId) {
            const employee = employees.find(emp => emp.id_usuario == userId);
            if (!employee) return;

            document.getElementById('modalUserId').value = employee.id_usuario;
            document.getElementById('modalName').value = employee.nombre;
            document.getElementById('modalEmail').value = employee.email;
            document.getElementById('modalPhone').value = employee.telefono || '';
            document.getElementById('modalRole').value = employee.id_rol;
            document.getElementById('modalStatus').value = employee.activo;
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalPassword').required = false;
            
            document.getElementById('modalTitle').textContent = '✏️ Editar Usuario';
            document.getElementById('submitButtonText').textContent = '💾 Actualizar Usuario';
            
            openModal('addEmployeeModal');
        }

        // Ver detalles del empleado
        function viewEmployee(userId) {
            const employee = employees.find(emp => emp.id_usuario == userId);
            if (!employee) return;

            const roleIcon = employee.id_rol == 1 ? '👔' : '🍽️';
            const statusIcon = employee.activo ? '✅' : '❌';
            
            alert(`👤 Detalles del Usuario:
            
Nombre: ${employee.nombre}
Email: ${employee.email}
Teléfono: ${employee.telefono || 'No registrado'}
Rol: ${roleIcon} ${employee.nombre_rol}
Estado: ${statusIcon} ${employee.activo ? 'Activo' : 'Inactivo'}
Fecha de Ingreso: ${formatDate(employee.fecha_ingreso)}`);
        }

        // Eliminar empleado
        function deleteEmployee(userId) {
            const employee = employees.find(emp => emp.id_usuario == userId);
            if (!employee) return;

            // No permitir eliminar al usuario actual
            if (userId == currentUser.id) {
                alert('❌ No puedes eliminar tu propia cuenta');
                return;
            }

            userToDelete = userId;
            document.getElementById('deleteMessage').textContent = 
                `¿Estás seguro de que deseas eliminar al usuario "${employee.nombre}"? Esta acción no se puede deshacer.`;
            
            openModal('deleteModal');
        }

        // Confirmar eliminación
        function confirmDelete() {
            if (!userToDelete) return;

            const employeeIndex = employees.findIndex(emp => emp.id_usuario == userToDelete);
            if (employeeIndex !== -1) {
                const employeeName = employees[employeeIndex].nombre;
                employees.splice(employeeIndex, 1);
                
                closeModal('deleteModal');
                renderEmployees();
                updateStats();
                userToDelete = null;
                
                alert(`🗑️ Usuario ${employeeName} eliminado exitosamente`);
            }
        }

        // Filtrar empleados
        function filterEmployees() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const employeeCards = document.querySelectorAll('.employee-card');
            
            employeeCards.forEach(card => {
                const name = card.getAttribute('data-name');
                const email = card.getAttribute('data-email');
                const phone = card.getAttribute('data-phone');
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Generar reporte
        function generateReport() {
            const total = employees.length;
            const active = employees.filter(emp => emp.activo == 1).length;
            const inactive = total - active;
            const admins = employees.filter(emp => emp.id_rol == 1).length;
            const waiters = employees.filter(emp => emp.id_rol == 2).length;

            alert(`📈 REPORTE DE USUARIOS - CONTAINER BAR
            
📊 Resumen General:
• Total de usuarios: ${total}
• Usuarios activos: ${active}
• Usuarios inactivos: ${inactive}

👥 Por Roles:
• Administradores: ${admins}
• Meseros: ${waiters}

📅 Fecha del reporte: ${new Date().toLocaleDateString('es-CO')}`);
        }

        // Formatear fecha
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-CO');
        }

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.employee-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>