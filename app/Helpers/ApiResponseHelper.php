<?php

namespace App\Helpers;

use App\Traits\ApiResponse;

class ApiResponseHelper
{
    use ApiResponse {
        successResponse as public;
        errorResponse as public;
        paginatedResponse as public;
        validationErrorResponse as public;
    }
}
