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
            'label' => 'بازه زمانی',
            'description' => 'نقش را فقط در بازه زمانی روزانه تعیین‌شده فعال می‌کند و از بازه‌های عبوری از نیمه‌شب نیز پشتیبانی می‌کند.',
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
            'label' => 'روزهای هفته',
            'description' => 'نقش را فقط در روزهای هفته انتخاب‌شده و بر اساس منطقه زمانی تعیین‌شده فعال می‌کند.',
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
            'label' => 'وضعیت کاربر',
            'description' => 'پیش از فعال‌کردن نقش، مقدار یکی از ویژگی‌های کاربر را با مقدار مورد انتظار مقایسه می‌کند.',
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
            'label' => 'بازه IP',
            'description' => 'استفاده از نقش را به آدرس‌های IP مشخص یا بازه‌های شبکه CIDR محدود می‌کند.',
            'ranges' => [
                'label' => 'بازه IP',
                'info' => 'آدرس‌های IP یا CIDR جداشده با کاما.',
            ],
        ],
        'location' => [
            'label' => 'موقعیت مکانی',
            'description' => 'نقش را بر اساس کد ISO کشور و در صورت نیاز نام شهر موجود در context درخواست محدود می‌کند.',
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
            'label' => 'محیط برنامه',
            'description' => 'نقش را فقط در محیط‌های مشخص برنامه مانند production یا staging فعال می‌کند.',
            'environments' => [
                'label' => 'محیط‌ها',
                'info' => 'نام محیط‌ها جداشده با کاما (مثل production و staging).',
            ],
        ],
        'role_count' => [
            'label' => 'تعداد نقش‌ها',
            'description' => 'بررسی می‌کند تعداد نقش‌های فعال کاربر در محدوده حداقل و حداکثر اختیاری قرار داشته باشد.',
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
            'label' => 'سهمیه عضویت',
            'description' => 'فعال‌شدن نقش را بر اساس تعداد عضویت‌های فعال یک مجموعه محدود می‌کند.',
            'key' => [
                'label' => 'کلید سهمیه',
                'info' => 'کلید collection عضویت برای شمارش سهمیه.',
            ],
            'limit' => [
                'label' => 'سقف',
            ],
        ],
        'custom_expression' => [
            'label' => 'شرط سفارشی',
            'description' => 'یک مقدار مجاز از context یا درخواست را بدون اجرای کد دلخواه به‌شکل امن مقایسه می‌کند.',
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

    'events' => [
        'role' => [
            'group' => 'نقش',
            'stored' => [
                'title' => 'نقش ذخیره شد',
                'description' => 'این رویداد هنگام ذخیره نقش فراخوانی می‌شود.',
            ],
            'updated' => [
                'title' => 'نقش به‌روزرسانی شد',
                'description' => 'این رویداد هنگام به‌روزرسانی نقش فراخوانی می‌شود.',
            ],
            'deleted' => [
                'title' => 'نقش حذف شد',
                'description' => 'این رویداد هنگام حذف نقش فراخوانی می‌شود.',
            ],
        ],
        'membership' => [
            'group' => 'عضویت',
            'stored' => [
                'title' => 'عضویت ذخیره شد',
                'description' => 'این رویداد هنگام ذخیره عضویت فراخوانی می‌شود.',
            ],
            'updated' => [
                'title' => 'عضویت به‌روزرسانی شد',
                'description' => 'این رویداد هنگام به‌روزرسانی عضویت فراخوانی می‌شود.',
            ],
            'deleted' => [
                'title' => 'عضویت حذف شد',
                'description' => 'این رویداد هنگام حذف عضویت فراخوانی می‌شود.',
            ],
            'restored' => [
                'title' => 'عضویت بازیابی شد',
                'description' => 'این رویداد هنگام بازیابی عضویت فراخوانی می‌شود.',
            ],
            'force_deleted' => [
                'title' => 'عضویت برای همیشه حذف شد',
                'description' => 'این رویداد هنگام حذف دائمی عضویت فراخوانی می‌شود.',
            ],
        ],
        'permission' => [
            'group' => 'مجوز',
            'paths_registering' => [
                'title' => 'ثبت مسیرهای مجوز',
                'description' => 'این رویداد مسیر فایل‌های مجوز را قبل از بارگذاری جمع‌آوری می‌کند.',
            ],
        ],
        'resource' => [
            'group' => 'ریسورس',
            'personable' => [
                'title' => 'ریسورس شخص',
                'description' => 'این رویداد ریسورس API برای مدل شخص را resolve می‌کند.',
            ],
            'memberable' => [
                'title' => 'ریسورس عضوپذیر',
                'description' => 'این رویداد ریسورس API برای مدل عضوپذیر را resolve می‌کند.',
            ],
            'actor' => [
                'title' => 'ریسورس عامل',
                'description' => 'این رویداد ریسورس API برای عامل فعالیت را resolve می‌کند.',
            ],
            'target' => [
                'title' => 'ریسورس هدف',
                'description' => 'این رویداد ریسورس API برای هدف فعالیت را resolve می‌کند.',
            ],
            'context' => [
                'title' => 'ریسورس زمینه',
                'description' => 'این رویداد ریسورس API برای زمینه فعالیت را resolve می‌کند.',
            ],
            'subject' => [
                'title' => 'ریسورس موضوع',
                'description' => 'این رویداد ریسورس API برای موضوع فعالیت را resolve می‌کند.',
            ],
        ],
    ],

];
