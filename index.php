<?php
// index.php - Página pública para seleccionar tenant

require_once 'include/mainApp.php';

// Obtener todos los tenants disponibles
$tenants = getAllTenants();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigo Secreto - Elige tu grupo</title>
    <!-- W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-container {
            max-width: 1200px;
            width: 100%;
            padding: 20px;
        }
        
        .hero-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .tenant-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .tenant-card-public {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }
        
        .tenant-card-public:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }
        
        .tenant-card-public .tenant-icon {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .tenant-card-public .tenant-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .tenant-card-public .tenant-meta {
            color: #888;
            font-size: 0.9rem;
            margin: 10px 0;
        }
        
        .tenant-card-public .status-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-enter {
            display: inline-block;
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-enter:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
        }
        
        .footer-note {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: 0.9rem;
        }
        
        @media (max-width: 600px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-card {
                padding: 20px;
            }
            
            .tenant-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Animación de entrada */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="hero-card fade-in-up">
            
            <!-- Header -->
            <div class="w3-center">
                <div style="font-size: 64px; margin-bottom: 10px;">🎄</div>
                <h1 class="hero-title">Amigo Secreto</h1>
                <p class="w3-large w3-text-grey" style="margin-top: 10px;">
                    <i class="fas fa-users"></i> Selecciona tu grupo para participar
                </p>
                <div style="margin-top: 10px;">
                    <span class="w3-tag w3-round-large w3-purple">
                        <i class="fas fa-building"></i> <?php echo count($tenants); ?> grupos disponibles
                    </span>
                </div>
            </div>
            
            <hr>
            
            <!-- Lista de Tenants -->
            <?php if (empty($tenants)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No hay grupos disponibles</h3>
                    <p>Parece que aún no se ha creado ningún grupo de amigo secreto.</p>
                    <p style="margin-top: 10px; font-size: 0.9rem; color: #aaa;">
                        <i class="fas fa-lock"></i> Si eres administrador, accede al panel de gestión
                    </p>
                </div>
            <?php else: ?>
                <div class="tenant-grid">
                    <?php foreach ($tenants as $tenant):
                        $stats = getTenantStats($tenant['tenant_id']);
                        $participantCount = $stats['total_participants'];
                        $isCompleted = $stats['draw_status'] === 'completed';
                    ?>
                        <div class="tenant-card-public fade-in-up" style="animation-delay: <?php echo rand(0, 300); ?>ms;">
                            <div class="w3-center">
                                <div class="tenant-icon">
                                    <i class="fas fa-<?php echo $isCompleted ? 'gift' : 'users'; ?>"></i>
                                </div>
                                <div class="tenant-name">
                                    <?php echo htmlspecialchars($tenant['tenant_id']); ?>
                                </div>
                                <div class="tenant-meta">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($tenant['admin_name']); ?>
                                    <br>
                                    <i class="fas fa-users"></i> <?php echo $participantCount; ?> participantes
                                </div>
                                <div style="margin: 10px 0;">
                                    <span class="status-indicator <?php echo $isCompleted ? 'status-active' : 'status-inactive'; ?>">
                                        <i class="fas fa-<?php echo $isCompleted ? 'check' : 'clock'; ?>"></i>
                                        <?php echo $isCompleted ? 'Sorteo realizado' : 'Sorteo pendiente'; ?>
                                    </span>
                                </div>
                                <a href="participate.php?tenant=<?php echo urlencode($tenant['tenant_id']); ?>" 
                                   class="btn-enter w3-block">
                                    <i class="fas fa-arrow-right"></i> Ingresar
                                </a>
                                <?php if ($isCompleted): ?>
                                    <small style="display: block; margin-top: 8px; color: #48bb78;">
                                        <i class="fas fa-check-circle"></i> ¡Ya puedes consultar tu amigo!
                                    </small>
                                <?php else: ?>
                                    <small style="display: block; margin-top: 8px; color: #ed8936;">
                                        <i class="fas fa-clock"></i> Esperando sorteo
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Footer -->
            <div class="footer-note">
                <i class="fas fa-heart w3-text-red"></i>
                <?php echo APP_NAME; ?> - v<?php echo APP_VERSION; ?>
                <i class="fas fa-heart w3-text-red"></i>
                <br>
                <small>
                    <i class="fas fa-lock"></i> 
                    ¿Eres administrador? 
                    <a href="tenant_admin.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                        Accede al panel de gestión
                    </a>
                </small>
            </div>
            
        </div>
    </div>
</body>
</html>