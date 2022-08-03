@extends('layouts.investor.admin_lte')

@section('content')
    <section class="content">

        <div class="row">
            <div class="col-md-12">

                <div class="grid box box-pad statementBox">
                    <table class="table">
                    
                        <tr>
                            <th>Statements</th>
                            <th>PDF Statement</th>
        <th style="padding-left: 30px;">Generated Date</th>
                            <th style="padding-left: 30px;">Period</th>
                        </tr>
                            @if(count($statements)<1)
                            <td>Sorry! No report generated yet!</td>
                            @endif
                                @foreach($statements as $statement)
                        <tr>
                            <td>
                            <div>
                                <?php $fileNameCSV=$statement->file_name.".csv" ?>

                                 <a href="{{ asset(\Storage::disk('s3')->url($fileNameCSV))  }}"> {{  $fileNameCSV  }}</a>
                                    <!-- <a href="{{URL::to('investor/report/weekly-statement',$statement->id)}}"> -->
                                  <!--   <a href="{{URL::to('exports')}}/{{$statement->file_name}}.{{$statement->file_type}}">
                                  
 -->                              
          
                                    </a></div>
                            </td>

                            <td>

                                 <div>
                                       <?php $fileNamePdf=$statement->file_name.".pdf" ?>
                                       <a href="{{ asset(\Storage::disk('s3')->url($fileNamePdf))  }}"> {{  $fileNamePdf  }}</a>

                                   <!--  <a href="{{URL::to('investor/report/weekly-statement',$statement->id)}}"> -->
                               
                               
          
                                    </a>

                                </div>
                                


                            </td>



                            <td style="padding-left:30px ">{{FFM::datetime(($statement->created_at))}}</td>
                            <td style="padding-left:30px ">{{FFM::date(($statement->from_date))}} to {{FFM::date( ($statement->to_date))}}</td>
                        </tr>
                                @endforeach
                        
                    </table>
                    {{ $statements->links() }}
                </div>

            </div>
        </div>
    </section>
@stop

