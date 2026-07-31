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
    
    // Redirigir para evitar reenvío del formulario
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
    <style>
        /* ... estilos ... */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎄 <?php echo APP_NAME; ?></h1>
            <p>Multitenant - Administración y gestión</p>
            <small>Versión <?php echo APP_VERSION; ?></small>
        </div>

        <?php displayFlashMessage(); ?>

        <div class="card">
            <h2>Crear Nuevo Tenant</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <div class="form-group">
                    <label>ID del Tenant (ej: empresa1)</label>
                    <input type="text" name="tenant_id" required pattern="[a-zA-Z0-9_-]+">
                </div>
                <div class="form-group">
                    <label>Nombre del Administrador</label>
                    <input type="text" name="admin_name" required>
                </div>
                <div class="form-group">
                    <label>Email del Administrador</label>
                    <input type="email" name="admin_email" required>
                </div>
                <button type="submit" name="create_tenant">Crear Tenant</button>
            </form>
        </div>

        <div class="card">
            <h2>Tenants Existentes</h2>
            <div class="tenant-grid">
                <?php
                $tenants = getAllTenants();
                if (empty($tenants)) {
                    echo '<p style="color: #888; text-align: center; padding: 20px;">No hay tenants creados aún.</p>';
                } else {
                    foreach ($tenants as $tenant):
                        $stats = getTenantStats($tenant['tenant_id']);
                ?>
                        <div class="tenant-card">
                            <h3>🏢 <?php echo htmlspecialchars($tenant['tenant_id']); ?></h3>
                            <p>👤 Admin: <?php echo htmlspecialchars($tenant['admin_name']); ?></p>
                            <p>📧 <?php echo htmlspecialchars($tenant['admin_email']); ?></p>
                            <p>📅 Creado: <?php echo formatDate($tenant['created_at']); ?></p>
                            <div class="stats">
                                👥 Participantes: <?php echo $stats['total_participants']; ?> |
                                🎯 Estado: <?php echo getDrawStatusText($stats['draw_status']); ?>
                            </div>
                            <a href="admin.php?tenant=<?php echo urlencode($tenant['tenant_id']); ?>" class="btn btn-sm">Administrar</a>
                            <a href="participate.php?tenant=<?php echo urlencode($tenant['tenant_id']); ?>" class="btn btn-success btn-sm">Participar</a>
                        </div>
                    <?php 
                    endforeach;
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>