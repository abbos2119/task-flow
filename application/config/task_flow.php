<?php

return [
    'pipeline' => [
        'stages' => [
            [
                'identifier' => 'dev_stage',
                'display_name' => 'Development',
                'task_states' => ['development'],
            ],
            [
                'identifier' => 'test_stage',
                'display_name' => 'Testing',
                'task_states' => ['testing'],
            ],
            [
                'identifier' => 'review_stage',
                'display_name' => 'Review',
                'task_states' => ['review'],
            ],
            [
                'identifier' => 'deploy_stage',
                'display_name' => 'Deployment',
                'task_states' => ['deployment'],
            ],
            [
                'identifier' => 'completed',
                'display_name' => 'Finished',
                'task_states' => ['done'],
            ],
        ],
    ],
];
