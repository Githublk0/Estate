<?php

return [
    [
        'key' => 'estateflow',
        'name' => 'estateflow::app.acl.title',
        'route' => 'admin.estateflow.properties.index',
        'sort' => 30,
    ], [
        'key' => 'estateflow.properties.view',
        'name' => 'estateflow::app.acl.view',
        'route' => 'admin.estateflow.properties.show',
        'sort' => 1,
    ], [
        'key' => 'estateflow.properties.create',
        'name' => 'estateflow::app.acl.create',
        'route' => ['admin.estateflow.properties.create', 'admin.estateflow.properties.store'],
        'sort' => 2,
    ], [
        'key' => 'estateflow.properties.edit',
        'name' => 'estateflow::app.acl.edit',
        'route' => [
            'admin.estateflow.properties.edit',
            'admin.estateflow.properties.update',
            'admin.estateflow.properties.status',
        ],
        'sort' => 3,
    ], [
        'key' => 'estateflow.properties.delete',
        'name' => 'estateflow::app.acl.delete',
        'route' => 'admin.estateflow.properties.destroy',
        'sort' => 4,
    ], [
        'key' => 'estateflow.qualifications',
        'name' => 'estateflow::app.acl.qualifications',
        'route' => 'admin.estateflow.qualifications.index',
        'sort' => 2,
    ],
];
