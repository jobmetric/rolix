<?php

namespace JobMetric\Rolix\Enums;

use JobMetric\PackageCore\Enums\EnumToArray;

/**
 * Enum representing the actor who initiated the cancellation of a representative request.
 *
 * @method static USER()
 * @method static ADMIN()
 * @method static SYSTEM()
 * @method static THIRD_PARTY()
 */
enum RepresentativeCancelActorEnum: string
{
    use EnumToArray;

    /** Cancellation initiated by the user */
    case USER = "user";

    /** Cancellation performed by an administrator */
    case ADMIN = "admin";

    /** Cancellation triggered by the system automatically */
    case SYSTEM = "system";

    /** Cancellation initiated by a third-party service or integration */
    case THIRD_PARTY = "third_party";
}
