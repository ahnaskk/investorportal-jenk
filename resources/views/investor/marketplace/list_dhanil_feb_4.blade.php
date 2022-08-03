@extends('layouts.marketplace.admin_lte')

@section('content')


    <div class="container">
    <div class="row">
        <div class="[ col-xs-12  col-sm-12 ]">
            <ul class="event-list">


                @foreach($funds as $fund)
                <?PHP
                   $maximum_amount=$fund->max_participant_fund - $fund->marketplaceInvestors()->sum('amount');
                   $max_per=$maximum_amount/$fund->funded*100;
                ?>



                @if($fund->funded == $maximum_amount )

                <?PHP
                    $fee=[
                    '0'=>'0'
                    ,'0.5'=>'0.5'
                    ,'1'=>'1'
                    ,'1.25'=>'1.25'
                    ,'1.5'=>'1.5'
                    ,'1.75'=>'1.75'
                    ,'2'=>'2'
                    ,'2.25'=>'2.25'
                    ,'2.5'=>'2.5'
                    ,'2.75'=>'2.75'
                    ,'3'=>'3'
                    ,'3.25'=>'3.25'
                    ,'3.5'=>'3.5'
                    ,'3.75'=>'3.75'
                    ,'4'=>'4'
                    ,'4.25'=>'4.25'
                    ,'4.5'=>'4.5'
                    ,'4.75'=>'4.75'
                    ,'5'=>'5'];
                ?>

 {!! Form::open(['route'=>'investor::funds_request', 'method'=>'POST']) !!}
                
                <li>
                    <div class="col-md-2 col-sm-6">
                        <div id="demo-pie-1" class="pie-title-center demo-pie-1" data-percent="{{100-$max_per}}"> <span class="pie-value"></span> <span class="pie-title">Funding Completed</span>
                        <span class="pie-value-pending"></span> <span class="pie-title-pending">Available</span>
                         </div>
                    </div>
                        


                    <div class="info col-md-10 col-sm-6">

                        <div class="row">
                            <div class="col-sm-6">

                                <h2 class="title">{{$fund->business_en_name}}</h2>
                                
                                <div class="row">
                                    <div class="col-sm-5">Total Fund</div>
                                    <div class="col-sm-7">{{Form::number('funded',$fund->funded,['id'=>'funded_'.$fund->id,'readonly'=>'readonly'])}}<!-- {{FFM::dollar($fund->funded)}} --></div>
                                </div>    

                                <div class="row">
                                    <div class="col-sm-5">Maximum Available Amount:</div>
                                    <div class="col-sm-7">{{Form::number('maximum_amount',$fund->max_participant_fund,['readonly'=>'readonly'])}}<!-- {{FFM::dollar($maximum_amount)}} --></div>
                                </div>



                                <div class="row">
                                    <div class="col-sm-5">Factor Rate:</div>
                                    <div class="col-sm-7">{{Form::number("factor_rate",old("factor_rate"),["placeholder"=>"ex: 1.25","id"=>"factor_rate_".$fund->id,"oninput"=>"set_factor_rate(this,$fund->id)"])}}</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-5">RTR</div>
                                    <div class="col-sm-7">{{Form::number('rtr','',['id'=>'rtr_'.$fund->id,'readonly'=>'readonly'])}}</div>
                                </div>


                                <div class="row">
                                    <div class="col-sm-5">Number of Payments</div>
                                    <div class="col-sm-7">{{Form::number("pmnts",old("pmnts"),["placeholder"=>"ex: 100","id"=>"pmnts_".$fund->id,"oninput"=>"set_pmnts(this,$fund->id)","max"=>"999"])}}</div>
                                </div>
  

                            <div class="row">
                                    <div class="col-sm-5">Daily Payment</div>
                                    <div class="col-sm-7">{{Form::text('daily_payment','',['readonly'=>'readonly','id'=>'daily_payment_'.$fund->id])}}</div>
                                </div>

                             

                            </div>
                        
                        <?PHP
                        $commissions=[1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10,11=>11,12=>12,13=>13,14=>14,15=>15];
                        ?>

                        <div class="col-sm-4">
                            <br><br>

                                <div class="row">
                                    <div class="col-sm-5">Commission Payable</div>
                                    <div class="col-sm-7">{{Form::select('commission',$commissions,['class'=>'form'])}}</div>
                                </div>

                               

                            <div class="row">
                                <div class="col-sm-5">Management Fee:</div>
                                <div class="col-sm-7">{{Form::select('mgmnt_fee',$fee)}}</div>
                              
                            </div>   <div class="row">
                                <div class="col-sm-5">Syndication Fee:</div>
                                <div class="col-sm-7">{{Form::select('syndication_fee',$fee)}}</div>
                            </div>   


                        </div>



                        <div class="col-sm-2">


                          
                           <input type="hidden" name="id" value="{{$fund->id}}" >
                           <div class="form-group">
                            <label class="control-label">Your Amount (2)</label>
                            <?PHP
                            $percentages =['100','95','90','85','80','75','70','65','60','55','50','45','40','35','30','25','20','15','10'
                                ];

                            $maximum_amount=$fund->max_participant_fund - $fund->marketplaceInvestors()->sum('amount');
                            ?>
                            <select id='funded_sel_{{$fund->id}}' style="padding: 1px;" class="form-control marktAmount" name="amount" onchange="change_amount(this,{{$fund->id}})" id="percentage">
                             <option selected="selected" value="{{$maximum_amount}}">{{$maximum_amount}} | {{$max_per}}%  </option>
                               
                                @foreach($percentages as $percentage)
                                @if($maximum_amount > $fund->funded*$percentage/100)
                                <option value="{{$fund->funded*$percentage/100}}">{{$fund->funded*$percentage/100}} | {{$percentage}}%</option>
                                @endif
                                @endforeach

                                        </select>

                                        <input   type="submit" class="form-control btn btn-success" value="Fund"  />
                                    </div>
                            <div>
                                <br>
                                <a class="form-control btn btn-success" href="{{route("investor::marketplace::document",["mid" => $fund->id])}}">View Docs</a>

                            </div>

                                    



                                </div>  
                            </div>

                    </div>
                </li>

                    {!! Form::close() !!}

                    @elseif($fund->max_participant_fund > $fund->marketplaceInvestors()->sum('amount') )
                
                <li>
                  

                    <div class="col-md-2 col-sm-6">
                    <div id="demo-pie-1" class="pie-title-center demo-pie-1" data-percent="{{100-$max_per}}"> <span class="pie-value"></span> <span class="pie-title">Funding Completed</span>
                    <span class="pie-value-pending"></span> <span class="pie-title-pending">Available</span>
                     </div>
                    </div>              


                    <div class="info">

                        <div class="row">
                            <div class="col-sm-6">

                                <h2 class="title">{{$fund->business_en_name}}</h2>
                                
                                <div class="row">
                                    <div class="col-sm-5">Total fund</div>
                                    <div class="col-sm-7">{{FFM::dollar($fund->funded)}}</div>
                                </div> <div class="row">
                                    <div class="col-sm-5">RTR</div>
                                    <div class="col-sm-7">{{FFM::dollar($fund->rtr)}}</div>
                                </div>

                              <div class="row">
                                    <div class="col-sm-5">Maximum Available Amount:</div>
                                    <div class="col-sm-7">{{FFM::dollar($maximum_amount)}}</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-sm-5">Number of Payments</div>
                                    <div class="col-sm-7">{{($fund->pmnts)}}</div>
                                </div>    

                               
                          


                            </div>

                        <div class="col-sm-4"><br><br>
                            <div class="row">
                                    <div class="col-sm-5">Daily Payment</div>
                                    <div class="col-sm-7">{{FFM::dollar($fund->payment_amount)}}</div>
                            </div>


                            <div class="row">
                                <div class="col-sm-5">Management Fee:</div>
                                <div class="col-sm-7">{{FFM::percent($fund->mgmnt_fee)}}</div>
                            </div>   <div class="row">
                                <div class="col-sm-5">Syndication Fee:</div>
                                <div class="col-sm-7">{{FFM::percent($fund->syndication_fee)}}</div>
                            </div>   


                        </div>



                        <div class="col-sm-2">


                           {!! Form::open(['route'=>'investor::funds_request', 'method'=>'POST']) !!}
                           <input type="hidden" name="id" value="{{$fund->id}}" >
                           <div class="form-group">
                            <label class="control-label">Your Amount</label>
                            <?PHP
                            $percentages =['100','95','90','85','80','75','70','65','60','55','50','45','40','35','30','25','20','15','10'
                                ];

                         
                            ?>
                            <select style="padding: 1px;" class="form-control marktAmount" name="amount"  id="percentage">
                             <option selected="selected" value="{{$maximum_amount}}">{{$maximum_amount}} | {{$maximum_amount/$fund->funded*100}}%  </option>
                               
                                @foreach($percentages as $percentage)
                                @if($maximum_amount > $fund->funded*$percentage/100)
                                <option value="{{$fund->funded*$percentage/100}}">{{$fund->funded*$percentage/100}} | {{$percentage}}%</option>
                                @endif
                                @endforeach

                                        </select>

                                        <input  onclick='return confirm("Are you sure that you want to funding this deal for {{$fund->name}}?  If you select yes, then you are committing to syndicating in this deal")' type="submit" class="form-control btn btn-success" value="Fund"  />
                                    </div>

                                       <div>
                                <br>
                                <a class="form-control btn btn-success" href="{{route("investor::marketplace::document",["mid" => $fund->id])}}">View Docs</a>

                            </div>

                            

                                    {!! Form::close() !!}

                                </div>  
                            </div>

                        </div>
                        
                </li>
                    @endif
                    @endforeach

                </ul>
            </div>


















            <!-- New Html 03-02-2018 -->
                        <div class="wrap"></div>
                        <div class="grid marktPlcCardContainer" style="margin-top: 50px;">
                            <ul class="marktPlcUl">
                                <li>
                                    <div class="marktPlceCard">
                                        <div class="marktPlceCardTitle">TRI STATE ROOFING00000</div>
                                        <div class="wrap"></div>
                                        <div class="firstSec grid">
                                            <div class="markt-diagram">
                                                <div id="demo-pie-1" class="pie-title-center demo-pie-1" data-percent="{{100-$max_per}}"> <span class="pie-value"></span> <span class="pie-title">Funding Completed</span>
                                                <span class="pie-value-pending"></span> <span class="pie-title-pending">Available</span>
                                                 </div>
                                            </div>
                                            <div class="markt-details-one">
                                                <table class="table">
                                                    <tr>
                                                        <td>Total fund</td>
                                                        <td>$45,000.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>RTR</td>
                                                        <td>$69,750.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Maximum Available Amount</td>
                                                        <td>$22,500.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Number of Payments</td>
                                                        <td>180</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="SecondSec grid">
                                            <div class="SecondSec-left">
                                                <table class="table">
                                                    <tr>
                                                        <td>Daily payment</td>
                                                        <td>$761.42</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Management Fee:</td>
                                                        <td>1.5 %</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="SecondSec-right">
                                                <table class="table">
                                                    <tr>
                                                        <td>Syndication Fee</td>
                                                        <td>0 %</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="thirdSec grid">
                                            <div class="thirdSec-bid grid">
                                                    <label class="control-label">Your Amount</label>
                                                    <select style="padding: 1px;" class="form-control" name="amount" id="percentage">
                                                        <option selected="selected" value="22500">22500 | 50% </option>
                                                        <option value="20250">20250 | 45%</option>
                                                        <option value="18000">18000 | 40%</option>
                                                        <option value="15750">15750 | 35%</option>
                                                        <option value="13500">13500 | 30%</option>
                                                        <option value="11250">11250 | 25%</option>
                                                        <option value="9000">9000 | 20%</option>
                                                        <option value="6750">6750 | 15%</option>
                                                        <option value="4500">4500 | 10%</option>
                                                    </select>
                                                
                                            </div>
                                            <div class="thirdSec-btns">
                                                <input onclick='return confirm("Are you sure that you want to funding this deal for TRI STATE ROOFING?  If you select yes, then you are committing to syndicating in this deal")' class="form-control btn btn-success" value="Fund" type="submit">
                                                <a class="form-control btn btn-success" href="http://merchant.iocod.com/investor/marketplace/3351/documents">View Docs</a>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="marktPlceCard">
                                        <div class="marktPlceCardTitle">TRI STATE ROOFING00000</div>
                                        <div class="wrap"></div>
                                        <div class="firstSec grid">
                                            <div class="markt-diagram">
                                                <div id="demo-pie-1" class="pie-title-center demo-pie-1" data-percent="50" style="width: 150px;height: 150px;float: left;background-color: #eee;">
                                                </div>
                                            </div>
                                            <div class="markt-details-one">
                                                <table class="table">
                                                    <tr>
                                                        <td>Total Fund</td>
                                                        <td><select><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option></select></td>
                                                    </tr>
                                                    <tr>
                                                        <td>RTR</td>
                                                        <td><input readonly="readonly"   value="" type="text"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Maximum Available Amount</td>
                                                        <td><input name="funded" value="74900" type="number"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Number of Payments</td>
                                                        <td>180</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="SecondSec grid">
                                            <div class="SecondSec-left">
                                                <table class="table">
                                                    <tr>
                                                        <td>Daily Payment</td>
                                                        <td>$761.42</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Management Fee:</td>
                                                        <td>1.5 %</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="SecondSec-right">
                                                <table class="table">
                                                    <tr>
                                                        <td>Syndication Fee</td>
                                                        <td>0 %</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="thirdSec grid">
                                            <div class="thirdSec-bid grid">
                                                    <label class="control-label">Your Amount</label>
                                                    <select style="padding: 1px;" class="form-control" name="amount" id="percentage">
                                                        <option selected="selected" value="22500">22500 | 50% </option>
                                                        <option value="20250">20250 | 45%</option>
                                                        <option value="18000">18000 | 40%</option>
                                                        <option value="15750">15750 | 35%</option>
                                                        <option value="13500">13500 | 30%</option>
                                                        <option value="11250">11250 | 25%</option>
                                                        <option value="9000">9000 | 20%</option>
                                                        <option value="6750">6750 | 15%</option>
                                                        <option value="4500">4500 | 10%</option>
                                                    </select>
                                                
                                            </div>
                                            <div class="thirdSec-btns">
                                                <input onclick='return confirm("Are you sure that you want to funding this deal for TRI STATE ROOFING?  If you select yes, then you are committing to syndicating in this deal")' class="form-control btn btn-success" value="Fund" type="submit">
                                                <a class="form-control btn btn-success" href="http://merchant.iocod.com/investor/marketplace/3351/documents">View Docs</a>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="marktPlceCard">
                                        <div class="marktPlceCardTitle">TRI STATE ROOFING00000</div>
                                        <div class="wrap"></div>
                                        <div class="firstSec grid">
                                            <div class="markt-diagram">
                                                <div id="demo-pie-1" class="pie-title-center demo-pie-1" data-percent="50" style="width: 150px;height: 150px;float: left;background-color: #eee;">
                                                </div>
                                            </div>
                                            <div class="markt-details-one">
                                                <table class="table">
                                                    <tr>
                                                        <td>Total fund</td>
                                                        <td>$45,000.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>RTR</td>
                                                        <td>$69,750.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Maximum Available Amount</td>
                                                        <td>$22,500.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Number of Payments</td>
                                                        <td>180</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="SecondSec grid">
                                            <div class="SecondSec-left">
                                                <table class="table">
                                                    <tr>
                                                        <td>Daily payment</td>
                                                        <td>$761.42</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Management Fee:</td>
                                                        <td>1.5 %</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="SecondSec-right">
                                                <table class="table">
                                                    <tr>
                                                        <td>Syndication fee</td>
                                                        <td>0 %</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="thirdSec grid">
                                            <div class="thirdSec-bid grid">
                                                    <label class="control-label">Your Amount</label>
                                                    <select style="padding: 1px;" class="form-control" name="amount" id="percentage">
                                                        <option selected="selected" value="22500">22500 | 50% </option>
                                                        <option value="20250">20250 | 45%</option>
                                                        <option value="18000">18000 | 40%</option>
                                                        <option value="15750">15750 | 35%</option>
                                                        <option value="13500">13500 | 30%</option>
                                                        <option value="11250">11250 | 25%</option>
                                                        <option value="9000">9000 | 20%</option>
                                                        <option value="6750">6750 | 15%</option>
                                                        <option value="4500">4500 | 10%</option>
                                                    </select>
                                                
                                            </div>
                                            <div class="thirdSec-btns">
                                                <input onclick='return confirm("Are you sure that you want to funding this deal for TRI STATE ROOFING?  If you select yes, then you are committing to syndicating in this deal")' class="form-control btn btn-success" value="Fund" type="submit">
                                                <a class="form-control btn btn-success" href="http://merchant.iocod.com/investor/marketplace/3351/documents">View Docs</a>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="marktPlceCard">
                                        <div class="marktPlceCardTitle">TRI STATE ROOFING00000</div>
                                        <div class="wrap"></div>
                                        <div class="firstSec grid">
                                            <div class="markt-diagram">
                                                <div id="demo-pie-1" class="pie-title-center demo-pie-1" data-percent="50" style="width: 150px;height: 150px;float: left;background-color: #eee;">
                                                </div>
                                            </div>
                                            <div class="markt-details-one">
                                                <table class="table">
                                                    <tr>
                                                        <td>Total Fund</td>
                                                        <td>$45,000.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>RTR</td>
                                                        <td>$69,750.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Maximum Available Amount</td>
                                                        <td>$22,500.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Number of Payments</td>
                                                        <td>180</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="SecondSec grid">
                                            <div class="SecondSec-left">
                                                <table class="table">
                                                    <tr>
                                                        <td>Daily Payment</td>
                                                        <td>$761.42</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Management Fee:</td>
                                                        <td>1.5 %</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="SecondSec-right">
                                                <table class="table">
                                                    <tr>
                                                        <td>Syndication Fee</td>
                                                        <td>0 %</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="thirdSec grid">
                                            <div class="thirdSec-bid grid">
                                                    <label class="control-label">Your Amount</label>
                                                    <select style="padding: 1px;" class="form-control" name="amount" id="percentage">
                                                        <option selected="selected" value="22500">22500 | 50% </option>
                                                        <option value="20250">20250 | 45%</option>
                                                        <option value="18000">18000 | 40%</option>
                                                        <option value="15750">15750 | 35%</option>
                                                        <option value="13500">13500 | 30%</option>
                                                        <option value="11250">11250 | 25%</option>
                                                        <option value="9000">9000 | 20%</option>
                                                        <option value="6750">6750 | 15%</option>
                                                        <option value="4500">4500 | 10%</option>
                                                    </select>
                                                
                                            </div>
                                            <div class="thirdSec-btns">
                                                <input onclick='return confirm("Are you sure that you want to funding this deal for TRI STATE ROOFING?  If you select yes, then you are committing to syndicating in this deal")' class="form-control btn btn-success" value="Fund" type="submit">
                                                <a class="form-control btn btn-success" href="http://merchant.iocod.com/investor/marketplace/3351/documents">View Docs</a>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <div class="marktPlceCard">
                                        <div class="marktPlceCardTitle">TRI STATE ROOFING00000</div>
                                        <div class="wrap"></div>
                                        <div class="firstSec grid">
                                            <div class="markt-diagram">
                                                <div id="demo-pie-1" class="pie-title-center demo-pie-1" data-percent="50" style="width: 150px;height: 150px;float: left;background-color: #eee;">
                                                </div>
                                            </div>
                                            <div class="markt-details-one">
                                                <table class="table">
                                                    <tr>
                                                        <td>Total Fund</td>
                                                        <td>$45,000.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>RTR</td>
                                                        <td>$69,750.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Maximum Available Amount</td>
                                                        <td>$22,500.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Number of Payments</td>
                                                        <td>180</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="SecondSec grid">
                                            <div class="SecondSec-left">
                                                <table class="table">
                                                    <tr>
                                                        <td>Daily Payment</td>
                                                        <td>$761.42</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Management Fee:</td>
                                                        <td>1.5 %</td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="SecondSec-right">
                                                <table class="table">
                                                    <tr>
                                                        <td>Syndication Fee</td>
                                                        <td>0 %</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="thirdSec grid">
                                            <div class="thirdSec-bid grid">
                                                    <label class="control-label">Your Amount</label>
                                                    <select style="padding: 1px;" class="form-control" name="amount" id="percentage">
                                                        <option selected="selected" value="22500">22500 | 50% </option>
                                                        <option value="20250">20250 | 45%</option>
                                                        <option value="18000">18000 | 40%</option>
                                                        <option value="15750">15750 | 35%</option>
                                                        <option value="13500">13500 | 30%</option>
                                                        <option value="11250">11250 | 25%</option>
                                                        <option value="9000">9000 | 20%</option>
                                                        <option value="6750">6750 | 15%</option>
                                                        <option value="4500">4500 | 10%</option>
                                                    </select>
                                                
                                            </div>
                                            <div class="thirdSec-btns">
                                                <input onclick='return confirm("Are you sure that you want to funding this deal for TRI STATE ROOFING?  If you select yes, then you are committing to syndicating in this deal")' class="form-control btn btn-success" value="Fund" type="submit">
                                                <a class="form-control btn btn-success" href="http://merchant.iocod.com/investor/marketplace/3351/documents">View Docs</a>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- New Html 03-02-2018 -->



































        </div>
    </div>







    @stop


    @section('scripts')
    <script src="{{ asset ("bower_components/datatables.net/js/jquery.dataTables.min.js") }}" type="text/javascript"></script>

    <script src="{{ asset ("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js") }}" type="text/javascript"></script>

       <script src='{{ asset("js/pie-chart.js")}}' type="text/javascript"></script>

    <script type="text/javascript">


function change_amount(this_f,id) {
 
    set_factor_rate(this_f,id);
    //set_pmnts(this_f,id);
    // body...
}
 function set_factor_rate(this_f,id)
 {
       // alert('he');
    (this_id)=this_f.id;
    rtr_id = 'rtr_'+id;//this_id.replace("factor_rate", "rtr")
    fund_id ='funded_sel_'+id;// this_id.replace("factor_rate", "funded")
    funded_amount =  $('#'+fund_id).val()?$('#'+fund_id).val():0;
   
    //alert(funded_amount);
 factor_rate =  $('#factor_rate_'+id).val()?$('#factor_rate_'+id).val():0;

    rtr =  parseFloat(funded_amount)*parseFloat(factor_rate);

    $('#'+rtr_id).val(rtr);//=33;
    this_f.rtr=rtr;
    set_pmnts(this_f,id);
  } 

  function set_pmnts(this_f2,id)
  {
       // alert('he');
    (this_id)=this_f2.id;
    daily_payment_id = 'daily_payment_'+id; //this_id.replace("pmnts", "daily_payment")
    rtr_id ='rtr_'+id;// this_id.replace("pmnts", "rtr")
    pmnts_id ='pmnts_'+id;// this_id.replace("pmnts", "rtr")
    rtr_amount =  $('#'+rtr_id).val()?$('#'+rtr_id).val():0;
    pmnts =  $('#'+pmnts_id).val()?$('#'+pmnts_id).val():0;
    //alert(funded_amount);
    daily_payment =  (parseFloat(rtr_amount)/parseFloat(pmnts)).toFixed(2);
    daily_payment = isFinite(daily_payment)?daily_payment:0;
//alert(daily_payment_id);
    $('#'+daily_payment_id).val(daily_payment);//=33;
  }



        $(document).ready(function () {
            $('.demo-pie-1').pieChart({
                barColor: '#68b828',
                trackColor: '#eee',
                lineCap: 'butt',
                lineWidth: 8,
                onStep: function (from, to, percent) {
                    $(this.element).find('.pie-value').text(Math.round(percent)+' %');
                    $(this.element).find('.pie-value-pending').text(Math.round(100-percent)+' %');
                }
            });


   });


           



</script>




@stop
@section('styles')
<link rel="stylesheet" href="{{ asset ("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css") }}">

 
<style type="text/css">
    @import url("http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,400italic");
    @import url("//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.css");
    body {
        padding: 60px 0px;
        background-color: rgb(220, 220, 220);
    }
    
    .flag{
        width: 50px;
    }
    .marktAmount{margin-bottom: 15px;}
    
    h2{
        font-size: 200%;
        margin-top: 0px;
        font-weight: bold;
    }
    
    .event-list .info .row{
        padding-left: 10px;
        /*font-size: 80%;*/
    }
    
    .event-list {
        list-style: none;
        font-family: 'Lato', sans-serif;
        margin: 0px;
        padding: 0px;
    }
    .event-list > li {
        background-color: rgb(255, 255, 255);
        box-shadow: 0px 0px 5px rgb(51, 51, 51);
        box-shadow: 0px 0px 5px rgba(51, 51, 51, 0.7);
        padding: 0px;
        margin: 0px 0px 20px;
    }
    .event-list > li > time {
        display: inline-block;
        width: 100%;
        padding: 5px;
        text-align: center;
        text-transform: uppercase;
    }
    
    .event-list > li > time > span {
        display: none;
    }
    .event-list > li > time > .day {
        display: block;
        font-size: 56pt;
        font-weight: 100;
        line-height: 1;
    }
    .event-list > li time > .month {
        display: block;
        font-size: 24pt;
        font-weight: 900;
        line-height: 1;
    }
    .event-list > li > img {
        width: 100%;
    }
    .event-list > li > .info {
        padding-top: 5px;
        text-align: center;
    }
    .event-list > li > .info > .title {
        font-size: 17pt;
        font-weight: 700;
        margin: 0px;
    }
    .event-list > li > .info > .desc {
        font-size: 13pt;
        font-weight: 300;
        margin: 0px;
    }
    .event-list > li > .info > ul,
    .event-list > li > .social > ul {
        display: table;
        list-style: none;
        margin: 10px 0px 0px;
        padding: 0px;
        width: 100%;
        text-align: center;
    }
    .event-list > li > .social > ul {
        margin: 0px;
    }
    .event-list > li > .info > ul > li,
    .event-list > li > .social > ul > li {
        display: table-cell;
        cursor: pointer;
        color: rgb(30, 30, 30);
        font-size: 11pt;
        font-weight: 300;
        padding: 3px 0px;
    }
    .event-list > li > .info > ul > li > a {
        display: block;
        width: 100%;
        color: rgb(30, 30, 30);
        text-decoration: none;
    } 
    .event-list > li > .social > ul > li {    
        padding: 0px;
    }
    .event-list > li > .social > ul > li > a {
        padding: 3px 0px;
    } 
    .event-list > li > .info > ul > li:hover,
    .event-list > li > .social > ul > li:hover {
        color: rgb(30, 30, 30);
        background-color: rgb(200, 200, 200);
    }
    .facebook a,
    .twitter a,
    .google-plus a {
        display: block;
        width: 100%;
        color: rgb(75, 110, 168) !important;
    }
    .twitter a {
        color: rgb(79, 213, 248) !important;
    }
    .google-plus a {
        color: rgb(221, 75, 57) !important;
    }
    .facebook:hover a {
        color: rgb(255, 255, 255) !important;
        background-color: rgb(75, 110, 168) !important;
    }
    .twitter:hover a {
        color: rgb(255, 255, 255) !important;
        background-color: rgb(79, 213, 248) !important;
    }
    .google-plus:hover a {
        color: rgb(255, 255, 255) !important;
        background-color: rgb(221, 75, 57) !important;
    }

    @media (min-width: 768px) {
        .event-list > li {
            position: relative;
            display: block;
            width: 100%;
            /*height: 120px;*/
            padding: 0px;
        }
        .event-list > li > time,
        .event-list > li > img  {
            display: inline-block;
        }
        .event-list > li > time,
        .event-list > li > img {
            width: 120px;
            float: left;
        }
        .event-list > li > .info {
            background-color: rgb(245, 245, 245);
            overflow: hidden;
            position: relative;
            /*height: 120px;*/
            text-align: left;
            padding-right: 40px;
            padding-bottom: 15px;
            padding-top: 15px;
        }
        .event-list > li > time,
        .event-list > li > img {
            width: 120px;
            height: 149px;
            padding: 0px;
            margin: 0px;
        }
        .event-list > li > time{
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .event-list > li > .info > .title, 
        .event-list > li > .info > .desc {
            padding: 0px 10px;
        }
        .event-list > li > .info > ul {
            position: absolute;
            left: 0px;
            bottom: 0px;
        }
        .event-list > li > .social {
            position: absolute;
            top: 0px;
            right: 0px;
            display: block;
            width: 40px;
        }
        .event-list > li > .social > ul {
            border-left: 1px solid rgb(230, 230, 230);
        }
        .event-list > li > .social > ul > li {          
            display: block;
            padding: 0px;
        }
        .event-list > li > .social > ul > li > a {
            display: block;
            width: 40px;
            padding: 10px 0px 9px;
        }
    }




    /*Progress bar*/



.pie-title-center {
  display: inline-block;
  position: relative;
  text-align: center;
}

.pie-value {
  display: block;
  position: absolute;
  font-size: 29px;
  height: 40px;
  top: 50%;
  left: 0;
  right: 0;
  margin-top: -50px;
  line-height: 40px;
}.pie-value {
     display: block;
    position: absolute;
    font-size: 29px;
    height: 40px;
    top: 50%;
    left: 0;
    right: 0;
    margin-top: -50px;
    line-height: 40px;
}.pie-value-pending {

    display: block;
    position: absolute;
    font-size: 22px;
    height: 40px;
    top: 82%;
    left: 0;
    right: 0;
    margin-top: -50px;
    line-height: 40px;
}
.pie-title {
display: block;
    position: absolute;
    font-size: 14px;
    height: 40px;
    top: 42%;
    left: 0;
    right: 0;
    margin-top: -15px;
    line-height: 40px;
}.pie-title-pending {
    display: block;
    position: absolute;
    font-size: 14px;
    height: 40px;
    top: 70%;
    left: 0;
    right: 0;
    margin-top: -15px;
    line-height: 40px;
}

</style>
@stop
