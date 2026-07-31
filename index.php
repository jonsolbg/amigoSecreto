<?php
// index.php

// Cargar la aplicación principal
require_once 'include/mainApp.php';

// Crear tenant si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tenant'])) {
    $tenantId = $_POST['tenant_id'];
    $adminName = $_POST['admin_name'];
    $adminEmail = $_POST['admin_email'];
    
    if (createTenant($tenantId, $adminName, $adminEmail)) {
        handleError("¡Tenant '$tenantId' creado exitosamente!", 'success');
    } else {
        handleError("Error: El tenant ya existe, el ID es inválido o los datos no son correctos.", 'error');
    }
    
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigo Secreto - Sistema Multitenant</title>
    <!-- W3.CSS -->
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="w3-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        
        <!-- Header -->
        <div class="w3-card header-gradient w3-text-white w3-margin-bottom">
            <div class="w3-center">
                <i class="fas fa-gift" style="font-size: 48px;"></i>
                <h1 class="w3-xxxlarge w3-margin-top"><?php echo APP_NAME; ?></h1>
                <p class="w3-large">Sistema Multitenant de Amigo Secreto</p>
                <span class="w3-tag w3-round-large w3-white w3-text-purple">v<?php echo APP_VERSION; ?></span>
            </div>
        </div>

        <!-- Mensajes Flash -->
        <?php displayFlashMessage(); ?>

        <!-- Formulario de creación -->
        <div class="w3-card-4 w3-padding w3-margin-bottom fade-in">
            <div class="w3-container">
                <h2 class="w3-text-purple"><i class="fas fa-plus-circle"></i> Crear Nuevo Tenant</h2>
                <hr>
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
                    
                    <div class="w3-col s12" style="padding: 8px; text-align: center;">
                        <button type="submit" name="create_tenant" class="w3-btn w3-purple w3-round-large w3-hover-purple w3-large">
                            <i class="fas fa-plus"></i> Crear Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Tenants -->
        <div class="w3-card-4 w3-padding fade-in">
            <div class="w3-container">
                <h2 class="w3-text-purple"><i class="fas fa-list"></i> Tenants Existentes</h2>
                <hr>
                
                <?php
                $tenants = getAllTenants();
                if (empty($tenants)):
                ?>
                    <div class="w3-center w3-padding-64">
                        <i class="fas fa-inbox" style="font-size: 64px; color: #ccc;"></i>
                        <p class="w3-large w3-text-grey">No hay tenants creados aún.</p>
                        <p class="w3-text-grey">¡Crea tu primer tenant arriba!</p>
                    </div>
                <?php else: ?>
                    <div class="w3-row-padding">
                        <?php foreach ($tenants as $tenant):
                            $stats = getTenantStats($tenant['tenant_id']);
                        ?>
                            <div class="w3-col l4 m6 s12" style="padding: 8px;">
                                <div class="w3-card-2 tenant-card w3-padding w3-hover-shadow fade-in">
                                    <h3 class="w3-text-purple">
                                        <i class="fas fa-building"></i> 
                                        <?php echo htmlspecialchars($tenant['tenant_id']); ?>
                                    </h3>
                                    <p><i class="fas fa-user"></i> <strong>Admin:</strong> <?php echo htmlspecialchars($tenant['admin_name']); ?></p>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($tenant['admin_email']); ?></p>
                                    <p><i class="fas fa-calendar"></i> Creado: <?php echo formatDate($tenant['created_at']); ?></p>
                                    
                                    <div class="w3-margin-top">
                                        <span class="w3-tag w3-round-large w3-blue">
                                            <i class="fas fa-users"></i> <?php echo $stats['total_participants']; ?>
                                        </span>
                                        <span class="w3-tag w3-round-large <?php echo $stats['draw_status'] === 'completed' ? 'w3-green' : 'w3-yellow'; ?>">
                                            <i class="fas fa-<?php echo $stats['draw_status'] === 'completed' ? 'check' : 'clock'; ?>"></i>
                                            <?php echo getDrawStatusText($stats['draw_status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="w3-row-padding w3-margin-top" style="margin: 0 -4px;">
                                        <div class="w3-col s6" style="padding: 4px;">
                                            <a href="admin.php?tenant=<?php echo urlencode($tenant['tenant_id']); ?>" 
                                               class="w3-btn w3-purple w3-round-large w3-block w3-hover-purple">
                                                <i class="fas fa-cog"></i> Admin
                                            </a>
                                        </div>
                                        <div class="w3-col s6" style="padding: 4px;">
                                            <a href="participate.php?tenant=<?php echo urlencode($tenant['tenant_id']); ?>" 
                                               class="w3-btn w3-green w3-round-large w3-block w3-hover-green">
                                                <i class="fas fa-gift"></i> Participar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="w3-center w3-padding-24 w3-text-grey">
            <small>
                <i class="fas fa-heart w3-text-red"></i> 
                <?php echo APP_NAME; ?> - Hecho con amor
                <i class="fas fa-heart w3-text-red"></i>
            </small>
        </div>
        
    </div>

    <!-- Scripts -->
    <script>
        // Auto-ocultar mensajes flash después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(msg) {
                setTimeout(function() {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(function() {
                        msg.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>