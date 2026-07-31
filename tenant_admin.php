<?php
// tenant_admin.php - Versión con token en URL

require_once 'include/mainApp.php';

// Token secreto - CAMBIA ESTO POR UN TOKEN SEGURO
$SECRET_TOKEN = 'mi_token_secreto_2026_amigo_secreto';

// Verificar token en URL
$token = $_GET['token'] ?? '';
if ($token !== $SECRET_TOKEN) {
    // Si no hay token válido, redirigir al index
    header('Location: index.php');
    exit;
}



// Procesar acciones del superadmin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        handleError('Token de seguridad inválido', 'error');
    } else {
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
        }
        
        // Eliminar tenant
        if (isset($_POST['delete_tenant'])) {
            $tenantId = $_POST['tenant_id'];
            if (deleteTenant($tenantId)) {
                handleError("Tenant '$tenantId' eliminado correctamente", 'success');
            } else {
                handleError("Error al eliminar el tenant", 'error');
            }
        }
    }
    
    header('Location: tenant_admin.php' . ($auth_token ? '?token=' . $auth_token : ''));
    exit;
}

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
                    <a href="index.php" class="w3-btn w3-white w3-text-purple w3-round-large">
                        <i class="fas fa-arrow-left"></i> Ir al sitio
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Aviso de seguridad -->
        <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            <strong>Nota de seguridad:</strong> Esta página es el panel de administración. 
            Considera proteger este archivo con autenticación adicional o cambiando su nombre.
            <br>
            <small>Acceso autorizado: <?php echo date('Y-m-d H:i:s'); ?></small>
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
            </ul>
        </div>
        
        <!-- Footer -->
        <div class="w3-center w3-padding-24" style="color: #999;">
            <small>
                <i class="fas fa-heart w3-text-red"></i> 
                <?php echo APP_NAME; ?> - Panel Superadmin v<?php echo APP_VERSION; ?>
                <i class="fas fa-heart w3-text-red"></i>
            </small>
        </div>
        
    </div>
</body>
</html>