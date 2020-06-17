<?php

namespace App\AuditLog\Repositories;

use App\AuditLog\AuditLog;
use Fuzy\Repository\ExtendedRepository;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class AuditLogRepository
 *
 * @property-read \App\AuditLog\AuditLog $model
 */
class AuditLogRepository extends ExtendedRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AuditLog::class;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getAll()
    {
        return $this->model->with('user');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\OwenIt\Auditing\Models\Audit
     */
    public function getUsers()
    {
        return $this->model
            ->whereHasAndWithUsers()
            ->get()
            ->pluck('user')
            ->sortBy('fullName')
            ->pluck('fullName', 'id')
            ->toArray();
    }
}
