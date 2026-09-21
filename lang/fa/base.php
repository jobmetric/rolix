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
        'role' => 'نقش',
    ],

    'fields' => [
        'type' => 'نوع',
        'parent_id' => 'والد',
        'name' => 'نام',
        'description' => 'توضیحات',
        'allow' => 'مجوزها',
        'deny' => 'ممنوعیت‌ها',
        'is_default' => 'پیش‌فرض',
        'is_super' => 'دسترسی مدیر کل',
        'ordering' => 'ترتیب',
        'rules' => 'قوانین',
    ],

    'validation' => [
        'role' => [
            'parent_type_mismatch' => 'نقش والد باید از همان نوع باشد.',
            'parent_self' => 'یک نقش نمی‌تواند والد خودش باشد.',
            'parent_cycle' => 'والد انتخاب‌شده باعث ایجاد حلقه سلسله‌مراتبی می‌شود.',
            'permission_not_registered' => 'مجوز :permission برای این نوع نقش ثبت نشده است.',
            'rule_driver_invalid' => 'درایور قانون نامعتبر است.',
            'is_super_already_exists' => 'برای این نوع نقش، دسترسی مدیر کل از قبل تعریف شده است.',
        ],
    ],

    'exceptions' => [
        'role_type_not_found' => 'نوع نقش :type ثبت نشده است.',
        'membership_memberable_mismatch' => 'فیلد memberable عضویت با قوانین نوع نقش مطابقت ندارد.',
        'membership_memberable_pair_invalid' => 'فیلدهای memberable_type و memberable_id باید هر دو خالی یا هر دو پر باشند.',
        'membership_system_requires_null_memberable' => 'عضویت نقش سیستمی باید memberable خالی داشته باشد.',
        'membership_model_requires_memberable' => 'این نوع نقش نیاز به memberable پر شده دارد.',
        'membership_memberable_type_mismatch' => 'نوع memberable با مدل ثبت‌شده برای این نوع نقش مطابقت ندارد.',
        'role_is_super_protected' => 'نقش با دسترسی مدیر کل قابل حذف نیست.',
        'role_is_super_cannot_demote' => 'دسترسی مدیر کل قابل حذف از نقش نیست.',
        'role_is_super_already_exists' => 'برای این نوع نقش، دسترسی مدیر کل از قبل تعریف شده است.',
        'role_hierarchy_invalid' => 'سلسله‌مراتب نقش نامعتبر است.',
        'role_hierarchy_self_parent' => 'یک نقش نمی‌تواند والد خودش باشد.',
        'role_hierarchy_parent_missing' => 'نقش والد پیدا نشد.',
        'role_hierarchy_type_mismatch' => 'نقش والد باید از همان نوع باشد.',
        'role_hierarchy_cycle' => 'والد انتخاب‌شده باعث ایجاد حلقه سلسله‌مراتبی می‌شود.',
    ],

    'types' => [
        'system' => [
            'label' => 'سیستم',
            'description' => 'نقش‌های سراسری سیستم که بدون context عضوپذیر اعمال می‌شوند.',
        ],
    ],

];
