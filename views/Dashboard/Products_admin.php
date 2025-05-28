<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="/style/style7.css">
    
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="breadcrumb">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                    <i class="fas fa-chevron-right"></i>
                    <span>Productos</span>
                </div>
                <h1>Gestión de Productos</h1>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <div class="category-filters">
                <button class="filter-btn active" data-category="todos">
                    <i class="fas fa-list"></i> Todos
                </button>
                <button class="filter-btn" data-category="bebidas">
                    <i class="fas fa-glass-water"></i> Bebidas
                </button>
                <button class="filter-btn" data-category="alimentos">
                    <i class="fas fa-utensils"></i> Alimentos
                </button>
                <button class="filter-btn" data-category="insumos">
                    <i class="fas fa-boxes-stacked"></i> Insumos
                </button>
            </div>
            
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar productos..." id="searchInput">
                </div>
                <button class="btn-add" onclick="openModal('add')">
                    <i class="fas fa-plus"></i>
                    Agregar Producto
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
            <!-- Los productos se cargarán aquí dinámicamente -->
        </div>

        <!-- Empty State -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <i class="fas fa-box-open"></i>
            <h3>No se encontraron productos</h3>
            <p>Agrega tu primer producto para comenzar</p>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Agregar Producto</h2>
                <button class="btn-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="productForm">
                <div class="form-group">
                    <label class="form-label">Nombre del Producto</label>
                    <input type="text" class="form-input" id="productName" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-textarea" id="productDescription" placeholder="Descripción del producto..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" id="productCategory" required>
                            <option value="">Seleccionar categoría</option>
                            <option value="bebidas">Bebidas</option>
                            <option value="alimentos">Alimentos</option>
                            <option value="insumos">Insumos</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Precio</label>
                        <input type="number" class="form-input" id="productPrice" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-input" id="productStock" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-input" id="productMinStock" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Código/SKU</label>
                    <input type="text" class="form-input" id="productCode" placeholder="Código único del producto">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Estado de la aplicación
        let products = [
            {
                id: 1,
                name: "Coca Cola 350ml",
                description: "Bebida gaseosa sabor cola en lata de 350ml",
                category: "bebidas",
                price: 2.50,
                stock: 48,
                minStock: 20,
                code: "COC001"
            },
            {
                id: 2,
                name: "Hamburguesa Clásica",
                description: "Hamburguesa con carne, lechuga, tomate y queso",
                category: "alimentos",
                price: 12.50,
                stock: 15,
                minStock: 10,
                code: "HAM001"
            },
            {
                id: 3,
                name: "Servilletas",
                description: "Paquete de servilletas blancas x100 unidades",
                category: "insumos",
                price: 3.75,
                stock: 5,
                minStock: 15,
                code: "SER001"
            },
            {
                id: 4,
                name: "Agua Mineral 500ml",
                description: "Agua mineral natural embotellada",
                category: "bebidas",
                price: 1.75,
                stock: 32,
                minStock: 25,
                code: "AGU001"
            },
            {
                id: 5,
                name: "Pizza Margherita",
                description: "Pizza con salsa de tomate, mozzarella y albahaca",
                category: "alimentos",
                price: 18.90,
                stock: 8,
                minStock: 5,
                code: "PIZ001"
            }
        ];

        let currentFilter = 'todos';
        let editingProductId = null;

        // Inicializar la aplicación
        document.addEventListener('DOMContentLoaded', function() {
            renderProducts();
            setupEventListeners();
        });

        // Configurar event listeners
        function setupEventListeners() {
            // Filtros de categoría
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.category;
                    renderProducts();
                });
            });

            // Búsqueda
            document.getElementById('searchInput').addEventListener('input', function() {
                renderProducts();
            });

            // Formulario
            document.getElementById('productForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveProduct();
            });
        }

        // Renderizar productos
        function renderProducts() {
            const grid = document.getElementById('productsGrid');
            const emptyState = document.getElementById('emptyState');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            let filteredProducts = products.filter(product => {
                const matchesCategory = currentFilter === 'todos' || product.category === currentFilter;
                const matchesSearch = product.name.toLowerCase().includes(searchTerm) || 
                                    product.description.toLowerCase().includes(searchTerm) ||
                                    product.code.toLowerCase().includes(searchTerm);
                return matchesCategory && matchesSearch;
            });

            if (filteredProducts.length === 0) {
                grid.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';
            grid.innerHTML = filteredProducts.map(product => createProductCard(product)).join('');
        }

        // Crear tarjeta de producto
        function createProductCard(product) {
            const stockLevel = getStockLevel(product.stock, product.minStock);
            const categoryIcon = getCategoryIcon(product.category);
            
            return `
                <div class="product-card">
                    <div class="product-header">
                        <div class="product-category category-${product.category}">
                            <i class="${categoryIcon}"></i>
                            ${product.category.charAt(0).toUpperCase() + product.category.slice(1)}
                        </div>
                        <div class="product-actions">
                            <button class="action-btn btn-edit" onclick="editProduct(${product.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn btn-delete" onclick="deleteProduct(${product.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    
                    <div class="product-details">
                        <div class="detail-item">
                            <span class="detail-label">Precio</span>
                            <span class="detail-value">$${product.price.toFixed(2)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Código</span>
                            <span class="detail-value">${product.code}</span>
                        </div>
                    </div>
                    
                    <div class="product-stock">
                        <div class="stock-info">
                            <div class="stock-indicator ${stockLevel.class}"></div>
                            <span>Stock: ${product.stock} unidades</span>
                        </div>
                        <span style="font-size: 0.8rem; color: var(--gray-light);">
                            Min: ${product.minStock}
                        </span>
                    </div>
                </div>
            `;
        }

        // Obtener nivel de stock
        function getStockLevel(stock, minStock) {
            if (stock <= minStock) return { class: 'stock-low', label: 'Bajo' };
            if (stock <= minStock * 2) return { class: 'stock-medium', label: 'Medio' };
            return { class: 'stock-high', label: 'Alto' };
        }

        // Obtener icono de categoría
        function getCategoryIcon(category) {
            const icons = {
                bebidas: 'fas fa-glass-water',
                alimentos: 'fas fa-utensils',
                insumos: 'fas fa-boxes-stacked'
            };
            return icons[category] || 'fas fa-box';
        }

        // Abrir modal
        function openModal(mode, productId = null) {
            const modal = document.getElementById('productModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('productForm');
            
            editingProductId = productId;
            
            if (mode === 'add') {
                title.textContent = 'Agregar Producto';
                form.reset();
            } else if (mode === 'edit' && productId) {
                title.textContent = 'Editar Producto';
                const product = products.find(p => p.id === productId);
                if (product) {
                    document.getElementById('productName').value = product.name;
                    document.getElementById('productDescription').value = product.description;
                    document.getElementById('productCategory').value = product.category;
                    document.getElementById('productPrice').value = product.price;
                    document.getElementById('productStock').value = product.stock;
                    document.getElementById('productMinStock').value = product.minStock;
                    document.getElementById('productCode').value = product.code;
                }
            }
            
            modal.classList.add('active');
        }

        // Cerrar modal
        function closeModal() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('active');
            editingProductId = null;
        }

        // Guardar producto
        function saveProduct() {
            const formData = {
                name: document.getElementById('productName').value,
                description: document.getElementById('productDescription').value,
                category: document.getElementById('productCategory').value,
                price: parseFloat(document.getElementById('productPrice').value),
                stock: parseInt(document.getElementById('productStock').value),
                minStock: parseInt(document.getElementById('productMinStock').value),
                code: document.getElementById('productCode').value
            };

            if (editingProductId) {
                // Editar producto existente
                const index = products.findIndex(p => p.id === editingProductId);
                if (index !== -1) {
                    products[index] = { ...products[index], ...formData };
                }
            } else {
                // Agregar nuevo producto
                const newProduct = {
                    id: Date.now(), // ID simple para demo
                    ...formData
                };
                products.push(newProduct);
            }

            renderProducts();
            closeModal();
        }

        // Editar producto
        function editProduct(id) {
            openModal('edit', id);
        }

        // Eliminar producto
        function deleteProduct(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
                products = products.filter(p => p.id !== id);
                renderProducts();
            }
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>