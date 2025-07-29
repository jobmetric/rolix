<?php

namespace JobMetric\Rolix\Enums;

use JobMetric\PackageCore\Enums\EnumMacros;

/**
 * Enum representing various reasons why a representative request may be canceled.
 *
 * @method static USER_REQUEST()
 * @method static PAYMENT_FAILED()
 * @method static QUOTA_EXCEEDED()
 * @method static POLICY_VIOLATION()
 * @method static EXPIRED()
 * @method static DUPLICATE()
 * @method static REPLACED()
 */
enum RepresentativeCancelReasonEnum: string
{
    use EnumMacros;

    /** Canceled by user request */
    case USER_REQUEST = "user_request";

    /** Payment was not successful or failed after retries */
    case PAYMENT_FAILED = "payment_failed";

    /** User has exceeded the allowed quota */
    case QUOTA_EXCEEDED = "quota_exceeded";

    /** Action or request violated a policy */
    case POLICY_VIOLATION = "policy_violation";

    /** The request or role expired before processing */
    case EXPIRED = "expired";

    /** A duplicate request was found */
    case DUPLICATE = "duplicate";

    /** This request was replaced by a newer or alternate one */
    case REPLACED = "replaced";
}
