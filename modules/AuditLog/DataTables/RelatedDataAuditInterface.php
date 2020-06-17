<?php

namespace App\AuditLog\DataTables;

/**
 * Interface RelatedDataAuditInterface
 */
interface RelatedDataAuditInterface
{
    /**
     * @param  int  $modelId
     *
     * @return array
     */
    public static function getRelated(int $modelId): array;
}
