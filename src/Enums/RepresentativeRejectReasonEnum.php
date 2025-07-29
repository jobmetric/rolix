<?php

namespace JobMetric\Rolix\Enums;

use JobMetric\PackageCore\Enums\EnumMacros;

/**
 * Enum representing various reasons why a representative request may be rejected.
 *
 * @method static INVALID_DATA()
 * @method static INSUFFICIENT_PERMISSIONS()
 * @method static ROLE_NOT_FOUND()
 * @method static ALREADY_EXISTS()
 * @method static UNAUTHORIZED()
 * @method static POLICY_VIOLATION()
 * @method static INCOMPLETE_DOCUMENTS()
 * @method static FRAUD_SUSPECTED()
 * @method static MANUAL_REVIEW_FAILED()
 * @method static AUTO_VALIDATION_FAILED()
 * @method static OTHER()
 */
enum RepresentativeRejectReasonEnum: string
{
    use EnumMacros;

    /** Data provided by user is invalid or malformed */
    case INVALID_DATA = "invalid_data";

    /** User does not have sufficient permissions to perform this action */
    case INSUFFICIENT_PERMISSIONS = "insufficient_permissions";

    /** Specified role was not found in the system */
    case ROLE_NOT_FOUND = "role_not_found";

    /** A similar request or role already exists */
    case ALREADY_EXISTS = "already_exists";

    /** The request was unauthorized or not properly authenticated */
    case UNAUTHORIZED = "unauthorized";

    /** The request violates one or more policies */
    case POLICY_VIOLATION = "policy_violation";

    /** Required documents were not submitted or are incomplete */
    case INCOMPLETE_DOCUMENTS = "incomplete_documents";

    /** The system suspects the request may be fraudulent */
    case FRAUD_SUSPECTED = "fraud_suspected";

    /** The request was reviewed manually and failed the criteria */
    case MANUAL_REVIEW_FAILED = "manual_review_failed";

    /** The automatic validation process failed */
    case AUTO_VALIDATION_FAILED = "auto_validation_failed";

    /** Reason does not fit into any specific category */
    case OTHER = "other";
}
