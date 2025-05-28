<?php include __DIR__ . '/../layouts/header.php'; ?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fas fa-chart-line"></i>
            <span>Sistema de Gestión Integral</span>
        </div>
        
        <h1 class="hero-title">
            Bienvenido a <span class="brand-highlight">BESTIA BAR</span>
        </h1>
        
        <p class="hero-description">
            Revoluciona la gestión de tu bar con nuestra plataforma integral. 
            Controla inventario, ventas, empleados y finanzas desde un solo lugar. 
            <strong>Simple, potente y diseñado para el éxito de tu negocio.</strong>
        </p>
        
        <div class="hero-features">
            <div class="feature-item">
                <i class="fas fa-boxes"></i>
                <span>Inventario Inteligente</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-cash-register"></i>
                <span>Ventas en Tiempo Real</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-users"></i>
                <span>Gestión de Personal</span>
            </div>
        </div>
        
        <div class="hero-actions">
            <a href="/views/auth/login.php" class="btn-primary">
                <i class="fas fa-rocket"></i>
                Comenzar Ahora
            </a>
            <a href="#features" class="btn-secondary">
                <i class="fas fa-play"></i>
                Ver Demo
            </a>
        </div>
        
        <div class="hero-stats">
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Bares Activos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">98%</div>
                <div class="stat-label">Satisfacción</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Soporte</div>
            </div>
        </div>
    </div>
    
    <div class="hero-visual">
        <div class="floating-card card-1">
            <i class="fas fa-chart-bar"></i>
            <div class="card-content">
                <div class="card-title">Ventas Hoy</div>
                <div class="card-value">$2,450,000</div>
                <div class="card-change">+15.2%</div>
            </div>
        </div>
        
        <div class="floating-card card-2">
            <i class="fas fa-cube"></i>
            <div class="card-content">
                <div class="card-title">Stock Total</div>
                <div class="card-value">1,234</div>
                <div class="card-change">-5 críticos</div>
            </div>
        </div>
        
        <div class="floating-card card-3">
            <i class="fas fa-users"></i>
            <div class="card-content">
                <div class="card-title">Personal</div>
                <div class="card-value">12</div>
                <div class="card-change">Activos</div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<section id="features" class="features-section">
    <div class="container">
        <div class="section-header">
            <h2>¿Por qué elegir BESTIA BAR?</h2>
            <p>La tecnología que necesitas para llevar tu bar al siguiente nivel</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-inventory"></i>
                </div>
                <h3>Control Total de Inventario</h3>
                <p>Monitorea tu stock en tiempo real, recibe alertas automáticas y nunca más te quedes sin productos.</p>
                <ul>
                    <li>Alertas de stock bajo</li>
                    <li>Gestión por categorías</li>
                    <li>Control de proveedores</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-analytics"></i>
                </div>
                <h3>Análisis Avanzado</h3>
                <p>Toma decisiones inteligentes con reportes detallados y estadísticas que impulsan tu crecimiento.</p>
                <ul>
                    <li>Reportes en tiempo real</li>
                    <li>Análisis de tendencias</li>
                    <li>Productos más vendidos</li>
                </ul>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Gestión de Personal</h3>
                <p>Administra empleados, horarios, pagos y comisiones desde una plataforma centralizada.</p>
                <ul>
                    <li>Control de horarios</li>
                    <li>Gestión de pagos</li>
                    <li>Roles y permisos</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Trust Section -->
<section class="trust-section">
    <div class="container">
        <div class="trust-content">
            <h2>Únete a la familia BESTIA BAR</h2>
            <p>Cientos de negocios ya confían en nosotros para transformar su administración diaria</p>
            
            <div class="trust-logos">
                <div class="trust-item">🍺 Bar Central</div>
                <div class="trust-item">🍻 La Cervecería</div>
                <div class="trust-item">🥃 Whisky Lounge</div>
                <div class="trust-item">🍷 Wine House</div>
            </div>
            
            <div class="cta-section">
                <h3>¿Listo para comenzar?</h3>
                <p>Prueba BESTIA BAR gratis por 30 días y descubre el poder de la gestión inteligente</p>
                <a href="/views/auth/login.php" class="btn-cta">
                    <i class="fas fa-star"></i>
                    Empezar Prueba Gratuita
                </a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../layouts/footer.php'; ?>