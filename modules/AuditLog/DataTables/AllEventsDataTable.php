<?php

namespace App\AuditLog\DataTables;

use App\AuditLog\DataTables\Filters\AuditLogDataTableFilters;
use App\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Yajra\DataTables\DataTables as Datatables;

/**
 * AllEventsDataTable Class
 */
class AllEventsDataTable
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $builder;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var \App\AuditLog\DataTables\Filters\AuditLogDataTableFilters
     */
    private $filters;

    /**
     * AllEventsDataTable constructor.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(Builder $builder, Request $request)
    {
        $this->builder = $builder;
        $this->request = $request;
        $this->filters = new AuditLogDataTableFilters();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeDataTable()
    {
        $request = $this->request;

        /** @var \Yajra\DataTables\EloquentDataTable */
        $dataTable = Datatables::of($this->builder)
            ->addColumn(
                'user',
                function ($model) {
                    return $this->fetchUserData($model->user);
                }
            )
            ->addColumn(
                'event_type',
                function ($model) {
                    return $this->fetchEventName($model->event);
                }
            )
            ->addColumn(
                'model',
                function ($model) {
                    return $this->fetchAuditableModelLink($model->auditable_type, $model->auditable_id);
                }
            )
            ->addColumn(
                'relatedTo',
                function ($model) {
                    return $this->fetchRelatedTo($model);
                }
            )
            ->editColumn(
                'url',
                function ($model) {
                    return $this->fetchEventTriggerUrl($model->url);
                }
            )
            ->addColumn(
                'old',
                function ($model) {
                    return $this->renderChangedData($model, 'old');
                }
            )
            ->addColumn(
                'new',
                function ($model) {
                    return $this->renderChangedData($model, 'new');
                }
            )
            ->editColumn(
                'created_at',
                function ($model) {
                    return $model->created_at->format('d M, g:i A (Y)');
                }
            );

        if ($request->input('search.value')) {
            $this->filters->fetchSearchData($request->input('search.value'), $dataTable);
        }
        if ($request->input('user_id') || $request->input('user_id') === '0') {
            $this->filters->fetchByUser((int) $request->input('user_id'), $dataTable);
        }
        if ($request->input('object_id')) {
            $this->filters->fetchByRelatedModelId((int) $request->input('object_id'), $dataTable);
        }
        if ($request->input('object_num') || $request->input('object_num') === '0') {
            $this->filters->fetchByRelatedModel((int) $request->input('object_num'), $dataTable);
        }
        if ($request->input('event_name')) {
            $this->filters->fetchByEventName($request->input('event_name'), $dataTable);
        }
        if ($request->input('date_from')) {
            $this->filters->fetchByDateTimeFrom($request->input('date_from'), $dataTable);
        }
        if ($request->input('date_to')) {
            $this->filters->fetchByDateTimeTo($request->input('date_to'), $dataTable);
        }

        return $dataTable->make(true);
    }

    private function fetchUserData(?User $user = null): string
    {
        if ($user) {
            return '<a href="' . route('users.show', $user->id) . '">' . $user->fullName . '</a>';
        }

        return '<span class="label" style="background-color: #507d91">System</span>';
    }

    private function fetchEventName(string $event): string
    {
        $eventName = ucfirst($event);

        switch ($event) {
            case 'updated':
                return '<span class="label" style="background-color: orange">' . $eventName . '</span>';
            case 'created':
                return '<span class="label" style="background-color: green">' . $eventName . '</span>';
            case 'deleted':
                return '<span class="label" style="background-color: darkred">' . $eventName . '</span>';
            case 'restored':
                return '<span class="label" style="background-color: darkgray">' . $eventName . '</span>';
            default:
                return 'unknown';
        }
    }

    private function fetchAuditableModelLink(string $auditableType, string $auditableId): string
    {
        $path = explode('\\', $auditableType);

        return array_pop($path) . ':  <br>' . $auditableId;
    }

    private function fetchEventTriggerUrl(string $urlData): string
    {
        if ($urlData === 'console') {
            return '<span class="label" style="background-color: #507d91">System</span>';
        }
        $removeBaseUrl = str_replace(app()->make('url')->to('/'), '', $urlData);
        $clearUrl = str_replace('?', '', $removeBaseUrl);

        return '<p style="color: seagreen">' . $clearUrl . '</p>';
    }

    /**
     * @param  \OwenIt\Auditing\Models\Audit  $model
     * @param  string  $dataType
     *
     * @return string|void
     *
     * @throws \Throwable
     */
    private function renderChangedData(Audit $model, string $dataType)
    {
        return view('audit.decorators._presenter_data_values', [
            'data' => $dataType === 'new' ? $model->new_values : $model->old_values,
            'modelType' => $model->auditable_type,
            'dataType' => $dataType,
        ])->render();
    }

    private function fetchRelatedTo(Audit $model): string
    {
        $data = $this->getRelatedData($model);

        if (!is_array($data)) {
            return '-';
        }

        $name = $data['type'] . ': <br> ' . $data['name'];

        if ($data['deleted']) {
            return $name;
        }

        return '<a href="' .   $data['link'] . '">' . $name . '</a>';
    }

    /**
     * @param  \OwenIt\Auditing\Models\Audit  $model
     *
     * @return array|null
     */
    private function getRelatedData(Audit $model)
    {
        $relatedModelId = $model->auditable_id;

        if (in_array(RelatedDataAuditInterface::class, class_implements($model->auditable_type), true)) {
            /** @var \App\AuditLog\DataTables\RelatedDataAuditInterface $class */
            $class = new $model->auditable_type();

            return $class::getRelated($relatedModelId);
        }

        return null;
    }
}
