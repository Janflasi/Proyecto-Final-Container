<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Sistema Moderno</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    /* Estilos adicionales para el modal e imagen */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: var(--dark-light);
        padding: 2rem;
        border-radius: 20px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 2rem;
        color: var(--text-secondary);
        cursor: pointer;
    }

    .product-image {
        height: 120px;
        background: var(--dark);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1rem 0;
        color: var(--text-secondary);
        font-size: 2rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .stock-low {
        color: #ef4444;
    }

    .stock-normal {
        color: var(--accent);
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            margin: 1rem;
            width: calc(100% - 2rem);
        }
    }
    :root{--primary:#FF6B35;--primary-dark:#E55A2B;--secondary:#FFD23F;--accent:#4ECDC4;--dark:#1A1A1A;--dark-light:#2A2A2A;--gray:#666;--gray-light:#CCC;--white:#FFF;--gradient-main:linear-gradient(135deg,#FF6B35 0%,#F7931E 50%,#FFD23F 100%);--gradient-dark:linear-gradient(135deg,#1A1A1A 0%,#2A2A2A 50%,#3A3A3A 100%);--shadow:0 10px 30px rgba(0,0,0,.3);--shadow-light:0 5px 15px rgba(0,0,0,.1)}*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:var(--gradient-dark);min-height:100vh;color:var(--white);overflow-x:hidden}.app-container{min-height:100vh;display:flex;flex-direction:column}.header{background:rgba(26,26,26,.95);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,107,53,.2);padding:1.5rem 2rem;position:sticky;top:0;z-index:100}.header-content{max-width:1400px;margin:0 auto;display:flex;justify-content:space-between;align-items:center}.logo-section{display:flex;align-items:center;gap:1rem}.logo{width:50px;height:50px;background:var(--gradient-main);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;box-shadow:var(--shadow-light)}.logo-text h1{font-size:1.8rem;font-weight:700;background:var(--gradient-main);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}.logo-text p{color:var(--gray-light);font-size:.9rem;font-weight:400}.header-actions{display:flex;gap:1rem;align-items:center}.search-container{position:relative}.search-input{background:rgba(42,42,42,.8);border:1px solid rgba(255,107,53,.3);border-radius:25px;padding:12px 20px 12px 45px;color:var(--white);font-size:.95rem;width:300px;transition:all .3s ease}.search-input:focus{outline:0;border-color:var(--primary);box-shadow:0 0 0 3px rgba(255,107,53,.1);width:350px}.search-icon{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:var(--gray);font-size:1rem}.btn-add-product{background:var(--gradient-main);border:0;border-radius:25px;padding:12px 25px;color:#fff;font-weight:600;font-size:.95rem;cursor:pointer;display:flex;align-items:center;gap:8px;transition:all .3s ease;box-shadow:var(--shadow-light)}.btn-add-product:hover{transform:translateY(-2px);box-shadow:var(--shadow)}.main-content{flex:1;padding:2rem;max-width:1400px;margin:0 auto;width:100%}.nav-tabs{display:flex;gap:1rem;margin-bottom:2rem;background:rgba(42,42,42,.5);padding:8px;border-radius:15px;backdrop-filter:blur(10px)}.nav-tab{padding:12px 25px;border:0;background:0 0;color:var(--gray-light);border-radius:10px;cursor:pointer;font-weight:500;transition:all .3s ease;display:flex;align-items:center;gap:8px}.nav-tab.active{background:var(--gradient-main);color:#fff;box-shadow:var(--shadow-light)}.nav-tab:hover:not(.active){background:rgba(255,107,53,.1);color:var(--primary)}.filters-section{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}.filter-chip{background:rgba(42,42,42,.8);border:1px solid rgba(255,107,53,.3);border-radius:20px;padding:8px 18px;color:var(--gray-light);cursor:pointer;transition:all .3s ease;font-size:.9rem;display:flex;align-items:center;gap:8px}.filter-chip.active{background:var(--gradient-main);color:#fff;border-color:transparent}.filter-chip:hover:not(.active){border-color:var(--primary);background:rgba(255,107,53,.1)}.products-section{display:none}.products-section.active{display:block}.products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:2rem;margin-top:2rem}.product-card{background:rgba(42,42,42,.8);border:1px solid rgba(255,107,53,.2);border-radius:20px;padding:1.5rem;transition:all .3s ease;backdrop-filter:blur(10px);position:relative;overflow:hidden}.product-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--gradient-main);opacity:0;transition:opacity .3s ease}.product-card:hover{transform:translateY(-5px);box-shadow:var(--shadow);border-color:var(--primary)}.product-card:hover::before{opacity:1}.product-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem}.product-category{background:var(--gradient-main);color:#fff;padding:6px 12px;border-radius:15px;font-size:.8rem;font-weight:500;display:flex;align-items:center;gap:6px}.product-actions{display:flex;gap:8px;opacity:0;transition:opacity .3s ease}.product-card:hover .product-actions{opacity:1}.action-btn{width:35px;height:35px;border:0;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .3s ease;font-size:.9rem}.btn-edit{background:rgba(76,205,196,.2);color:var(--accent)}.btn-edit:hover{background:var(--accent);color:#fff;transform:scale(1.1)}.btn-delete{background:rgba(239,68,68,.2);color:#ef4444}.btn-delete:hover{background:#ef4444;color:#fff;transform:scale(1.1)}.product-name{font-size:1.3rem;font-weight:600;color:var(--white);margin-bottom:1rem;line-height:1.3}.product-details{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.detail-item{display:flex;flex-direction:column;gap:4px}.detail-label{font-size:.85rem;color:var(--gray);text-transform:uppercase;font-weight:500;letter-spacing:.5px}.detail-value{font-size:1.1rem;font-weight:600;color:var(--white)}.price-value{color:var(--secondary);font-size:1.3rem}.stock-low{color:#ef4444}.stock-normal{color:var(--accent)}.add-product-section{display:none}.add-product-section.active{display:block}.form-container{background:rgba(42,42,42,.8);border:1px solid rgba(255,107,53,.2);border-radius:20px;padding:2rem;max-width:600px;margin:0 auto;backdrop-filter:blur(10px)}.form-title{font-size:1.8rem;font-weight:700;margin-bottom:2rem;text-align:center;background:var(--gradient-main);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}.form-group{margin-bottom:1.5rem}.form-label{display:block;margin-bottom:8px;font-weight:500;color:var(--white)}.form-input,.form-select{width:100%;background:rgba(26,26,26,.8);border:1px solid rgba(255,107,53,.3);border-radius:12px;padding:15px;color:var(--white);font-size:1rem;transition:all .3s ease}.form-input:focus,.form-select:focus{outline:0;border-color:var(--primary);box-shadow:0 0 0 3px rgba(255,107,53,.1)}.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.form-actions{display:flex;gap:1rem;justify-content:center;margin-top:2rem}.btn{padding:15px 30px;border:0;border-radius:12px;font-size:1rem;font-weight:600;cursor:pointer;transition:all .3s ease;display:flex;align-items:center;gap:8px}.btn-primary{background:var(--gradient-main);color:#fff;box-shadow:var(--shadow-light)}.btn-primary:hover{transform:translateY(-2px);box-shadow:var(--shadow)}.btn-secondary{background:rgba(102,102,102,.2);color:var(--gray-light);border:1px solid rgba(102,102,102,.3)}.btn-secondary:hover{background:rgba(102,102,102,.3);color:var(--white)}.empty-state{text-align:center;padding:4rem 2rem;color:var(--gray-light)}.empty-state i{font-size:4rem;margin-bottom:1rem;opacity:.5}.empty-state h3{font-size:1.5rem;margin-bottom:.5rem;color:var(--white)}.loading{display:flex;justify-content:center;align-items:center;padding:4rem}.spinner{width:50px;height:50px;border:4px solid rgba(255,107,53,.2);border-top:4px solid var(--primary);border-radius:50%;animation:spin 1s linear infinite}@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}.notification{position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:12px;color:#fff;font-weight:500;z-index:1000;transform:translateX(400px);transition:all .3s ease;display:flex;align-items:center;gap:10px;min-width:300px}.notification.show{transform:translateX(0)}.notification.success{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 10px 30px rgba(16,185,129,.3)}.notification.error{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 10px 30px rgba(239,68,68,.3)}@media (max-width:768px){.header-content{flex-direction:column;gap:1rem}.search-input{width:100%}.search-input:focus{width:100%}.main-content{padding:1rem}.products-grid{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}.filters-section{justify-content:center}}
    </style>
</head>
<?php
// config.php - Configuración de base de datos
$host = 'localhost';
$dbname = 'container_bar';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Funciones CRUD
function getProductos($pdo, $categoria = null, $search = null) {
    $sql = "SELECT p.*, c.nombre_categoria FROM productos p 
            JOIN categorias c ON p.id_categoria = c.id_categoria 
            WHERE p.activo = 1";
    
    $params = [];
    if ($categoria && $categoria != 'all') {
        $sql .= " AND c.nombre_categoria = ?";
        $params[] = $categoria;
    }
    if ($search) {
        $sql .= " AND p.nombre_producto LIKE ?";
        $params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategorias($pdo) {
    $stmt = $pdo->query("SELECT * FROM categorias");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addProducto($pdo, $data) {
    $sql = "INSERT INTO productos (nombre_producto, precio, stock, id_categoria) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$data['nombre'], $data['precio'], $data['stock'], $data['categoria']]);
}

function updateProducto($pdo, $id, $data) {
    $sql = "UPDATE productos SET nombre_producto = ?, precio = ?, stock = ?, id_categoria = ? WHERE id_producto = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$data['nombre'], $data['precio'], $data['stock'], $data['categoria'], $id]);
}

function deleteProducto($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id_producto = ?");
    return $stmt->execute([$id]);
}

function getProductoById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Procesar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'add':
            $success = addProducto($pdo, $_POST);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'update':
            $success = updateProducto($pdo, $_POST['id'], $_POST);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'delete':
            $success = deleteProducto($pdo, $_POST['id']);
            echo json_encode(['success' => $success]);
            exit;
            
        case 'get':
            $producto = getProductoById($pdo, $_POST['id']);
            echo json_encode($producto);
            exit;
    }
}

// Obtener datos para la vista
$categoria_filtro = $_GET['categoria'] ?? 'all';
$busqueda = $_GET['search'] ?? '';
$productos = getProductos($pdo, $categoria_filtro, $busqueda);
$categorias = getCategorias($pdo);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Container Bar - Gestión de Productos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/Aajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- Tu CSS existente -->
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
             <div class="logo-section">
    <div class="logo">
    <a href="/admin">
        <img src="/assets/unnamed.png" alt="Logo" style="width: 50px; height: 50px;">
    </a>
</div>

    <div class="logo-text">
        <h1>Container Bar</h1>
        <p>Sistema de Gestión</p>
    </div>
</div>

                
                <div class="header-actions">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Buscar productos..." id="searchInput">
                    </div>
                    <button class="btn-add-product" onclick="showAddModal()">
                        <i class="fas fa-plus"></i>
                        Agregar Producto
                    </button>
                </div>
            </div>
        </header>

        <!-- Filtros de categoría -->
        <div class="filters-section">
            <div class="filter-chip <?= $categoria_filtro === 'all' ? 'active' : '' ?>" onclick="filterByCategory('all')">
                <i class="fas fa-th-large"></i> Todos
            </div>
            <?php foreach ($categorias as $cat): ?>
            <div class="filter-chip <?= $categoria_filtro === $cat['nombre_categoria'] ? 'active' : '' ?>" 
                 onclick="filterByCategory('<?= $cat['nombre_categoria'] ?>')">
                <i class="fas fa-tag"></i> <?= $cat['nombre_categoria'] ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Grid de productos -->
        <div class="products-grid" id="productsGrid">
            <?php if (empty($productos)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No hay productos</h3>
                <p>Agrega tu primer producto</p>
            </div>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                <div class="product-card" data-id="<?= $producto['id_producto'] ?>">
                    <div class="product-header">
                        <div class="product-category">
                            <i class="fas fa-tag"></i>
                            <?= $producto['nombre_categoria'] ?>
                        </div>
                        <div class="product-actions">
                            <button class="action-btn btn-edit" onclick="editProduct(<?= $producto['id_producto'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn btn-delete" onclick="deleteProduct(<?= $producto['id_producto'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="product-image">
                        <i class="fas fa-image"></i>
                    </div>
                    
                    <h3 class="product-name"><?= htmlspecialchars($producto['nombre_producto']) ?></h3>
                    
                    <div class="product-details">
                        <div class="detail-item">
                            <span class="detail-label">Precio</span>
                            <span class="detail-value price-value">$<?= number_format($producto['precio'], 0) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Stock</span>
                            <span class="detail-value <?= $producto['stock'] < 10 ? 'stock-low' : 'stock-normal' ?>">
                                <?= $producto['stock'] ?> unidades
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para agregar/editar producto -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Agregar Producto</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="productForm">
                <input type="hidden" id="productId">
                
                <div class="form-group">
                    <label>Nombre del Producto *</label>
                    <input type="text" id="nombre" required>
                </div>

                <div class="form-group">
                    <label>Categoría *</label>
                    <select id="categoria" required>
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= $cat['nombre_categoria'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Precio *</label>
                        <input type="number" id="precio" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" id="stock" min="0" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let editingId = null;

    // Búsqueda en tiempo real
    document.getElementById('searchInput').addEventListener('input', function() {
        const search = this.value;
        window.location.href = `?search=${encodeURIComponent(search)}`;
    });

    // Filtrar por categoría
    function filterByCategory(categoria) {
        window.location.href = `?categoria=${categoria}`;
    }

    // Mostrar modal para agregar
    function showAddModal() {
        document.getElementById('modalTitle').textContent = 'Agregar Producto';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        editingId = null;
        document.getElementById('productModal').style.display = 'flex';
    }

    // Cerrar modal
    function closeModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    // Editar producto
    async function editProduct(id) {
        try {
            const formData = new FormData();
            formData.append('action', 'get');
            formData.append('id', id);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const producto = await response.json();
            
            document.getElementById('modalTitle').textContent = 'Editar Producto';
            document.getElementById('productId').value = id;
            document.getElementById('nombre').value = producto.nombre_producto;
            document.getElementById('categoria').value = producto.id_categoria;
            document.getElementById('precio').value = producto.precio;
            document.getElementById('stock').value = producto.stock;
            editingId = id;
            document.getElementById('productModal').style.display = 'flex';
        } catch (error) {
            alert('Error al cargar el producto');
        }
    }

    // Eliminar producto
    async function deleteProduct(id) {
        if (!confirm('¿Eliminar este producto?')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                alert('Error al eliminar');
            }
        } catch (error) {
            alert('Error de conexión');
        }
    }

    // Enviar formulario
    document.getElementById('productForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('action', editingId ? 'update' : 'add');
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('categoria', document.getElementById('categoria').value);
        formData.append('precio', document.getElementById('precio').value);
        formData.append('stock', document.getElementById('stock').value);
        
        if (editingId) {
            formData.append('id', editingId);
        }

        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                alert('Error al guardar');
            }
        } catch (error) {
            alert('Error de conexión');
        }
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    </script>

    
</body>
</html>