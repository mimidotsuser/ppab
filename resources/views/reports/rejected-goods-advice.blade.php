@extends('reports.layouts.internal-doc')

@section('header-meta')
    <div class="meta-section float-right">
        <table class="meta-table">
            <tr>
                <td class="title-col">RGA NUMBER MDT</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$receipt->sn}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">SUPPLIER</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$receipt->purchaseOrder->vendor->name}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">LPO. NUMBER</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$receipt->purchaseOrder->sn}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">D/NOTE. NUMBER</td>
                <td class="value-col">
                    <div class="underlined w-100">
                        {{$receipt->reference}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">DATE</td>
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
    REJECTED GOODS ADVICE (RGA)
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
            <th>PO QTY</th>
            <th>ACCEPTED</th>
            <th>REJECTED</th>
            <th>REMARKS</th>
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
                    {{$item->delivered_qty-$item->rejected_qty}}
                </td>
                <td align="center">
                    {{$item->rejected_qty}}
                </td>
                <td align="center">
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
