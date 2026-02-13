<?php

return [
    'pipeline' => [
        'stages' => [
            [
                'identifier' => 'dev_stage',
                'display_name' => 'Development',
                'task_states' => ['development'],
                'theme_color' => '#3490dc',
            ],
            [
                'identifier' => 'test_stage',
                'display_name' => 'Testing',
                'task_states' => ['testing'],
                'theme_color' => '#38c172',
            ],
            [
                'identifier' => 'review_stage',
                'display_name' => 'Review',
                'task_states' => ['review'],
                'theme_color' => '#f6993f',
            ],
            [
                'identifier' => 'deploy_stage',
                'display_name' => 'Deployment',
                'task_states' => ['deployment'],
                'theme_color' => '#e3342f',
            ],
            [
                'identifier' => 'completed',
                'display_name' => 'Finished',
                'task_states' => ['done'],
                'theme_color' => '#6c757d',
            ],
        ],
    ],
];
