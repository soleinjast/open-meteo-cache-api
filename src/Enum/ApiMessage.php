<?php

namespace App\Enum;

enum ApiMessage: string
{
    case SUCCESS             = 'success';
    case FAILED              = 'failed';

    case VALIDATION_FAILED   = 'validation_failed';
    case UNAUTHORIZED        = 'unauthorized';
    case FORBIDDEN           = 'forbidden';
    case NOT_FOUND           = 'not_found';

    case INTERNAL_ERROR      = 'internal_error';
}
