<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas - Container Bar</title>
        <link rel="stylesheet" href="/style/style8.css">
    
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍻 Container Bar - Historial de Ventas</h1>
            <p>Gestiona y analiza todas las ventas de tu container bar con reportes detallados</p>
        </div>

        <!-- Filtros -->
        <div class="controls-section">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="dateFrom">Fecha Desde</label>
                    <input type="date" id="dateFrom" name="dateFrom">
                </div>
                <div class="filter-group">
                    <label for="dateTo">Fecha Hasta</label>
                    <input type="date" id="dateTo" name="dateTo">
                </div>
                <div class="filter-group">
                    <label for="product">Producto</label>
                    <select id="product" name="product">
                        <option value="">Todos los productos</option>
                        <option value="cerveza">Cerveza Artesanal</option>
                        <option value="cocktail">Cocktail Premium</option>
                        <option value="vino">Copa de Vino</option>
                        <option value="whisky">Whisky Nacional</option>
                        <option value="mojito">Mojito</option>
                        <option value="pisco">Pisco Sour</option>
                        <option value="nachos">Nachos</option>
                        <option value="alitas">Alitas BBQ</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="employee">Bartender</label>
                    <select id="employee" name="employee">
                        <option value="">Todos los bartenders</option>
                        <option value="juan">Juan Pérez</option>
                        <option value="maria">María García</option>
                        <option value="carlos">Carlos López</option>
                        <option value="ana">Ana Martínez</option>
                        <option value="luis">Luis Rodríguez</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Estado</label>
                    <select id="status" name="status">
                        <option value="">Todos los estados</option>
                        <option value="completed">Servida</option>
                        <option value="pending">Preparando</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="minAmount">Monto Mínimo</label>
                    <input type="number" id="minAmount" name="minAmount" placeholder="$0.00" step="0.01">
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn btn-primary" onclick="applyFilters()">
                    🔍 Aplicar Filtros
                </button>
                <button class="btn btn-secondary" onclick="exportData()">
                    📊 Exportar
                </button>
                <button class="btn btn-clear" onclick="clearFilters()">
                    🗑️ Limpiar
                </button>
            </div>
        </div>

        <!-- Reportes -->
        <div class="reports-section">
            <div class="report-card active" onclick="showReport('daily')">
                <h3>Ventas Diarias</h3>
                <div class="report-value" id="dailySales">$2,450</div>
                <div class="report-period">Hoy</div>
            </div>
            <div class="report-card" onclick="showReport('weekly')">
                <h3>Ventas Semanales</h3>
                <div class="report-value" id="weeklySales">$18,340</div>
                <div class="report-period">Esta semana</div>
            </div>
            <div class="report-card" onclick="showReport('monthly')">
                <h3>Ventas Mensuales</h3>
                <div class="report-value" id="monthlySales">$87,620</div>
                <div class="report-period">Este mes</div>
            </div>
        </div>

        <!-- Gráfico -->
        <div class="chart-container">
            <h3 id="chartTitle">📈 Ventas Diarias - Últimos 7 días</h3>
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>

        <!-- Tabla de Ventas -->
        <div class="sales-table-container">
            <div class="table-header">
                <h2>Registro de Órdenes</h2>
                <div class="table-actions">
                    <button class="btn btn-secondary btn-sm" onclick="refreshData()">
                        🔄 Actualizar
                    </button>
                </div>
            </div>

            <div class="loading" id="loading">
                <p>Cargando datos...</p>
            </div>

            <table class="sales-table" id="salesTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">ID Orden</th>
                        <th onclick="sortTable(1)">Fecha</th>
                        <th onclick="sortTable(2)">Mesa</th>
                        <th onclick="sortTable(3)">Producto</th>
                        <th onclick="sortTable(4)">Bartender</th>
                        <th onclick="sortTable(5)">Cantidad</th>
                        <th onclick="sortTable(6)">Total</th>
                        <th onclick="sortTable(7)">Estado</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    <!-- Los datos se cargarán aquí -->
                </tbody>
            </table>

            <div class="pagination" id="pagination">
                <!-- La paginación se generará aquí -->
            </div>
        </div>
    </div>

    <script>
        // Datos de ejemplo para container bar
        let salesData = [
            {id: 'O001', date: '2025-05-23', mesa: 'Mesa 1', product: 'Cerveza Artesanal', employee: 'María García', quantity: 3, total: 45.00, status: 'completed'},
            {id: 'O002', date: '2025-05-23', mesa: 'Mesa 5', product: 'Cocktail Premium', employee: 'Juan Pérez', quantity: 2, total: 32.00, status: 'completed'},
            {id: 'O003', date: '2025-05-23', mesa: 'Barra', product: 'Whisky Nacional', employee: 'Carlos López', quantity: 1, total: 18.00, status: 'pending'},
            {id: 'O004', date: '2025-05-22', mesa: 'Mesa 3', product: 'Mojito', employee: 'Ana Martínez', quantity: 4, total: 56.00, status: 'completed'},
            {id: 'O005', date: '2025-05-22', mesa: 'Mesa 7', product: 'Pisco Sour', employee: 'Luis Rodríguez', quantity: 2, total: 24.00, status: 'cancelled'},
            {id: 'O006', date: '2025-05-21', mesa: 'Mesa 2', product: 'Copa de Vino', employee: 'María García', quantity: 2, total: 28.00, status: 'completed'},
            {id: 'O007', date: '2025-05-21', mesa: 'Mesa 4', product: 'Nachos', employee: 'Juan Pérez', quantity: 1, total: 12.00, status: 'completed'},
            {id: 'O008', date: '2025-05-20', mesa: 'Mesa 6', product: 'Alitas BBQ', employee: 'Carlos López', quantity: 2, total: 26.00, status: 'completed'},
            {id: 'O009', date: '2025-05-20', mesa: 'Barra', product: 'Cerveza Artesanal', employee: 'Ana Martínez', quantity: 5, total: 75.00, status: 'completed'},
            {id: 'O010', date: '2025-05-19', mesa: 'Mesa 8', product: 'Cocktail Premium', employee: 'Luis Rodríguez', quantity: 3, total: 48.00, status: 'pending'},
            {id: 'O011', date: '2025-05-19', mesa: 'Mesa 1', product: 'Mojito', employee: 'María García', quantity: 2, total: 28.00, status: 'completed'},
            {id: 'O012', date: '2025-05-18', mesa: 'Mesa 9', product: 'Pisco Sour', employee: 'Juan Pérez', quantity: 6, total: 72.00, status: 'completed'},
            {id: 'O013', date: '2025-05-18', mesa: 'Barra', product: 'Whisky Nacional', employee: 'Carlos López', quantity: 2, total: 36.00, status: 'completed'},
            {id: 'O014', date: '2025-05-17', mesa: 'Mesa 10', product: 'Copa de Vino', employee: 'Ana Martínez', quantity: 4, total: 56.00, status: 'completed'},
            {id: 'O015', date: '2025-05-17', mesa: 'Mesa 2', product: 'Alitas BBQ', employee: 'Luis Rodríguez', quantity: 3, total: 39.00, status: 'completed'},
            {id: 'O016', date: '2025-05-16', mesa: 'Mesa 3', product: 'Cerveza Artesanal', employee: 'María García', quantity: 4, total: 60.00, status: 'completed'},
            {id: 'O017', date: '2025-05-16', mesa: 'Barra', product: 'Cocktail Premium', employee: 'Juan Pérez', quantity: 1, total: 16.00, status: 'completed'},
            {id: 'O018', date: '2025-05-15', mesa: 'Mesa 5', product: 'Mojito', employee: 'Carlos López', quantity: 3, total: 42.00, status: 'completed'},
            {id: 'O019', date: '2025-05-15', mesa: 'Mesa 7', product: 'Pisco Sour', employee: 'Ana Martínez', quantity: 2, total: 24.00, status: 'completed'},
            {id: 'O020', date: '2025-05-14', mesa: 'Mesa 1', product: 'Copa de Vino', employee: 'Luis Rodríguez', quantity: 3, total: 42.00, status: 'completed'}
        ];

        let filteredData = [...salesData];
        let currentPage = 1;
        let itemsPerPage = 10;
        let sortColumn = 1;
        let sortDirection = 'desc';
        let currentReport = 'daily';

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            setDefaultDates();
            renderTable();
            updateReports();
            drawChart();
        });

        function setDefaultDates() {
            const today = new Date();
            const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            
            document.getElementById('dateFrom').value = lastWeek.toISOString().split('T')[0];
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];
        }

        function applyFilters() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            const product = document.getElementById('product').value;
            const employee = document.getElementById('employee').value;
            const status = document.getElementById('status').value;
            const minAmount = parseFloat(document.getElementById('minAmount').value) || 0;

            filteredData = salesData.filter(sale => {
                const saleDate = new Date(sale.date);
                const fromDate = dateFrom ? new Date(dateFrom) : new Date('1900-01-01');
                const toDate = dateTo ? new Date(dateTo) : new Date('2100-12-31');

                return (!dateFrom || saleDate >= fromDate) &&
                       (!dateTo || saleDate <= toDate) &&
                       (!product || sale.product.toLowerCase().includes(product)) &&
                       (!employee || sale.employee.toLowerCase().includes(employee)) &&
                       (!status || sale.status === status) &&
                       (sale.total >= minAmount);
            });

            currentPage = 1;
            renderTable();
            updateReports();
            drawChart();
        }

        function clearFilters() {
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            document.getElementById('product').value = '';
            document.getElementById('employee').value = '';
            document.getElementById('status').value = '';
            document.getElementById('minAmount').value = '';
            
            filteredData = [...salesData];
            currentPage = 1;
            setDefaultDates();
            renderTable();
            updateReports();
            drawChart();
        }

        function renderTable() {
            const tbody = document.getElementById('salesTableBody');
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageData = filteredData.slice(startIndex, endIndex);

            if (pageData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="no-results">
                            <h3>No se encontraron resultados</h3>
                            <p>Intenta ajustar los filtros de búsqueda</p>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = pageData.map(sale => `
                    <tr>
                        <td><strong>${sale.id}</strong></td>
                        <td>${formatDate(sale.date)}</td>
                        <td><span class="mesa-badge">🪑 ${sale.mesa}</span></td>
                        <td>${sale.product}</td>
                        <td>${sale.employee}</td>
                        <td>${sale.quantity}</td>
                        <td class="amount">$${sale.total.toFixed(2)}</td>
                        <td><span class="status-badge status-${sale.status}">${getStatusText(sale.status)}</span></td>
                    </tr>
                `).join('');
            }

            renderPagination();
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            const pagination = document.getElementById('pagination');

            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let paginationHTML = `
                <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    ‹ Anterior
                </button>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    paginationHTML += `
                        <button onclick="changePage(${i})" ${i === currentPage ? 'class="active"' : ''}>
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    paginationHTML += '<span>...</span>';
                }
            }

            paginationHTML += `
                <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    Siguiente ›
                </button>
            `;

            pagination.innerHTML = paginationHTML;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                renderTable();
            }
        }

        function sortTable(column) {
            const columns = ['id', 'date', 'mesa', 'product', 'employee', 'quantity', 'total', 'status'];
            const columnKey = columns[column];

            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }

            filteredData.sort((a, b) => {
                let aVal = a[columnKey];
                let bVal = b[columnKey];

                if (columnKey === 'date') {
                    aVal = new Date(aVal);
                    bVal = new Date(bVal);
                } else if (columnKey === 'total' || columnKey === 'quantity') {
                    aVal = Number(aVal);
                    bVal = Number(bVal);
                } else {
                    aVal = String(aVal).toLowerCase();
                    bVal = String(bVal).toLowerCase();
                }

                if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            currentPage = 1;
            renderTable();
        }

        function showReport(period) {
            currentReport = period;
            document.querySelectorAll('.report-card').forEach(card => {
                card.classList.remove('active');
            });
            event.target.closest('.report-card').classList.add('active');
            
            drawChart();
        }

        function updateReports() {
            const today = new Date();
            const yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000);
            const weekStart = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);

            const dailySales = filteredData
                .filter(sale => new Date(sale.date).toDateString() === today.toDateString())
                .reduce((sum, sale) => sum + sale.total, 0);

            const weeklySales = filteredData
                .filter(sale => new Date(sale.date) >= weekStart)
                .reduce((sum, sale) => sum + sale.total, 0);

            const monthlySales = filteredData
                .filter(sale => new Date(sale.date) >= monthStart)
                .reduce((sum, sale) => sum + sale.total, 0);

            document.getElementById('dailySales').textContent = `${dailySales.toFixed(2)}`;
            document.getElementById('weeklySales').textContent = `${weeklySales.toFixed(2)}`;
            document.getElementById('monthlySales').textContent = `${monthlySales.toFixed(2)}`;
        }

        function drawChart() {
            const canvas = document.getElementById('salesChart');
            const ctx = canvas.getContext('2d');
            
            // Limpiar canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            let data = [];
            let labels = [];
            let title = '';

            if (currentReport === 'daily') {
                title = '📈 Ventas Diarias - Últimos 7 días';
                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    const dateStr = date.toISOString().split('T')[0];
                    const daySum = filteredData
                        .filter(sale => sale.date === dateStr)
                        .reduce((sum, sale) => sum + sale.total, 0);
                    
                    data.push(daySum);
                    labels.push(date.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric' }));
                }
            } else if (currentReport === 'weekly') {
                title = '📈 Ventas Semanales - Últimas 4 semanas';
                for (let i = 3; i >= 0; i--) {
                    const endDate = new Date();
                    endDate.setDate(endDate.getDate() - (i * 7));
                    const startDate = new Date(endDate);
                    startDate.setDate(startDate.getDate() - 6);
                    
                    const weekSum = filteredData
                        .filter(sale => {
                            const saleDate = new Date(sale.date);
                            return saleDate >= startDate && saleDate <= endDate;
                        })
                        .reduce((sum, sale) => sum + sale.total, 0);
                    
                    data.push(weekSum);
                    labels.push(`Sem ${4-i}`);
                }
            } else if (currentReport === 'monthly') {
                title = '📈 Ventas Mensuales - Últimos 6 meses';
                for (let i = 5; i >= 0; i--) {
                    const date = new Date();
                    date.setMonth(date.getMonth() - i);
                    const monthStart = new Date(date.getFullYear(), date.getMonth(), 1);
                    const monthEnd = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                    
                    const monthSum = filteredData
                        .filter(sale => {
                            const saleDate = new Date(sale.date);
                            return saleDate >= monthStart && saleDate <= monthEnd;
                        })
                        .reduce((sum, sale) => sum + sale.total, 0);
                    
                    data.push(monthSum);
                    labels.push(date.toLocaleDateString('es-ES', { month: 'short' }));
                }
            }

            document.getElementById('chartTitle').textContent = title;

            // Configuración del gráfico
            const padding = 50;
            const chartWidth = canvas.width - (padding * 2);
            const chartHeight = canvas.height - (padding * 2);
            const maxValue = Math.max(...data) || 100;
            const barWidth = chartWidth / data.length;

            // Fondo del gráfico
            ctx.fillStyle = '#2A2A2A';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Dibujar barras
            data.forEach((value, index) => {
                const barHeight = (value / maxValue) * chartHeight;
                const x = padding + (index * barWidth) + (barWidth * 0.1);
                const y = canvas.height - padding - barHeight;
                const width = barWidth * 0.8;

                // Gradiente para las barras
                const gradient = ctx.createLinearGradient(0, y, 0, y + barHeight);
                gradient.addColorStop(0, '#FF6B35');
                gradient.addColorStop(0.5, '#F7931E');
                gradient.addColorStop(1, '#FFD23F');

                ctx.fillStyle = gradient;
                ctx.fillRect(x, y, width, barHeight);

                // Valor en la parte superior de la barra
                ctx.fillStyle = '#FFFFFF';
                ctx.font = '12px Segoe UI';
                ctx.textAlign = 'center';
                ctx.fillText(`${value.toFixed(0)}`, x + width/2, y - 5);

                // Etiqueta en el eje X
                ctx.fillStyle = '#CCCCCC';
                ctx.font = '11px Segoe UI';
                ctx.fillText(labels[index], x + width/2, canvas.height - padding + 20);
            });

            // Líneas de grilla horizontales
            ctx.strokeStyle = '#444444';
            ctx.lineWidth = 1;
            for (let i = 0; i <= 5; i++) {
                const y = padding + (chartHeight / 5) * i;
                ctx.beginPath();
                ctx.moveTo(padding, y);
                ctx.lineTo(padding + chartWidth, y);
                ctx.stroke();

                // Etiquetas del eje Y
                const value = maxValue - (maxValue / 5) * i;
                ctx.fillStyle = '#CCCCCC';
                ctx.font = '10px Segoe UI';
                ctx.textAlign = 'right';
                ctx.fillText(`${value.toFixed(0)}`, padding - 10, y + 3);
            }
        }

        function exportData() {
            // Crear CSV
            const headers = ['ID Orden', 'Fecha', 'Mesa', 'Producto', 'Bartender', 'Cantidad', 'Total', 'Estado'];
            const csvContent = [
                headers.join(','),
                ...filteredData.map(sale => [
                    sale.id,
                    sale.date,
                    sale.mesa,
                    `"${sale.product}"`,
                    `"${sale.employee}"`,
                    sale.quantity,
                    sale.total,
                    sale.status
                ].join(','))
            ].join('\n');

            // Descargar archivo
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `ventas_container_bar_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Mostrar mensaje de éxito
            alert('📊 Datos exportados exitosamente!');
        }

        function refreshData() {
            const loading = document.getElementById('loading');
            loading.classList.add('show');
            
            // Simular carga de datos
            setTimeout(() => {
                loading.classList.remove('show');
                renderTable();
                updateReports();
                drawChart();
                alert('🔄 Datos actualizados correctamente!');
            }, 1000);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function getStatusText(status) {
            const statusMap = {
                'completed': 'Servida',
                'pending': 'Preparando',
                'cancelled': 'Cancelada'
            };
            return statusMap[status] || status;
        }

        // Eventos de teclado para filtros rápidos
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'f':
                        e.preventDefault();
                        document.getElementById('product').focus();
                        break;
                    case 'e':
                        e.preventDefault();
                        exportData();
                        break;
                    case 'r':
                        e.preventDefault();
                        refreshData();
                        break;
                }
            }
        });

    </script>
</body>
</html>