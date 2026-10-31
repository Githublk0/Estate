<?php

return [
    'acl' => [
        'title' => 'EstateFlow',
        'qualifications' => 'Qualification history',
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],

    'menu' => [
        'title' => 'EstateFlow',
        'qualifications' => 'Qualification history',
    ],

    'properties' => [
        'index' => [
            'title' => 'Property inventory',
            'create-btn' => 'Add property',
            'empty' => 'No properties yet. Add a property to start your inventory.',
            'name' => 'Name',
            'address' => 'Address',
            'price' => 'Price (minor units)',
            'bedrooms' => 'Bedrooms',
            'status' => 'Status',
            'actions' => 'Actions',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'view' => 'View',
            'edit' => 'Edit',
            'activate' => 'Activate',
            'deactivate' => 'Deactivate',
            'delete' => 'Delete',
            'delete-confirm' => 'Delete this property?',
        ],
        'create' => [
            'title' => 'Add property',
            'save' => 'Create property',
        ],
        'edit' => [
            'title' => 'Edit property',
            'save' => 'Save changes',
        ],
        'show' => [
            'title' => 'Property details',
            'edit' => 'Edit property',
            'back' => 'Back to properties',
        ],
        'fields' => [
            'name' => 'Name',
            'address' => 'Address',
            'price-cents' => 'Price (minor currency units)',
            'bedrooms' => 'Bedrooms',
        ],
        'messages' => [
            'created' => 'Property created.',
            'updated' => 'Property updated.',
            'status-updated' => 'Property status updated.',
            'deleted' => 'Property deleted.',
        ],
    ],

    'qualifications' => [
        'title' => 'Lead qualification history',
        'empty' => 'No qualification events are available for your account yet.',
        'unlinked' => 'Unlinked lead',
        'lead' => 'Lead',
        'score' => 'Score',
        'status' => 'Qualification',
        'recorded' => 'Recorded',
        'review' => 'Human review required',
        'no-review' => 'No human review flagged',
        'open-lead' => 'Open lead',
    ],
];
