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

    /** Si true, la re-evaluación mensual puede bajar de rango cuando el PV no cumple umbrales. */
    'rank' => [
        'allow_downgrade_on_monthly_eval' => filter_var(env('MLM_RANK_ALLOW_DOWNGRADE', true), FILTER_VALIDATE_BOOL),
    ],

    /** Código numérico consecutivo del socio (también es referral_code en string). */
    'member_code' => [
        'min' => (int) env('MLM_MEMBER_CODE_MIN', 10),
        'max' => (int) env('MLM_MEMBER_CODE_MAX', 1_000_000),
    ],

    /** PV mínimo mensual para considerar activo en red (comisión residual / reglas). */
    'monthly_activation_pv' => (float) env('MLM_MONTHLY_ACTIVATION_PV', 200),

    /**
     * Ciclo de calificación comercial (día 1 al 27 del mes; el resto puede ser cierre/admin).
     */
    'qualification_cycle' => [
        'start_day' => (int) env('MLM_CYCLE_START_DAY', 1),
        'end_day' => (int) env('MLM_CYCLE_END_DAY', 27),
    ],

    /**
     * Valor monetario de 1 PV en moneda local (p. ej. BOB por PV).
     * Usado en BIR (21%/15%/6% sobre PV del paquete) y en binario (21% sobre PV emparejado).
     */
    'pv_value' => [
        'bob_per_pv' => (string) env('MLM_BOB_PER_PV', '9'),
    ],

    /**
     * Bono inicio rápido (BIR): % sobre PV del ítem de paquete, por generación de patrocinio.
     * Importe en moneda = (PV_línea × tasa_nivel) × bob_per_pv.
     */
    'bir' => [
        'base' => env('MLM_BIR_BASE', 'pv'), // pv | commissionable_amount
        'schedule' => [
            1 => 0.21,
            2 => 0.15,
            3 => 0.06,
        ],
        'period' => 'weekly',
    ],

    /**
     * Binario: sobre el volumen emparejado (pierna débil) en la semana ISO.
     * Importe = matched_pv × bob_per_pv × matched_pv_commission_rate
     * Si MLM_BINARY_LEGACY_FLAT=true, se usa solo payout_per_matched_pv (modo anterior).
     */
    'binary' => [
        'legacy_flat' => filter_var(env('MLM_BINARY_LEGACY_FLAT', false), FILTER_VALIDATE_BOOL),
        'payout_per_matched_pv' => (string) env('MLM_BINARY_PAYOUT_PER_PV', '1'),
        'bob_per_pv' => (string) env('MLM_BINARY_BOB_PER_PV', env('MLM_BOB_PER_PV', '9')),
        'matched_pv_commission_rate' => (string) env('MLM_BINARY_MATCHED_RATE', '0.21'),
        'cache_ttl_seconds' => (int) env('MLM_BINARY_CACHE_TTL', 600),
        'cache_prefix' => 'mlm:binary:',
        /** Tope de pago binario acumulado por periodo (solo aplica a eventos con meta.cap_period_key). */
        'cap' => [
            'enabled' => filter_var(env('MLM_BINARY_CAP_ENABLED', false), FILTER_VALIDATE_BOOL),
            'mode' => env('MLM_BINARY_CAP_MODE', 'weekly'),
            'max_bob' => (string) env('MLM_BINARY_CAP_MAX_BOB', '50000'),
        ],
    ],

    /**
     * Residual unilevel: porcentajes por generación según el rango efectivo del beneficiario.
     * effective_rank = mayor rango cuyo umbral de PV mensual cumple el usuario (ver rank_thresholds_pv).
     * Si no hay matriz para el slug, se usa default_generations.
     */
    'residual' => [
        'period' => 'monthly',
        'base' => 'order_pv',
        'default_generations' => [
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
        'matrix_by_rank_slug' => [
            'plata' => [1 => 0.12, 2 => 0.09, 3 => 0.09],
            'oro' => [1 => 0.12, 2 => 0.09, 3 => 0.09],
            'zafiro' => [1 => 0.12, 2 => 0.09, 3 => 0.09],
            'esmeralda' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06],
            'rubi' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03],
            'diamante' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03, 6 => 0.03],
            'diamante_ejecutivo' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03, 6 => 0.03, 7 => 0.03],
            'doble_diamante' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03, 6 => 0.03, 7 => 0.03, 8 => 0.03],
            'triple_diamante' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03, 6 => 0.03, 7 => 0.03, 8 => 0.03, 9 => 0.03],
            'diamante_corona' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03, 6 => 0.03, 7 => 0.03, 8 => 0.03, 9 => 0.03, 10 => 0.03],
            'doble_diamante_corona' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03, 6 => 0.03, 7 => 0.03, 8 => 0.03, 9 => 0.03, 10 => 0.03, 11 => 0.03],
            'triple_diamante_corona' => [1 => 0.12, 2 => 0.09, 3 => 0.09, 4 => 0.06, 5 => 0.03, 6 => 0.03, 7 => 0.03, 8 => 0.03, 9 => 0.03, 10 => 0.03, 11 => 0.03, 12 => 0.03],
        ],
        /** PV mensuales mínimos para “poseer” cada rango a efectos de residual (orden descendente de evaluación). */
        'rank_thresholds_pv' => [
            'triple_diamante_corona' => 3_000_000,
            'doble_diamante_corona' => 2_000_000,
            'diamante_corona' => 1_000_000,
            'triple_diamante' => 360_000,
            'doble_diamante' => 240_000,
            'diamante_ejecutivo' => 180_000,
            'diamante' => 120_000,
            'rubi' => 30_000,
            'esmeralda' => 60_000,
            'zafiro' => 15_000,
            'oro' => 7200,
            'plata' => (float) env('MLM_RANK_PLATA_PV', 1200),
        ],
    ],

    /**
     * Onboarding: requisito para alcanzar rango Plata (mensaje en dashboard hasta lograrlo).
     */
    'onboarding' => [
        'plata_pv_required' => (float) env('MLM_ONBOARDING_PLATA_PV', 1200),
        'plata_months_window' => (int) env('MLM_ONBOARDING_PLATA_MONTHS', 3),
    ],

    /**
     * Bono Auto OKM: meta de ganancias acumuladas (comisiones) en USD equivalente.
     */
    'auto_okm' => [
        'target_total_earnings_usd' => (float) env('MLM_AUTO_OKM_TARGET_USD', 90000),
        /** Cuántos BOB equivalen a 1 USD (para convertir comisiones en BOB → USD). */
        'bob_per_usd' => (string) env('MLM_BOB_PER_USD', '6.96'),
    ],

    /** Bono liderazgo: % sobre PV de calificación cuando aplica racha (ver LeadershipStreakService). */
    'leadership' => [
        'default_rate' => 0.10,
        'consecutive_months_required' => 3,
    ],

    'queues' => [
        'binary' => env('MLM_QUEUE_BINARY', 'default'),
        'residual' => env('MLM_QUEUE_RESIDUAL', 'default'),
        'withdrawals' => env('MLM_QUEUE_WITHDRAWALS', 'default'),
    ],

    /** Facturación automática al completar pedido (InvoiceService). */
    'invoice' => [
        'default_tax_rate' => (string) env('MLM_INVOICE_TAX_PCT', '0'),
    ],
];
