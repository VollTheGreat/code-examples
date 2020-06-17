<?php

namespace Tests\Properties\Observers;

use App\BuildingSetUps\Models\Entry;
use App\Workflow\Resolvers\TransitionByStatusResolver;
use Tests\TestCase;
use Mockery;
use App\Properties\Observers\PropertyObserver;
use App\Properties\Property;
use App\Properties\Mailers\PropertyMailer;
use App\Properties\Exceptions\NotAllowedStatusTransitionException;

class PropertyObserverTest extends TestCase
{
    /**
     * @var \App\Properties\Observers\PropertyObserver
     */
    private $propertyObserver;

    /**
     * Set up
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var \App\Properties\Mailers\PropertyMailer */
        $propertyMailer = app()->make(Mockery::class)->mock(PropertyMailer::class);
        $resolver = app()->make(Mockery::class)->mock(TransitionByStatusResolver::class);
        $resolver->shouldReceive('resolve')->andReturn(true);
        $this->propertyObserver = new PropertyObserver($propertyMailer, $resolver);
    }

    /**
     * Test manual status validation upon saving
     *
     * @param int $targetStatus
     * @param bool $shouldFail
     *
     * @dataProvider providerTestManualStatusValidationUponSaving
     *
     * @throws \App\Properties\Exceptions\NotAllowedStatusTransitionException
     */
    public function testManualStatusValidationUponSaving(int $targetStatus, bool $shouldFail)
    {
        /** @var \App\Properties\Property */
        $property = app()->make(Mockery::class)->mock(Property::class);
        $property->shouldReceive('getAttribute')->with('status_manual')->andReturn(
            $targetStatus
        );
        $property->shouldReceive('isDirty')->with('status_manual')->andReturn(true);
        $property->shouldReceive('getOriginal')->andReturn([
            'status_manual' => config('property.status_reverse.Maintenance'),
        ]);
        $entry = app()->make(Mockery::class)->mock(Entry::class);
        $entry->shouldReceive('getStatus')->andReturn('new');
        $property->shouldReceive('getAttribute')->with('entry')->andReturn(
            $entry
        );

        if ($shouldFail) {
            $this->expectException(NotAllowedStatusTransitionException::class);
        }

        $this->propertyObserver->saving($property);
    }

    /**
     * Data provider for testManualStatusValidationUponSaving
     */
    public function providerTestManualStatusValidationUponSaving()
    {
        return [
            [
                9, // config('property.status_reverse.Pre-Offer'),
                true,
            ],
            [
                0,// config('property.status_reverse.Under_Offer'),
                true,
            ],
            [
                4, // config('property.status_reverse.Signed_Proposal'),
                false,
            ],
            [
                6, // config('property.status_reverse.Rejected_Offer'),
                false,
            ],
            [
                11,// config('property.status_reverse.Key_Holding'),
                true,
            ],
            [
                999,// config('property.status_reverse.Automatic'),
                false,
            ],

        ];
    }
}
