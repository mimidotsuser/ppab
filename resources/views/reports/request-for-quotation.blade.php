@extends('reports.layouts.external-doc')

@section('header-meta')
    <table class="meta-table">
        <tr>
            <td class="title-col">RFQ NUMBER</td>
            <td class="value-col">
                <div class="underlined">{{$rfq->sn}}</div>
            </td>
        </tr>
        <tr>
            <td class="title-col">PR NUMBER</td>
            <td class="value-col">
                <div class="underlined">{{$rfq->purchaseRequest->sn}}</div>
            </td>
        </tr>
        <tr>
            <td class="title-col">DOC. DATE</td>
            <td class="value-col">
                <div class="underlined">{{$rfq->closing_date}}</div>
            </td>
        </tr>
        <tr>
            <td class="title-col">CLOSING DATE</td>
            <td class="value-col">
                <div class="underlined">{{$rfq->closing_date->format('F d, Y')}}</div>
            </td>
        </tr>
    </table>
@endsection

@section('doc-title')
    REQUEST FOR QUOTATION/PFI(RFQ)
@endsection

@section('content')
    <div class="instructions py-3">
        <div class="title">YOU ARE INVITED TO QUOTE FOR ITEMS LISTED BELOW NOTES:-</div>
        <ol class="list" type="1">
            <li>Your quote should reach the buyer on or before the closing date.</li>
            <li>All prices should include all costs delivered Nairobi</li>
            <li>Replies should be in the same format and a copy retained by the seller for records
            </li>
            <li>Any price variations MUST be communicated before delivery</li>
        </ol>
    </div>
    <table class="table table-bordered main-content-table">
        <thead>
        <tr>
            <th>S/N</th>
            <th>ITEM CODE</th>
            <th>M/PN</th>
            <th>ITEM DESCRIPTION</th>
            <th>UOM</th>
            <th>QTY</th>
            <th>UNIT PRICE</th>
            <th>TOTAL PRICE</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rfq->items as $item)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$item->product->item_code}}</td>
                <td>{{$item->product->manufacturer_part_number}}</td>
                <td>{{$item->product->description}}</td>
                <td>{{$item->uom->title}}</td>
                <td>{{$item->qty}}</td>
                <td style="padding:0;vertical-align: top" align="top" class="amount-col">
                    <table>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                </td>
                <td style="padding:0;vertical-align: top" align="top" class="amount-col">
                    <table>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>

@endsection

@section('activities')
    <table class="activities-table">
        <tbody>
        <tr>
            <td class="title-col">Prepared by</td>
            <td class="value-col">
                <div class="underlined">
                    {{$rfq->createdBy->first_name}}
                    {{$rfq->createdBy->last_name}}
                </div>
            </td>
            <td class="title-col"> Approved by</td>
            <td class="value-col">
                <div class="underlined">
                    {{$rfq->createdBy->first_name}}
                    {{$rfq->createdBy->last_name}}
                </div>
            </td>

        </tr>
        <tr>
            <td class="title-col">Sign</td>
            <td class="value-col">
                <div class="underlined"></div>
            </td>

            <td class="title-col">Sign</td>
            <td class="value-col">
                <div class="underlined"></div>
            </td>
        </tr>
        </tbody>
    </table>
@endsection
