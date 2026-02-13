<?php

use App\Models\Task;

return [
    'classes' => [
        Task::class,
    ],

    'mapping' => [
        'project_task' => [
            'initial_state' => 'development',
            'states' => [
                'development' => [
                    'label' => 'Development',
                    'transitions' => [
                        'resolve' => [
                            'to' => 'testing',
                            'label' => 'Testga yuborish',
                            'permission' => \App\Constants\RoleNames::EMPLOYEE,
                        ],
                    ],
                ],
                'testing' => [
                    'label' => 'Testing',
                    'transitions' => [
                        'pass' => [
                            'to' => 'review',
                            'label' => 'Leadga yuborish',
                            'permission' => \App\Constants\RoleNames::EMPLOYEE,
                        ],
                        'reject' => [
                            'to' => 'development',
                            'label' => 'Xatolik (Devga qaytarish)',
                            'permission' => \App\Constants\RoleNames::EMPLOYEE,
                        ],
                    ],
                ],
                'review' => [
                    'label' => 'Review',
                    'transitions' => [
                        'approve' => [
                            'to' => 'deployment',
                            'label' => 'Deployga ruxsat',
                            'permission' => \App\Constants\RoleNames::EMPLOYEE,
                        ],
                        'reject' => [
                            'to' => 'development',
                            'label' => 'Qayta ishlashga',
                            'permission' => \App\Constants\RoleNames::EMPLOYEE,
                        ],
                    ],
                ],
                'deployment' => [
                    'label' => 'Deployment',
                    'transitions' => [
                        'finish' => [
                            'to' => 'done',
                            'label' => 'Yakunlash',
                            'permission' => \App\Constants\RoleNames::EMPLOYEE,
                        ],
                        'fail' => [
                            'to' => 'development',
                            'label' => 'Crash (Devga qaytarish)',
                            'permission' => \App\Constants\RoleNames::EMPLOYEE,
                        ],
                    ],
                ],
                'done' => [
                    'label' => 'Bajarildi',
                    'transitions' => [],
                ],
            ],
        ],
    ],
];
