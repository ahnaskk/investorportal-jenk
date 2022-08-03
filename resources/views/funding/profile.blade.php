@extends('funding.includes.app')
@section('content')
    <header class="sub-header">
        <div class="container">

            <!-- Heading -->
            <div class="heading profile-heading">
                <div class="profile-avatar">
                    <svg id="Component_2_1" data-name="Component 2 – 1" xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96">
                        <g id="Rectangle_153" data-name="Rectangle 153" fill="#b5c0cf" stroke="#eff6f9" stroke-width="1">
                            <rect width="96" height="96" rx="48" stroke="none"/>
                            <rect x="0.5" y="0.5" width="95" height="95" rx="47.5" fill="none"/>
                        </g>
                        <path id="Path_23" data-name="Path 23" d="M147.465,126.075A10.977,10.977,0,1,1,158.441,115.1,10.978,10.978,0,0,1,147.465,126.075Zm0-18.789a7.813,7.813,0,1,0,7.812,7.812A7.814,7.814,0,0,0,147.465,107.286Zm17.523,42.3H129.942L128.36,148a19.1,19.1,0,1,1,38.21,0ZM131.6,146.421h31.732a15.942,15.942,0,0,0-31.732,0Z" transform="translate(-99.484 -78.995)" fill="#f9fbfc" stroke="#b5c0cf" stroke-width="0.5"/>
                    </svg>

                </div>
                <h1>{{Auth::user()->name}}</h1>
            </div>
            <!-- /.Heading -->

        </div>
    </header>


    <section class="content-area page-content">
        <div class="container">

            <div class="row">
                <div class="col-lg-12">
                    <div class="contact-form profile-box">
                        <div class="row">


                            <form action="" method="post" class="col-md-8" id="fundings-signup">
                                @include('layouts.admin.partials.lte_alerts')
                                @csrf
                                <div class="data-group">
                                    <label>Name <span class="required">*</span></label>
                                    <input required name="name" type="text" class="form-control" value="{{Auth::user()->name}}">
                                </div>
                                <div class="data-group">
                                    <label>Email </label>
                                    <input readonly required name="email" type="email" class="form-control" value="{{Auth::user()->email}}">
                                </div>
                                <div class="data-group">
                                    <label>Phone <span class="required">*</span></label>
                                    <input required name="cell_phone" type="text" class="form-control" value="{{Auth::user()->cell_phone}}" id="phoneNumber">
                                </div>
                                {{--<div class="data-group">
                                    <label>Address</label>
                                    <textarea class="form-control" >2769  Morningview Lane, New York</textarea>
                                </div>--}}
                                <div class="data-group">
                                    <button type="submit" class="btn btn-submit">Submit</button>
                                </div>
                            </form>


                            <div class="col-md-4">
                                <a href="{{ url('/fundings') }}" class="btn btn-edit">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
@push('scripts')
<script src="{{ asset ('js/jquery_validate_min.js') }}"></script>
<script>
    $("#tab-container").skeletabs({
                                      equalHeights: true,
                                      animation: "fade-scale",
                                      keyboard: false,
                                      responsive: {
                                          breakpoint: 800,
                                          headingTagName: "h4"
                                      }
                                  });


    function submit_comment(){
        var comment = $('.commentar').val();
        el = document.createElement('li');
        el.className = "box_result row";
        el.innerHTML =
                '<div class=\"avatar_comment col-md-1\">'+
                '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\"/>'+
                '</div>'+
                '<div class=\"result_comment col-md-11\">'+
                '<h4>Anonimous</h4>'+
                '<p>'+ comment +'</p>'+
                '<div class=\"tools_comment\">'+
                '<a class=\"like\" href=\"#\">Like</a><span aria-hidden=\"true\"> · </span>'+
                '<i class=\"fa fa-thumbs-o-up\"></i> <span class=\"count\">0</span>'+
                '<span aria-hidden=\"true\"> · </span>'+
                '<a class=\"replay\" href=\"#\">Reply</a><span aria-hidden=\"true\"> · </span>'+
                '<span>1m</span>'+
                '</div>'+
                '<ul class="child_replay"></ul>'+
                '</div>';
        document.getElementById('list_comment').prepend(el);
        $('.commentar').val('');
    }

    $(document).ready(function() {
        $('#list_comment').on('click', '.like', function (e) {
            $current = $(this);
            var x = $current.closest('div').find('.like').text().trim();
            var y = parseInt($current.closest('div').find('.count').text().trim());

            if (x === "Like") {
                $current.closest('div').find('.like').text('Unlike');
                $current.closest('div').find('.count').text(y + 1);
            } else if (x === "Unlike"){
                $current.closest('div').find('.like').text('Like');
                $current.closest('div').find('.count').text(y - 1);
            } else {
                var replay = $current.closest('div').find('.like').text('Like');
                $current.closest('div').find('.count').text(y - 1);
            }
        });

        $('#list_comment').on('click', '.replay', function (e) {
            cancel_reply();
            $current = $(this);
            el = document.createElement('li');
            el.className = "box_reply row";
            el.innerHTML =
                    '<div class=\"col-md-12 reply_comment\">'+
                    '<div class=\"row\">'+
                    '<div class=\"avatar_comment col-md-1\">'+
                    '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\"/>'+
                    '</div>'+
                    '<div class=\"box_comment col-md-10\">'+
                    '<textarea class=\"comment_replay\" placeholder=\"Add a comment...\"></textarea>'+
                    '<div class=\"box_post\">'+
                    '<div class=\"pull-right\">'+
                    '<span>'+
                    '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\" />'+
                    '<i class=\"fa fa-caret-down\"></i>'+
                    '</span>'+
                    '<button class=\"cancel\" onclick=\"cancel_reply()\" type=\"button\">Cancel</button>'+
                    '<button onclick=\"submit_reply()\" type=\"button\" value=\"1\">Reply</button>'+
                    '</div>'+
                    '</div>'+
                    '</div>'+
                    '</div>'+
                    '</div>';
            $current.closest('li').find('.child_replay').prepend(el);
        });
        $("#fundings-signup").validate({
            rules:{
                cell_phone:{
                    required:true,
                    validPhone:true
                }
            }
        })
        /**
         * jQuery validation custom rules
         */
        jQuery.validator.addMethod("validPhone", function(value, element) {
            return  /^\([0-9]{3}\)[0-9]{3}-[0-9]{4}$/.test(value);
        }, "Enter a valid phone number");
        /**
         * format phone number
         */
        $("#phoneNumber").keyup(function(e){
            var phone = $(this).val()
            phone = phone.replace(/[^0-9]/g, '');
            phone = phone.replace(/(\d{3})(\d{3})(\d{4})/, "($1)$2-$3");
            $(this).val(phone)
        })
    });

    function submit_reply(){
        var comment_replay = $('.comment_replay').val();
        el = document.createElement('li');
        el.className = "box_reply row";
        el.innerHTML =
                '<div class=\"avatar_comment col-md-1\">'+
                '<img src=\"https://static.xx.fbcdn.net/rsrc.php/v1/yi/r/odA9sNLrE86.jpg\" alt=\"avatar\"/>'+
                '</div>'+
                '<div class=\"result_comment col-md-11\">'+
                '<h4>Anonimous</h4>'+
                '<p>'+ comment_replay +'</p>'+
                '<div class=\"tools_comment\">'+
                '<a class=\"like\" href=\"#\">Like</a><span aria-hidden=\"true\"> · </span>'+
                '<i class=\"fa fa-thumbs-o-up\"></i> <span class=\"count\">0</span>'+
                '<span aria-hidden=\"true\"> · </span>'+
                '<a class=\"replay\" href=\"#\">Reply</a><span aria-hidden=\"true\"> · </span>'+
                '<span>1m</span>'+
                '</div>'+
                '<ul class="child_replay"></ul>'+
                '</div>';
        $current.closest('li').find('.child_replay').prepend(el);
        $('.comment_replay').val('');
        cancel_reply();
    }

    function cancel_reply(){
        $('.reply_comment').remove();
    }


</script>
@endpush