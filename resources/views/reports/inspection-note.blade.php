<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta http-equiv="Content-Type" content="charset=utf-8"/>
    <style type="text/css">
        .checklist-table .outcome {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 21px;
        }
    </style>
    <link rel="stylesheet" href="./css/reports/main.css">
    <style>
        header {
            margin-top: 35px;

        }

        header, section {
            margin-left: 35px;
            margin-right: 35px;
            font-size: 12px;
        }

        header .company-info {
            text-transform: uppercase;
            line-height: 1.8em;
        }

        header .doc-title {
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            text-decoration: underline;
        }

        .intro {
            line-height: 1.8em;
        }

        table.table {
            font-size: 11px;
        }

        section .subtitle {
            text-decoration: underline;
            font-size: 13px;

        }

        section .remarks {
            line-height: 1.8;
            font-size: 11px;
        }

        section .activities table {
            width: 40%;

        }

        section .activities td {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
    </style>
</head>

<body>
<header>
    <div class="company-info">
        @if(!empty($company) )
            <div class="col-12">
                {!! $company->name !!}
            </div>
            <div> {!! $company->postal_address !!}</div>
        @endif
    </div>

    <div class="doc-title">
        INSPECTION FORM
    </div>
</header>
<section>
    <div class="intro py-2">
        <strong>To, </strong>
        <div><strong>The Inspection and acceptance Officer</strong></div>

        Please inspect the items listed herein below to facilitate receipt.
    </div>

</section>
<section>

</section>
<section>
    <table class="table table-bordered grn-items-table">
        <thead>
        <tr>
            <th>S/N</th>
            <th>ITEM CODE</th>
            <th>UOM</th>
            <th>QTY ORDERED</th>
            <th>QTY RECEIVED</th>
            <th>QTY ACCEPTED</th>
            <th>QTY Rejected</th>
        </tr>
        </thead>
        <tbody>
        @foreach($note->goodsReceiptNote->items as $item)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$item->product->item_code}}</td>
                <td>{{$item->purchaseOrderItem->uom->title}}</td>
                <td>{{$item->purchaseOrderItem->qty}}</td>
                <td>{{($item->delivered_qty??0)/$item->purchaseOrderItem->uom->unit }}</td>
                <td>
                    {{(($item->delivered_qty??0)- ($item->rejected_qty??0))/$item->purchaseOrderItem->uom->unit }}
                </td>
                <td>{{ ($item->rejected_qty??0)/$item->purchaseOrderItem->uom->unit }}</td>
            </tr>
        @endforeach

        </tbody>
    </table>

</section>
<section>
    <div class="subtitle py-3">PARAMETERS CHECKED</div>
    <table class="table table-bordered checklist-table">
        <thead>
        <tr>
            <th>S/N</th>
            <th>FEATURES INSPECTED</th>
            <th>Yes</th>
            <th>No</th>
        </tr>
        </thead>
        <tbody>
        @foreach($note->checklist as $checklist)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$checklist->feature}}</td>
                <td class="outcome">
                    @if($checklist->passed)
                        &#10004;
                    @endif
                </td>
                <td class="outcome">
                    @unless($checklist->passed)
                        &#10006;
                    @endunless
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</section>
<section>
    <div class="subtitle py-5 mt-4">
        REMARKS
    </div>
    <div class="remarks">
        @unless(empty($inspection))
            {{$inspection->remarks}}
        @endunless
    </div>
</section>
<section>
    <div class="pt-3 activities">
        <table>
            <tbody>
            <tr>
                <td class="title-col" style="width: 97px;">INSPECTED BY</td>
                <td class="value-col">
                    <div class="underlined" style="line-height: 1.5">
                        @unless(empty($inspection))
                            {{$inspection->createdBy->first_name}}
                            {{$inspection->createdBy->last_name}}
                        @endunless
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">DATE</td>
                <td class="value-col">
                    <div class="underlined" style="line-height: 1.5">
                        @unless(empty($inspection))
                            {{$inspection->created_at->format('F d, Y')}}
                        @endunless

                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</section>


</body>
</html>
