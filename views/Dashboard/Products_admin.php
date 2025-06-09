<?php
// Configuración y conexión
$pdo = new PDO("mysql:host=localhost;dbname=container_bar;charset=utf8", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

function uploadImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido');
    }
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('El archivo es demasiado grande');
    }
    
    // CAMBIO: Crear carpeta en la raíz del proyecto (mismo nivel que este archivo PHP)
    $uploadDir = 'images/productos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // CAMBIO: Retornar la ruta tal como se mostrará en el HTML
        return 'images/productos/' . $filename;
    }
    
    throw new Exception('Error al subir la imagen');
}

// Funciones CRUD
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

function crudProducto($pdo, $action, $data, $id = null, $imagen = null) {
    $queries = [
        'add' => "INSERT INTO productos (nombre_producto, precio, stock, id_categoria, imagen) VALUES (?, ?, ?, ?, ?)",
        'update' => $imagen ? "UPDATE productos SET nombre_producto = ?, precio = ?, stock = ?, id_categoria = ?, imagen = ? WHERE id_producto = ?" : "UPDATE productos SET nombre_producto = ?, precio = ?, stock = ?, id_categoria = ? WHERE id_producto = ?",
        'delete' => "UPDATE productos SET activo = 0 WHERE id_producto = ?",
        'get' => "SELECT * FROM productos WHERE id_producto = ?"
    ];
    
    $stmt = $pdo->prepare($queries[$action]);
    $params = match($action) {
        'add' => [$data['nombre'], $data['precio'], $data['stock'], $data['categoria'], $imagen],
        'update' => $imagen ? [$data['nombre'], $data['precio'], $data['stock'], $data['categoria'], $imagen, $id] : [$data['nombre'], $data['precio'], $data['stock'], $data['categoria'], $id],
        'delete', 'get' => [$id]
    };
    
    $stmt->execute($params);
    return $action === 'get' ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->rowCount() > 0;
}

// Procesamiento AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    try {
        $action = $_POST['action'];
        $imagen = null;
        
        // Procesar imagen si existe
        if (($action === 'add' || $action === 'update') && isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $imagen = uploadImage($_FILES['imagen']);
        }
        
        $result = match($action) {
            'add', 'update' => ['success' => crudProducto($pdo, $action, $_POST, $_POST['id'] ?? null, $imagen), 'message' => ucfirst($action) . ' ' . (crudProducto($pdo, $action, $_POST, $_POST['id'] ?? null, $imagen) ? 'exitoso' : 'fallido')],
            'delete' => ['success' => crudProducto($pdo, $action, [], $_POST['id']), 'message' => 'Producto ' . (crudProducto($pdo, $action, [], $_POST['id']) ? 'eliminado' : 'no eliminado')],
            'get' => crudProducto($pdo, $action, [], $_POST['id']),
            default => ['success' => false, 'message' => 'Acción inválida']
        };
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Obtener datos
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
    <link rel="stylesheet" href="/style/style10.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/admin" class="logo-link">
                <div class="logo">
                    <img src="/assets/unnamed.png" alt="Logo Container Bar" class="logo-img">
                    <h1>Container Bar</h1>
                </div>
            </a>
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Buscar productos..." id="searchInput" value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            <button class="btn-add" onclick="showModal()">
                <i class="fas fa-plus"></i>Agregar Producto
            </button>
        </div>
    </header>

    <main class="main">
        <div class="filters">
            <div class="filter-chip <?= $categoria_filtro === 'all' ? 'active' : '' ?>" onclick="filterBy('all')">
                <i class="fas fa-th-large"></i> Todos
            </div>
            <?php foreach ($categorias as $cat): ?>
            <div class="filter-chip <?= $categoria_filtro === $cat['nombre_categoria'] ? 'active' : '' ?>" onclick="filterBy('<?= htmlspecialchars($cat['nombre_categoria']) ?>')">
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
            <?php else: foreach ($productos as $p): ?>
                <div class="product-card">
                    <?php if (!empty($p['imagen'])): ?>
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre_producto']) ?>" onerror="this.style.display='none'">
                    </div>
                    <?php endif; ?>
                    <div class="product-header">
                        <div class="product-category">
                            <i class="fas fa-tag"></i><?= htmlspecialchars($p['nombre_categoria']) ?>
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
            <?php endforeach; endif; ?>
        </div>
    </main>

    <!-- Modal de formulario -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Agregar Producto</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form id="form" enctype="multipart/form-data">
                <input type="hidden" id="productId">
                <div class="form-group">
                    <label class="form-label">Imagen del Producto</label>
                    <div class="image-upload-container">
                        <input type="file" id="imagen" name="imagen" accept="image/*" class="file-input" onchange="previewImage(this)">
                        <label for="imagen" class="file-label">
                            <i class="fas fa-camera"></i>
                            <span>Seleccionar imagen</span>
                        </label>
                        <div class="image-preview" id="imagePreview" style="display: none;">
                            <img id="previewImg" src="" alt="Vista previa">
                            <button type="button" class="remove-image" onclick="removeImage()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
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

    <!-- Modal de confirmación -->
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
    let editingId = null, deleteProductId = null, searchTimeout;

    const notify = (msg, type = 'success') => {
        const n = document.createElement('div');
        n.className = `notification ${type}`;
        n.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i><span>${msg}</span>`;
        document.body.appendChild(n);
        setTimeout(() => n.classList.add('show'), 100);
        setTimeout(() => {
            n.classList.remove('show');
            setTimeout(() => document.body.removeChild(n), 300);
        }, 3000);
    };

    const apiCall = async (action, data = {}) => {
        const fd = new FormData();
        fd.append('action', action);
        Object.entries(data).forEach(([k, v]) => {
            if (k === 'imagen' && v instanceof File) {
                fd.append('imagen', v);
            } else {
                fd.append(k, v);
            }
        });
        const res = await fetch(window.location.pathname, { method: 'POST', body: fd });
        return res.json();
    };

    // Funciones para manejar imagen
    const previewImage = (input) => {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
                document.querySelector('.file-label').style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    const removeImage = () => {
        document.getElementById('imagen').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.querySelector('.file-label').style.display = 'flex';
    };

    // Búsqueda
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const search = this.value;
            const category = new URLSearchParams(window.location.search).get('categoria') || 'all';
            window.location.href = search ? `?categoria=${category}&search=${encodeURIComponent(search)}` : `?categoria=${category}`;
        }, 500);
    });

    const filterBy = (categoria) => {
        const search = new URLSearchParams(window.location.search).get('search') || '';
        window.location.href = search ? `?categoria=${categoria}&search=${encodeURIComponent(search)}` : `?categoria=${categoria}`;
    };

    const showModal = () => {
        document.getElementById('modalTitle').textContent = 'Agregar Producto';
        document.getElementById('form').reset();
        document.getElementById('productId').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.querySelector('.file-label').style.display = 'flex';
        editingId = null;
        document.getElementById('modal').style.display = 'flex';
    };

    const closeModal = () => document.getElementById('modal').style.display = 'none';

    const confirmDelete = (id, productName) => {
        deleteProductId = id;
        document.getElementById('productToDelete').textContent = productName;
        document.getElementById('confirmModal').style.display = 'flex';
    };

    const closeConfirmModal = () => {
        document.getElementById('confirmModal').style.display = 'none';
        deleteProductId = null;
    };

    const executeDelete = async () => {
        if (!deleteProductId) return;
        try {
            const result = await apiCall('delete', { id: deleteProductId });
            closeConfirmModal();
            notify(result.message, result.success ? 'success' : 'error');
            if (result.success) setTimeout(() => location.reload(), 1000);
        } catch (e) {
            closeConfirmModal();
            notify('Error al eliminar', 'error');
        }
    };

    const editProduct = async (id) => {
        try {
            const p = await apiCall('get', { id });
            if (p) {
                document.getElementById('modalTitle').textContent = 'Editar Producto';
                document.getElementById('productId').value = id;
                ['nombre', 'categoria', 'precio', 'stock'].forEach(field => {
                    document.getElementById(field).value = p[field === 'categoria' ? 'id_categoria' : field === 'nombre' ? 'nombre_producto' : field] || '';
                });
                
                // Mostrar imagen actual si existe
                if (p.imagen) {
                    document.getElementById('previewImg').src = p.imagen;
                    document.getElementById('imagePreview').style.display = 'block';
                    document.querySelector('.file-label').style.display = 'none';
                }
                
                editingId = id;
                document.getElementById('modal').style.display = 'flex';
            }
        } catch (e) {
            notify('Error al cargar producto', 'error');
        }
    };

    document.getElementById('form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = {};
        
        ['nombre', 'categoria', 'precio', 'stock'].forEach(field => {
            data[field] = document.getElementById(field).value.trim();
        });
        
        if (Object.values(data).some(v => !v)) return notify('Completa todos los campos', 'error');
        
        // Agregar imagen si se seleccionó una
        const imageFile = document.getElementById('imagen').files[0];
        if (imageFile) {
            data.imagen = imageFile;
        }
        
        try {
            if (editingId) data.id = editingId;
            const result = await apiCall(editingId ? 'update' : 'add', data);
            notify(result.message, result.success ? 'success' : 'error');
            if (result.success) {
                closeModal();
                setTimeout(() => location.reload(), 1000);
            }
        } catch (e) {
            notify('Error al guardar', 'error');
        }
    });

    // Eventos globales
    document.addEventListener('keydown', e => e.key === 'Escape' && (closeModal(), closeConfirmModal()));
    ['modal', 'confirmModal'].forEach(id => {
        document.getElementById(id).addEventListener('click', e => e.target.id === id && (id === 'modal' ? closeModal() : closeConfirmModal()));
    });
    </script>
</body>
</html>