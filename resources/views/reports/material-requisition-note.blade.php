@extends('reports.layouts.internal-doc')

@section('header-meta')
    <div class="meta-section float-right">
        <table class="meta-table">
            <tr>
                <td class="title-col">MRN. NUMBER MDT</td>
                <td class="value-col">
                    <div class="underlined">{{$mrn->sn}}</div>
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
    MATERIAL REQUISITION NOTE(MRN)
@endsection


@section('content')
    <table class="table table-bordered main-content-table">
        <thead>
        <tr>
            <th>S/N</th>
            <th>ITEM CODE</th>
            <th>M/PN</th>
            <th>ITEM DESCRIPTION</th>
            <th>QTY-RQD</th>
            <th>REASON/S FOR REQUEST</th>
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
                <td>{{$item->purpose_title}}</td>
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
                <td class="title-col">Requested by</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$mrn->createdBy->first_name}}
                        {{$mrn->createdBy->last_name}}
                    </div>
                </td>
                <td class="title-col">Verified by</td>
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
                        {{$mrn->created_at->format('F d, Y')}}
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
