amigo-secreto/
├── index.php                 ← Página pública (selección de tenants)
├── tenant_admin.php          ← Panel superadmin (crear/administrar tenants)
├── admin.php                 ← Panel de admin de tenant (sin cambios)
├── participate.php           ← Participación (sin cambios)
├── include/
│   ├── mainApp.php
│   └── functions/
│       ├── tenant.php
│       ├── participants.php
│       ├── draw.php
│       ├── assignments.php
│       └── helpers.php
├── assets/
│   └── css/
│       └── custom.css
├── tenants/
│   └── {tenant_id}/
│       ├── config.json
│       ├── participants.json
│       ├── assignments.json
│       └── draw_status.json
└── .htaccess