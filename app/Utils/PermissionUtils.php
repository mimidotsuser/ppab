<?php

namespace App\Utils;

interface PermissionUtils
{
    const  Permissions = [
        ['name' => 'organization.settings.view', 'group' => 'organization',
            'description' => 'User can view organization settings'],
        ['name' => 'organization.settings.edit', 'group' => 'organization',
            'description' => 'User can edit organization settings'],

        ['name' => 'user.roles.view', 'group' => 'roles',
            'description' => 'User can view all roles'],
        ['name' => 'user.roles.create', 'group' => 'roles',
            'description' => 'User can create role'],
        ['name' => 'user.roles.edit', 'group' => 'roles',
            'description' => 'User can edit roles'],
        ['name' => 'user.roles.delete', 'group' => 'roles',
            'description' => 'User can delete a role'],

        ['name' => 'identity.users.view', 'group' => 'users',
            'description' => 'User can view all user accounts'],
        ['name' => 'identity.users.create', 'group' => 'users',
            'description' => 'User can create a user account'],
        ['name' => 'identity.users.edit', 'group' => 'users',
            'description' => 'User can edit a user account details'],
        ['name' => 'identity.users.delete', 'group' => 'users',
            'description' => 'User can delete a user account'],

        ['name' => 'inventory.products.view', 'group' => 'products',
            'description' => 'User can view all products'],
        ['name' => 'inventory.products.create', 'group' => 'products',
            'description' => 'User can create a product'],
        ['name' => 'inventory.products.edit', 'group' => 'products',
            'description' => 'User can edit a product'],
        ['name' => 'inventory.products.delete', 'group' => 'products',
            'description' => 'User can delete a product'],

        ['name' => 'inventory.tracking.view', 'group' => 'product tracking',
            'description' => 'User can view product tracking logs '],
        ['name' => 'inventory.tracking.create', 'group' => 'product tracking',
            'description' => 'User can add product tracking log/current product item location'],
        ['name' => 'inventory.tracking.edit', 'group' => 'product tracking',
            'description' => 'User can update product tracking logs/current product item location'],

        ['name' => 'organization.customers.view', 'group' => 'customers',
            'description' => 'User can view all clients'],
        ['name' => 'organization.customers.create', 'group' => 'customers',
            'description' => 'User can create a client'],
        ['name' => 'organization.customers.edit', 'group' => 'customers',
            'description' => 'User can edit client details'],
        ['name' => 'organization.customers.delete', 'group' => 'customers',
            'description' => 'User can delete a customers/client'],

        ['name' => 'user.worksheets.create', 'group' => 'worksheets',
            'description' => 'User can create a worksheet'],
        ['name' => 'user.worksheets.delete', 'group' => 'worksheets',
            'description' => 'User can delete other user\'s worksheets'],

        ['name' => 'inventory.ledger.view', 'group' => 'stock ledger',
            'description' => 'User can view stock ledger'],
        ['name' => 'inventory.ledger.edit', 'group' => 'stock ledger',
            'description' => 'User can adjust stock ledger items quantity'],

        ['name' => 'checkin.receivingReports.view', 'group' => 'receiving report',
            'description' => 'User can view all RGA and GRN documents'],
        ['name' => 'checkin.receivingReports.approve', 'group' => 'receiving report',
            'description' => 'User can approve RGA and GRN documents'],


        ['name' => 'checkin.inspectionReports.view', 'group' => 'product inspection',
            'description' => 'User can view all created inspection reports'],
        ['name' => 'checkin.inspectionReports.create', 'group' => 'product inspection',
            'description' => 'User can inspect products'],

        ['name' => 'checkin.receive.create', 'group' => 'checkin',
            'description' => 'User can receive/checkin purchased, leased,
            out of demo and standby reminder products'],

        ['name' => 'procurement.purchaseOrders.view', 'group' => 'purchase orders',
            'description' => 'User can view all purchase orders'],
        ['name' => 'procurement.purchaseOrders.create', 'group' => 'purchase orders',
            'description' => 'User can create a purchase order'],
        ['name' => 'procurement.purchaseOrders.delete', 'group' => 'purchase orders',
            'description' => 'User can delete any purchase order'],

        ['name' => 'procurement.rfqs.view', 'group' => 'RFQ\'s',
            'description' => 'User can view all RFQ\'s'],
        ['name' => 'procurement.rfqs.create', 'group' => 'RFQ\'s',
            'description' => 'User can create an RFQ'],
        ['name' => 'procurement.rfqs.delete', 'group' => 'RFQ\'s',
            'description' => 'User can delete any RFQ'],

        ['name' => 'procurement.purchaseRequests.create', 'group' => 'purchase requests',
            'description' => 'User can create a purchase request'],
        ['name' => 'procurement.purchaseRequests.check', 'group' => 'purchase requests',
            'description' => 'User can check/verify purchase requests'],
        ['name' => 'procurement.purchaseRequests.approve', 'group' => 'purchase requests',
            'description' => 'User can approve purchase requests'],

        ['name' => 'checkout.requests.create', 'group' => 'material requisition',
            'description' => 'User can create a material requisition request'],
        ['name' => 'checkout.requests.verify', 'group' => 'material requisition',
            'description' => 'User can verify MRN'],
        ['name' => 'checkout.requests.approve', 'group' => 'material requisition',
            'description' => 'User can approve MRN'],
        ['name' => 'checkout.requests.issue', 'group' => 'material requisition',
            'description' => 'User can issue products on MRN approval'],

        ['name' => 'report.worksheets.view', 'group' => 'reports',
            'description' => 'User can view all worksheets'],
        ['name' => 'report.checkouts.view', 'group' => 'reports',
            'description' => 'User can view all checkouts'],
    ];


}
