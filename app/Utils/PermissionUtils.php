<?php

namespace App\Utils;

abstract class PermissionUtils
{
    const  Permissions = [
        ['name' => 'organizationSettings.view', 'group' => 'organization',
            'description' => 'User can view organization settings'],
        ['name' => 'organizationSettings.edit', 'group' => 'organization',
            'description' => 'User can edit organization settings'],

        ['name' => 'roles.view', 'group' => 'roles',
            'description' => 'User can view all roles'],
        ['name' => 'roles.create', 'group' => 'roles',
            'description' => 'User can create role'],
        ['name' => 'roles.edit', 'group' => 'roles',
            'description' => 'User can edit roles'],
        ['name' => 'roles.delete', 'group' => 'roles',
            'description' => 'User can delete a role'],

        ['name' => 'users.search', 'group' => 'users',
            'description' => 'User can search user accounts'],
        ['name' => 'users.view', 'group' => 'users',
            'description' => 'User can view all user accounts'],
        ['name' => 'users.create', 'group' => 'users',
            'description' => 'User can create a user account'],
        ['name' => 'users.edit', 'group' => 'users',
            'description' => 'User can edit a user account details'],
        ['name' => 'users.delete', 'group' => 'users',
            'description' => 'User can delete a user account'],

        ['name' => 'products.search', 'group' => 'products',
            'description' => 'User can search products'],
        ['name' => 'products.view', 'group' => 'products',
            'description' => 'User can view all products'],
        ['name' => 'products.create', 'group' => 'products',
            'description' => 'User can create a product'],
        ['name' => 'products.edit', 'group' => 'products',
            'description' => 'User can edit a product'],
        ['name' => 'products.delete', 'group' => 'products',
            'description' => 'User can delete a product'],

        ['name' => 'productItems.search', 'group' => 'products',
            'description' => 'User can search product items'],
        ['name' => 'productItems.view', 'group' => 'products',
            'description' => 'User can view all trackable product items'],
        ['name' => 'productItems.create', 'group' => 'products',
            'description' => 'User can add a trackable product item'],
        ['name' => 'productItems.edit', 'group' => 'products',
            'description' => 'User can edit trackable product item'],
        ['name' => 'productItems.delete', 'group' => 'products',
            'description' => 'User can delete trackable product item'],

        ['name' => 'tracking.view', 'group' => 'product tracking',
            'description' => 'User can view product item history logs '],
        ['name' => 'tracking.create', 'group' => 'product tracking',
            'description' => 'User can update product item location'],

        ['name' => 'customers.search', 'group' => 'customers',
            'description' => 'User can search customers'],
        ['name' => 'customers.view', 'group' => 'customers',
            'description' => 'User can view all customers'],
        ['name' => 'customers.create', 'group' => 'customers',
            'description' => 'User can create a customers'],
        ['name' => 'customers.edit', 'group' => 'customers',
            'description' => 'User can edit customers details'],
        ['name' => 'customers.delete', 'group' => 'customers',
            'description' => 'User can delete a customers'],

        ['name' => 'customerContracts.view', 'group' => 'contracts',
            'description' => 'User can view all contracts'],
        ['name' => 'customerContracts.create', 'group' => 'contracts',
            'description' => 'User can add a customer contract'],
        ['name' => 'customerContracts.edit', 'group' => 'contracts',
            'description' => 'User can edit customer contracts'],
        ['name' => 'customerContracts.delete', 'group' => 'contracts',
            'description' => 'User can delete any customer\' contract'],

        ['name' => 'worksheets.search', 'group' => 'worksheets',
            'description' => 'User can search worksheets'],
        ['name' => 'worksheets.view', 'group' => 'worksheets',
            'description' => 'User can view all worksheets (report)'],
        ['name' => 'worksheets.create', 'group' => 'worksheets',
            'description' => 'User can create a worksheet'],
        ['name' => 'worksheets.delete', 'group' => 'worksheets',
            'description' => 'User can delete any worksheets'],

        ['name' => 'stockBalances.view', 'group' => 'stock balances',
            'description' => 'User can view all stock balances'],
        ['name' => 'stockBalances.edit', 'group' => 'stock balances',
            'description' => 'User can adjust stock balances'],

        ['name' => 'standByCheckIn.view', 'group' => 'product checkin',
            'description' => 'User can view standby reminder products'],
        ['name' => 'standByCheckIn.create', 'group' => 'product checkin',
            'description' => 'User can receive standby reminder products'],

        ['name' => 'goodsReceiptNote.view', 'group' => 'goods receipt note',
            'description' => 'User can view all RGA and GRN documents'],
       ['name' => 'goodsReceiptNote.create', 'group' => 'goods receipt note',
            'description' => 'User can create  goods receipt note (GRN) for respective PO'],
        ['name' => 'goodsReceiptNote.approve', 'group' => 'goods receipt note',
            'description' => 'User can approve RGA and GRN documents'],

        ['name' => 'inspectionNote.view', 'group' => 'product inspection',
            'description' => 'User can view all created inspection reports'],
        ['name' => 'inspectionNote.create', 'group' => 'product inspection',
            'description' => 'User can inspect products'],


        ['name' => 'purchaseOrders.search', 'group' => 'purchase orders',
            'description' => 'User can search purchase orders'],
        ['name' => 'purchaseOrders.view', 'group' => 'purchase orders',
            'description' => 'User can view all purchase orders'],
        ['name' => 'purchaseOrders.create', 'group' => 'purchase orders',
            'description' => 'User can create a purchase order'],
        ['name' => 'purchaseOrders.delete', 'group' => 'purchase orders',
            'description' => 'User can delete any purchase order'],

        ['name' => 'rfqs.search', 'group' => 'RFQ\'s',
            'description' => 'User can search RFQ\'s'],
        ['name' => 'rfqs.view', 'group' => 'RFQ\'s',
            'description' => 'User can view all RFQ\'s'],
        ['name' => 'rfqs.create', 'group' => 'RFQ\'s',
            'description' => 'User can create an RFQ'],
        ['name' => 'rfqs.edit', 'group' => 'RFQ\'s',
            'description' => 'User can edit an RFQ'],
        ['name' => 'rfqs.delete', 'group' => 'RFQ\'s',
            'description' => 'User can delete any RFQ'],

        ['name' => 'purchaseRequests.search', 'group' => 'purchase requests',
            'description' => 'User can search purchase requests'],
        ['name' => 'purchaseRequests.view', 'group' => 'purchase requests',
            'description' => 'User can view all purchase requests'],
        ['name' => 'purchaseRequests.create', 'group' => 'purchase requests',
            'description' => 'User can create a purchase request'],
        ['name' => 'purchaseRequests.verify', 'group' => 'purchase requests',
            'description' => 'User can check/verify purchase requests'],
        ['name' => 'purchaseRequests.approve', 'group' => 'purchase requests',
            'description' => 'User can approve purchase requests'],

        ['name' => 'materialRequisition.view', 'group' => 'material requisition',
            'description' => 'User can view all material requisition requests (report)'],
        ['name' => 'materialRequisition.create', 'group' => 'material requisition',
            'description' => 'User can create a material requisition request'],
        ['name' => 'materialRequisition.verify', 'group' => 'material requisition',
            'description' => 'User can verify material requisition form request'],
        ['name' => 'materialRequisition.approve', 'group' => 'material requisition',
            'description' => 'User can approve material requisition from request'],

        ['name' => 'checkout.create', 'group' => 'checkout',
            'description' => 'User can issue products on MRN approval'],
        ['name' => 'checkout.view', 'group' => 'checkout',
            'description' => 'User can view all checkouts (report)'],

        ['name' => 'vendors.view', 'group' => 'vendors',
            'description' => 'User can view all vendors'],
        ['name' => 'vendors.create', 'group' => 'vendors',
            'description' => 'User can create a vendor'],
        ['name' => 'vendors.edit', 'group' => 'vendors',
            'description' => 'User can edit vendors info'],
        ['name' => 'vendors.delete', 'group' => 'vendors',
            'description' => 'User can delete vendors'],

        ['name' => 'uom.view', 'group' => 'uom',
            'description' => 'User can view all unit of measures'],
        ['name' => 'uom.create', 'group' => 'uom',
            'description' => 'User can create a unit of measure'],
        ['name' => 'uom.edit', 'group' => 'uom',
            'description' => 'User can edit unit of measure info'],
        ['name' => 'uom.delete', 'group' => 'uom',
            'description' => 'User can delete unit of measure'],
    ];


}
