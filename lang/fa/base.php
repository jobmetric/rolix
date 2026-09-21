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
        'role_type_not_found' => 'نوع نقش :type ثبت نشده است.',
        'membership_memberable_mismatch' => 'فیلد memberable عضویت با قوانین نوع نقش مطابقت ندارد.',
        'membership_memberable_pair_invalid' => 'فیلدهای memberable_type و memberable_id باید هر دو خالی یا هر دو پر باشند.',
        'membership_system_requires_null_memberable' => 'عضویت نقش سیستمی باید memberable خالی داشته باشد.',
        'membership_model_requires_memberable' => 'این نوع نقش نیاز به memberable پر شده دارد.',
        'membership_memberable_type_mismatch' => 'نوع memberable با مدل ثبت‌شده برای این نوع نقش مطابقت ندارد.',
    ],

    'types' => [
        'system' => [
            'label' => 'سیستم',
            'description' => 'نقش‌های سراسری سیستم که بدون context عضوپذیر اعمال می‌شوند.',
        ],
    ],

];
