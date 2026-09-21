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
        'membership' => 'عضویت',
    ],

    'fields' => [
        'personable_type' => 'نوع شخص',
        'personable_id' => 'شخص',
        'memberable_type' => 'نوع عضوپذیر',
        'memberable_id' => 'عضوپذیر',
        'role_id' => 'نقش',
        'collection' => 'مجموعه',
        'is_owner' => 'مالک',
        'expired_at' => 'انقضا',
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
            'parent_not_allowed' => 'این نوع نقش از سلسله‌مراتب پشتیبانی نمی‌کند.',
        ],
    ],

    'exceptions' => [
        'role_type_not_found' => 'نوع نقش :type ثبت نشده است.',
        'membership_memberable_mismatch' => 'فیلد memberable عضویت با قوانین نوع نقش مطابقت ندارد.',
        'membership_memberable_pair_invalid' => 'فیلدهای memberable_type و memberable_id باید هر دو خالی یا هر دو پر باشند.',
        'membership_system_requires_null_memberable' => 'عضویت نقش سیستمی باید memberable خالی داشته باشد.',
        'membership_model_requires_memberable' => 'این نوع نقش نیاز به memberable پر شده دارد.',
        'membership_memberable_type_mismatch' => 'نوع memberable با مدل ثبت‌شده برای این نوع نقش مطابقت ندارد.',
        'membership_already_exists' => 'عضویتی با همان شخص، عضوپذیر، نقش و مجموعه از قبل وجود دارد.',
        'membership_default_role_missing' => 'نقش پیش‌فرضی برای این نوع عضویت تنظیم نشده است.',
        'role_is_super_protected' => 'نقش با دسترسی مدیر کل قابل حذف نیست.',
        'role_is_super_cannot_demote' => 'دسترسی مدیر کل قابل حذف از نقش نیست.',
        'role_is_super_already_exists' => 'برای این نوع نقش، دسترسی مدیر کل از قبل تعریف شده است.',
        'role_hierarchy_invalid' => 'سلسله‌مراتب نقش نامعتبر است.',
        'role_hierarchy_self_parent' => 'یک نقش نمی‌تواند والد خودش باشد.',
        'role_hierarchy_parent_missing' => 'نقش والد پیدا نشد.',
        'role_hierarchy_type_mismatch' => 'نقش والد باید از همان نوع باشد.',
        'role_hierarchy_cycle' => 'والد انتخاب‌شده باعث ایجاد حلقه سلسله‌مراتبی می‌شود.',
        'role_hierarchy_not_allowed' => 'این نوع نقش از سلسله‌مراتب پشتیبانی نمی‌کند.',
    ],

    'types' => [
        'system' => [
            'label' => 'سیستم',
            'description' => 'نقش‌های سراسری سیستم که بدون context عضوپذیر اعمال می‌شوند.',
        ],
    ],

    'rule_evaluators' => [
        'common' => [
            'settings_tab' => 'تنظیمات',
        ],
        'time' => [
            'from' => [
                'label' => 'از ساعت',
                'info' => 'شروع بازه زمانی مجاز (HH:MM).',
            ],
            'to' => [
                'label' => 'تا ساعت',
                'info' => 'پایان بازه زمانی مجاز (HH:MM).',
            ],
            'timezone' => [
                'label' => 'منطقه زمانی',
                'info' => 'منطقه زمانی IANA برای ارزیابی (پیش‌فرض: منطقه زمانی برنامه).',
            ],
        ],
        'weekday' => [
            'days' => [
                'label' => 'روزهای هفته',
                'info' => 'روزهای مجاز هفته.',
            ],
            'timezone' => [
                'label' => 'منطقه زمانی',
            ],
            'options' => [
                'monday' => 'دوشنبه',
                'tuesday' => 'سه‌شنبه',
                'wednesday' => 'چهارشنبه',
                'thursday' => 'پنج‌شنبه',
                'friday' => 'جمعه',
                'saturday' => 'شنبه',
                'sunday' => 'یکشنبه',
            ],
        ],
        'user_status' => [
            'attribute' => [
                'label' => 'ویژگی',
                'info' => 'ویژگی شخص برای مقایسه (پیش‌فرض: status).',
            ],
            'expected' => [
                'label' => 'مقدار مورد انتظار',
                'info' => 'مقداری که باید با ویژگی برابر باشد.',
            ],
        ],
        'ip_range' => [
            'ranges' => [
                'label' => 'بازه IP',
                'info' => 'آدرس‌های IP یا CIDR جداشده با کاما.',
            ],
        ],
        'location' => [
            'countries' => [
                'label' => 'کشورها',
                'info' => 'کدهای ISO کشور جداشده با کاما.',
            ],
            'cities' => [
                'label' => 'شهرها',
                'info' => 'نام شهرها جداشده با کاما.',
            ],
        ],
        'env' => [
            'environments' => [
                'label' => 'محیط‌ها',
                'info' => 'نام محیط‌ها جداشده با کاما (مثل production و staging).',
            ],
        ],
        'role_count' => [
            'type' => [
                'label' => 'نوع نقش',
                'info' => 'فیلتر اختیاری نوع نقش برای شمارش عضویت.',
            ],
            'min' => [
                'label' => 'حداقل',
            ],
            'max' => [
                'label' => 'حداکثر',
            ],
        ],
        'quota' => [
            'key' => [
                'label' => 'کلید سهمیه',
                'info' => 'کلید collection عضویت برای شمارش سهمیه.',
            ],
            'limit' => [
                'label' => 'سقف',
            ],
        ],
        'custom_expression' => [
            'left_key' => [
                'label' => 'کلید چپ',
                'info' => 'کلید امن از context یا request (مثل status یا request.ip).',
            ],
            'operator' => [
                'label' => 'عملگر',
            ],
            'right_value' => [
                'label' => 'مقدار راست',
            ],
        ],
    ],

];
