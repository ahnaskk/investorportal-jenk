@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Do not refresh this page. </div>

                <div class="panel-body">
                    <pre>



                    <?PHP 

                    print_r($merchants);


                    ?>
                </pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

<script type="text/javascript">
    // $('div[rel=\'next\']').click();


$(function(){
    next_page = '<?PHP echo URL::to('/calicut78io/debug/Data2') ?>?page=<?PHP echo $_GET['page']+1; ?>';

    $(location).attr('href', next_page)


   // $(".page-link:last-of-type").trigger("click");       
});

</script>
@endsection