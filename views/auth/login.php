<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style/style2.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <main>
        <div class="login-wrapper">
            <div class="login-icon-section">
                <div class="login-icon">
    <img src="/assets/unnamed.png" alt="Iniciar Sesión" class="login-image">
</div>
            </div>
            <div class="login-form">
                <h2>Iniciar Sesión</h2>

                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger">
                        <svg class="alert-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn-login">Ingresar</button>
                </form>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
