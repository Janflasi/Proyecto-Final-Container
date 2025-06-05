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

// Funciones CRUD optimizadas
function getProductos($pdo, $categoria = null, $search = null) {
    $sql = "SELECT p.*, c.nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.activo = 1";
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
    return $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);
}

function addProducto($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO productos (nombre_producto, precio, stock, id_categoria) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$data['nombre'], $data['precio'], $data['stock'], $data['categoria']]);
}

function updateProducto($pdo, $id, $data) {
    $stmt = $pdo->prepare("UPDATE productos SET nombre_producto = ?, precio = ?, stock = ?, id_categoria = ? WHERE id_producto = ?");
    return $stmt->execute([$data['nombre'], $data['precio'], $data['stock'], $data['categoria'], $id]);
}

function deleteProducto($pdo, $id) {
    return $pdo->prepare("UPDATE productos SET activo = 0 WHERE id_producto = ?")->execute([$id]);
}

function getProductoById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Procesar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $response = ['success' => false, 'message' => 'Acción no válida'];
        
        switch ($_POST['action']) {
            case 'add':
                $response['success'] = addProducto($pdo, $_POST);
                $response['message'] = $response['success'] ? 'Producto agregado' : 'Error al agregar';
                break;
                
            case 'update':
                $response['success'] = updateProducto($pdo, $_POST['id'], $_POST);
                $response['message'] = $response['success'] ? 'Producto actualizado' : 'Error al actualizar';
                break;
                
            case 'delete':
                $response['success'] = deleteProducto($pdo, $_POST['id']);
                $response['message'] = $response['success'] ? 'Producto eliminado' : 'Error al eliminar';
                break;
                
            case 'get':
                $response = getProductoById($pdo, $_POST['id']);
                break;
        }
        
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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
    <title>Gestión de Productos - Container Bar</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
    :root{--primary:#FF6B35;--dark:#1A1A1A;--dark-light:#2A2A2A;--gray:#666;--white:#FFF;--accent:#4ECDC4;--shadow:0 10px 30px rgba(0,0,0,.3);--danger:#ef4444;--danger-light:rgba(239,68,68,.1)}
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:system-ui,sans-serif;background:linear-gradient(135deg,var(--dark) 0%,var(--dark-light) 100%);min-height:100vh;color:var(--white)}
    
    .header{background:rgba(26,26,26,.95);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,107,53,.2);padding:1.5rem 2rem;position:sticky;top:0;z-index:100}
    .header-content{max-width:1400px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
    .logo{display:flex;align-items:center;gap:1rem}
    .logo i{width:50px;height:50px;background:linear-gradient(135deg,var(--primary),#F7931E);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem}
    .logo h1{font-size:1.8rem;font-weight:700;color:var(--primary)}
    
    .search-container{position:relative;flex:1;max-width:300px}
    .search-input{width:100%;background:rgba(42,42,42,.8);border:1px solid rgba(255,107,53,.3);border-radius:25px;padding:12px 20px 12px 45px;color:var(--white);font-size:.95rem}
    .search-icon{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:var(--gray)}
    
    .btn-add{background:linear-gradient(135deg,var(--primary),#F7931E);border:0;border-radius:25px;padding:12px 25px;color:#fff;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;transition:transform .3s}
    .btn-add:hover{transform:translateY(-2px)}
    
    .main{padding:2rem;max-width:1400px;margin:0 auto}
    
    .filters{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
    .filter-chip{background:rgba(42,42,42,.8);border:1px solid rgba(255,107,53,.3);border-radius:20px;padding:8px 18px;color:var(--gray);cursor:pointer;transition:all .3s;font-size:.9rem}
    .filter-chip.active{background:linear-gradient(135deg,var(--primary),#F7931E);color:#fff;border-color:transparent}
    .filter-chip:hover:not(.active){border-color:var(--primary);background:rgba(255,107,53,.1)}
    
    .products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:2rem}
    .product-card{background:rgba(42,42,42,.8);border:1px solid rgba(255,107,53,.2);border-radius:20px;padding:1.5rem;transition:all .3s;position:relative;overflow:hidden}
    .product-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(135deg,var(--primary),#F7931E);opacity:0;transition:opacity .3s}
    .product-card:hover{transform:translateY(-5px);box-shadow:var(--shadow);border-color:var(--primary)}
    .product-card:hover::before{opacity:1}
    .product-card:hover .product-actions{opacity:1}
    
    .product-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem}
    .product-category{background:linear-gradient(135deg,var(--primary),#F7931E);color:#fff;padding:6px 12px;border-radius:15px;font-size:.8rem;font-weight:500}
    .product-actions{display:flex;gap:8px;opacity:0;transition:opacity .3s}
    .action-btn{width:35px;height:35px;border:0;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .3s;font-size:.9rem}
    .btn-edit{background:rgba(76,205,196,.2);color:var(--accent)}
    .btn-edit:hover{background:var(--accent);color:#fff;transform:scale(1.1)}
    .btn-delete{background:rgba(239,68,68,.2);color:#ef4444}
    .btn-delete:hover{background:#ef4444;color:#fff;transform:scale(1.1)}
    
    .product-name{font-size:1.3rem;font-weight:600;margin-bottom:1rem}
    .product-details{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    .detail-item{display:flex;flex-direction:column;gap:4px}
    .detail-label{font-size:.85rem;color:var(--gray);text-transform:uppercase;font-weight:500}
    .detail-value{font-size:1.1rem;font-weight:600}
    .price-value{color:#FFD23F;font-size:1.3rem}
    .stock-low{color:#ef4444}
    .stock-normal{color:var(--accent)}
    
    .modal{display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.8);align-items:center;justify-content:center;backdrop-filter:blur(5px)}
    .modal-content{background:var(--dark-light);padding:2rem;border-radius:20px;width:90%;max-width:500px;max-height:90vh;overflow-y:auto;border:1px solid rgba(255,107,53,.2);animation:modalSlide .3s ease-out}
    .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem}
    .close-btn{background:none;border:none;font-size:2rem;color:var(--gray);cursor:pointer;transition:color .3s}
    .close-btn:hover{color:var(--white)}
    
    /* Modal de confirmación personalizado */
    .confirm-modal{display:none;position:fixed;z-index:1100;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.9);align-items:center;justify-content:center;backdrop-filter:blur(10px)}
    .confirm-content{background:var(--dark-light);border-radius:25px;width:90%;max-width:450px;text-align:center;border:1px solid rgba(239,68,68,.3);animation:confirmSlide .4s ease-out;overflow:hidden;position:relative}
    .confirm-content::before{content:'';position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(135deg,var(--danger),#dc2626)}
    
    .confirm-icon{padding:3rem 2rem 1rem;color:var(--danger);font-size:4rem;animation:shake .6s ease-in-out}
    .confirm-text{padding:0 2rem 2rem;color:var(--white)}
    .confirm-title{font-size:1.5rem;font-weight:700;margin-bottom:1rem;color:var(--danger)}
    .confirm-message{font-size:1rem;color:var(--gray);line-height:1.5;margin-bottom:2rem}
    .product-info{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);border-radius:15px;padding:1rem;margin:1rem 0;text-align:left}
    .product-info-title{font-size:.9rem;color:var(--gray);margin-bottom:.5rem;text-transform:uppercase;font-weight:500}
    .product-info-name{font-size:1.1rem;font-weight:600;color:var(--white)}
    
    .confirm-actions{display:flex;gap:0;background:rgba(26,26,26,.5)}
    .confirm-btn{flex:1;padding:1.2rem;border:0;cursor:pointer;font-size:1rem;font-weight:600;transition:all .3s;position:relative}
    .confirm-btn:first-child{border-right:1px solid rgba(255,255,255,.1)}
    .btn-cancel{background:transparent;color:var(--gray)}
    .btn-cancel:hover{background:rgba(102,102,102,.2);color:var(--white)}
    .btn-confirm{background:transparent;color:var(--danger)}
    .btn-confirm:hover{background:var(--danger);color:#fff}
    
    @keyframes modalSlide{
        from{opacity:0;transform:translateY(-50px) scale(.9)}
        to{opacity:1;transform:translateY(0) scale(1)}
    }
    
    @keyframes confirmSlide{
        from{opacity:0;transform:translateY(-80px) scale(.8)}
        to{opacity:1;transform:translateY(0) scale(1)}
    }
    
    @keyframes shake{
        0%,100%{transform:translateX(0)}
        25%{transform:translateX(-10px)}
        75%{transform:translateX(10px)}
    }
    
    .form-group{margin-bottom:1.5rem}
    .form-label{display:block;margin-bottom:8px;font-weight:500}
    .form-input,.form-select{width:100%;background:rgba(26,26,26,.8);border:1px solid rgba(255,107,53,.3);border-radius:12px;padding:15px;color:var(--white);font-size:1rem;transition:all .3s}
    .form-input:focus,.form-select:focus{outline:0;border-color:var(--primary);box-shadow:0 0 0 3px rgba(255,107,53,.1)}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    .form-actions{display:flex;gap:1rem;justify-content:center;margin-top:2rem}
    
    .btn{padding:15px 30px;border:0;border-radius:12px;font-size:1rem;font-weight:600;cursor:pointer;transition:all .3s;display:flex;align-items:center;gap:8px}
    .btn-primary{background:linear-gradient(135deg,var(--primary),#F7931E);color:#fff}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(255,107,53,.3)}
    .btn-secondary{background:rgba(102,102,102,.2);color:var(--gray);border:1px solid rgba(102,102,102,.3)}
    .btn-secondary:hover{background:rgba(102,102,102,.3);color:var(--white)}
    
    .notification{position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:12px;color:#fff;font-weight:500;z-index:1000;transform:translateX(400px);transition:all .3s;display:flex;align-items:center;gap:10px;min-width:300px}
    .notification.show{transform:translateX(0)}
    .notification.success{background:linear-gradient(135deg,#10b981,#059669)}
    .notification.error{background:linear-gradient(135deg,#ef4444,#dc2626)}
    
    .empty-state{text-align:center;padding:4rem 2rem;color:var(--gray)}
    .empty-state i{font-size:4rem;margin-bottom:1rem;opacity:.5}
    .empty-state h3{font-size:1.5rem;margin-bottom:.5rem;color:var(--white)}
    
    @media (max-width:768px){
        .header-content{flex-direction:column}
        .search-container{max-width:100%}
        .main{padding:1rem}
        .products-grid{grid-template-columns:1fr}
        .form-row{grid-template-columns:1fr}
        .modal-content,.confirm-content{margin:1rem;width:calc(100% - 2rem)}
        .confirm-actions{flex-direction:column}
        .confirm-btn:first-child{border-right:none;border-bottom:1px solid rgba(255,255,255,.1)}
    }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-box"></i>
                <h1>Container Bar</h1>
            </div>
            
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Buscar productos..." id="searchInput" value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            
            <button class="btn-add" onclick="showModal()">
                <i class="fas fa-plus"></i>
                Agregar Producto
            </button>
        </div>
    </header>

    <main class="main">
        <div class="filters">
            <div class="filter-chip <?= $categoria_filtro === 'all' ? 'active' : '' ?>" onclick="filterBy('all')">
                <i class="fas fa-th-large"></i> Todos
            </div>
            <?php foreach ($categorias as $cat): ?>
            <div class="filter-chip <?= $categoria_filtro === $cat['nombre_categoria'] ? 'active' : '' ?>" 
                 onclick="filterBy('<?= htmlspecialchars($cat['nombre_categoria']) ?>')">
                <i class="fas fa-tag"></i> <?= htmlspecialchars($cat['nombre_categoria']) ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="products-grid">
            <?php if (empty($productos)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No hay productos</h3>
                <p>Agrega tu primer producto para comenzar</p>
            </div>
            <?php else: ?>
                <?php foreach ($productos as $p): ?>
                <div class="product-card">
                    <div class="product-header">
                        <div class="product-category">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($p['nombre_categoria']) ?>
                        </div>
                        <div class="product-actions">
                            <button class="action-btn btn-edit" onclick="editProduct(<?= $p['id_producto'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn btn-delete" onclick="confirmDelete(<?= $p['id_producto'] ?>, '<?= htmlspecialchars($p['nombre_producto'], ENT_QUOTES) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <h3 class="product-name"><?= htmlspecialchars($p['nombre_producto']) ?></h3>
                    
                    <div class="product-details">
                        <div class="detail-item">
                            <span class="detail-label">Precio</span>
                            <span class="detail-value price-value">$<?= number_format($p['precio'], 0) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Stock</span>
                            <span class="detail-value <?= $p['stock'] < 10 ? 'stock-low' : 'stock-normal' ?>">
                                <?= $p['stock'] ?> unidades
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal de formulario -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Agregar Producto</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form id="form">
                <input type="hidden" id="productId">
                
                <div class="form-group">
                    <label class="form-label">Nombre del Producto *</label>
                    <input type="text" class="form-input" id="nombre" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select class="form-select" id="categoria" required>
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Precio *</label>
                        <input type="number" class="form-input" id="precio" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock *</label>
                        <input type="number" class="form-input" id="stock" min="0" required>
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

    <!-- Modal de confirmación personalizado -->
    <div class="confirm-modal" id="confirmModal">
        <div class="confirm-content">
            <div class="confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="confirm-text">
                <h3 class="confirm-title">¿Eliminar producto?</h3>
                <p class="confirm-message">Esta acción no se puede deshacer. El producto será eliminado permanentemente del inventario.</p>
                <div class="product-info">
                    <div class="product-info-title">Producto a eliminar:</div>
                    <div class="product-info-name" id="productToDelete"></div>
                </div>
            </div>
            <div class="confirm-actions">
                <button class="confirm-btn btn-cancel" onclick="closeConfirmModal()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button class="confirm-btn btn-confirm" onclick="executeDelete()">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>

    <script>
    let editingId = null;
    let deleteProductId = null;

    function notify(msg, type = 'success') {
        const n = document.createElement('div');
        n.className = `notification ${type}`;
        n.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i><span>${msg}</span>`;
        document.body.appendChild(n);
        setTimeout(() => n.classList.add('show'), 100);
        setTimeout(() => {
            n.classList.remove('show');
            setTimeout(() => document.body.removeChild(n), 300);
        }, 3000);
    }

    // Búsqueda
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const search = this.value;
            const category = new URLSearchParams(window.location.search).get('categoria') || 'all';
            window.location.href = search ? `?categoria=${category}&search=${encodeURIComponent(search)}` : `?categoria=${category}`;
        }, 500);
    });

    function filterBy(categoria) {
        const search = new URLSearchParams(window.location.search).get('search') || '';
        window.location.href = search ? `?categoria=${categoria}&search=${encodeURIComponent(search)}` : `?categoria=${categoria}`;
    }

    function showModal() {
        document.getElementById('modalTitle').textContent = 'Agregar Producto';
        document.getElementById('form').reset();
        document.getElementById('productId').value = '';
        editingId = null;
        document.getElementById('modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('modal').style.display = 'none';
    }

    // Funciones del modal de confirmación
    function confirmDelete(id, productName) {
        deleteProductId = id;
        document.getElementById('productToDelete').textContent = productName;
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
        deleteProductId = null;
    }

    async function executeDelete() {
        if (!deleteProductId) return;
        
        try {
            const fd = new FormData();
            fd.append('action', 'delete');
            fd.append('id', deleteProductId);
            
            const res = await fetch(window.location.pathname, { method: 'POST', body: fd });
            const result = await res.json();
            
            closeConfirmModal();
            
            if (result.success) {
                notify(result.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                notify(result.message, 'error');
            }
        } catch (e) {
            closeConfirmModal();
            notify('Error al eliminar', 'error');
        }
    }

    async function editProduct(id) {
        try {
            const fd = new FormData();
            fd.append('action', 'get');
            fd.append('id', id);
            
            const res = await fetch(window.location.pathname, { method: 'POST', body: fd });
            const p = await res.json();
            
            if (p) {
                document.getElementById('modalTitle').textContent = 'Editar Producto';
                document.getElementById('productId').value = id;
                document.getElementById('nombre').value = p.nombre_producto || '';
                document.getElementById('categoria').value = p.id_categoria || '';
                document.getElementById('precio').value = p.precio || '';
                document.getElementById('stock').value = p.stock || '';
                editingId = id;
                document.getElementById('modal').style.display = 'flex';
            }
        } catch (e) {
            notify('Error al cargar producto', 'error');
        }
    }

    document.getElementById('form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nombre = document.getElementById('nombre').value.trim();
        const categoria = document.getElementById('categoria').value;
        const precio = document.getElementById('precio').value;
        const stock = document.getElementById('stock').value;
        
        if (!nombre || !categoria || !precio || !stock) {
            notify('Completa todos los campos', 'error');
            return;
        }
        
        try {
            const fd = new FormData();
            fd.append('action', editingId ? 'update' : 'add');
            fd.append('nombre', nombre);
            fd.append('categoria', categoria);
            fd.append('precio', precio);
            fd.append('stock', stock);
            if (editingId) fd.append('id', editingId);

            const res = await fetch(window.location.pathname, { method: 'POST', body: fd });
            const result = await res.json();
            
            if (result.success) {
                notify(result.message);
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                notify(result.message, 'error');
            }
        } catch (e) {
            notify('Error al guardar', 'error');
        }
    });

    // Eventos globales
    document.addEventListener('keydown', e => { 
        if (e.key === 'Escape') {
            closeModal();
            closeConfirmModal();
        }
    });
    
    document.getElementById('modal').addEventListener('click', e => { 
        if (e.target.id === 'modal') closeModal(); 
    });
    
    document.getElementById('confirmModal').addEventListener('click', e => { 
        if (e.target.id === 'confirmModal') closeConfirmModal(); 
    });
    </script>
</body>
</html>