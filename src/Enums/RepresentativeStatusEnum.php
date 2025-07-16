<?php

namespace JobMetric\Rolix\Enums;

use JobMetric\PackageCore\Enums\EnumToArray;

/**
 * @method static PENDING()
 * @method static ACTIVE()
 * @method static CANCEL()
 * @method static REJECT()
 * @method static EXPIRE()
 */
enum RepresentativeStatusEnum: string
{
    use EnumToArray;

    case PENDING = "pending";
    case ACTIVE = "active";
    case CANCEL = "cancel";
    case REJECT = "reject";
    case EXPIRE = "expire";
}
