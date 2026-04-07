<?php

return [

    /**
     * Valores en columna users.mlm_role.
     * admin: panel empresa; support: opcional mismo acceso que admin según rutas; member: socio.
     */
    'roles' => [
        'member' => 'member',
        'admin' => 'admin',
        'superadmin' => 'superadmin',
        'support' => 'support',
    ],

    /** Roles con acceso a /api/admin/* (variable de entorno separada por comas). */
    'admin_roles' => array_filter(array_map('trim', explode(',', (string) env('MLM_ADMIN_ROLES', 'admin,superadmin,support')))),

    'currency' => env('MLM_CURRENCY', 'BOB'),

    /** Código numérico consecutivo del socio (también es referral_code en string). */
    'member_code' => [
        'min' => (int) env('MLM_MEMBER_CODE_MIN', 10),
        'max' => (int) env('MLM_MEMBER_CODE_MAX', 1_000_000),
    ],

    /** PV mínimo mensual para considerar activo en red (comisión residual / reglas). */
    'monthly_activation_pv' => (float) env('MLM_MONTHLY_ACTIVATION_PV', 200),

    /** Bono inicio rápido: % sobre monto comisionable del paquete / ítem, por generación patrocinio. */
    'bir' => [
        'schedule' => [
            1 => 0.21,
            2 => 0.15,
            3 => 0.06,
        ],
        'period' => 'weekly',
    ],

    /** Binario: factor de pago (moneda local por cada PV emparejado en pierna débil). */
    'binary' => [
        'payout_per_matched_pv' => (float) env('MLM_BINARY_PAYOUT_PER_PV', 1),
        'cache_ttl_seconds' => (int) env('MLM_BINARY_CACHE_TTL', 600),
        'cache_prefix' => 'mlm:binary:',
    ],

    /**
     * Residual unilevel: porcentaje por generación (1..12). El rango limita cuántas se pagan.
     * Ajustar según plan real; valores demo decrecientes.
     */
    'residual' => [
        'generations' => [
            1 => 0.05,
            2 => 0.04,
            3 => 0.03,
            4 => 0.02,
            5 => 0.015,
            6 => 0.01,
            7 => 0.008,
            8 => 0.006,
            9 => 0.005,
            10 => 0.004,
            11 => 0.003,
            12 => 0.002,
        ],
        'period' => 'monthly',
        /** Base: PV del volumen del pedido atribuible al comprador en la línea. */
        'base' => 'order_pv',
    ],

    /** Bono liderazgo: % sobre comisiones de downline (rangos inferiores) — flexible por rango en DB. */
    'leadership' => [
        'default_rate' => 0.10,
    ],

    'queues' => [
        'binary' => env('MLM_QUEUE_BINARY', 'default'),
        'residual' => env('MLM_QUEUE_RESIDUAL', 'default'),
        'withdrawals' => env('MLM_QUEUE_WITHDRAWALS', 'default'),
    ],
];
