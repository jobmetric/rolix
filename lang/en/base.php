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
        'membership' => 'membership',
    ],

    'fields' => [
        'personable_type' => 'personable type',
        'personable_id' => 'personable',
        'memberable_type' => 'memberable type',
        'memberable_id' => 'memberable',
        'role_id' => 'role',
        'collection' => 'collection',
        'is_owner' => 'owner',
        'expired_at' => 'expiration',
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
            'parent_not_allowed' => 'This role type does not support hierarchy.',
        ],
    ],

    'exceptions' => [
        'role_type_not_found' => 'The :type role type is not registered.',
        'membership_memberable_mismatch' => 'Membership memberable does not match the role type rules.',
        'membership_memberable_pair_invalid' => 'memberable_type and memberable_id must both be null or both be set.',
        'membership_system_requires_null_memberable' => 'System role membership requires memberable to be null.',
        'membership_model_requires_memberable' => 'This role type requires a filled memberable.',
        'membership_memberable_type_mismatch' => 'memberable_type does not match the model registered for this role type.',
        'membership_already_exists' => 'A membership with the same person, memberable, role, and collection already exists.',
        'membership_default_role_missing' => 'No default role is configured for this membership type.',
        'role_is_super_protected' => 'A role with full administrator access cannot be deleted.',
        'role_is_super_cannot_demote' => 'Full administrator access cannot be removed from a role.',
        'role_is_super_already_exists' => 'Full administrator access is already defined for this role type.',
        'role_hierarchy_invalid' => 'The role hierarchy is invalid.',
        'role_hierarchy_self_parent' => 'A role cannot be its own parent.',
        'role_hierarchy_parent_missing' => 'The parent role was not found.',
        'role_hierarchy_type_mismatch' => 'The parent role must belong to the same type.',
        'role_hierarchy_cycle' => 'The selected parent would create a hierarchy cycle.',
        'role_hierarchy_not_allowed' => 'This role type does not support hierarchy.',
    ],

    'types' => [
        'system' => [
            'label' => 'System',
            'description' => 'System-wide roles that apply without a memberable context.',
        ],
    ],

    'rule_evaluators' => [
        'common' => [
            'settings_tab' => 'Settings',
        ],
        'time' => [
            'label' => 'Time range',
            'description' => 'Activates the role only during the configured daily time range. Overnight ranges are supported.',
            'from' => [
                'label' => 'From',
                'info' => 'Start of the allowed time range (HH:MM).',
            ],
            'to' => [
                'label' => 'To',
                'info' => 'End of the allowed time range (HH:MM).',
            ],
            'timezone' => [
                'label' => 'Timezone',
                'info' => 'Choose the timezone whose local clock controls this rule. Leave empty to use the application timezone.',
            ],
        ],
        'weekday' => [
            'label' => 'Weekdays',
            'description' => 'Activates the role only on the selected weekdays in the chosen timezone.',
            'days' => [
                'label' => 'Days',
                'info' => 'Allowed weekdays.',
            ],
            'timezone' => [
                'label' => 'Timezone',
                'info' => 'Choose the timezone used to determine the current weekday. Leave empty to use the application timezone.',
            ],
            'options' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
        ],
        'ip_range' => [
            'label' => 'IP range',
            'description' => 'Restricts the role to exact IP addresses or CIDR network ranges.',
            'ranges' => [
                'label' => 'IP ranges',
                'info' => 'Enter exact addresses or CIDR networks separated by commas. Example: 192.168.1.10, 10.0.0.0/24',
            ],
        ],
        'env' => [
            'label' => 'Application environment',
            'description' => 'Activates the role only in named application environments such as production or staging.',
            'environments' => [
                'label' => 'Environments',
                'info' => 'Select every application environment in which this role may be active.',
            ],
            'options' => ['local' => 'Local', 'development' => 'Development', 'testing' => 'Testing', 'staging' => 'Staging', 'production' => 'Production'],
        ],
    ],

    'events' => [
        'role' => [
            'group' => 'Role',
            'stored' => [
                'title' => 'Role Stored',
                'description' => 'This event is triggered when a role is stored.',
            ],
            'updated' => [
                'title' => 'Role Updated',
                'description' => 'This event is triggered when a role is updated.',
            ],
            'deleted' => [
                'title' => 'Role Deleted',
                'description' => 'This event is triggered when a role is deleted.',
            ],
        ],
        'membership' => [
            'group' => 'Membership',
            'stored' => [
                'title' => 'Membership Stored',
                'description' => 'This event is triggered when a membership is stored.',
            ],
            'updated' => [
                'title' => 'Membership Updated',
                'description' => 'This event is triggered when a membership is updated.',
            ],
            'deleted' => [
                'title' => 'Membership Deleted',
                'description' => 'This event is triggered when a membership is deleted.',
            ],
            'restored' => [
                'title' => 'Membership Restored',
                'description' => 'This event is triggered when a membership is restored.',
            ],
            'force_deleted' => [
                'title' => 'Membership Force Deleted',
                'description' => 'This event is triggered when a membership is permanently deleted.',
            ],
        ],
        'permission' => [
            'group' => 'Permission',
            'paths_registering' => [
                'title' => 'Permission Paths Registering',
                'description' => 'This event collects permission file paths before they are loaded.',
            ],
        ],
        'resource' => [
            'group' => 'Resource',
            'personable' => [
                'title' => 'Personable Resource',
                'description' => 'This event resolves the API resource for a personable model.',
            ],
            'memberable' => [
                'title' => 'Memberable Resource',
                'description' => 'This event resolves the API resource for a memberable model.',
            ],
            'actor' => [
                'title' => 'Actor Resource',
                'description' => 'This event resolves the API resource for an activity actor.',
            ],
            'target' => [
                'title' => 'Target Resource',
                'description' => 'This event resolves the API resource for an activity target.',
            ],
            'context' => [
                'title' => 'Context Resource',
                'description' => 'This event resolves the API resource for an activity context.',
            ],
            'subject' => [
                'title' => 'Subject Resource',
                'description' => 'This event resolves the API resource for an activity subject.',
            ],
        ],
    ],

];
