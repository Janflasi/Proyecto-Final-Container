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
</div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Empleados</h3>
                <div class="number" id="totalEmployees">12</div>
                <p>Personal activo</p>
            </div>
            <div class="stat-card">
                <h3>Turno Actual</h3>
                <div class="number" id="currentShift">8</div>
                <p>En servicio ahora</p>
            </div>
            <div class="stat-card">
                <h3>De Vacaciones</h3>
                <div class="number" id="onVacation">2</div>
                <p>Ausentes temporalmente</p>
            </div>
            <div class="stat-card">
                <h3>Nuevos</h3>
                <div class="number" id="newHires">3</div>
                <p>Este mes</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Employees Section -->
            <div class="employees-section">
                <div class="section-header">
                    <h2 class="section-title">📋 Lista de Empleados</h2>
                    <button class="btn" onclick="openModal('addEmployeeModal')">+ Nuevo Empleado</button>
                </div>

                <input type="text" class="search-bar" placeholder="🔍 Buscar empleado..." id="searchInput" onkeyup="filterEmployees()">

                <div id="employeesList">
                    <!-- Employee Cards -->
                    <div class="employee-card" data-name="carlos martinez" data-position="bartender">
                        <div class="employee-info">
                            <div class="employee-details">
                                <h4>Carlos Martínez</h4>
                                <p>🍸 Bartender Senior</p>
                                <p>📧 carlos@containerbar.com</p>
                                <p>📱 +34 123 456 789</p>
                            </div>
                            <span class="status-badge status-active">Activo</span>
                        </div>
                        <div class="employee-actions">
                            <button class="btn btn-secondary btn-small">✏️ Editar</button>
                            <button class="btn btn-small">📅 Horarios</button>
                            <button class="btn btn-danger btn-small">🗑️ Eliminar</button>
                        </div>
                    </div>

                    <div class="employee-card" data-name="maria rodriguez" data-position="camarera">
                        <div class="employee-info">
                            <div class="employee-details">
                                <h4>María Rodríguez</h4>
                                <p>🍽️ Camarera</p>
                                <p>📧 maria@containerbar.com</p>
                                <p>📱 +34 987 654 321</p>
                            </div>
                            <span class="status-badge status-vacation">Vacaciones</span>
                        </div>
                        <div class="employee-actions">
                            <button class="btn btn-secondary btn-small">✏️ Editar</button>
                            <button class="btn btn-small">📅 Horarios</button>
                            <button class="btn btn-danger btn-small">🗑️ Eliminar</button>
                        </div>
                    </div>

                    <div class="employee-card" data-name="david lopez" data-position="cocinero">
                        <div class="employee-info">
                            <div class="employee-details">
                                <h4>David López</h4>
                                <p>👨‍🍳 Cocinero</p>
                                <p>📧 david@containerbar.com</p>
                                <p>📱 +34 555 123 456</p>
                            </div>
                            <span class="status-badge status-active">Activo</span>
                        </div>
                        <div class="employee-actions">
                            <button class="btn btn-secondary btn-small">✏️ Editar</button>
                            <button class="btn btn-small">📅 Horarios</button>
                            <button class="btn btn-danger btn-small">🗑️ Eliminar</button>
                        </div>
                    </div>

                    <div class="employee-card" data-name="ana garcia" data-position="gerente">
                        <div class="employee-info">
                            <div class="employee-details">
                                <h4>Ana García</h4>
                                <p>👩‍💼 Gerente de Turno</p>
                                <p>📧 ana@containerbar.com</p>
                                <p>📱 +34 666 789 123</p>
                            </div>
                            <span class="status-badge status-active">Activo</span>
                        </div>
                        <div class="employee-actions">
                            <button class="btn btn-secondary btn-small">✏️ Editar</button>
                            <button class="btn btn-small">📅 Horarios</button>
                            <button class="btn btn-danger btn-small">🗑️ Eliminar</button>
                        </div>
                    </div>
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
                        <label>Posición</label>
                        <select id="quickPosition">
                            <option value="">Seleccionar...</option>
                            <option value="bartender">🍸 Bartender</option>
                            <option value="camarera">🍽️ Camarera/o</option>
                            <option value="cocinero">👨‍🍳 Cocinero/a</option>
                            <option value="gerente">👔 Gerente</option>
                            <option value="seguridad">🛡️ Seguridad</option>
                            <option value="limpieza">🧹 Limpieza</option>
                        </select>
                    </div>
                    <button class="btn" onclick="quickAddEmployee()">➕ Agregar Rápido</button>
                </div>

                <!-- Schedule Info -->
                <div class="schedule-info">
                    <h3 class="section-title">🕐 Turnos de Hoy</h3>
                    <div class="schedule-item">
                        <span>Turno Mañana</span>
                        <span class="shift-time">08:00 - 16:00</span>
                    </div>
                    <div class="schedule-item">
                        <span>Turno Tarde</span>
                        <span class="shift-time">16:00 - 00:00</span>
                    </div>
                    <div class="schedule-item">
                        <span>Turno Noche</span>
                        <span class="shift-time">00:00 - 08:00</span>
                    </div>
                    <button class="btn btn-secondary" style="margin-top: 15px; width: 100%;">📊 Ver Horarios Completos</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar empleado -->
    <div id="addEmployeeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addEmployeeModal')">&times;</span>
            <h2 class="section-title">➕ Agregar Nuevo Empleado</h2>
            <div class="form-group">
                <label>Nombre Completo</label>
                <input type="text" id="modalName" placeholder="Nombre y apellidos">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="modalEmail" placeholder="email@containerbar.com">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="tel" id="modalPhone" placeholder="+34 xxx xxx xxx">
            </div>
            <div class="form-group">
                <label>Posición</label>
                <select id="modalPosition">
                    <option value="">Seleccionar posición...</option>
                    <option value="bartender">🍸 Bartender</option>
                    <option value="camarera">🍽️ Camarera/o</option>
                    <option value="cocinero">👨‍🍳 Cocinero/a</option>
                    <option value="gerente">👔 Gerente</option>
                    <option value="seguridad">🛡️ Seguridad</option>
                    <option value="limpieza">🧹 Limpieza</option>
                </select>
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select id="modalStatus">
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="vacation">Vacaciones</option>
                </select>
            </div>
            <button class="btn" onclick="addEmployee()" style="width: 100%; margin-top: 20px;">✅ Crear Empleado</button>
        </div>
    </div>

    <script>
        // Variables globales
        let employees = [];

        // Funciones para el modal
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
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

        // Función para buscar empleados
        function filterEmployees() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const employeeCards = document.querySelectorAll('.employee-card');
            
            employeeCards.forEach(card => {
                const name = card.getAttribute('data-name');
                const position = card.getAttribute('data-position');
                
                if (name.includes(searchTerm) || position.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Función para agregar empleado rápido
        function quickAddEmployee() {
            const name = document.getElementById('quickName').value;
            const position = document.getElementById('quickPosition').value;
            
            if (name && position) {
                alert(`✅ Empleado ${name} agregado como ${position}`);
                document.getElementById('quickName').value = '';
                document.getElementById('quickPosition').value = '';
                updateStats();
            } else {
                alert('⚠️ Por favor completa todos los campos');
            }
        }

        // Función para agregar empleado completo
        function addEmployee() {
            const name = document.getElementById('modalName').value;
            const email = document.getElementById('modalEmail').value;
            const phone = document.getElementById('modalPhone').value;
            const position = document.getElementById('modalPosition').value;
            const status = document.getElementById('modalStatus').value;
            
            if (name && email && position) {
                alert(`✅ Empleado ${name} agregado exitosamente`);
                closeModal('addEmployeeModal');
                // Limpiar formulario
                document.getElementById('modalName').value = '';
                document.getElementById('modalEmail').value = '';
                document.getElementById('modalPhone').value = '';
                document.getElementById('modalPosition').value = '';
                document.getElementById('modalStatus').value = 'active';
                updateStats();
            } else {
                alert('⚠️ Por favor completa los campos obligatorios');
            }
        }

        // Función para actualizar estadísticas
        function updateStats() {
            const currentTotal = parseInt(document.getElementById('totalEmployees').textContent);
            document.getElementById('totalEmployees').textContent = currentTotal + 1;
        }

        // Agregar efectos de hover y animaciones
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada para las tarjetas
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

        // Funciones adicionales para los botones de acción
        function editEmployee(name) {
            alert(`✏️ Editando empleado: ${name}`);
        }

        function viewSchedule(name) {
            alert(`📅 Viendo horarios de: ${name}`);
        }

        function deleteEmployee(name) {
            if (confirm(`¿Estás seguro de eliminar a ${name}?`)) {
                alert(`🗑️ Empleado ${name} eliminado`);
            }
        }
    </script>
</body>
</html>