@extends('funding.includes.app')
@section('content')
    <!-- /.NavBar -->

    <!-- Banner -->
    <header class="banner marketplace-header">
        <div class="container">

        <div class="wrapper">    
            <div class="caption-in">
                <h1>Marketplace</h1>
                <p>Thousands are crowdfunding for various business. Support a fundraiser today!</p>
                {{--<a href="#" class="btn">Explore More</a>--}}
            </div>
            <form action="" method="post" class="form-wrapper">
                @csrf
                <div class="field-block-outer">
                    <div class="field-block">
                        <label for="industry_id">Industry</label>
                        <div class="select-wrapper">
                            <select name="industry_id" id="industry_id" class="vel-form custom">
                                <option value="null" class="placeholder" selected>Please Select</option>
                                @foreach($industry_filter as $key => $val)
                                    <option @if($post_data && $post_data['industry_id'] ==$key ) selected @endif value="{{$key}}">{{$val}}</option>
                                @endforeach
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12.335" height="7.226" viewBox="0 0 12.335 7.226">
                                <g id="Group_7" data-name="Group 7" transform="translate(-545.514 -163.515)">
                                    <rect id="Rectangle_140" data-name="Rectangle 140" width="8.516" height="1.703" rx="0.852" transform="translate(546.719 163.515) rotate(45)" fill="#ccc"/>
                                    <rect id="Rectangle_141" data-name="Rectangle 141" width="8.516" height="1.703" rx="0.852" transform="translate(557.85 164.719) rotate(135)" fill="#ccc"/>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="field-block-outer">
                    <div class="field-block">
                        <label for="monthly_revenue">Monthly Revenue</label>
                        <div class="select-wrapper">
                            <select name="monthly_revenue" id="monthly_revenue" class="vel-form form-select custom">
                                <option value="null" class="placeholder" selected>Please Select</option>
                                @foreach($monthly_revenue_filer as $key => $val)
                                    <option @if($post_data && $post_data['monthly_revenue'] ==$val ) selected @endif  value="{{$val}}">{{$val}}</option>
                                @endforeach


                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12.335" height="7.226" viewBox="0 0 12.335 7.226">
                                <g id="Group_7" data-name="Group 7" transform="translate(-545.514 -163.515)">
                                    <rect id="Rectangle_140" data-name="Rectangle 140" width="8.516" height="1.703" rx="0.852" transform="translate(546.719 163.515) rotate(45)" fill="#ccc"/>
                                    <rect id="Rectangle_141" data-name="Rectangle 141" width="8.516" height="1.703" rx="0.852" transform="translate(557.85 164.719) rotate(135)" fill="#ccc"/>
                                </g>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="field-block-outer">
                    <div class="field-block">
                        <label for="factor_rate">Factor Rate</label>
                        <div class="select-wrapper slider">
                            {{--<select name="factor_rate" id="factor_rate" class="vel-form custom">
                                <option value="null" class="placeholder" selected>Please Select</option>
                                    --}}{{--@foreach($merchants as $merchant)
                                        <option value="{{$merchant->factor_rate}}">{{round($merchant->factor_rate,2)}}</option>
                                    @endforeach--}}{{--


                                    --}}{{--@for ($x = 0; $x <= 2; $x = $x + 0.5)
                                        <option value="{{$x}}">{{$x}}</option>
                                    @endfor--}}{{--
                                    @foreach($factor_rate_filter as $key => $val)
                                        <option @if($post_data && $post_data['factor_rate'] ==$val ) selected @endif value="{{$val}}">{{$val}}</option>
                                    @endforeach


                            </select>--}}
                            <input type="text" class="js-range-slider" name="factor_rate" value=""/>
                        </div>
                    </div>
                </div>
                <div class="field-block-outer np">
                    <div class="field-block controls-wrapper">
                        <a href="{{url('fundings/marketplace')}}"  class="vel-form submit clear">Clear </a>
                        <input type="submit" value="Apply Filter" class="vel-form submit">
                    </div>
                </div>
            </form>
        </div>
        </div>
    </header>
    <!-- /.Banner -->

    <!-- Home Content -->
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
                                    <a href="{{url("fundings/$merchant->id/marketplace-details")}}">
                                        <img src="@isset($merchant->story_image) {{url(Storage::url($merchant->story_image)) }} @else {{url('/images/login_page_bg.jpg')}} @endisset" alt=""></a>
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

                        <div class="pagination-wrapper"> {!! $merchants->appends(['search' => Request::get('search')])->render() !!} </div>

                    {{--<div class="load-more-wrap">
                        <a href="#" class="load-more-btn">Explore More</a>
                    </div>--}}

                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/css/ion.rangeSlider.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.0/js/ion.rangeSlider.min.js"></script>
<script>
    $(".js-range-slider").ionRangeSlider(
            {
                skin: "round",
                step: 0.1,
                type: "double",
                grid: true,
                min: 1,
                max: 2,
                from: {{$factor_rate[0]}},
                to: {{$factor_rate[1]}},
                skin:'flat'

            });
            let my_range = $(".js-range-slider").data("ionRangeSlider");
</script>
@endpush
@push('scripts')
    <script>
		$(document).ready(function(){
			$('#nav-icon').click(function(){
				$(this).toggleClass('open');
			});
        });
        $('.clear').click(function(e){
            $.each(['#industry_id','#monthly_revenue','#factor_rate'],function(i,id){
                $(id).val(null);
                $(id).attr('selected',null)
                $(id).parents('.select').find('.select-styled').text('Please Select')
            })
            my_range.reset();
        })
        $('select.custom').each(function(item,option){
            var placeholder = ($(this).children('option:selected')).text()
            var $this = $(this), numberOfOptions = $(this).children('option').length;
            $this.addClass('select-hidden'); 
            $this.parents('.field-block').wrap('<div class="select"></div>');
            $this.parents('.field-block').after('<div class="select-styled"></div>');
            var $styledSelect = $this.parents('.field-block').next('div.select-styled');
            $styledSelect.text(placeholder);
            var $list = $('<ul/>', {
                'class': 'select-options hidden'
            }).insertAfter($styledSelect);
            for (var i = 0; i < numberOfOptions; i++) {
                $('<li/>', {
                    text: $this.children('option').eq(i).text(),
                    rel: $this.children('option').eq(i).val(),
                    class:$this.children('option').eq(i).attr('class')
                }).appendTo($list);
            }
        
            var $listItems = $list.children('li');
        
            $styledSelect.click(function(e) {
                e.stopPropagation();
                $('div.select-styled.active').not(this).each(function(){
                    $(this).removeClass('active');
                });
                $(this).toggleClass('active')
                $('.select-options').addClass('hidden');
                $(this).parents().children('.select-options').toggleClass('hidden')
            });
        
            $listItems.click(function(e) {
                e.stopPropagation();
                $styledSelect.text($(this).text()).removeClass('active');
                $this.val($(this).attr('rel'));
                $list.addClass('hidden');
                //console.log($this.val());
            });
            $(document).click(function() {
                $styledSelect.removeClass('active');
                $list.addClass('hidden');
            });
        });
    </script>
@endpush

@push('style')
    <style>
        .vel-form{
            height: 30px;
            color: #2A4A76;
            border: 1px solid #e8e8e8;
            border-radius: 5px;
            box-shadow: 3px 4px 10px #eaeaea;
            background-color: #fff;
            width: 150px;
        }
        .vel-form{
            margin-bottom: 5px;
        }
        .field-block.controls-wrapper{
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        .vel-form.submit.clear{
            margin-right: 10px!important;
            width: auto;
            padding: 0 20px;
        }
        .select-wrapper.slider{
            height: 50px;
        }
        option.placeholder{
            color: #b4becc;
        }
        .wrapper{
            display: flex;
            flex-direction: column;
        }
        .submit{
            background: #FA5440;
            color: #fff;
            border-radius: 5px;
            border:1px solid #FA5440;
            box-shadow: 3px 4px 10px #FA544022;
        }
        .irs--flat.irs-with-grid {
            height: 37px!important;
        }
        .irs--flat .irs-bar {
            background-color: #fa5440!important;
        }
        .form-wrapper{
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 6px 19px rgba(179,220,252,0.16);
            border-radius: 5px;
            height: 82px;
            margin-bottom: 44px;
        }
        .form-wrapper label{
            align-self: flex-start;
            margin-bottom:2px;
            color: #8e949c;
        }
        .caption-in{
            margin: auto 0;
        }
        .caption-in h1{
            text-align: left;
            font-size: 24px;
            font-weight: bold;
            color: #40414D;
        }
        .caption-in p{
            color: #A5A9B1;
            font-size: 19px;
            margin-bottom: 30px;
            text-align: left;
        }
        .marketplace-header{
            height: 181px;
        }
        .content-area {
            margin-top: 44px;
        }
        .field-block{
            height: 100%;
            padding: 15px 30px 0 30px;
            display: flex;
            flex-direction: column;
        }
        .field-block-outer{
            width: 25%;
            height: 100%;
            position: relative;
        }
        .field-block-outer.np .field-block {
            padding: 15px 30px;
        }
        .field-block-outer+.field-block-outer{
            border-left: 1px solid #ECEFF0;
        }
        .field-block label{
            text-align: left;
            color: #8D909F;
            font-size: 15px;
            width: 100%;
        }
        .field-block input.vel-form.submit{
            height: 40px;
            border-radius: 48px;
        }
        .select-wrapper select{
            border: none;
            box-shadow:none;
            width: 100%;
            color: #484B5D;
            font-size: 16px;
            font-weight: bold;
            -webkit-appearance: none;
            -moz-appearance: none;
            text-indent: 1px;
            margin: 0;
            text-overflow: '';
            display: none;
        }
        .select-wrapper select option,.select-wrapper select{
            text-transform: capitalize;
        }
        .select-wrapper{
            position: relative;
        }
        .select-wrapper svg{
            position: absolute;
            right: 0;
            top: 8px;
            pointer-events: none;
        }
        .select-styled{
            text-align: left;
            padding: 0 30px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 90%;
        }
        .select-hidden {
            display: none;
            visibility: hidden;
            padding-right: 10px;
        }
        .select-options{
            position: absolute;
            width: 100%;
            background: #fff;
            z-index: 9;
            box-shadow: 0 6px 19px rgb(179 220 252 / 16%);
            top: 100%;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
            opacity: 1;
            overflow: hidden;
        }
        .select-options li{
            list-style: none;
            padding: 0;
            text-align: left;
            cursor: pointer;
            padding: 5px 30px;
        }
        .select-options li:hover{
            background: #f9fbfc;
        }
        .select-options li.placeholder{
            opacity: 0.5;
            pointer-events: none;
        }
        .select-options.hidden{
            opacity: 0;
            transition:  all ease-in-out 0.5s;
            z-index: -1;
        }
        a.vel-form.submit.clear{
            margin-right: 10px!important;
            width: auto;
            padding: 0 20px;
            height: 40px;
            display: flex;
            justify-content: center;
            margin-bottom: 0;
            align-items: center;
            background: #edf1f5;
            border-radius: 65px;
            border-color: #edf1f5;
            box-shadow: 3px 4px 10px #edf1f56b;
            color: #40414d;
        }
        @media(max-width:991px){
            .content-area {
                margin-top: 280px;
            }
            .form-wrapper{
                flex-direction: column;
                height: auto;
            }
            .field-block-outer{
                width: 100%;
                padding-bottom: 15px;
            }
            .field-block-outer+.field-block-outer{
                border-top: 1px solid #ECEFF0;
            }
            .field-block-outer:last-child{
                padding-bottom: 0;
            }
            .field-block-outer.np{
                padding: calc(9px / 2) 0;
            }
            .controls-wrapper{
                justify-content: space-evenly;
            }
        }
    </style>
@endpush