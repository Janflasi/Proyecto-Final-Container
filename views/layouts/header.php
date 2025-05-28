<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BESTIA BAR - Gestión Integral para Bares</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #FF6B35;
            --primary-dark: #E55A2B;
            --secondary: #FFD23F;
            --accent: #4ECDC4;
            --dark: #1A1A1A;
            --dark-light: #2A2A2A;
            --gray: #666666;
            --gray-light: #CCCCCC;
            --white: #FFFFFF;
            --gradient-main: linear-gradient(135deg, #FF6B35 0%, #F7931E 50%, #FFD23F 100%);
            --gradient-dark: linear-gradient(135deg, #1A1A1A 0%, #2A2A2A 50%, #3A3A3A 100%);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            --shadow-light: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gradient-dark);
            color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(26, 26, 26, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 900;
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .navbar-nav .nav-link {
            color: var(--white) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.75rem 1.5rem !important;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 107, 53, 0.1);
            color: var(--primary) !important;
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link.active {
            background: var(--gradient-main);
            color: var(--white) !important;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 120px 50px 50px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; 
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(255, 107, 53, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 210, 63, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(78, 205, 196, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .hero-content {
            flex: 1;
            max-width: 600px;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid rgba(255, 107, 53, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 2rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .brand-highlight {
            background: var(--gradient-main);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description {
            font-size: 1.2rem;
            color: var(--gray-light);
            margin-bottom: 2rem;
            line-height: 1.7;
        }

        .hero-features {
            display: flex;
            gap: 2rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray-light);
        }

        .feature-item i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary, .btn-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--gradient-main);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
            color: var(--white);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            transform: translateY(-3px);
        }

        .btn-cta {
            background: var(--gradient-main);
            color: var(--white);
            font-size: 1.1rem;
            padding: 1.2rem 2.5rem;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
            color: var(--white);
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray-light);
        }

        /* Hero Visual */
        .hero-visual {
            flex: 1;
            position: relative;
            height: 500px;
            margin-left: 2rem;
        }

        .floating-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: float 6s ease-in-out infinite;
            box-shadow: var(--shadow-light);
        }

        .floating-card i {
            font-size: 2rem;
            color: var(--primary);
        }

        .card-content {
            flex: 1;
        }

        .card-title {
            font-size: 0.8rem;
            color: var(--gray-light);
            margin-bottom: 0.25rem;
        }

        .card-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--white);
        }

        .card-change {
            font-size: 0.8rem;
            color: var(--accent);
        }

        .card-1 {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .card-2 {
            top: 50%;
            right: 20%;
            animation-delay: 2s;
        }

        .card-3 {
            bottom: 20%;
            left: 30%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: rgba(255, 255, 255, 0.02);
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .section-header p {
            font-size: 1.2rem;
            color: var(--gray-light);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 2.5rem;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow);
            background: rgba(255, 255, 255, 0.08);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-main);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon i {
            font-size: 1.5rem;
            color: var(--white);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--gray-light);
            margin-bottom: 1.5rem;
        }

        .feature-card ul {
            list-style: none;
            padding: 0;
        }

        .feature-card ul li {
            color: var(--gray-light);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .feature-card ul li::before {
            content: '✓';
            color: var(--accent);
            font-weight: bold;
        }

        /* Trust Section */
        .trust-section {
            padding: 100px 0;
            text-align: center;
        }

        .trust-content h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .trust-logos {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }

        .trust-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
        }

        .cta-section {
            margin-top: 4rem;
            padding: 3rem;
            background: rgba(255, 107, 53, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(255, 107, 53, 0.2);
        }

        .cta-section h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-section p {
            color: var(--gray-light);
            margin-bottom: 2rem;
        }

        /* Footer */
        footer {
            background: var(--dark) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 0 !important;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .hero-visual {
                display: none;
            }
            
            .hero-section {
                justify-content: center;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 100px 20px 50px;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-actions {
                justify-content: center;
            }
            
            .hero-stats {
                justify-content: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-crown"></i>
                BESTIA BAR
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="/">
                            <i class="fas fa-home"></i>
                            Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>