amigo-secreto/
├── index.php
├── admin.php
├── participate.php
├── include/
│   ├── mainApp.php          ← Archivo de inicialización principal
│   └── functions/
│       ├── tenant.php
│       ├── participants.php
│       ├── draw.php
│       ├── assignments.php
│       └── helpers.php
├── tenants/
│   └── {tenant_id}/
│       ├── config.json
│       ├── participants.json
│       ├── assignments.json
│       └── draw_status.json
└── .htaccess