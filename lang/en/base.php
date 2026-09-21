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

    'exceptions' => [
        'role_type_not_found' => 'The :type role type is not registered.',
        'membership_memberable_mismatch' => 'Membership memberable does not match the role type rules.',
        'membership_memberable_pair_invalid' => 'memberable_type and memberable_id must both be null or both be set.',
        'membership_system_requires_null_memberable' => 'System role membership requires memberable to be null.',
        'membership_model_requires_memberable' => 'This role type requires a filled memberable.',
        'membership_memberable_type_mismatch' => 'memberable_type does not match the model registered for this role type.',
    ],

    'types' => [
        'system' => [
            'label' => 'System',
            'description' => 'System-wide roles that apply without a memberable context.',
        ],
    ],

];
