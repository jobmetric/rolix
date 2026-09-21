<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Table name in database
    */

    "tables" => [
        'role' => 'roles',
        'role_path' => 'role_paths',
        'membership' => 'memberships',
        'role_rule' => 'role_rules',
        'role_activity_log' => 'role_activity_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Cache
    |--------------------------------------------------------------------------
    |
    | Shared cache for HasRole permission snapshots. Request-scoped memoization
    | is always active on the model. When enabled, Laravel Cache also stores
    | allow/deny results with versioned keys invalidated on membership/role
    | mutations.
    */

    'cache' => [
        'enabled' => env('ROLIX_CACHE_ENABLED', true),
        'ttl' => env('ROLIX_CACHE_TTL', 60),
        'store' => env('ROLIX_CACHE_STORE', null),
        'prefix' => env('ROLIX_CACHE_PREFIX', 'rolix'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Types (Registry defaults)
    |--------------------------------------------------------------------------
    |
    | Types registered by default in RoleTypeRegistry. You can register more
    | at runtime via RoleTypeRegistry::register() or in a service provider.
    | Each key is the type name, value is an optional options array.
    |
    | Common option keys:
    | - label: translation key for the type label
    | - description: translation key for the type description
    | - hierarchical: whether roles of this type form a parent/child tree
    | - model: FQCN of the memberable model; omit for system-wide roles
    */

    'types' => [
        'system' => [
            'label' => 'rolix::base.types.system.label',
            'description' => 'rolix::base.types.system.description',
            'hierarchical' => true,
        ],
        // 'department' => [
        //     'label' => 'rolix::base.types.department.label',
        //     'description' => 'rolix::base.types.department.description',
        //     'hierarchical' => true,
        // ],
    ],

];
