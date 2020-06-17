<?php

namespace Tests\Feature\AuditLog;

use App\AuditLog\AuditLog;
use App\Users\User;
use Tests\Feature\TestCase;

/**
 * Class BaseAuditTests
 */
abstract class BaseAuditTests extends TestCase
{
    private const DEFAULT_ID_FIELD_NAME = 'id';

    abstract public function getAuditClass(): string;

    /**
     * @param \Eloquent $model
     */
    abstract public function updateModel($model): void;

    public function getIDName(): ?string
    {
        return null;
    }

    /** @test
     */
    public function auditCreatedIsSaved()
    {
        $idFieldName = $this->getIDName() ?? self::DEFAULT_ID_FIELD_NAME;
        // Arrange
        $this->asAdmin();
        // Act
        $model = factory($this->getAuditClass())->create();
        $audit = app()->make(AuditLog::class)
            ->where('auditable_id', $model->{$idFieldName})
            ->where('auditable_type', $this->getAuditClass())->get()->last();
        // Assert
        $this->assertNotNull($audit);
        $this->assertEquals($audit->auditable_id, $model->{$idFieldName});
        $this->assertEquals($audit->auditable_type, $this->getAuditClass());
        $this->assertEquals($audit->event, 'created');
        $this->assertEquals($audit->user_id, app('auth.driver')->user()->id);
        $this->assertEquals($audit->user_type, User::class);
    }

    /** @test
     */
    public function auditUpdatedIsSaved()
    {
        $idFieldName = $this->getIDName() ?? self::DEFAULT_ID_FIELD_NAME;
        // Arrange
        $this->asAdmin();
        // Act
        $model = factory($this->getAuditClass())->create();
        $this->updateModel($model);
        $audit = app()->make(AuditLog::class)
            ->where('auditable_id', $model->{$idFieldName})
            ->where('auditable_type', $this->getAuditClass())->get()->last();
        // Assert
        $this->assertNotNull($audit);
        $this->assertEquals($audit->auditable_id, $model->{$idFieldName});
        $this->assertEquals($audit->auditable_type, $this->getAuditClass());
        $this->assertEquals($audit->event, 'updated');
        $this->assertEquals($audit->user_id, app('auth.driver')->user()->id);
        $this->assertEquals($audit->user_type, User::class);
    }

    /** @test
     */
    public function auditDeletedIsSaved()
    {
        $idFieldName = $this->getIDName() ?? self::DEFAULT_ID_FIELD_NAME;
        // Arrange
        $this->asAdmin();
        // Act
        $model = factory($this->getAuditClass())->create();
        $model->delete();
        $audit = app()->make(AuditLog::class)
            ->where('auditable_id', $model->{$idFieldName})
            ->where('auditable_type', $this->getAuditClass())->get()->last();
        // Assert
        $this->assertNotNull($audit);
        $this->assertEquals($audit->auditable_id, $model->{$idFieldName});
        $this->assertEquals($audit->auditable_type, $this->getAuditClass());
        $this->assertEquals($audit->event, 'deleted');
        $this->assertEquals($audit->user_id, app('auth.driver')->user()->id);
        $this->assertEquals($audit->user_type, User::class);
    }

    /** @test
     */
    public function userRestoredAuditIsSaved()
    {
        $idFieldName = $this->getIDName() ?? self::DEFAULT_ID_FIELD_NAME;
        // Arrange
        $this->asAdmin();
        // Act
        $model = factory($this->getAuditClass())->create();
        $model->delete();
        $model->restore();
        $audit = app()->make(AuditLog::class)
            ->where('auditable_id', $model->{$idFieldName})
            ->where('auditable_type', $this->getAuditClass())->get()->last();
        // Assert
        $this->assertNotNull($audit);
        $this->assertEquals($audit->auditable_id, $model->{$idFieldName});
        $this->assertEquals($audit->auditable_type, $this->getAuditClass());
        $this->assertEquals($audit->event, 'restored');
        $this->assertEquals($audit->user_id, app('auth.driver')->user()->id);
        $this->assertEquals($audit->user_type, User::class);
    }
}
