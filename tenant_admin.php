<?php
// tenant_admin.php - Panel de superadmin con autenticación por sesión (CORREGIDO)

require_once 'include/mainApp.php';

// Token secreto - CAMBIA ESTO POR UN TOKEN SEGURO
define('SECRET_TOKEN', 'mi_token_secreto_2026_amigo_secreto');

// ============================================
// PRIMERO: Procesar autenticación POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $token = $_POST['token'] ?? '';
    
    if ($token === SECRET_TOKEN) {
        // Token válido - establecer sesión
        $_SESSION['superadmin_auth'] = true;
        $_SESSION['superadmin_auth_time'] = time();
        $_SESSION['superadmin_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['superadmin_user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        // Redirigir para evitar reenvío del formulario
        header('Location: tenant_admin.php?login=success');
        exit;
    } else {

        // Token inválido
        handleError('Token incorrecto. Intenta nuevamente.', 'error');
        header('Location: tenant_admin.php');
        exit;
    }
}

// ============================================
// SEGUNDO: Verificar autenticación por sesión
// ============================================
$is_authenticated = isset($_SESSION['superadmin_auth']) && 
                    $_SESSION['superadmin_auth'] === true;

// Si está autenticado, verificar seguridad adicional
if ($is_authenticated) {
    // Verificar que la IP no haya cambiado (seguridad)
    if (isset($_SESSION['superadmin_ip']) && 
        $_SESSION['superadmin_ip'] !== $_SERVER['REMOTE_ADDR']) {
        // Posible sesión robada
        session_destroy();
        $is_authenticated = false;
        handleError('Sesión inválida: IP no coincide. Vuelve a autenticarte.', 'error');
        header('Location: tenant_admin.php');
        exit;
    }
    
    // Verificar tiempo de sesión (8 horas)
    $session_timeout = 8 * 3600; // 8 horas en segundos
    if (isset($_SESSION['superadmin_auth_time']) && 
        (time() - $_SESSION['superadmin_auth_time']) > $session_timeout) {
        // Sesión expirada
        session_destroy();
        $is_authenticated = false;
        handleError('Sesión expirada. Por favor, autentícate nuevamente.', 'error');
        header('Location: tenant_admin.php');
        exit;
    }
}

// ============================================
// TERCERO: Procesar acciones del panel (solo si está autenticado)
// ============================================
if ($is_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF para acciones del panel
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        handleError('Token de seguridad inválido', 'error');
        header('Location: tenant_admin.php');
        exit;
    }
    
    // Cerrar sesión
    if (isset($_POST['logout'])) {
        session_destroy();
        header('Location: tenant_admin.php');
        exit;
    }
    
    // Crear tenant
    if (isset($_POST['create_tenant'])) {
        $tenantId = $_POST['tenant_id'];
        $adminName = $_POST['admin_name'];
        $adminEmail = $_POST['admin_email'];
        
        if (createTenant($tenantId, $adminName, $adminEmail)) {
            handleError("¡Tenant '$tenantId' creado exitosamente!", 'success');
        } else {
            handleError("Error: El tenant ya existe, el ID es inválido o los datos no son correctos.", 'error');
        }
        header('Location: tenant_admin.php');
        exit;
    }
    
    // Eliminar tenant
    if (isset($_POST['delete_tenant'])) {
        $tenantId = $_POST['tenant_id'];
        if (deleteTenant($tenantId)) {
            handleError("Tenant '$tenantId' eliminado correctamente", 'success');
        } else {
            handleError("Error al eliminar el tenant", 'error');
        }
        header('Location: tenant_admin.php');
        exit;
    }
}

// ============================================
// CUARTO: Si NO está autenticado, mostrar login
// ============================================
if (!$is_authenticated) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Acceso Panel Superadmin</title>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                margin: 0;
                padding: 20px;
            }
            .login-container {
                background: white;
                border-radius: 20px;
                padding: 40px;
                max-width: 400px;
                width: 100%;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                animation: fadeIn 0.5s ease-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .login-container h1 {
                color: #333;
                text-align: center;
                margin-bottom: 10px;
            }
            .login-container .subtitle {
                text-align: center;
                color: #888;
                margin-bottom: 30px;
            }
            .login-container .icon {
                text-align: center;
                font-size: 64px;
                margin-bottom: 20px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                font-weight: 600;
                color: #555;
                margin-bottom: 5px;
            }
            .form-group input {
                width: 100%;
                padding: 12px;
                border: 2px solid #ddd;
                border-radius: 10px;
                font-size: 16px;
                transition: border-color 0.3s;
                box-sizing: border-box;
            }
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
            }
            .btn-login {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 18px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .btn-login:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }
            .btn-login:active {
                transform: translateY(0);
            }
            .error-message {
                background: #f8d7da;
                color: #721c24;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                border: 1px solid #f5c6cb;
            }
            .info-text {
                text-align: center;
                color: #999;
                font-size: 0.85rem;
                margin-top: 20px;
            }
            .info-text i {
                color: #667eea;
            }
            @media (max-width: 600px) {
                .login-container {
                    padding: 25px;
                    margin: 15px;
                }
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="icon">🔐</div>
            <h1>Panel Superadmin</h1>
            <p class="subtitle">Ingresa el token de acceso</p>
            
            <?php 
            // Mostrar mensajes flash
            if (isset($_SESSION['flash_message'])) {
                $flash = $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . $flash['message'] . '</div>';
            }
            ?>
            
            <form method="POST" action="tenant_admin.php">
                <input type="hidden" name="login_action" value="1">
                <div class="form-group">
                    <label><i class="fas fa-key"></i> Token de acceso</label>
                    <input type="password" name="token" placeholder="Ingresa el token secreto" required autofocus>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Acceder
                </button>
            </form>
            
            <div class="info-text">
                <i class="fas fa-shield-alt"></i> Acceso restringido a administradores
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================
// QUINTO: Si está autenticado, mostrar el panel
// ============================================

// Obtener todos los tenants
$tenants = getAllTenants();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Superadmin - Amigo Secreto</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body {
            background: #1a1a2e;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
        }
        
        .admin-header a {
            color: white !important;
        }
        
        .admin-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .admin-card h2 {
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .tenant-admin-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        
        .tenant-admin-item:hover {
            background: #f8f9ff;
        }
        
        .tenant-admin-item:last-child {
            border-bottom: none;
        }
        
        .tenant-info {
            flex: 1;
        }
        
        .tenant-info h4 {
            margin: 0;
            color: #333;
        }
        
        .tenant-info p {
            margin: 5px 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .tenant-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }
        
        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .btn-danger-sm {
            background: #e53e3e;
            color: white;
        }
        
        .btn-danger-sm:hover {
            background: #c53030;
        }
        
        .btn-admin-sm {
            background: #667eea;
            color: white;
        }
        
        .btn-admin-sm:hover {
            background: #5a67d8;
        }
        
        .btn-participate-sm {
            background: #48bb78;
            color: white;
        }
        
        .btn-participate-sm:hover {
            background: #38a169;
        }
        
        .empty-state-admin {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state-admin i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .security-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .security-notice i {
            color: #856404;
        }
        
        .session-info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #0c5460;
        }
        
        .session-info i {
            color: #17a2b8;
        }
        
        .btn-logout {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        .status-indicator {
            display: inline-block;
            padding: 2px 10px;
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
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 600px) {
            .tenant-admin-item {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            
            .tenant-actions {
                justify-content: center;
            }
            
            .admin-header h1 {
                font-size: 24px;
            }
            
            .admin-header .w3-right-align {
                text-align: center !important;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        
        <!-- Header -->
        <div class="admin-header">
            <div class="w3-row">
                <div class="w3-col s12 m8">
                    <h1><i class="fas fa-crown"></i> Panel Superadmin</h1>
                    <p><i class="fas fa-shield-alt"></i> Gestión de tenants - Amigo Secreto</p>
                </div>
                <div class="w3-col s12 m4 w3-right-align">
                    <a href="index.php" class="w3-btn w3-white w3-text-purple w3-round-large" style="margin-right: 8px; color: #667eea !important;">
                        <i class="fas fa-arrow-left"></i> Sitio
                    </a>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <button type="submit" name="logout" class="btn-logout" onclick="return confirm('¿Cerrar sesión?')">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Información de sesión -->
        <div class="session-info">
            <i class="fas fa-check-circle"></i>
            <strong>Sesión activa</strong>
            <span style="margin: 0 10px;">|</span>
            <i class="fas fa-clock"></i> Inicio: <?php echo date('H:i:s', $_SESSION['superadmin_auth_time'] ?? time()); ?>
            <span style="margin: 0 10px;">|</span>
            <i class="fas fa-ip"></i> IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
            <span style="margin: 0 10px;">|</span>
            <small>Expira: <?php echo date('H:i:s', ($_SESSION['superadmin_auth_time'] ?? time()) + 28800); ?></small>
        </div>
        
        <!-- Aviso de seguridad -->
        <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            <strong>Nota de seguridad:</strong> Esta página es el panel de administración. 
            Considera proteger este archivo con autenticación adicional o cambiando su nombre.
        </div>
        
        <!-- Mensajes Flash -->
        <?php displayFlashMessage(); ?>
        
        <!-- Crear Tenant -->
        <div class="admin-card">
            <h2><i class="fas fa-plus-circle"></i> Crear Nuevo Tenant</h2>
            <form method="POST" class="w3-row-padding">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="w3-col l4 m6 s12" style="padding: 8px;">
                    <label class="w3-text-grey"><i class="fas fa-building"></i> ID del Tenant</label>
                    <input class="w3-input w3-border w3-round" type="text" name="tenant_id" 
                           placeholder="ej: empresa1" required pattern="[a-zA-Z0-9_-]+">
                </div>
                
                <div class="w3-col l4 m6 s12" style="padding: 8px;">
                    <label class="w3-text-grey"><i class="fas fa-user"></i> Nombre del Admin</label>
                    <input class="w3-input w3-border w3-round" type="text" name="admin_name" 
                           placeholder="Nombre completo" required>
                </div>
                
                <div class="w3-col l4 m6 s12" style="padding: 8px;">
                    <label class="w3-text-grey"><i class="fas fa-envelope"></i> Email del Admin</label>
                    <input class="w3-input w3-border w3-round" type="email" name="admin_email" 
                           placeholder="admin@ejemplo.com" required>
                </div>
                
                <div class="w3-col s12" style="padding: 8px;">
                    <button type="submit" name="create_tenant" class="w3-btn w3-purple w3-round-large w3-block">
                        <i class="fas fa-plus"></i> Crear Tenant
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Lista de Tenants -->
        <div class="admin-card">
            <h2>
                <i class="fas fa-list"></i> Tenants Existentes
                <span class="w3-tag w3-blue w3-round-large" style="margin-left: 10px;">
                    <?php echo count($tenants); ?>
                </span>
            </h2>
            
            <?php if (empty($tenants)): ?>
                <div class="empty-state-admin">
                    <i class="fas fa-inbox"></i>
                    <p>No hay tenants creados aún</p>
                    <p style="font-size: 0.9rem; color: #bbb;">Crea tu primer tenant usando el formulario arriba</p>
                </div>
            <?php else: ?>
                <?php foreach ($tenants as $tenant):
                    $stats = getTenantStats($tenant['tenant_id']);
                ?>
                    <div class="tenant-admin-item">
                        <div class="tenant-info">
                            <h4>
                                <i class="fas fa-building" style="color: #667eea;"></i>
                                <?php echo htmlspecialchars($tenant['tenant_id']); ?>
                            </h4>
                            <p>
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($tenant['admin_name']); ?>
                                <span style="margin: 0 10px;">|</span>
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($tenant['admin_email']); ?>
                            </p>
                            <p style="font-size: 0.8rem; color: #999;">
                                <i class="fas fa-calendar"></i> Creado: <?php echo formatDate($tenant['created_at']); ?>
                                <span style="margin: 0 10px;">|</span>
                                <i class="fas fa-users"></i> <?php echo $stats['total_participants']; ?> participantes
                                <span style="margin: 0 10px;">|</span>
                                <span class="status-indicator <?php echo $stats['draw_status'] === 'completed' ? 'status-active' : 'status-inactive'; ?>" style="font-size: 0.75rem;">
                                    <?php echo getDrawStatusText($stats['draw_status']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="tenant-actions">
                            <a href="admin.php?tenant=<?php echo urlencode($tenant['tenant_id']); ?>" 
                               class="btn-sm btn-admin-sm">
                                <i class="fas fa-cog"></i> Admin
                            </a>
                            <a href="participate.php?tenant=<?php echo urlencode($tenant['tenant_id']); ?>" 
                               class="btn-sm btn-participate-sm">
                                <i class="fas fa-gift"></i> Participar
                            </a>
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('¿Eliminar el tenant <?php echo htmlspecialchars($tenant['tenant_id']); ?>? Esta acción no se puede deshacer.')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <input type="hidden" name="tenant_id" value="<?php echo htmlspecialchars($tenant['tenant_id']); ?>">
                                <button type="submit" name="delete_tenant" class="btn-sm btn-danger-sm">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Información de seguridad -->
        <div class="w3-panel w3-pale-yellow w3-round">
            <h4><i class="fas fa-shield-alt"></i> Recomendaciones de seguridad</h4>
            <ul>
                <li><i class="fas fa-key"></i> Cambia el nombre de este archivo (<code>tenant_admin.php</code>) a uno más difícil de adivinar</li>
                <li><i class="fas fa-lock"></i> Configura autenticación HTTP básica en el archivo <code>.htaccess</code></li>
                <li><i class="fas fa-user-secret"></i> No compartas la URL de este panel con personas no autorizadas</li>
                <li><i class="fas fa-file"></i> Realiza respaldos periódicos de la carpeta <code>tenants/</code></li>
                <li><i class="fas fa-clock"></i> La sesión expira automáticamente después de 8 horas</li>
                <li><i class="fas fa-ip"></i> La sesión está vinculada a tu IP actual</li>
            </ul>
        </div>
        
        <!-- Footer -->
        <div class="w3-center w3-padding-24" style="color: #999;">
            <small>
                <i class="fas fa-heart w3-text-red"></i> 
                <?php echo APP_NAME; ?> - Panel Superadmin v<?php echo APP_VERSION; ?>
                <i class="fas fa-heart w3-text-red"></i>
                <br>
                <i class="fas fa-user-shield"></i> Sesión activa desde: <?php echo date('d/m/Y H:i:s', $_SESSION['superadmin_auth_time'] ?? time()); ?>
            </small>
        </div>
        
    </div>
</body>
</html>