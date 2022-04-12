@extends('reports.layouts.master')
@section('styles')
    <link rel="stylesheet" href="./css/reports/external-doc.css">
@endsection

@section('body')

    <header  class="ps-4 ms-5">
        <div class="vendor-address float-left">
            <strong>To:</strong>
            @if(!empty($vendor))
                @if(isset( $vendor->name ))
                    <div class="col-12">
                        {!! $vendor->name !!}
                    </div>
                @endif
                @if(isset( $vendor->street_address ))
                    <div class="col-12">
                        {!! $vendor->street_address !!}
                    </div>
                @endif
                @if(isset( $vendor->postal_address ))
                    <div class="col-12">
                        {!! $vendor->postal_address !!}
                    </div>
                @endif
                @if(isset( $vendor->telephone ))
                    <div class="col-12">
                        {!! $vendor->telephone !!}
                    </div>
                @endif
            @endif
        </div>

        <div class="address-container ps-2">
            @if(!empty($company))
                @if(isset( $company->name ))
                    <div class="col-12">
                        {!! $company->name !!}
                    </div>
                @endif

                @if(isset( $company->postal_address ))
                    <div class="col-12">
                        {!! $company->postal_address !!}
                    </div>
                @endif

                @if(isset( $company->telephone ))
                    <div class="col-12">
                        Tel: {!! $company->telephone !!}
                    </div>
                @endif
            @endif
        </div>
        <div class="clear-left"></div>
    </header>

    <section class="sub-header">
        <div class="meta-section float-right">
            @yield('header-meta')
        </div>

        <div class="doc-title clear-right">
            @yield('doc-title')
        </div>
    </section>

    <section class="main-content mb-0">
        @yield('content')
    </section>

    <section class="activities">
        <div class="py-3"></div>
        @yield('activities')
    </section>
@endsection
