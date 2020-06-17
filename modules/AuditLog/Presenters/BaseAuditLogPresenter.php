<?php

namespace App\AuditLog\Presenters;

use App\AuditLog\Presenters\Contracts\DisplayNameInterface;
use App\Countries\Country;
use App\Guardians\HearAbout;
use App\Religion;
use App\Roles\Role;
use App\Users\Department;
use Carbon\Carbon;

/**
 * Class BaseAuditLogPresenter
 */
class BaseAuditLogPresenter
{
    private const AUDIT_OLD_VALUE_KEY = 'old_values';
    private const AUDIT_NEW_VALUE_KEY = 'new_values';

    /**
     * @var \Eloquent|null
     */
    public $model;

    /**
     * Is used as base, rules template.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'role_id'       => [
                'type'  => 'relation',
                'label' => 'Role',
                'model' => Role::class,
            ],
            'department_id' => [
                'type'  => 'relation',
                'label' => 'Department',
                'model' => Department::class,
            ],
            'country_id'    => [
                'type'  => 'relation',
                'label' => 'Country',
                'model' => Country::class,
            ],
            'religion_id'   => [
                'type'  => 'relation',
                'label' => 'Religion',
                'model' => Religion::class,
            ],
            'hear_about_id' => [
                'type'  => 'relation',
                'label' => 'HearAbout',
                'model' => HearAbout::class,
            ],
            'id'            => [
                'type'  => 'text',
                'label' => 'ID',
            ],
        ];
    }

    /**
     * @param  array  $auditLogData
     * @param  \Illuminate\Database\Eloquent\Model|null $model        = null
     *
     * @return array
     */
    public function handle(array $auditLogData, $model = null): array
    {
        $this->model = $model;

        $auditableData = $auditLogData;

        foreach ($this->rules() as $column => $rule) {
            $auditableData = $this->manageAuditOldValue($auditableData, $column, $rule);
            $auditableData = $this->manageAuditNewValue($auditableData, $column, $rule);
        }

        return $auditableData;
    }

    /**
     * @param  array  $auditableData
     * @param  string $column
     * @param  array  $rule
     *
     * @return array
     */
    private function manageAuditOldValue(array $auditableData, string $column, array $rule): array
    {
        if (array_has($auditableData[self::AUDIT_OLD_VALUE_KEY], $column)) {
            $result = $this->getTransformedFieldData($rule, $column, $auditableData[self::AUDIT_OLD_VALUE_KEY]);
            $auditableData[self::AUDIT_OLD_VALUE_KEY] = $this->setValuesField(
                $auditableData[self::AUDIT_OLD_VALUE_KEY],
                $column,
                $rule['label'],
                $result
            );
        }

        return $auditableData;
    }

    /**
     * @param  array  $auditableData
     * @param  string $column
     * @param  array  $rule
     *
     * @return array
     */
    private function manageAuditNewValue(array $auditableData, string $column, array $rule): array
    {
        if (array_has($auditableData[self::AUDIT_NEW_VALUE_KEY], $column)) {
            $result = $this->getTransformedFieldData($rule, $column, $auditableData[self::AUDIT_NEW_VALUE_KEY]);
            $auditableData[self::AUDIT_NEW_VALUE_KEY] = $this->setValuesField(
                $auditableData[self::AUDIT_NEW_VALUE_KEY],
                $column,
                $rule['label'],
                $result
            );
        }

        return $auditableData;
    }

    /**
     * @param  array  $auditData
     * @param  string  $oldKey
     * @param  string  $newKey
     * @param  string  $value
     *
     * @return array
     */
    private function setValuesField(array $auditData, string $oldKey, string $newKey, string $value): array
    {
        $newValues = [
            'oldValue'   => $auditData[$oldKey],
            'oldKeyName' => $oldKey,
            'newValue'   => $value ?? null,
            'newKey'     => $newKey ?? null,
        ];
        unset($auditData[$oldKey]);
        $auditData[$oldKey] = $newValues;

        return $auditData;
    }

    /**
     * @param  array  $rule
     * @param  string  $column
     * @param  array  $attributes
     *
     * @return bool|mixed
     */
    private function getTransformedFieldData(array $rule, string $column, array $attributes): string
    {
        switch ($rule['type']) {
            case 'callback':
                return call_user_func($rule['callable'], $attributes[$column]);
            case 'boolean':
                return (bool) $attributes[$column] ? 'Yes' : 'NO';
            case 'date':
                return app()->make(Carbon::class)->parse($attributes[$column])->format('d M, g:i A (Y)');
            case 'relation':
                return $this->fetchRelationData($rule, $column, $attributes);
            case 'hidden':
                return '-';
            case 'text':
            default:
                return $attributes[$column] ?? '-';
        }
    }

    /**
     * @param  array  $rule
     * @param  string  $column
     * @param  array  $attributes
     *
     * @return string
     */
    private function fetchRelationData(array $rule, string $column, array $attributes): string
    {
        if ($attributes[$column] === null) {
            return '-';
        }
        if (in_array(DisplayNameInterface::class, class_implements($rule['model']), true)) {
            /** @var \App\AuditLog\Presenters\Contracts\DisplayNameInterface $class */
            $class = new $rule['model']();

            return $class::getDisplayNameById((int) $attributes[$column]);
        }

        return $attributes[$column];
    }
}
