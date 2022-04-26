@extends('reports.layouts.internal-doc')

@section('header-meta')
    <div class="meta-section float-right">
        <table class="meta-table">
            <tr>
                <td class="title-col">SIV. NUMBER MDT</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$mrn->sn}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">MRN. NUMBER MDT</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$mrn->sn}}
                    </div>
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
    STORES ISSUE VOUCHER (SIV)
@endsection


@section('content')
    <table class="table table-bordered main-content-table">
        <thead>
        <tr>
            <th>S/N</th>
            <th>ITEM CODE</th>
            <th>M/PN</th>
            <th>DESCRIPTION</th>
            <th>REQUESTED</th>
            <th>ISSUED</th>
            <th>CUSTOMER</th>
            <th>REMARKS</th>
        </tr>
        </thead>
        <tbody>
        @foreach($mrn->items as $item)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$item->product->item_code}}</td>
                <td>{{$item->product->manufacturer_part_number}}</td>
                <td>{{$item->product->description}}</td>
                <td>{{$item->requested_qty}}</td>
                <td>{{$item->issued_qty}}</td>
                <td>{{$item->customer->name}} | {{$item->customer->branch??$item->customer->region}}</td>
                <td>{{$item->purpose_title}}</td>
            </tr>
        @endforeach

        </tbody>
    </table>

    <div class="mt-3 ms-3 me-3">
        <span class="title pb-2" style="font-size: 14px">Machines Serial Numbers Issued</span>
        <div style="font-size: 12px;line-height: 2em">
            @foreach($mrn->allocationActivities as $allocation)
                {{$allocation->productItem->serial_number}}
                @if (!$loop->last)
                    ,
                @endif
            @endforeach
        </div>
    </div>
@endsection


@section('footer')
    <div class="mx-4 activities">
        <table class="activities-table">
            <tbody>
            <tr>
                <td class="title-col">Raised by</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$issue->createdBy->first_name}}
                        {{$issue->createdBy->last_name}}
                    </div>
                </td>
                <td class="title-col">Approved by</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$approval->createdBy->first_name}}
                        {{$approval->createdBy->last_name}}
                    </div>
                </td>
                <td class="title-col">Received by</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$mrn->createdBy->first_name}}
                        {{$mrn->createdBy->last_name}}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$mrn->created_at->format('F d, Y')}}
                    </div>
                </td>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$issue->created_at->format('F d, Y')}}
                    </div>
                </td>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$issue->created_at->format('F d, Y')}}
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
