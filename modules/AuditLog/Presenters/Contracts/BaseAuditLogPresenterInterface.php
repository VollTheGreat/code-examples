<?php

namespace App\AuditLog\Presenters\Contracts;

/**
 * Class BaseAuditLogPresenter
 */
interface BaseAuditLogPresenterInterface
{
    /**
     * @return array
     */
    public function rules(): array;

    /**
     * @param  array  $auditLogData
     *
     * @return mixed
     */
    public function handle(array $auditLogData): array;
}
