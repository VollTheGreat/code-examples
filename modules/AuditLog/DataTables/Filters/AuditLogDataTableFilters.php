<?php

namespace App\AuditLog\DataTables\Filters;

use App\DataTables\BaseDatatableFilters;
use Yajra\Datatables\EloquentDataTable;

/**
 * AuditLogDataTableFilters Class
 */
class AuditLogDataTableFilters extends BaseDatatableFilters
{
    /**
     * @param  string  $eventName
     * @param  \Yajra\Datatables\EloquentDataTable  $dataTable
     *
     * @return void
     */
    public function fetchByEventName(string $eventName, EloquentDataTable $dataTable)
    {
        $dataTable->getQuery()->where('event', $eventName);
    }

    /**
     * @param  int  $modelNum
     * @param  \Yajra\Datatables\EloquentDataTable  $dataTable
     *
     * @return void
     */
    public function fetchByRelatedModel(int $modelNum, EloquentDataTable $dataTable)
    {
        $dataTable->getQuery()->where('auditable_type', config('audit.models')[$modelNum]);
    }

    /**
     * @param  int  $auditableId
     * @param  \Yajra\Datatables\EloquentDataTable  $dataTable
     *
     * @return void
     */
    public function fetchByRelatedModelId(int $auditableId, EloquentDataTable $dataTable)
    {
        $dataTable->getQuery()->where('auditable_id', $auditableId);
    }

    /**
     * @param  string  $keyWord
     * @param  \Yajra\Datatables\EloquentDataTable  $dataTable
     *
     * @return void
     */
    public function fetchSearchData(string $keyWord, EloquentDataTable $dataTable)
    {
        $dataTable->getQuery()->whereHas('actionUser', function ($q) use ($keyWord) {
            $q->where('first_name', 'LIKE', '%' . $keyWord . '%')
                ->orWhere('last_name', 'LIKE', '%' . $keyWord . '%')
                ->orWhere('email', 'LIKE', '%' . $keyWord . '%')
                ->orWhereRaw("CONCAT(first_name,' ',last_name) like ?", [$keyWord])
                ->orWhereRaw("CONCAT(last_name,' ',first_name) like ?", [$keyWord]);
        });
    }
}
