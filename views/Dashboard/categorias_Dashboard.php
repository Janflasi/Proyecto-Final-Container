<?php
require_once 'config/Conexion.php';

$database = new Database();
$pdo = $database->getConnection();
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar':
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                
                if (!empty($nombre)) {
                    try {
                        // NUEVA VALIDACIÓN: Verificar si ya existe una categoría con ese nombre
                        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE UPPER(nombre_categoria) = UPPER(?)");
                        $stmt_check->execute([$nombre]);
                        $existe = $stmt_check->fetchColumn();
                        
                        if ($existe > 0) {
                            $mensaje = "Ya existe una categoría con el nombre '$nombre'. Por favor, elige otro nombre.";
                            $tipo_mensaje = "error";
                        } else {
                            // Si no existe, proceder a insertar
                            $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)");
                            $stmt->execute([$nombre, $descripcion]);
                            $mensaje = "Categoría agregada exitosamente";
                            $tipo_mensaje = "success";
                        }
                    } catch(PDOException $e) {
                        // Manejo específico para errores de duplicado
                        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            $mensaje = "Ya existe una categoría con ese nombre. Por favor, elige otro nombre.";
                        } else {
                            $mensaje = "Error al agregar categoría: " . $e->getMessage();
                        }
                        $tipo_mensaje = "error";
                    }
                } else {
                    $mensaje = "El nombre de la categoría es obligatorio";
                    $tipo_mensaje = "error";
                }
                break;
                
            case 'editar':
                $id = $_POST['id'];
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                
                if (!empty($nombre)) {
                    try {
                        // NUEVA VALIDACIÓN: Verificar si ya existe otra categoría con ese nombre (excluyendo la actual)
                        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE UPPER(nombre_categoria) = UPPER(?) AND id_categoria != ?");
                        $stmt_check->execute([$nombre, $id]);
                        $existe = $stmt_check->fetchColumn();
                        
                        if ($existe > 0) {
                            $mensaje = "Ya existe otra categoría con el nombre '$nombre'. Por favor, elige otro nombre.";
                            $tipo_mensaje = "error";
                        } else {
                            // Si no hay conflicto, proceder a actualizar
                            $stmt = $pdo->prepare("UPDATE categorias SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?");
                            $stmt->execute([$nombre, $descripcion, $id]);
                            $mensaje = "Categoría actualizada exitosamente";
                            $tipo_mensaje = "success";
                        }
                    } catch(PDOException $e) {
                        // Manejo específico para errores de duplicado
                        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            $mensaje = "Ya existe otra categoría con ese nombre. Por favor, elige otro nombre.";
                        } else {
                            $mensaje = "Error al actualizar categoría: " . $e->getMessage();
                        }
                        $tipo_mensaje = "error";
                    }
                } else {
                    $mensaje = "El nombre de la categoría es obligatorio";
                    $tipo_mensaje = "error";
                }
                break;
                
            case 'eliminar':
                $id = $_POST['id'];
                try {
                    // NUEVA VALIDACIÓN: Verificar si tiene productos asociados
                    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE id_categoria = ? AND activo = 1");
                    $stmt_check->execute([$id]);
                    $productos_asociados = $stmt_check->fetchColumn();
                    
                    if ($productos_asociados > 0) {
                        $mensaje = "No se puede eliminar la categoría porque tiene $productos_asociados producto(s) asociado(s)";
                        $tipo_mensaje = "error";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id_categoria = ?");
                        $stmt->execute([$id]);
                        $mensaje = "Categoría eliminada exitosamente";
                        $tipo_mensaje = "success";
                    }
                } catch(PDOException $e) {
                    $mensaje = "Error al eliminar categoría: " . $e->getMessage();
                    $tipo_mensaje = "error";
                }
                break;
        }
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categorias = [];
    $mensaje = "Error al cargar categorías: " . $e->getMessage();
    $tipo_mensaje = "error";
}

$categoria_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id_categoria = ?");
        $stmt->execute([$id_editar]);
        $categoria_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $mensaje = "Error al cargar categoría: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Categorías - Container Bar</title>
<style>
:root{--primary:#FF6B35;--primary-dark:#E55A2B;--secondary:#FFD23F;--accent:#4ECDC4;--success:#10B981;--warning:#F7931E;--danger:#EF4444;--dark:#1A1A1A;--dark-light:#2A2A2A;--gray:#666666;--gray-light:#CCCCCC;--bg:#1A1A1A;--bg-card:#2A2A2A;--bg-hover:#3A3A3A;--text:#FFFFFF;--text-muted:#CCCCCC;--border:#444444;--gradient-main:linear-gradient(135deg,#FF6B35 0%,#F7931E 50%,#FFD23F 100%);--gradient-dark:linear-gradient(135deg,#1A1A1A 0%,#2A2A2A 50%,#3A3A3A 100%);--shadow:0 10px 30px rgba(0,0,0,0.3);--shadow-light:0 5px 15px rgba(0,0,0,0.2)}*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Segoe UI',sans-serif;background:var(--gradient-dark);min-height:100vh;padding:20px;color:var(--text)}.container{max-width:1200px;margin:0 auto;background:var(--bg-card);border-radius:15px;box-shadow:var(--shadow);overflow:hidden}.header{background:var(--gradient-main);padding:30px;text-align:center}.header h1{font-size:2.5rem;margin-bottom:10px}.content{padding:30px}.mensaje{padding:15px;margin-bottom:20px;border-radius:8px;font-weight:bold}.mensaje.success{background:rgba(16,185,129,0.1);color:var(--success);border:1px solid var(--success)}.mensaje.error{background:rgba(239,68,68,0.1);color:var(--danger);border:1px solid var(--danger)}.form-section{background:var(--bg);padding:25px;border-radius:15px;margin-bottom:30px;border:1px solid var(--border);backdrop-filter:blur(10px)}.form-section h2{margin-bottom:20px;color:var(--text);font-size:1.5rem}.form-group{margin-bottom:20px}.form-group label{display:block;margin-bottom:8px;font-weight:600;color:var(--text);font-size:14px}.form-group input,.form-group textarea{width:100%;padding:12px 16px;border:2px solid var(--border);border-radius:12px;font-size:16px;background:var(--bg-hover);color:var(--text);transition:all 0.3s ease}.form-group input:focus,.form-group textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(255,107,53,0.1);background:var(--bg-card)}.form-group textarea{resize:vertical;min-height:100px}.btn{padding:12px 24px;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;transition:all 0.3s ease;text-decoration:none;display:inline-block;position:relative;overflow:hidden}.btn::before{content:'';position:absolute;top:50%;left:50%;width:0;height:0;background:rgba(255,255,255,0.2);border-radius:50%;transition:all 0.3s ease;transform:translate(-50%,-50%)}.btn:hover::before{width:300px;height:300px}.btn-primary{background:var(--gradient-main);color:var(--text)}.btn-warning{background:var(--warning);color:var(--text)}.btn-danger{background:var(--danger);color:var(--text)}.btn-cancel{background:var(--gray);color:var(--text);margin-left:10px}.btn:hover{transform:translateY(-2px);box-shadow:var(--shadow-light)}.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}.stat-card{text-align:center;padding:25px;background:var(--gradient-main);border-radius:15px;position:relative;overflow:hidden}.stat-card::before{content:'';position:absolute;top:-50%;right:-50%;width:100px;height:100px;background:rgba(255,255,255,0.1);border-radius:50%;animation:float 6s ease-in-out infinite}.stat-card h3{font-size:2.5rem;margin-bottom:5px;font-weight:700}.categorias-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px, 1fr));gap:20px;margin-top:20px}.categoria-card{background:var(--bg);border:2px solid var(--border);border-radius:15px;padding:25px;transition:all 0.3s ease;position:relative;overflow:hidden}.categoria-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-light);border-color:var(--primary)}.categoria-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--gradient-main)}.categoria-card h3{color:var(--text);margin-bottom:10px;font-size:1.3rem;font-weight:600}.categoria-card p{color:var(--text-muted);margin-bottom:15px;line-height:1.4}.categoria-actions{display:flex;gap:10px;margin-top:20px}.categoria-actions .btn{flex:1;padding:10px 16px;font-size:14px}.form-actions{display:flex;gap:15px;margin-top:25px}.form-actions .btn{min-width:120px}@keyframes float{0%,100%{transform:translateY(0px)}50%{transform:translateY(-20px)}}@media(max-width:768px){.categorias-grid{grid-template-columns:1fr}.categoria-actions{flex-direction:column}.header h1{font-size:2rem}.content{padding:20px}.form-actions{flex-direction:column}.form-actions .btn{width:100%}.header div{flex-direction:column;text-align:center}.header div div{margin-bottom:15px}}
</style>
</head>
<body>
<div class="container">
<div class="header">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:15px;">
<div>
<h1>🍺 Gestión de Categorías</h1>
<p>Container Bar - Administración de categorías</p>
</div>
<a href="javascript:history.back()" class="btn" style="background:rgba(255,255,255,0.2);color:var(--text);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.3);min-width:auto;padding:10px 20px;">
← Volver
</a>
</div>
</div>
<div class="content">
<?php if ($mensaje): ?>
<div class="mensaje <?php echo $tipo_mensaje; ?>">
<?php echo htmlspecialchars($mensaje); ?>
</div>
<?php endif; ?>
<div class="stats">
<div class="stat-card">
<h3><?php echo count($categorias); ?></h3>
<p>Total Categorías</p>
</div>
</div>
<div class="form-section">
<h2><?php echo $categoria_editar ? '✏️ Editar Categoría' : '➕ Nueva Categoría'; ?></h2>
<form method="POST" action="" onsubmit="return validarFormulario()">
<input type="hidden" name="accion" value="<?php echo $categoria_editar ? 'editar' : 'agregar'; ?>">
<?php if ($categoria_editar): ?>
<input type="hidden" name="id" value="<?php echo $categoria_editar['id_categoria']; ?>">
<?php endif; ?>
<div class="form-group">
<label for="nombre">Nombre de la Categoría *</label>
<input type="text" id="nombre" name="nombre" required 
value="<?php echo $categoria_editar ? htmlspecialchars($categoria_editar['nombre_categoria']) : ''; ?>"
placeholder="Ej: Bebidas, Comidas, Postres...">
</div>
<div class="form-group">
<label for="descripcion">Descripción</label>
<textarea id="descripcion" name="descripcion" 
placeholder="Descripción opcional de la categoría..."><?php echo $categoria_editar ? htmlspecialchars($categoria_editar['descripcion']) : ''; ?></textarea>
</div>
<div class="form-actions">
<button type="submit" class="btn btn-primary">
<?php echo $categoria_editar ? '💾 Actualizar' : '➕ Agregar'; ?>
</button>
<?php if ($categoria_editar): ?>
<a href="?" class="btn btn-cancel">❌ Cancelar</a>
<?php endif; ?>
</div>
</form>
</div>
<div class="form-section">
<h2>📋 Categorías Registradas</h2>
<?php if (empty($categorias)): ?>
<p style="text-align:center;color:var(--text-muted);padding:40px;">
No hay categorías registradas. ¡Agrega la primera categoría!
</p>
<?php else: ?>
<div class="categorias-grid">
<?php foreach ($categorias as $categoria): ?>
<div class="categoria-card">
<h3><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></h3>
<p><?php echo $categoria['descripcion'] ? htmlspecialchars($categoria['descripcion']) : '<em>Sin descripción</em>'; ?></p>
<div class="categoria-actions">
<a href="?editar=<?php echo $categoria['id_categoria']; ?>" class="btn btn-warning">
✏️ Editar
</a>
<form method="POST" style="display:inline;flex:1;" 
onsubmit="return confirm('¿Está seguro de eliminar esta categoría?\n\nEsta acción no se puede deshacer.');">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="id" value="<?php echo $categoria['id_categoria']; ?>">
<button type="submit" class="btn btn-danger" style="width:100%;">
🗑️ Eliminar
</button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
<script>
// Validación del formulario
function validarFormulario() {
    const nombre = document.getElementById('nombre').value.trim();
    
    if (nombre.length === 0) {
        alert('El nombre de la categoría es obligatorio');
        return false;
    }
    
    if (nombre.length < 2) {
        alert('El nombre de la categoría debe tener al menos 2 caracteres');
        return false;
    }
    
    if (nombre.length > 50) {
        alert('El nombre de la categoría no puede tener más de 50 caracteres');
        return false;
    }
    
    // Validar caracteres especiales problemáticos
    const caracteresProblematicos = /[<>'"&]/;
    if (caracteresProblematicos.test(nombre)) {
        alert('El nombre de la categoría no puede contener los caracteres: < > \' " &');
        return false;
    }
    
    return true;
}

// Auto-ocultar mensajes después de 5 segundos
setTimeout(function(){
    const mensaje = document.querySelector('.mensaje');
    if(mensaje){
        mensaje.style.opacity = '0';
        mensaje.style.transition = 'opacity 0.5s ease';
        setTimeout(() => mensaje.remove(), 500);
    }
}, 5000);

// Limpiar formulario después de agregar exitosamente
<?php if ($tipo_mensaje === 'success' && !$categoria_editar): ?>
document.getElementById('nombre').value = '';
document.getElementById('descripcion').value = '';
<?php endif; ?>
</script>
</body>
</html>