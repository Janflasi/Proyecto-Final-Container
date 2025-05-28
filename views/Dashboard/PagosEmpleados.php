<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Container Bar - Pagos de Nómina</title>
    <link rel="stylesheet" href="/style/style6.css">
   </head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>💰 CONTAINER BAR</h1>
            <p>Sistema de Nómina y Pagos a Empleados</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>💸 Total Nómina Mes</h3>
                <div class="number">€8,450</div>
                <div class="description">12 empleados activos</div>
            </div>
            <div class="stat-card">
                <h3>✅ Pagos Realizados</h3>
                <div class="number">8</div>
                <div class="description">66% completado</div>
            </div>
            <div class="stat-card">
                <h3>⏰ Pendientes</h3>
                <div class="number">4</div>
                <div class="description">€2,100 por pagar</div>
            </div>
            <div class="stat-card">
                <h3>📊 Promedio Salarial</h3>
                <div class="number">€704</div>
                <div class="description">Por empleado/mes</div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="main-grid">
            <!-- Payroll Section -->
            <div class="payroll-section">
                <div class="section-header">
                    <h2 class="section-title">💳 Gestión de Nómina</h2>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn btn-secondary" onclick="openModal('bulkPayModal')">💰 Pago Masivo</button>
                        <button class="btn" onclick="openModal('newPaymentModal')">➕ Nuevo Pago</button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-bar">
                    <select class="filter-select" id="statusFilter" onchange="filterByStatus()">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendientes</option>
                        <option value="paid">Pagados</option>
                        <option value="overdue">Vencidos</option>
                        <option value="partial">Parciales</option>
                    </select>
                    <select class="filter-select" id="positionFilter" onchange="filterByPosition()">
                        <option value="">Todas las posiciones</option>
                        <option value="bartender">Bartenders</option>
                        <option value="camarera">Camareros</option>
                        <option value="cocinero">Cocineros</option>
                        <option value="gerente">Gerentes</option>
                    </select>
                    <select class="filter-select" id="monthFilter">
                        <option value="2024-01">Enero 2024</option>
                        <option value="2024-02" selected>Febrero 2024</option>
                        <option value="2024-03">Marzo 2024</option>
                    </select>
                </div>

                <!-- Employee Payroll Cards -->
                <div id="payrollList">
                    <div class="employee-payroll-card" data-status="pending" data-position="bartender">
                        <div class="employee-header">
                            <div class="employee-info">
                                <h4>Carlos Martínez</h4>
                                <p>🍸 Bartender Senior • Fecha venc: 28/02/2024</p>
                            </div>
                            <span class="status-badge status-pending">Pendiente</span>
                        </div>
                        
                        <div class="salary-info">
                            <div class="salary-item">
                                <div class="label">Salario Base</div>
                                <div class="value">€800</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Horas Extra</div>
                                <div class="value">€120</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Propinas</div>
                                <div class="value">€85</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Deducciones</div>
                                <div class="value">-€95</div>
                            </div>
                            <div class="salary-item total">
                                <div class="label">Total Neto</div>
                                <div class="value">€910</div>
                            </div>
                        </div>

                        <div class="payment-actions">
                            <button class="btn btn-success btn-small" onclick="payEmployee('Carlos Martínez', 910)">💰 Pagar Ahora</button>
                            <button class="btn btn-secondary btn-small" onclick="viewPayslip('Carlos Martínez')">🧾 Ver Recibo</button>
                            <button class="btn btn-small">✏️ Editar</button>
                            <button class="btn btn-warning btn-small">📧 Recordatorio</button>
                        </div>
                    </div>

                    <div class="employee-payroll-card" data-status="paid" data-position="camarera">
                        <div class="employee-header">
                            <div class="employee-info">
                                <h4>María Rodríguez</h4>
                                <p>🍽️ Camarera • Pagado: 25/02/2024</p>
                            </div>
                            <span class="status-badge status-paid">Pagado</span>
                        </div>
                        
                        <div class="salary-info">
                            <div class="salary-item">
                                <div class="label">Salario Base</div>
                                <div class="value">€650</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Horas Extra</div>
                                <div class="value">€80</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Propinas</div>
                                <div class="value">€95</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Deducciones</div>
                                <div class="value">-€78</div>
                            </div>
                            <div class="salary-item total">
                                <div class="label">Total Neto</div>
                                <div class="value">€747</div>
                            </div>
                        </div>

                        <div class="payment-actions">
                            <button class="btn btn-secondary btn-small" onclick="viewPayslip('María Rodríguez')">🧾 Ver Recibo</button>
                            <button class="btn btn-small">📄 Reenviar</button>
                            <button class="btn btn-small">📊 Historial</button>
                        </div>
                    </div>

                    <div class="employee-payroll-card" data-status="overdue" data-position="cocinero">
                        <div class="employee-header">
                            <div class="employee-info">
                                <h4>David López</h4>
                                <p>👨‍🍳 Cocinero • ⚠️ Vencido: 26/02/2024</p>
                            </div>
                            <span class="status-badge status-overdue">Vencido</span>
                        </div>
                        
                        <div class="salary-info">
                            <div class="salary-item">
                                <div class="label">Salario Base</div>
                                <div class="value">€720</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Horas Extra</div>
                                <div class="value">€60</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Propinas</div>
                                <div class="value">€40</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Deducciones</div>
                                <div class="value">-€85</div>
                            </div>
                            <div class="salary-item total">
                                <div class="label">Total Neto</div>
                                <div class="value">€735</div>
                            </div>
                        </div>

                        <div class="payment-actions">
                            <button class="btn btn-danger btn-small" onclick="payEmployee('David López', 735)">🚨 Pagar Urgente</button>
                            <button class="btn btn-secondary btn-small" onclick="viewPayslip('David López')">🧾 Ver Recibo</button>
                            <button class="btn btn-small">✏️ Editar</button>
                            <button class="btn btn-warning btn-small">📧 Notificar</button>
                        </div>
                    </div>

                    <div class="employee-payroll-card" data-status="partial" data-position="gerente">
                        <div class="employee-header">
                            <div class="employee-info">
                                <h4>Ana García</h4>
                                <p>👩‍💼 Gerente • Anticipo: €500 de €1,200</p>
                            </div>
                            <span class="status-badge status-partial">Parcial</span>
                        </div>
                        
                        <div class="salary-info">
                            <div class="salary-item">
                                <div class="label">Salario Base</div>
                                <div class="value">€1,000</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Bonus</div>
                                <div class="value">€300</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Propinas</div>
                                <div class="value">€50</div>
                            </div>
                            <div class="salary-item">
                                <div class="label">Deducciones</div>
                                <div class="value">-€150</div>
                            </div>
                            <div class="salary-item total">
                                <div class="label">Resta Pagar</div>
                                <div class="value">€700</div>
                            </div>
                        </div>

                        <div class="payment-actions">
                            <button class="btn btn-success btn-small" onclick="payEmployee('Ana García', 700)">💰 Pagar Resto</button>
                            <button class="btn btn-secondary btn-small" onclick="viewPayslip('Ana García')">🧾 Ver Recibo</button>
                            <button class="btn btn-small">📊 Historial</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Payment Summary -->
                <div class="payment-summary">
                    <h3 class="section-title">📊 Resumen Mensual</h3>
                    <div class="summary-item">
                        <span>Total Nómina</span>
                        <span>€8,450</span>
                    </div>
                    <div class="summary-item">
                        <span>Ya Pagado</span>
                        <span>€6,350</span>
                    </div>
                    <div class="summary-item">
                        <span>Pendiente</span>
                        <span>€2,100</span>
                    </div>
                    <div class="summary-item">
                        <span>Vencidos</span>
                        <span style="color: var(--danger);">€735</span>
                    </div>
                    <div class="summary-item">
                        <span>Por Procesar</span>
                        <span style="color: var(--primary);">€2,835</span>
                    </div>
                </div>

                <!-- Payroll Calendar -->
                <div class="payroll-calendar">
                    <h3 class="section-title">📅 Calendario Nómina</h3>
                    <div class="calendar-item">
                        <div class="date-info">
                            <div class="date-day">28 Feb</div>
                            <div class="date-description">Cierre nómina</div>
                        </div>
                        <span class="status-badge status-paid">✓</span>
                    </div>
                    <div class="calendar-item">
                        <div class="date-info">
                            <div class="date-day">1 Mar</div>
                            <div class="date-description">Inicio pagos</div>
                        </div>
                        <span class="status-badge status-pending">Hoy</span>
                    </div>
                    <div class="calendar-item">
                        <div class="date-info">
                            <div class="date-day">5 Mar</div>
                            <div class="date-description">Fecha límite</div>
                        </div>
                        <span class="status-badge status-overdue">!</span>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h3 class="section-title">⚡ Acciones Rápidas</h3>
                    <button class="btn action-btn" onclick="openModal('reportModal')">📊 Generar Reporte</button>
                    <button class="btn action-btn" onclick="exportPayroll()">📄 Exportar Nómina</button>
                    <button class="btn action-btn" onclick="openModal('settingsModal')">⚙️ Configuración</button>
                    <button class="btn btn-secondary action-btn" onclick="sendReminders()">📧 Enviar Recordatorios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Pago -->
    <div id="newPaymentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('newPaymentModal')">&times;</span>
            <h2 class="section-title">➕ Registrar Nuevo Pago</h2>
            
            <div class="form-group">
                <label>Empleado</label>
                <select id="employeeSelect">
                    <option value="">Seleccionar empleado...</option>
                    <option value="carlos">Carlos Martínez - Bartender</option>
                    <option value="maria">María Rodríguez - Camarera</option>
                    <option value="david">David López - Cocinero</option>
                    <option value="ana">Ana García - Gerente</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Salario Base</label>
                    <input type="number" id="baseSalary