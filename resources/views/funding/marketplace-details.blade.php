@extends('funding.includes.app')
@section('content')
<header class="sub-header">
    <div class="alert-bg" id="alerWrapper">
        <div class="alert-box">
            <div class="sec msg">
                <img src="/images/liquidity-alert-icon.svg?4149a867660f379f4a82c40811dc4b25" class="img">
                <p  class="msg-text d-none">You don't have enough liquidity to fund this deal</p>
            </div>
            <div class="sec user-input">
                <div class="check line" id="checkLine">
                    <span class="inline-block">
                        <div class="check-box" id="authorize">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="check" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-check fa-w-16">
                                <path fill="currentColor" d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z" class="tick"></path>
                            </svg>
                        </div>
                    </span>
                    I hereby authorize Velocity Group USA to debit <span>$ <span id="debitAmount">{{ $merchant->max_participant_fund}}</span></span> <br>
                    from my account.
                </div>
                <div class="input-group line"><span class="sign">$</span> <input id="decimalMode" type="text" class="input-amount" value="{{ $merchant->max_participant_fund}}"></div>
            </div>
            <div class="sec submit">
                <button type="button" class="blue-bt loader disabled" id="sendACH">Pay Now</button>
                @auth
                <a target="_blank" href="{{"/fundings/make_payment/".Auth::user()->id."/15"}}" class="blue-bt credit-card-payment-link {{--disabled--}}">Pay with credit card</a>
                    @endauth
            </div>
        </div>
    </div>
    <div id="agreementBox" class="agreement-box hide">
        <div class="content-box">
            <button type="button" class="close-btn" id="closePopUp">
                <img  src="/images/close-icon.svg?3743f32cf93cf6b1de5ebcbb6545bc7d" alt="">
            </button>
            <div class="agreement-terms-wrapper">
                <div class="details-wrapper">
                    <h1 class="title">EXHIBIT B</h1>
                    <h2 class="title">PARTICIPATION AGREEMENT</h2>
                    <p>
                        THIS IS THE OFFER AS DESCRIBED IN THE PARTICIPATION AGREEMENT DATED <b> {{date('d/m/Y')}} </b>
                        BETWEEN <b>{{$merchant->name}}</b> AND <b>VELOCITY GROUP USA INC.</b>. RELATING TO A MERCHANT AGREEMENT
                        DATED <br><b>{{date('m-d-Y')}} </b> BETWEEN LEAD AND <b>{{$merchant->name}}</b>.
                    </p>
                    <p>
                        WHEREAS THE LEAD HAS BEEN REQUESTED BY CLIENT TO PURCHASE CERTAIN FUTURE RECEIVABLES AT THE DESCRIBED TOTAL
                        CONTRACT PURCHASE PRICE ("PENDING PURCHASE"), NOW AND THEREFORE THE LEAD HEREBY OFFERS TO SELL TO
                        PARTICIPANT A CERTAIN PERCENTAGE INTEREST IN THE PENDING PURCHASE ("PARTICIPATION PERCENT") AS DESCRIBED
                        BELOW.
                    </p>
                    <p>
                        PLEASE RETURN A SIGNED COPY OF THIS OFFER TO US, INDICATING WHETHER YOU ACCEPT THIS OFFER. IF YOU ACCEPT THE
                        OFFER, PLEASE BE PREPARED TO PAY PARTICIPANT'S PURCHASE PRICE WITHIN TWENTY-FOUR (24) HOURS OF YOUR
                        EXECUTION OF THIS FORM OF PARTICIPATION OR YOU WILL LOSE YOUR PARTICIPATION AGREEMENT, AS DESCRIBED IN THE
                        PARTICIPATION AGREEMENT.
                    </p>
                    <table>
                        <thead>
                            <tr>
                                <td width="50%">
                                    <label>By :</label>
                                    VELOCITY GROUP USA INC.
                                </td>
																<td><label>Date :</label>
																	<span class="date_en"></span>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><b>Funder (Lead) Name</b></td>
                                <td class="business_en_name"></td>
                            </tr>
                            <tr>
                                <td><b>Merchant DBA</b></td>
                                <td class="participant">
                                </td>
                            </tr>
                            <tr>
                                <td><b>Legal Name</b></td>
                                <td class="business_en_name"></td>
                            </tr>
                            <tr>
                                <td><b>Merchant Funding Date:</b></td>
                                <td class="date_funded">
                                    
                                </td>
                            </tr>
                            <tr>
                                <td><b>Merchant Cash Advance (Yes/No)</b></td>
                                <td class="mca">
                                </td>
                            </tr>
                            <tr>
                                <td><b>Credit Card Sales Split (Yes/No)</b></td>
                                <td class="credit_card">
                                </td>
                            </tr>
                            <tr>
                                <td><b>Total Contract Purchase Price (Funded Amount)</b></td>
                                <td class="funded"></td>
                            </tr>
                            <tr>
                                <td><b>Total Contract Receivable ("RTR")</b></td>
                                <td class="rtr"></td>
                            </tr>
                            <tr>
                                <td><b>Factor Rate</b></td>
                                <td class="factor_rate"></td>
                            </tr>
                            <tr>
                                <td><b>Contract Payment Amount</b></td>
                                <td class="daily_payment"></td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <td><b>Payment Interval (Daily or Weekly):</b></td>
                                <td class="advance_type">
                                    {{$merchant->advance_type}}
                                </td>
                            </tr>
                            <tr>
                                <td><b>Number of Payments</b></td>
                                <td class="pmnts"></td>
                            </tr>
                            <tr>
                                <td><b>Estimated Term (Months)</b></td>
                                <td class="estimated_term_months"></td>
                            </tr>
                            <tr>
                                <td><b>Upfront Broker Commission $</b></td>
                                <td class="upfront_commission"></td>
                            </tr>
                            <tr>
                                <td><b>Upfront Broker Commission %</b></td>
                                <td class="upfront_commission_per"></td>
                            </tr>
                            <tr>
                                <td><b>Management Fee $</b></td>
                                <td class="management_fee"></td>
                            </tr>
                            <tr>
                                <td><b>Management Fee %</b></td>
                                <td class="management_fee_per"></td>
                            </tr>
                            <tr>
                                <td><b>Syndication Fee $</b></td>
                                <td class="m_syndication_fee"></td>
                            </tr>
                            <tr>
                                <td><b>Syndication Fee %</b></td>
                                <td class="m_syndication_fee_per"></td>
                            </tr>
                            <tr>
                                <td><b>Underwriting Fee $</b></td>
                                <td class="underwriting_fee"></td>
                            </tr>
                            <tr>
                                <td><b>Underwriting Fee %</b></td>
                                <td class="underwriting_fee_per"></td>
                            </tr>
                            <tr>
                                <td><b>Participant Purchase Price (Funding Amount)</b></td>
                                <td class="participant_funded_amount"></td>
                            </tr>
                            <tr>
                                <td><b>Participant's percentage of Contract</b></td>
                                <td class="participant_percent"></td>
                            </tr>
                            <tr>
                                <td><b>Participant RTR (Gross)</b></td>
                                <td class="rtr_gross"></td>
                            </tr>
                            <tr>
                                <td><b>Participant RTR (Net)</b></td>
                                <td class="rtr_net"></td>
                            </tr>
                            <tr>
                                <td><b> Participant Payment Amount</b></td>
                                <td class="payment_amount"></td>
                            </tr>
                            <tr>
                                <td><b> Total Amount Due From Participant</b></td>
                                <td class="duetotal"></td>
                            </tr>
                        </tbody>
                    </table>
                    <p  style="margin-top: 50px;">
                        THE PARTICIPATION <span  title="Return on Investment"><b >ROI</b></span> OFFERED HEREBY IS HIGHLY SPECULATIVE AND INVOLVES A HIGH DEGREE OF RISK. THE LEAD
                        DOES NOT MAKE ANY REPRESENTATION REGARDING THE SUITABILITY AND/OR PROSPECTS OF THIS INVESTMENT APART FROM
                        THOSE EXPLICITLY OUTLINED IN THE PARTICIPATION AGREEMENT. THIS PARTICIPATION SHOULD NOT BE PURCHASED BY ANY
                        INVESTOR WHO CANNOT AFFORD THE LOSS OF THEIR ENTIRE INVESTMENT. 
                    </p>
                    <p>
                        THE INFORMATION CONTAINED IN THIS 'EXHIBIT B: PARTICIPATION AGREEMENT' IS HIGHLY CONFIDENTIAL INFORMATION AS
                        DEFINED IN THE CONFIDENTIALITY AGREEMENT EXECUTED ON <b class="date_en">02/01/2021 </b> BETWEEN Velocity Group USA Inc. AND
                        <b class="participant">92name</b>, WHICH IS INCORPORATED HEREIN BY REFERENCE AND MADE A 'EXHIBIT B'.
                    </p>
                    <p> ACKNOWLEDGE AND ACCEPTED UNDER THE TERMS AND CONDITIONS OF THE PARTICIPATION AGREEMENT REFERENCED ABOVE.
                    </p>
                    <div action="" class="signature">
                        <div class="sign-pad sigPad signature-pad">
                            <div  class="overlay">
                                <div class="msg">Click here to add Signature.</div>
                            </div>
                            <div class="sig sigWrapper">
                                <div class="typed"></div>
                                <canvas class="pad" width="1000" height="350"></canvas>
                                <input type="hidden" name="output" class="output" id="signatureOut">
                            </div>
                        </div>
                        <div class="control-row "><button  class="btn undo clearButton">Clear</button></div>
                    </div>
                    <p class="error hide" id="signatureError">Please add your signature</p>
                    <div data-v-14fd616e="" class="submit-row"><div data-v-14fd616e="" class="info"><p data-v-14fd616e="" class="info"><span data-v-14fd616e="" class="bold">
                By :
              </span>
              &nbsp;<span class="participant"></span>
            </p> <p data-v-14fd616e="" class="info"><span data-v-14fd616e="" class="bold">
                Date :
              </span>
              &nbsp;<span id="timestamp"></span>
            </p> <p data-v-14fd616e="" class="info"><span data-v-14fd616e="" class="bold">
                Server :
              </span>
              &nbsp;<span id="server"></span>
            </p></div>
            <button data-v-14fd616e="" id="submitAgreement" class="btn save loader">Save</button></div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">

        <!-- Heading -->
        <div class="heading">
            <h1>{{$merchant->name}}</h1>
            <p>{{$merchant->story_caption}}</p>
        </div>
        <!-- /.Heading -->

        {{--todo remove after use--}}
        <style>
            .item {
                color      : #48453d;
                margin-top : 30px;
                overflow   : hidden;
            }

            .block-title {
                color          : #48453d;
                margin-bottom  : 0px;
                font-size      : 18px;
                margin-top     : 0px;
                font-weight    : 700;
            }

            .item-content-block {
                padding          : 20px;
                border-top       : 2px solid #f6f6f2;
                background-color : #FFF;
                display          : block;
            }

            .tags a {
                background-color : gray;
                padding          : 10px;
                color            : #fff;
                display          : inline-block;
                font-size        : 11px;
                line-height      : 11px;
                border-radius    : 2px;
                margin-bottom    : 5px;
                margin-right     : 2px;
                text-decoration  : none;
            }

            .tags a:hover {
                background-color : #FA5440;
            }
        </style>

        <!-- Banner Content -->
        <div class="row">
            <div class="col-md-8">
                <div class="marketplace-banner">
                    <img src="@isset($merchant->story_image) {{url(Storage::url($merchant->story_image)) }} @else {{url('/images/login_page_bg.jpg')}}  @endisset" alt="">
                </div>
            </div>
            <div class="col-md-4">





                @if($merchant->daysleft > 0)
                <div class="price-wrapper">
                    <span class="title">Raised
                        @isset($merchant_market_data['maximumParticipationAvailable'])
                    <a href="#" class="show-details" data-toggle="modal" data-target="#marketplace-tags">Show Details</a>
                        @endisset </span>
                    <p class="price">{{ FFM::dollar($fundings['part_total_amount'])}}</p>
                    <p class="goal-price"><span>Goal</span> {{ FFM::dollar($merchant->max_participant_fund)}}</p>
                    <div class="funder-count-wrap">
                        <div class="funder-count">
                            <span>Investors</span>
                            <p>{{count($fundings['investor_data'])}}</p>
                        </div>
                        <div class="days-left">
                            {{--<span>Days Left</span>
                                <p>{{$merchant->daysleft}}</p>--}}
                            <span>Projected funding date</span>
                                <p>{{dateUS($merchant->date_funded)}}</p>
                        </div>
                    </div>
                    <div class="percentage-bar-wrap">
                        <div class="bar-outer">
                            <div class="bar-inner" style="width:{{calculatePercentage($merchant->max_participant_fund,$fundings['part_total_amount'])}}%"></div>
                        </div>
                        <span class="value">{{calculatePercentage($merchant->max_participant_fund,$fundings['part_total_amount'])}}% Funded</span>
                    </div>
                    <div class="error-fund" id="fundError">

                    </div>


                    @auth
                        @if($hasFunded || (calculatePercentage($merchant->max_participant_fund,$fundings['part_total_amount']) >= 100))
                            <a href="{{url('fundings/marketplace')}}" class="btn btn-fund btn-grey">Browse Other Deals</a>
                        @else
                        {{--<a href="javascript:void(0)" class="btn btn-fund" id="fundThisDeal">Fund this Deal</a>--}}
                            <input onkeyup="addURL(this)" type="number" name="amount" id="amount" class="amount" min="100"> <br>
                        <a href="{{url('/investors/marketplace')}}" class="btn btn-fund disabled">Fund this Deal</a>
                    @endif
                    @else
                        <a href="{{url('fundings/login')}}" class="btn btn-fund">Fund this Deal</a>
                    @endauth


                </div>

                @else

                <div class="price-wrapper zero-days days-ended">
                    <div class="img">
                        <img src="{{url('funding/images/clock.png')}}" alt="">
                    </div>
                    <p class="price">0 Days Left</p>
                    <p class="message">DEAL NOT AVAILABLE!</p>
                    <a href="{{url('fundings/marketplace')}}" class="btn btn-fund btn-grey">Browse Other Deals</a>
                    <a href="#" class="show-details" data-toggle="modal" data-target="#marketplace-tags">Show Details</a>
                </div>
                @endif



                @if(false)
                <div class="price-wrapper zero-days">
                    <div class="img">
                        <img src="{{url('assets/images/success.png')}}" alt="">
                    </div>
                    <span class="title">Goal Reached!</span>
                    <p class="price">₹ 8,00,000</p>
                    <div class="donut-chart">
                        <div class="percentage">100%</div>
                        <div class="text">Funded</div>
                    </div>
                    <p class="message">DEAL NOT AVAILABLE!</p>
                    <a href="{{url('fundings/marketplace')}}" class="btn btn-fund btn-grey">Browse Other Deals</a>
                </div>
                    @endif





            </div>
        </div>
        <!-- /.Banner Content -->

        <!-- Banner tags -->
        <div class="row banner-tags-row">
            <div class="col-md-6 brand">
                <p><a href="#">{{$merchant->name}} </a> <span>Posted on {{date("m-d-Y", strtotime($merchant->created_at))}}</span></p>
            </div>
            <div class="col-md-6 tags-wrap">
                <p><i class="tag"></i><a href="#">{{ucfirst($merchant->industry->name)}}</a></p>
                <p><i class="map"></i><a href="#">{{$merchant->city}}</a></p>
            </div>
        </div>
    </header>

    <section class="content-area page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 content-left">
                    {{--<div class="item-content-block tags">
                        <a href="#">Maximum Participation Available : {{$merchant_market_data['maximumParticipationAvailable']}} </a>
                        <a href="#">Total Funded Amount : {{$merchant_market_data['dailyPayment']}} </a>
                        <a href="#">RTR : {{$merchant_market_data['rtr']}} </a>
                        <a href="#">Factor Rate : {{$merchant_market_data['factorRate']}} </a>
                        <a href="#">Monthly Revenue : {{$merchant_market_data['monthly_revenue']}} </a>
                        <a href="#">Daily Payment : {{$merchant_market_data['dailyPayment']}} </a>
                        <a href="#">No. of Payment : {{$merchant_market_data['numberOfPayments']}} </a>
                        <a href="#">Commission : {{$merchant_market_data['commissionPayable']}} % </a>
                        <a href="#">Syndication : {{$merchant_market_data['prepaid']}} </a>
                        <a href="#">Underwriting Fee : {{$merchant_market_data['underwritingFee']}} % </a>
                        <a href="#">Management Fee : {{$merchant_market_data['managementFee']}} %</a>
                    </div>--}}

                    <div id="tab-container" class="skltbs">
                        <ul role="tablist" class="skltbs-tab-group">
                            <li role="presentation" class="skltbs-tab-item">
                                <a role="tab" class="skltbs-tab" href="#marketplace-story">Story</a>
                            </li>
                            <li role="presentation" class="skltbs-tab-item">
                                <a role="tab" class="skltbs-tab" href="#marketplace-faq">FAQ</a>
                            </li>
                            <li role="presentation" class="skltbs-tab-item">
                                <a role="tab" class="skltbs-tab" href="#marketplace-comments">Comments</a>
                            </li>
                        </ul>
                        <div class="skltbs-panel-group">

                            <div role="tabpanel" id="marketplace-story" class="skltbs-panel">
                               {{-- <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur</p>
                                <img src="{{url('funding/images/content-img-01.jpg')}}" alt="">
                            <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur</p>
                            --}}
                            {!! $merchant->story !!}

                        </div>


                            <div role="tabpanel" id="marketplace-faq" class="skltbs-panel">
                                <div class="accordion" id="accordionExample">


                                @foreach($merchant->faqs as $faq)
                                <div class="card">
                                    <div class="card-header" id="heading{{$faq->id}}">
                                        <h2 class="mb-0">
                                            <button class="btn btn-link  @if($loop->iteration != 1) collapsed @endif" type="button" data-toggle="collapse" data-target="#collapse{{$faq->id}}" aria-expanded="false" aria-controls="collapseTwo">
                                                {{$faq->title}}
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapse{{$faq->id}}" class="collapse @if($loop->iteration == 1) show @endif" aria-labelledby="heading{{$faq->id}}" data-parent="#accordionExample">
                                        <div class="card-body">
                                            {{$faq->description}}
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        </div>


                            <div role="tabpanel" id="marketplace-comments" class="skltbs-panel">

                            <div class="fb-comments" data-href="{{url()->current()}}" data-width="" data-numposts="5"></div>
                        </div>
                    </div>
                </div>
            </div>


                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

                <div class="col-lg-4 right-bar">
                <div class="sidebar-social">
                    <div class="title">Social Share</div>
                    {{--<ul class="social">
                        <li><a href="#"><i class="whatsapp"></i></a></li>
                        <li><a href="#"><i class="facebook"></i></a></li>
                        <li><a href="#"><i class="twitter"></i></a></li>
                        <li><a href="#"><i class="email"></i></a></li>
                    </ul>--}}

                    {!! Share::page(Request::url(), 'Share title')->facebook()->twitter()->linkedin()->whatsapp() !!}


                </div>

                <div class="side-bar">
                    <div class="title">{{count($fundings['investor_data'])}} people just funded</div>
                    <ul>

                        @foreach($fundings['investor_data'] as $investor_data)
                        <li>
                            <div class="img">
                                <img src="{{url('funding/images/avatar.png')}}" alt="">
                            </div>
                            <div class="content">
                                <p class="name">{{$investor_data->name}}</p>
                                <p class="price">{{ FFM::dollar($investor_data->amount)}} <span>6 hrs</span></p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    {{--<a href="#" class="read-more">View All</a>--}}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="marketplace-tags" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg-8">
    <div class="modal-content no-radius">
        <div class="wrapper bg-white">
           <div class="close-wrapper">
                <button type="button" class="close-btn-m" data-dismiss="modal" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="20.953" height="20.953" viewBox="0 0 20.953 20.953">
                    <path id="Icon_ionic-md-close" data-name="Icon ionic-md-close" d="M28.477,9.619l-2.1-2.1L18,15.9,9.619,7.523l-2.1,2.1L15.9,18,7.523,26.381l2.1,2.1L18,20.1l8.381,8.381,2.1-2.1L20.1,18Z" transform="translate(-7.523 -7.523)" fill="#40414d"/>
                </svg>

                </button>                
           </div>
            @isset($merchant_market_data['maximumParticipationAvailable'])
           <div class="popper-header">
                <h5 class="" id="exampleModalLabel">Showing more information</h5>
           </div>
           <div class="progress-row">
               <div class="info-row">
                   <div class="info">
                        <span class="title">Total Funded Amount</span>
                        <span class="amount" id="total_funded_amount">{{FFM::dollar($fundings['part_total_amount'])}}</span>

                   </div>
                   <div class="info text-right">
                        <span class="title">Maximum Participation Available</span>
                        <span class="amount" id="max_available">{{$merchant_market_data['maximumParticipationAvailable']}}</span>
                   </div>
               </div>
               <div class="progress-outer">
                    <div class="progress" id="progress_percentage"></div>
               </div>
           </div>
      <div class="modal-body">
        <div class="tags">
            <div class="tag-item">
                <span class="title">Maximum Participation Available</span>
                <span class="amount">{{$merchant_market_data['maximumParticipationAvailable']}} </span>
            </div>
            <div class="tag-item">
                <span class="title">Total Funded Amount</span>
                                <span class="amount">{{FFM::dollar($fundings['part_total_amount'])}}</span>
            </div>
            <div class="tag-item">
                <span class="title">RTR</span>
                <span class="amount">{{$merchant_market_data['rtr']}}</span>
            </div>
            <div class="tag-item">
                <span class="title">Factor Rate</span>
                <span class="amount">{{$merchant_market_data['factorRate']}}</span>
            </div>
            <div class="tag-item">
                <span class="title">Monthly Revenue</span>
                <span class="amount">{{FFM::dollar($merchant_market_data['monthly_revenue'])}}</span>
            </div>
            <div class="tag-item">
                <span class="title">Daily Payment</span>
                <span class="amount">{{$merchant_market_data['dailyPayment']}}</span>
            </div>
            <div class="tag-item">
                <span class="title">No. of Payment</span>
                <span class="amount">{{$merchant_market_data['numberOfPayments']}}</span>
            </div>
            <div class="tag-item">
                <span class="title">Commission</span>
                <span class="amount">{{$merchant_market_data['commissionPayable']}} %</span>
            </div>
            @auth
            <div class="tag-item">
                <span class="title">Syndication</span>
                <span class="amount">{{$merchant_market_data['prepaid']}} %</span>
            </div>
            <div class="tag-item">
                <span class="title">Underwriting Fee</span>
                <span class="amount">{{$merchant_market_data['underwritingFee']}} %</span>
            </div>
            <div class="tag-item">
                <span class="title">Management Fee</span>
                <span class="amount">{{$merchant_market_data['managementFee']}} %</span>
            </div>
            @endauth
                @endisset
        </div>
      </div>
        </div>
    </div>
  </div>
</div>


@endsection






@push('scripts')
<script>

	function grossAmountTotal(total) {
		total = parseFloat(total);
		return (((17/ 100) * total)+total).toFixed(2)
	}
    $("#tab-container").skeletabs({
        equalHeights: true,
        animation: "fade-scale",
        autoplayInterval: 4500,
        responsive: {
            breakpoint: 800,
            headingTagName: "h4"
        }
    });


    function submit_comment() {
        var comment = $('.commentar').val();
        el = document.createElement('li');
        el.className = "box_result row";
        el.innerHTML =
            '<div class=\"avatar_comment col-md-1\">' +
            '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\"/>' +
            '</div>' +
            '<div class=\"result_comment col-md-11\">' +
            '<h4>Anonimous</h4>' +
            '<p>' + comment + '</p>' +
            '<div class=\"tools_comment\">' +
            '<a class=\"like\" href=\"#\">Like</a><span aria-hidden=\"true\"> · </span>' +
            '<i class=\"fa fa-thumbs-o-up\"></i> <span class=\"count\">0</span>' +
            '<span aria-hidden=\"true\"> · </span>' +
            '<a class=\"replay\" href=\"#\">Reply</a><span aria-hidden=\"true\"> · </span>' +
            '<span>1m</span>' +
            '</div>' +
            '<ul class="child_replay"></ul>' +
            '</div>';
        document.getElementById('list_comment').prepend(el);
        $('.commentar').val('');
    }

    $(document).ready(function() {
        let  total_funded_amount = $('#total_funded_amount').text()
        total_funded_amount = total_funded_amount.replace('$','')
        total_funded_amount = total_funded_amount.replace(',','')
        total_funded_amount = parseInt(total_funded_amount)
        let  max_available = $('#max_available').text()
        max_available = max_available.replace('$','')
        max_available = max_available.replace(',','')
        max_available = parseInt(max_available)
        let progress_percentage = ( total_funded_amount / max_available ) * 100
        $("#progress_percentage").css('width',progress_percentage+"%")
        $('#list_comment').on('click', '.like', function(e) {
            $current = $(this);
            var x = $current.closest('div').find('.like').text().trim();
            var y = parseInt($current.closest('div').find('.count').text().trim());

            if (x === "Like") {
                $current.closest('div').find('.like').text('Unlike');
                $current.closest('div').find('.count').text(y + 1);
            } else if (x === "Unlike") {
                $current.closest('div').find('.like').text('Like');
                $current.closest('div').find('.count').text(y - 1);
            } else {
                var replay = $current.closest('div').find('.like').text('Like');
                $current.closest('div').find('.count').text(y - 1);
            }
        });

        $('#list_comment').on('click', '.replay', function(e) {
            cancel_reply();
            $current = $(this);
            el = document.createElement('li');
            el.className = "box_reply row";
            el.innerHTML =
                '<div class=\"col-md-12 reply_comment\">' +
                '<div class=\"row\">' +
                '<div class=\"avatar_comment col-md-1\">' +
                '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\"/>' +
                '</div>' +
                '<div class=\"box_comment col-md-10\">' +
                '<textarea class=\"comment_replay\" placeholder=\"Add a comment...\"></textarea>' +
                '<div class=\"box_post\">' +
                '<div class=\"pull-right\">' +
                '<span>' +
                '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\" />' +
                '<i class=\"fa fa-caret-down\"></i>' +
                '</span>' +
                '<button class=\"cancel\" onclick=\"cancel_reply()\" type=\"button\">Cancel</button>' +
                '<button onclick=\"submit_reply()\" type=\"button\" value=\"1\">Reply</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
            $current.closest('li').find('.child_replay').prepend(el);
        });
    });



    function submit_reply() {
        var comment_replay = $('.comment_replay').val();
        el = document.createElement('li');
        el.className = "box_reply row";
        el.innerHTML =
            '<div class=\"avatar_comment col-md-1\">' +
            '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\"/>' +
            '</div>' +
            '<div class=\"result_comment col-md-11\">' +
            '<h4>Anonimous</h4>' +
            '<p>' + comment_replay + '</p>' +
            '<div class=\"tools_comment\">' +
            '<a class=\"like\" href=\"#\">Like</a><span aria-hidden=\"true\"> · </span>' +
            '<i class=\"fa fa-thumbs-o-up\"></i> <span class=\"count\">0</span>' +
            '<span aria-hidden=\"true\"> · </span>' +
            '<a class=\"replay\" href=\"#\">Reply</a><span aria-hidden=\"true\"> · </span>' +
            '<span>1m</span>' +
            '</div>' +
            '<ul class="child_replay"></ul>' +
            '</div>';
        $current.closest('li').find('.child_replay').prepend(el);
        $('.comment_replay').val('');
        cancel_reply();
    }

    function cancel_reply() {
        $('.reply_comment').remove();
    }


    function process_payment(){
	    var merchantId = "{{$merchant->id}}";
	    var amount =  $('#decimalMode').val();
	    var grossAmount =  grossAmountTotal($('#decimalMode').val());

	    let instance = $(".signature").signaturePad();
	    let b64 = instance.getSignatureImage();
	    //console.log(b64)
	    //var signed = "data:image/png;base64,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IECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoSEGjXVG1rJUCAAAECBAgQIECAAAECBAgQIECAQMECAu2Ci2fqBAgQIECAAAECBAgQIECAAAECBAgQqElAoF1Tta2VAAECBAgQIECAAAECBAgQIECAAAECBQsItAsunqkTIECAAAECBAgQIECAAAECBAgQIECgJgGBdk3VtlYCBAgQIECAAAECBAgQIECAAAECBAgULCDQLrh4pk6AAAECBAgQIECAAAECBAgQIECAAIGaBATaNVXbWgkQIECAAAECBAgQIECAAAECBAgQIFCwgEC74OKZOgECBAgQIECAAAECBAgQIECAAAECBGoS+H+Z7SF5W+pdkwAAAABJRU5ErkJggg=="
	    var data = {merchantId: merchantId, amount: amount, grossAmount: grossAmount ,signed:b64};
	    $.ajax({
		           headers: {
			           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		           },
		           type   : 'POST',
		           data   : data,
		           url    : "{{route('fundings.postMarketplaceFund')}}",

	           }).done(function (data) {


		    $("#alerWrapper").removeClass("active");
		    $("#agreementBox").removeClass("hide");

		    if (data.hasOwnProperty('errors')) {
			    postInvestorAchRequestSend();
			    //$(".msg-text").removeClass("d-none");
			    //$("#sendACH").html('Send ACH & Continue');

		    } else {

			    if (data.status === true) {
				    window.location.href = data.data.pdfUrl;
				    setTimeout(function () {
                        {{--window.location.href = "{{url('fundings/marketplace')}}";--}}
					    location.reload();
				    }, 5000);
				    $("#agreementBox").addClass("hide");

			    }
            }



		    //$('#decimalMode').val(data.errors.data.funded);
		    //$('#debitAmount').html(data.errors.data.funded);
		    //$("#alerWrapper").removeClass("active").addClass("active");
		    //console.log(data.data.pdfUrl);
		    console.log(data);
	    });
    }





    $("#submitAgreement").click(function(){
	    if($("#signatureOut").val() != ''){
				process_payment();

	    }
	    else{
		    $("#signatureError").removeClass("hide")
		    showError();
	    }
    });

    var errorTimer;
    function showError(){
	    clearTimeout(errorTimer)
	    errorTimer = setTimeout(function(){
		    $("#signatureError").addClass("hide")
	    }, 2000);
    }



    $(document).on("input", ".decimal-number", function(e) {
        this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
    });
    $("#authorize,#checkLine").click(function(e) {
        e.stopPropagation();
        if(e.target.id == 'authorize' ){
            $(this).toggleClass("agree")
        }
        else{
            $('#authorize').toggleClass("agree")
        }
        if ($('#authorize').hasClass("agree")) {
            $(".submit button").removeClass("disabled")
        } else {
            $(".submit button").addClass("disabled")
        }
    });
    $(document).mouseup(function(e) {
        var container = $("#alerWrapper");

        if (container.is(e.target) && container.has(e.target).length === 0) {
            container.removeClass("active")
        }
    });
    $(document).on('mouseup','#agreementBox',function(e){
        if($(this).is(e.target) && $(this).has(e.target).length ===0){
            $(this).addClass("hide")
        }
    })
    $(document).ready(function() {
        $("#decimalMode").numeric({
            decimal: ".",
            negative: false,
            decimalPlaces: 2
        });
        var canvas = document.querySelector("canvas.pad");
        var parentWidth = $(canvas).parent().outerWidth();
        canvas.setAttribute("width", parentWidth);
        $(".signature").signaturePad({
            penColour :'#333',
            drawOnly:true,
            defaultAction :'drawIt',
            lineTop:0,
            lineWidth:0
        })
        $(".overlay").click(function(){
            $(".overlay").addClass("hide")
            $(".sigWrapper").addClass("active")
        })
        $("#closePopUp").click(function(){
            $("#agreementBox").addClass("hide");
        })
        $("#decimalMode").keyup(function(){
            $("#debitAmount").text(grossAmountTotal($(this).val()));
        });




    })

	var btn_real = $('.btn-fund').attr('href');


	function addURL() {
		var amount = $('#amount').val();
        var btnFund = $('.btn-fund');
		//btnFund.attr('href', btn_real + amount);
		localStorage.amount = amount;
		if (amount >= 100 && amount && amount <= {{$merchant_market_data['maximum_amount']}}  ) {
            btnFund.removeClass('disabled');
            btnFund.prop("disabled",false)
            $("#fundError").empty();
        } 
        else{
            btnFund.addClass('disabled')
            btnFund.prop("disabled",true)
            $("#fundError").empty();
            $("#fundError").html('<p class="error">Kindly fund an amount greater than $100 and less than {{$merchant_market_data['maximumParticipationAvailable']}} </p>')
        }
	}


</script>
<script defer src="https://cdn.jsdelivr.net/npm/jquery.numeric@1.0.0/jquery.numeric.min.js"></script>
<script src="{{url('funding/js/jquery.signaturepad.js')}}"></script>

@auth
    <script>
		localStorage.setItem("merchant_id","{{$id}}");
    </script>
@endauth
@endpush

@push('style')
<style type="text/css">
    .alert-bg {
        position: fixed;
        z-index: 2;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        display: none;
        transition: opacity ease-in-out 0.5s;
    }

    .alert-bg.active {
        opacity: 1;
        display: flex;
    }

    .alert-box {
        background: white;
        box-shadow: 0 0 60px #EAEBF2;
        width: 700px;
        min-height: 350px;
        border-radius: 10px;
    }

    .alert-box .sec {
        padding: 20px 30px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
    }

    .alert-box .sec.msg .img {
        color: transparent;
        height: 150px;
        width: 150px;
        background: #FCFCFC;
        border-radius: 50%;
        border: 1px solid #EEE;
        outline: none;
    }

    .alert-box .sec.msg .msg-text {
        text-align: center;
        color: #DE3B51;
        margin: 10px 0 0;
        font-size: 24px;
    }
    .agreement-box p.error{
        color: #ff4a4a;
        font-weight: bold;
    }
    .alert-box .sec.user-input .check .check-box {
        height: 25px;
        width: 25px;
        border: 2px solid #B9BFCC;
        border-radius: 4px;
        margin: 0 3px 0 0;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 17px;
        cursor: pointer;
    }

    .alert-box .sec.user-input {
        color: #929497;
        background: #FBFBFF;
        font-size: 20px;
        border: 1px solid;
        border-color: #EEEEF1 transparent #EEEEF1 transparent;
        position: relative;
    }

    .inline-block {
        display: inline-block;
    }

    .svg-inline--fa {
        display: inline-block;
        font-size: inherit;
        height: 1em;
        overflow: visible;
        vertical-align: -0.125em;
    }

    .check-box svg .tick {
        opacity: 0;
        transition: opacity ease-in-out 0.2s;
    }

    .check-box.agree svg .tick {
        opacity: 1;
    }

    .alert-box .sec.user-input .input-group {
        background: white;
        border: 1px solid #E6E6E6;
        display: flex;
        border-radius: 10px;
        height: 40px;
        width: 200px;
        overflow: hidden;
        box-shadow: 0 0 20px #0105810D;
        position: relative;
    }
    .hide{
        z-index: -99;
        opacity: 0;
        pointer-events: none;
    }
    .error.hide{
        display: none;
    }
    .alert-box .sec.user-input .input-group .sign {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 17.5px;
        flex-shrink: 0;
        position: absolute;
        left: 0;
        top: 0;
    }

    .alert-box .sec.user-input .input-group input.input-amount {
        border: none;
        width: 100%;
        height: 100%;
        flex-grow: 1;
        font-size: 17px;
        color: inherit;
        text-align: center;
    }

    .alert-box .sec.submit {
        padding-top: 40px;
        padding-bottom: 40px;
        flex-direction: row;
    }

    .blue-bt {
        border: none;
        outline: none;
        background: #3246D3;
        border-radius: 10px;
        height: 49px;
        line-height: 49px;
        text-decoration: none;
        text-align: center;
        width: 180px;
        font-size: 20px;
        color: white;
        font-weight: bold;
        box-shadow: 0 11px 23px -8px #3B4ED5;
        cursor: pointer;
    }

    .alert-box .sec.submit .blue-bt.credit-card-payment-link {
        text-decoration: none;
        padding: 0;
        border-radius: 7px;
        text-align: center;
        margin-left: 30px;
        background: #e56576;
        box-shadow: 0 11px 23px -8px #e56576;
        color: white;
    }

    .alert-box .sec.submit .blue-bt {
        width: 250px;
        height: 50px;
        line-height: 50px;
        font-size: 15px;
    }

    .alert-box .sec.user-input .line {
        margin-bottom: 10px;
        cursor: pointer;
    }

    .blue-bt.disabled {
        background: #dfdfdf !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        pointer-events: none;
    }
    .agreement-box{
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 19px;
        line-height: 40px;
    }
    .content-box{
        width: 1200px;
        min-width: 80%;
        max-width: 90%;
        height: 90%;
        overflow: auto;
        border: 10px solid white;
        border-right: 0;
        border-radius: 10px;
        background: white;
        position: relative;
    }
    .close-btn {
        height: 52px;
        width: 52px;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
        margin-bottom: -45px;
        background: #E6ECF3;
        display: flex;
        justify-content: center;
        align-items: center;
        border: none;
        border-radius: 50%;
    }
    .details-wrapper{
        padding: 25px 30px;
        color: #515151;
    }
    .title{
        text-align: center;
        font-size: 30px;
        color: #515151;
        font-weight: bold;
    }
    .agreement-box p {
        margin: 25px 0 50px;
        font-size: 19px;
        line-height: 28px;
        color: #5B5C67;
        line-height: 40px;
    }
    .agreement-box table{
        border-spacing: 0;
        border-collapse: collapse;
        width: 100%;
    }
    .agreement-box table tr td{
        padding: 10px 15px;
        border: 1px solid #ddd;
        line-height: inherit;
    }
    .sign-pad{
        background: white;
        height: 350px;
        position: relative;
    }
    canvas{
        border: 2px dashed #ff6977;
    }
    .overlay{
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        padding: inherit;
        z-index: 2;
    }
    .overlay .msg{
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f0f0f0;
        color: #8a8a8a;
        outline: 2px dashed;
        outline-offset: -20px;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        font-size: inherit;
    }
    .sigWrapper{
        opacity: 0;
        z-index: -10;
    }
    .sigWrapper.active{
        opacity: 1;
        z-index: 2;
    }
    .control-row{
        display: flex;
        justify-content: flex-end;
    }
    .control-row button{
        background-color: #ff6977;
        border-radius: 7px;
        margin-top: 30px;
    }
    .control-row button:hover{
        background-color: #ff6977;
    }
    .submit-row[data-v-14fd616e] {
        display: flex;
        justify-content: space-between;
        margin: 0 0 20px;
        color: #515151;
        position: relative;
    }
    .submit-row .info[data-v-14fd616e] {
        margin: 0;
    }
    .btn.save[data-v-14fd616e] {
        background-color: #14c765;
        margin-top: auto;
        border-radius: 7px;
    }
    .bold{
        font-weight:bold;
    }
    .amount{
        border-radius: 5px;
        padding: 0 10px;
        height: 40px;
        background: #EFF6F9 ;
        border: 1px solid #D8E6EB;
        border-radius: 48px;
        padding: 0 20px;
    }
    .bar-outer {
        background-color: #f3a29980;
    }
    .funder-count-wrap{
        justify-content: space-between;
    }
    .sub-header .price-wrapper .funder-count-wrap .funder-count, .sub-header .price-wrapper .funder-count-wrap .days-left{
        width: auto;
    }
    .sub-header .price-wrapper a.show-details{
        margin-top: 0;
    }
    p.error{
        font-size: 14px;
        font-weight: 600;
        color: #fa4040;
        margin-bottom: .5rem;
    }
    .close-wrapper {
        display: flex;
        height: 59px;
        justify-content: flex-end;
        background: #fff;
    }
    .close-btn-m{
        width: 59px;
        height: 59px;
        background-color: #E6EAF2;
        border-radius: 0;
        border: none;
    }
    .no-radius{
        border-radius: 0!important;
    }
    .popper-header h5{
        color: #40414D;
        font-size: 24px;
        background: #fff;
        padding: 0 25px;
        font-weight: bold;
    }
    .popper-header{
        background-color: #fff;
    }
    .progress-row{
        display: flex;
        flex-direction: column;
        padding: 0 25px;
        background: #fff;
    }
    .info-row{
        display: flex;
        justify-content: space-between;
        background: #fff;
    }
    .modal-lg-8{
        max-width: 80%;
    }
    .info-row .info{
        display: flex;
        flex-direction: column;
    }
    .info-row .info span.title{
        color: #BABDCE;
        font-size: 20px;
        font-weight: normal;
    }
    .info-row .info span.amount{
        color: #CB405A;
        font-size: 24px;
        font-weight: bold;
        background: #fff;
        border: none;
        padding: 0;
    }
    .info-row .info+.info span.amount{
        color: #4BBE9A;
    }
    .bg-white{
        background:#fff;
    }
    .progress-outer{
        background-color: #F4F4F4;
        margin-top: 10px;
        height: 14px;
        border-radius: 10px;
        display: flex;
    }
    .progress-outer .progress {
        background-color: #CB405A;
        border-radius: 10px;
        height: 14px;
    }
    .modal-body .tags{
        display: flex;
        flex-wrap: wrap;
        margin: 0 -25px;
    }
    .modal-body .tags .tag-item {
        width: calc((100% / 3 ) - 50px);
        padding: 0 25px;
        background-color: #FDFDFD;
        height: 99px;
        border: 1px solid #E6EAF0;
        margin:  0 25px 25px 25px;
        border-radius: 10px;
        justify-content: center;
        display: flex;
        flex-direction: column;
    }
    @media(max-width:1500px){
        .modal-body .tags .tag-item{
            width: calc((100% / 2 ) - 50px);
        }
    }
    @media (max-width:991px){
        .modal-body .tags .tag-item{
            width: calc((100%) - 50px);
        }
    }
    .tag-item span.title{
        color: #BABDCE;
        font-size:20px;
        font-weight: normal;
        display: block;
        text-align: left;
    }
    .tag-item span.amount{
        color: #40414D;
        background: #fff;
        border: none;
        font-size: 22px;
        font-weight: bold;
    }
</style>
@endpush
