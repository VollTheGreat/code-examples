<?php

namespace App\AuditLog\Presenters\Contracts;

/**
 * Interface DisplayNameInterface
 */
interface DisplayNameInterface
{
    public static function getDisplayNameById(int $modelId): string;
}
