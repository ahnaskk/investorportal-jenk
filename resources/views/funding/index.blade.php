@extends('funding.includes.app')

@push('alerts')
    @include('layouts.admin.partials.lte_alerts')
@endpush
@section('content')

    <header class="banner">
        <img src="{{url('funding/images/banner-01.jpg')}}" alt="">
        <div class="caption">
            <div class="container">
                <div class="caption-in">
                    <h1>Business Crowdfunding</h1>
                    <p>Now anyone can fund your business.</p>
                    <!-- <a class="btn" href="#">RAISE CAPITAL</a> -->
                </div>
            </div>
        </div>
    </header>


    <section class="content-area home-content">
        <div class="container">
            {{--<div class="heading"><h2>Marketplace</h2> <a href="#" class="btn">Explore More</a></div>--}}
            <div class="marketplace-wrapper">
                <div class="row">

                    @foreach($merchants as $merchant)
                        @if(App\Funding::merchant_market_data($merchant->id))
                    <div class="col-lg-4 col-md-6">
                        <div class="marketplace">
                            <div class="img">
                                <a href="{{url("fundings/$merchant->id/marketplace-details")}}"><img src="@isset($merchant->story_image) {{url(Storage::url($merchant->story_image)) }} @else {{url('/images/login_page_bg.jpg')}} @endisset" alt=""></a>
                            </div>
                            <div class="content">
                                <a href="{{url("fundings/$merchant->id/marketplace-details")}}"><h3>{{$merchant->name}}</h3></a>
                                <p class="amount"><span class="get">{{FFM::dollar($merchant->funding['part_total_amount'])}}</span>of<span class="total">{{ FFM::dollar($merchant->max_participant_fund)}}</span></p>
                                <div class="bar-outer">
                                    <div class="bar-inner" style="width:{{calculatePercentage($merchant->max_participant_fund,$merchant->funding['part_total_amount'])}}%"></div>
                                </div>
                                <div class="brand-date">
                                    <a href="{{url("fundings/$merchant->id/marketplace-details")}}" class="brand">{{$merchant->name}}</a>
                                    <span class="date">{{date("m-d-Y", strtotime($merchant->created_at))}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                        @endif
                    @endforeach

                    <div class="load-more-wrap">
                        <a href="{{url('/fundings/marketplace')}}" class="load-more-btn">Explore More</a>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
		$(document).ready(function () {
			$('#nav-icon').click(function () {
				$(this).toggleClass('open');
			});
		});
    </script>
@endpush
