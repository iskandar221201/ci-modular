<?php

namespace Config;

class AppConstants
{
    // -------------------------------------------------------------------------
    // HTTP Status Codes
    // -------------------------------------------------------------------------

    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_UNPROCESSABLE = 422;
    public const HTTP_SERVER_ERROR = 500;

    // -------------------------------------------------------------------------
    // App Status
    // -------------------------------------------------------------------------

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_PENDING = 'pending';

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public const DEFAULT_PER_PAGE = 15;
    public const MAX_PER_PAGE = 100;
}
