<?php

namespace Tests\Feature\Guardians;

/**
 * Class BaseAuditTests
 */
interface BaseAuditTestsInterface
{
    public function getAuditClass(): string;

    public function updateModel(): void;
}
