<?php

namespace JobMetric\Rolix\Enums;

use JobMetric\PackageCore\Enums\EnumMacros;

/**
 * Enum representing the various statuses of a representative delegation request.
 *
 * @method static PENDING()
 * @method static ACTIVE()
 * @method static CANCEL()
 * @method static REJECT()
 * @method static EXPIRE()
 */
enum RepresentativeStatusEnum: string
{
    use EnumMacros;

    /** The delegation request is pending and awaiting action */
    case PENDING = "pending";

    /** The delegation is currently active and valid */
    case ACTIVE = "active";

    /** The delegation has been canceled */
    case CANCEL = "cancel";

    /** The delegation request has been rejected */
    case REJECT = "reject";

    /** The delegation has expired due to passing its valid time */
    case EXPIRE = "expire";
}
