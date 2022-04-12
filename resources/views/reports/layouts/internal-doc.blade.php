@extends('reports.layouts.master')
@section('styles')
    <link rel="stylesheet" href="./css/reports/internal-doc.css">
@endsection

@section('body')

    <header class="ps-5">
        <div class="logo-container float-left pe-4">
            @if(!empty($company) && isset($company->logo_url))

                @if(\Illuminate\Support\Facades\Storage::exists($company->logo_url))
                    <img alt="" class="logo"
                         src="{{asset( \Illuminate\Support\Facades\Storage::url($company->logo_url))}}">
                @else
                    <img src="{{$company->logo_url}}" class="logo" alt="">
                @endif
            @else
                <img src="{{asset('images/main-logo.png')}}" class="logo" alt="">
            @endif
        </div>

        <div class="address-container ps-2">
            @if(!empty($company))
                @if(isset( $company->street_address ))
                    <div class="col-12">
                        {!! $company->street_address !!}
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

                @if(isset( $company->mobile_phone ))
                    <div class="col-12">
                        Cell: {!! $company->mobile_phone !!}
                    </div>
                @endif
                @if(isset( $company->website ))
                    <div class="col-12">
                        {!! $company->website !!}
                    </div>
                @endif
            @endif
        </div>

    </header>

    <section class="sub-header">
        @yield('header-meta')

        <div class="doc-title pb-3 clear-right w-100 my-3">
            @yield('doc-title')
        </div>
    </section>

    <main class="main-content mb-0">
        @yield('content')
    </main>
@endsection


