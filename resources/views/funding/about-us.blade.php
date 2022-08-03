@extends('funding.includes.app')

@push('alerts')
@include('layouts.admin.partials.lte_alerts')
@endpush
@section('content')
    <header class="sub-header">
        <div class="container">
            <div class="heading">
                <h1>About Velocity Business Crowdfunding</h1>
                <p>Now anyone can fund your business.</p>
                <div class="banner-img">
                    <img src="{{url('funding/images/about-banner.jpg')}}">
                </div>
            </div>
        </div>
    </header>


    <section class="content-area page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <p class="main-content">
                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
                    </p>
                    <p>
                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur
                    </p>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="about-box">
                                <h3>Our History</h3>
                                <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="about-box">
                                <h3>Our Mission</h3>
                                <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?</p>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="about-blue-box">
                                <h5>Thousands are crowdfunding for various business. Support a fundraiser today!</h5>
                                <a href="#" class="btn">RAISE CAPITAL</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
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
