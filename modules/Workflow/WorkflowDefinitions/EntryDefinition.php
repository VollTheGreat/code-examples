<?php

namespace App\BuildingSetUps\WorkflowDefinitions;

class EntryDefinition
{
    public const NAME = 'building-set-ups-entry';

    public const PROPERTY = 'status';
    public const PLACES = [
        self::PLACE_NEW,
        self::PLACE_IN_PROGRESS,
        self::PLACE_COMPLETED,
    ];
    public const PLACE_NEW = 'new';
    public const PLACE_IN_PROGRESS = 'in-progress';
    public const PLACE_COMPLETED = 'completed';
    public const TRANSITION_MARK_AS_IN_PROGRESS = 'mark as in-progress';
    public const TRANSITION_MARK_AS_COMPLETE = 'mark as complete';

    /**
     * Get workflow
     *
     * @return array
     */
    public function getWorkflow(): array
    {
        return [
            'name' => self::NAME,
            'places' => self::PLACES,
            'transitions' => [
                [
                    'name' => self::TRANSITION_MARK_AS_IN_PROGRESS,
                    'from' => self::PLACE_NEW,
                    'to' => self::PLACE_IN_PROGRESS,
                ],
                [
                    'name' => self::TRANSITION_MARK_AS_COMPLETE,
                    'from' => self::PLACE_IN_PROGRESS,
                    'to' => self::PLACE_COMPLETED,
                ],
            ],
            'property' => self::PROPERTY,
            'initial_marking' => self::PLACE_NEW,
        ];
    }
}
