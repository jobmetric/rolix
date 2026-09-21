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
        'representative' => 'representatives',
        'role_rule' => 'role_rules',
        'role_activity_log' => 'role_activity_logs',
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
    */

    'types' => [
        // 'department' => [
        //     'label' => 'rolix::base.types.department.label',
        //     'description' => 'rolix::base.types.department.description',
        //     'hierarchical' => true,
        // ],
    ],

];
