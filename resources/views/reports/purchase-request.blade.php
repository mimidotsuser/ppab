@extends('reports.layouts.internal-doc')

@section('header-meta')
    <div class="meta-section float-right">
        <table class="meta-table">

            <tr>
                <td class="title-col">PR. NUMBER MDT</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$pr->sn}}
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
    PURCHASE REQUISITION (PR)
@endsection


@section('content')
    <table class="table table-bordered main-content-table">
        <thead>
        <tr>
            <th>S/N</th>
            <th>ITEM CODE</th>
            <th>M/PN</th>
            <th>ITEM DESCRIPTION</th>
            <th>RQ</th>
            <th>MIN</th>
            <th>MAX</th>
            <th>BAL.</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pr->items as $item)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$item->product->item_code}}</td>
                <td>{{$item->product->manufacturer_part_number}}</td>
                <td>{{$item->product->description}}</td>
                <td>{{$item->requested_qty}}</td>
                <td>{{$item->product->min_level}}</td>
                <td>{{$item->product->max_level}}</td>
                <td>{{$item->product->balance->stock_balance}}</td>
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
                        {{$pr->createdBy->first_name}}
                        {{$pr->createdBy->last_name}}
                    </div>
                </td>
                <td class="title-col">Verified by</td>
                <td class="value-col">
                    <div class="underlined">
                        @if(!empty($verification))
                            {{$verification->createdBy->first_name}}
                            {{$verification->createdBy->last_name}}
                        @endif
                    </div>
                </td>
                <td class="title-col">Approved by</td>
                <td class="value-col">
                    <div class="underlined">
                        @if(!empty($approval))
                            {{$approval->createdBy->first_name}}
                            {{$approval->createdBy->last_name}}
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        {{$pr->created_at->format('F d, Y')}}
                    </div>
                </td>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        @if(!empty($verification))
                            {{$verification->created_at->format('F d, Y')}}
                        @endif
                    </div>
                </td>
                <td class="title-col">Date</td>
                <td class="value-col">
                    <div class="underlined">
                        @if(!empty($approval))
                            {{$approval->created_at->format('F d, Y')}}
                        @endif
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection
