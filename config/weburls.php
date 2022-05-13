<?php

return [

    /**
     *---------------
     * Root Url to the frontend app
     * --------------
     * This is used when resolving clickable urls on emails.
     *
     */
    'root' => env('FRONTEND_URL', env('APP_URL', 'http://localhost')),

    'material_requests' => [
        'history' => '/main/material-requisition/history',
        'approval' => '/main/material-requisition/approval',
        'verification' => '/main/material-requisition/verification',
        'issue' => '/main/checkout/issue-requests'
    ],
    'purchase_requests' => [
        'history' => '/main/purchase-requisition/history',
        'approval' => '/main/purchase-requisition/approve',
        'verification' => '/main/purchase-requisition/check'
    ]

];
