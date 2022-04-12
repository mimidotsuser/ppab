@extends('reports.layouts.external-doc')

@section('header-meta')
    <table class="meta-table">
        <tr>
            <td class="title-col">PO NUMBER</td>
            <td class="value-col">
                <div class="underlined">
                    {{$purchaseOrder->sn}}
                    {{$purchaseOrder->sn}}
                </div>
            </td>
        </tr>
        <tr>
            <td class="title-col">DOC. DATE</td>
            <td class="value-col">
                <div class="underlined">
                    {{\Illuminate\Support\Carbon::now()->format('F d, Y')}}
                </div>
            </td>
        </tr>
        <tr>
            <td class="title-col">DOC. VALIDITY</td>
            <td class="value-col">
                <div class="underlined">
                    {{$purchaseOrder->doc_validity->format('F d, Y')}}
                </div>
            </td>
        </tr>
    </table>
@endsection

@section('doc-title')
    PURCHASE ORDER
@endsection

@section('content')
    <div class="instructions py-3">
        PLEASE SUPPLY THE FOLLOWING GOODS/SERVICES
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
        @foreach($purchaseOrder->items as $item)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$item->product->item_code}}</td>
                <td>{{$item->product->manufacturer_part_number}}</td>
                <td>{{$item->product->description}}</td>
                <td>{{$item->uom->title}}</td>
                <td>{{$item->qty}}</td>
                <td style="padding:0;vertical-align: top" align="top">
                    <table>
                        <tr>
                            <td  align="center">
                                {{formatCurrency($item->unit_price)}}
                            </td>
                            <td  align="center">
                                {{$purchaseOrder->currency->code}}
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding:0;vertical-align: top" align="top">
                    <table class="currency-table">
                        <tr>
                            <td  align="center">
                                {{formatCurrency($item->unit_price * $item->qty)}}
                            </td>
                            <td  align="center">
                                {{$purchaseOrder->currency->code}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endforeach

        <tr>
            <td colspan="7">TOTAL</td>
            <td style="padding:0;vertical-align: top;margin:0" align="top">
                <table align="center">
                    <tr>
                        <td align="center">
                            {{formatCurrency($total)}}
                        </td>
                        <td align="center">
                            {{$purchaseOrder->currency->code}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="py-3"></div>
    <table class="amount-in-words-table ">
        <tbody>
        <tr>

            <td class="title-col">AMOUNT IN WORDS</td>
            <td class="value-col">
                <div class="underlined">
                    {{moneyInWords($total, $purchaseOrder->currency->code)}} Only
                </div>
            </td>
        </tr>
        </tbody>
    </table>
@endsection

@section('activities')
    <div class="py-3"></div>
    <table class="activities-table">
        <tbody>
        <tr>
            <td class="title-col">Prepared by</td>
            <td class="value-col">
                <div class="underlined">
                    {{$purchaseOrder->createdBy->first_name}}
                    {{$purchaseOrder->createdBy->last_name}}
                </div>
            </td>
            <td class="title-col"> Approved by</td>
            <td class="value-col">
                <div class="underlined">
                    {{$purchaseOrder->createdBy->first_name}}
                    {{$purchaseOrder->createdBy->last_name}}
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
