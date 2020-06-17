<?php

namespace App\BuildingSetUps\WorkflowDefinitions;

class RequirementDefinition
{
    public const NAME = 'building-set-ups-requirement';

    public const PROPERTY = 'status';
    public const PLACES = [
        self::PLACE_MET,
        self::PLACE_NOT_MET,
        self::PLACE_NOT_APPLICABLE,
    ];
    public const PLACE_MET = 'met';
    public const PLACE_NOT_MET = 'not-met';
    public const PLACE_NOT_APPLICABLE = 'not-applicable';
    public const TRANSITION_MARK_AS_MET = 'mark as met';
    public const TRANSITION_MARK_AS_NOT_MET = 'mark as not met';
    public const TRANSITION_MARK_AS_NOT_APPLICABLE = 'mark as not applicable';

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
                    'name' => self::TRANSITION_MARK_AS_NOT_APPLICABLE,
                    'from' => self::PLACE_NOT_MET,
                    'to' => self::PLACE_NOT_APPLICABLE,
                ],
                [
                    'name' => self::TRANSITION_MARK_AS_MET,
                    'from' => self::PLACE_NOT_MET,
                    'to' => self::PLACE_MET,
                ],
                [
                    'name' => self::TRANSITION_MARK_AS_MET,
                    'from' => self::PLACE_NOT_APPLICABLE,
                    'to' => self::PLACE_MET,
                ],
                [
                    'name' => self::TRANSITION_MARK_AS_NOT_MET,
                    'from' => self::PLACE_MET,
                    'to' => self::PLACE_NOT_MET,
                ],
                [
                    'name' => self::TRANSITION_MARK_AS_NOT_MET,
                    'from' => self::PLACE_NOT_APPLICABLE,
                    'to' => self::PLACE_NOT_MET,
                ],
            ],
            'property' => self::PROPERTY,
            'initial_marking' => self::PLACE_NOT_MET,
        ];
    }
}
