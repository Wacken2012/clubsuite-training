<?php
return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'group#index', 'url' => '/groups', 'verb' => 'GET'],
        ['name' => 'group#create', 'url' => '/groups', 'verb' => 'POST'],
        ['name' => 'group#show', 'url' => '/groups/{id}', 'verb' => 'GET'],
        ['name' => 'event#index', 'url' => '/events', 'verb' => 'GET'],
        ['name' => 'event#create', 'url' => '/events', 'verb' => 'POST'],
        ['name' => 'event#show', 'url' => '/events/{id}', 'verb' => 'GET'],
        ['name' => 'event#update', 'url' => '/events/{id}', 'verb' => 'PUT'],
        ['name' => 'attendance#index', 'url' => '/attendance', 'verb' => 'GET'],
        ['name' => 'attendance#create', 'url' => '/attendance', 'verb' => 'POST'],
        ['name' => 'attendance#update', 'url' => '/attendance/{id}', 'verb' => 'PUT'],
    ],
];
