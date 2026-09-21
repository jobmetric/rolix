<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Rolix Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Rolix for
    | various messages that we need to display to the user.
    |
    */

    'entity_names' => [
        'role' => 'role',
    ],

    'fields' => [
        'type' => 'type',
        'parent_id' => 'parent',
        'name' => 'name',
        'description' => 'description',
        'allow' => 'allow',
        'deny' => 'deny',
        'is_default' => 'default',
        'is_super' => 'full administrator access',
        'ordering' => 'ordering',
        'rules' => 'rules',
    ],

    'validation' => [
        'role' => [
            'parent_type_mismatch' => 'The parent role must belong to the same type.',
            'parent_self' => 'A role cannot be its own parent.',
            'parent_cycle' => 'The selected parent would create a hierarchy cycle.',
            'permission_not_registered' => 'The permission :permission is not registered for this role type.',
            'rule_driver_invalid' => 'The rule driver is invalid.',
            'is_super_already_exists' => 'Full administrator access is already defined for this role type.',
        ],
    ],

    'exceptions' => [
        'role_type_not_found' => 'The :type role type is not registered.',
        'membership_memberable_mismatch' => 'Membership memberable does not match the role type rules.',
        'membership_memberable_pair_invalid' => 'memberable_type and memberable_id must both be null or both be set.',
        'membership_system_requires_null_memberable' => 'System role membership requires memberable to be null.',
        'membership_model_requires_memberable' => 'This role type requires a filled memberable.',
        'membership_memberable_type_mismatch' => 'memberable_type does not match the model registered for this role type.',
        'role_is_super_protected' => 'A role with full administrator access cannot be deleted.',
        'role_is_super_cannot_demote' => 'Full administrator access cannot be removed from a role.',
        'role_is_super_already_exists' => 'Full administrator access is already defined for this role type.',
        'role_hierarchy_invalid' => 'The role hierarchy is invalid.',
        'role_hierarchy_self_parent' => 'A role cannot be its own parent.',
        'role_hierarchy_parent_missing' => 'The parent role was not found.',
        'role_hierarchy_type_mismatch' => 'The parent role must belong to the same type.',
        'role_hierarchy_cycle' => 'The selected parent would create a hierarchy cycle.',
    ],

    'types' => [
        'system' => [
            'label' => 'System',
            'description' => 'System-wide roles that apply without a memberable context.',
        ],
    ],

];
