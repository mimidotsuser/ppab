<?php

namespace App\Utils;

class PurchaseOrderUtils
{

    public static function deliveredItemsSubQuery(): string
    {
        $subQuery = <<< EOL
            SELECT `grn_item`.po_item_id,
                   SUM(`grn_item`.`delivered_qty`) as `delivered_qty`,
                   SUM( `grn_item`.`rejected_qty`) AS `rejected_qty`

            FROM `goods_receipt_notes` as `grn`
                     left join (
                select `activity`.`id`
                FROM `goods_receipt_note_activities` `activity`
                         left join
                     (select MAX(`latest`.`id`) as `id`,
                             `stage`,
                             `latest`.`goods_receipt_note_id`
                      from `goods_receipt_note_activities` `latest`
                      group by `latest`.`goods_receipt_note_id`, `latest`.`stage`) as `latestOfMany`
                     on `latestOfMany`.`id` = `activity`.`id`
                where `activity`.`stage` != 'APPROVAL_REJECTED'
            )
                as `activities` on `grn`.`id` = `activities`.`id`
                     left join `goods_receipt_note_items` `grn_item`
                               on `grn`.`id` = `grn_item`.`goods_receipt_note_id`
                GROUP BY  `grn_item`.po_item_id
            EOL;

        return str_replace("\n", "", $subQuery);
    }

    public static function purchaseOrderTotalQtySubQuery(): string
    {
        $subQuery = <<<EOL
        SELECT  `po_item`.`id`, (`uom`.`unit`*`po_item`.`qty`) as `total_qty`
            FROM `purchase_order_items` `po_item`
         JOIN `unit_of_measures` `uom` ON `uom`.`id` = `po_item`.`unit_of_measure_id`
        GROUP BY `po_item`.`id`
        EOL;
        return str_replace("\n", "", $subQuery);
    }
}
