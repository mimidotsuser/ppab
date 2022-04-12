@extends('reports.layouts.internal-doc')

@section('header-meta')
    <div class="meta-section float-right">
        <table class="meta-table">

            <tr>
                <td class="title-col">GRN. NUMBER MDT</td>
                <td class="value-col">
                    <div class="underlined">{{$receipt->sn}}</div>
                </td>
            </tr>
            <tr>
                <td class="title-col">LPO. NUMBER</td>
                <td class="value-col">
                    <div class="underlined">{{$receipt->purchaseOrder->sn}}</div>
                </td>
            </tr>
            <tr>
                <td class="title-col">D/NOTE. NUMBER</td>
                <td class="value-col">
                    <div class="underlined">{{$receipt->reference}}</div>
                </td>
            </tr>
            <tr>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{\Illuminate\Support\Carbon::now()->format('F d, Y')}}
                    </div>
                </td>
            </tr>
        </table>
    </div>

@endsection
@section('doc-title')
    GOODS RECEIVED NOTE (GRN)
@endsection


@section('content')
    <table class="table table-bordered main-content-table">
        <thead>
        <tr>
            <th>S/N</th>
            <th>ITEM CODE</th>
            <th>M/PN</th>
            <th>ITEM DESCRIPTION</th>
            <th>UOM</th>
            <th>ORD QTY</th>
            <th>RCD QTY</th>
            <th>UNIT PRICE</th>
            <th>TOTAL COST</th>
        </tr>
        </thead>
        <tbody>
        @foreach($receipt->items as $item)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$item->product->item_code}}</td>
                <td>{{$item->product->manufacturer_part_number}}</td>
                <td>{{$item->product->description}}</td>
                <td align="center">{{$item->purchaseOrderItem->uom->title}}</td>
                <td align="center">{{$item->purchaseOrderItem->qty}}</td>
                <td align="center">
                    @if(empty($item->delivered_qty ) || $item->delivered_qty == 0)
                        0
                    @else
                        {{$item->delivered_qty/$item->purchaseOrderItem->uom->unit}}
                    @endif
                </td>
                <td>
                    {{formatCurrency( $item->purchaseOrderItem->unit_price,
                        $receipt->purchaseOrder->currency->code)}}
                </td>
                <td>
                    @if(empty($item->delivered_qty ) || $item->delivered_qty == 0)
                        {{formatCurrency(0,   $receipt->purchaseOrder->currency->code)}}
                    @else
                        {{formatCurrency(
                        $item->purchaseOrderItem->unit_price *
                         ($item->delivered_qty/$item->purchaseOrderItem->uom->unit),
                       $receipt->purchaseOrder->currency->code
                        )}}
                    @endif

                </td>
            </tr>

        @endforeach
        </tbody>
    </table>
@endsection


@section('footer')
    <div class="mx-4 activities">
        <table class="activities-table">
            <tbody>
            <tr>
                <td class="title-col">Prepared by</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$receipt->createdBy->first_name}}
                        {{$receipt->createdBy->last_name}}
                    </div>
                </td>
                <td class="title-col">Checked by</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$verification->createdBy->first_name}}
                        {{$verification->createdBy->last_name}}
                    </div>
                </td>
                <td class="title-col">Approved by</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$approval->createdBy->first_name}}
                        {{$approval->createdBy->last_name}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$receipt->created_at->format('F d, Y')}}
                    </div>
                </td>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$verification->created_at->format('F d, Y')}}
                    </div>
                </td>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$approval->created_at->format('F d, Y')}}
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
