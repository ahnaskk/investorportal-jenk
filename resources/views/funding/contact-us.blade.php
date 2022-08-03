@extends('funding.includes.app')
@section('content')
    <!-- Banner -->
    <header class="sub-header">
        <div class="container">

            <!-- Heading -->
            <div class="heading">
                <h1>Contact us</h1>
                <p>Our team is available to answer your questions. Please call or email us about our offerings or the investment process.</p>
            </div>
            <!-- /.Heading -->

        </div>
    </header>
    <!-- /.Banner -->


    <section class="content-area page-content">
        <div class="container">

            <div class="row">
                <div class="col-lg-12">
                    <form action="" method="post" class="contact-form" id="contactForm">
                        @include('layouts.admin.partials.lte_alerts')
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Name <span class="required">*</span></label>
                                    <input required name="name" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email <span class="required">*</span></label>
                                    <input required name="email" type="email" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Phone Number <span class="required">*</span></label>
                                    <input required name="phone" type="text" class="form-control" id="phoneNumber">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Company Name </label>
                                    <input name="company"  type="text" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Message </label>
                                    <textarea name="message" type="text" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-submit">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 contact-details">
                    <h5>Address</h5>
                    <p>
                        290-300 Broadhollow Road, Melville,<br>
                        New York 11747
                    </p>
                </div>
                <div class="col-md-4 contact-details">
                    <h5>Phone</h5>
                    <p>
                        631-201-0703
                    </p>
                </div>
                <div class="col-md-4 contact-details">
                    <h5>Email</h5>
                    <p>
                        <a href="#">info@vgusa.com</a>
                    </p>
                </div>

                <div class="col-md-12">
                    <div class="map-box">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6042.024212451114!2d-73.418767!3d40.783748!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e82a2d31d41a41%3A0xb2f9bc596394d851!2s290300%20Broadhollow%20Rd%2C%20Melville%2C%20NY%2011747!5e0!3m2!1sen!2sus!4v1620086405625!5m2!1sen!2sus" width="600" height="180" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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

        $("#contactForm").validate({
            rules:{
                phone:{
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
