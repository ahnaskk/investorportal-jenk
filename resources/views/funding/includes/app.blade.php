<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Velocity Business Crowdfunding</title>

    <!-- favicon -->
    <link rel="icon" href="{{url('funding/images/favicon.ico')}}" type="image/x-icon">

    <!-- Custom CSS -->
    <link href="{{url('funding/css/bootstrap.css')}}" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{url('funding/css/style.css')}}" rel="stylesheet">
    <link href="{{url('funding/css/skeletabs.css')}}" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    @stack('style')
</head>

<body>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v9.0&appId=201782190549727&autoLogAppEvents=1" nonce="Qs6i3Qn5"></script>
@stack('alerts')
<nav class="navbar navbar-expand-lg ">
    <div class="container">
        <a class="navbar-brand logo" href="{{url('/fundings')}}"><img src="{{url('funding/images/logo.png')}}" class=""></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <div id="nav-icon">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <div class="menu-right">
                <form>
                    <input type="text" class="search-field" name="" placeholder="Search...">
                    <button class="search-btn">Search</button>
                </form>
                <ul class="navbar-nav">
                    <li @if (Request::path() == 'fundings') class="nav-item active" @endif class="nav-item">
                        <a class="nav-link" href="{{url('/fundings')}}">Home
                            <span class="sr-only">(current)</span>
                        </a>
                    </li>
                    <li  @if (Request::path() == 'fundings/marketplace') class="nav-item active" @endif class="nav-item">
                        <a class="nav-link" href="{{url('fundings/marketplace')}}">Marketplace</a>
                    </li>
                    <li  @if  (Request::path() == 'fundings/contact-us') class="nav-item active" @endif class="nav-item">
                        <a class="nav-link" href="{{url('fundings/contact-us')}}">Contact us</a>
                    </li>

                    @auth
                        <li class="nav-item dropdown user">
                            <a class="nav-link dropdown-toggle" href="#"  data-toggle="dropdown" title="Profile">
                                <span class="name">{{Auth::user()->name}}</span>
                                <span class="avatar">
                                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                         viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                                        <g>
                                            <g>
                                                <path d="M437.02,330.98c-27.883-27.882-61.071-48.523-97.281-61.018C378.521,243.251,404,198.548,404,148
                                                    C404,66.393,337.607,0,256,0S108,66.393,108,148c0,50.548,25.479,95.251,64.262,121.962
                                                    c-36.21,12.495-69.398,33.136-97.281,61.018C26.629,379.333,0,443.62,0,512h40c0-119.103,96.897-216,216-216s216,96.897,216,216
                                                    h40C512,443.62,485.371,379.333,437.02,330.98z M256,256c-59.551,0-108-48.448-108-108S196.449,40,256,40
                                                    c59.551,0,108,48.448,108,108S315.551,256,256,256z"/>
                                            </g>
                                        </g>
                                        <g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g>
                                    </svg>
                                </span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{url('fundings/profile')}}">Profile</a></li>
                                <li><a href="{{url('/fundings/logout')}}">Logout</a></li>
                            </ul>
                        </li>
                        @else
                        <li class="nav-item login">
                            <a class="nav-link" href="{{url('fundings/login')}}">Login</a>
                        </li>

                    @endauth
                </ul>
            </div>
        </div>
    </div>
</nav>



@yield('content')


<footer>
    <div class="footer-top">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="title">ABOUT</div>
                    <ul class="footer-menu">
                        <li><a href="{{url('fundings/about-us')}}">About us</a></li>
                        <li><a href="{{url('fundings/contact-us')}}">Contact us</a></li>
                        {{--<li><a href="#">Partner Network</a></li>--}}
                        <li><a href="{{url('fundings/how-it-works')}}">How it works</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <div class="title">SUPPORT</div>
                    <ul class="footer-menu">
                        <li><a href="{{url('fundings/privacy-policy')}}">Privacy policy</a></li>
                        <li><a href="{{url('fundings/terms-and-condition')}}">Terms and Condition</a></li>
                        {{--<li><a href="{{url('fundings/about-us')}}">Support</a></li>--}}
                    </ul>
                </div>
                <div class="col-md-3">
                    <div class="title">Categories</div>
                    <ul class="footer-menu">
                        @foreach(App\Industries::all()->random(5) as $industry)
                        <li><a href="{{url('fundings/marketplace/'.$industry->id)}}"> {{$industry->name}}</a></li>
                      @endforeach
                        <li><a href="{{url('fundings/marketplace')}}">More...</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <div class="title">Social with us</div>
                    <ul class="social">
                        <li>
                            <a href="https://twitter.com/" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="23.992" height="19.491" viewBox="0 0 23.992 19.491">
                                    <path id="Path_5" data-name="Path 5" d="M23.327,3.36A9.886,9.886,0,0,1,20.2,4.555a4.924,4.924,0,0,0-8.387,4.487A13.973,13.973,0,0,1,1.67,3.9a4.928,4.928,0,0,0,1.523,6.57A4.923,4.923,0,0,1,.964,9.852v.061a4.927,4.927,0,0,0,3.948,4.826,4.922,4.922,0,0,1-2.223.084,4.928,4.928,0,0,0,4.6,3.418,9.877,9.877,0,0,1-6.113,2.107A10.116,10.116,0,0,1,0,20.279a13.936,13.936,0,0,0,7.544,2.211,13.908,13.908,0,0,0,14-14c0-.213,0-.426-.015-.637A9.919,9.919,0,0,0,23.99,5.306a9.828,9.828,0,0,1-2.826.775A4.936,4.936,0,0,0,23.327,3.36Z" transform="translate(0.002 -3)" fill="#b9bcc7"/>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.instagram.com/" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <g id="Group_1" data-name="Group 1" transform="translate(-40 -40)">
                                        <rect id="Rectangle_47" data-name="Rectangle 47" width="24" height="24" transform="translate(40 40)" fill="none"/>
                                        <path id="Path_4" data-name="Path 4" d="M110.9,199a5.9,5.9,0,0,0-5.9-5.9H95a5.9,5.9,0,0,0-5.9,5.9v10a5.9,5.9,0,0,0,5.9,5.9h10a5.9,5.9,0,0,0,5.9-5.9Zm-1.8,0v10a4.1,4.1,0,0,1-4.1,4.1H95a4.1,4.1,0,0,1-4.1-4.1V199a4.1,4.1,0,0,1,4.1-4.1h10A4.1,4.1,0,0,1,109.1,199Zm-4.21,4.238a4.909,4.909,0,0,0-4.128-4.128,4.8,4.8,0,0,0-.719-.053,4.9,4.9,0,1,0,4.9,4.9A4.8,4.8,0,0,0,104.89,203.238Zm-1.78.264a3.155,3.155,0,0,1,.033.455,3.1,3.1,0,1,1-3.1-3.1,3.155,3.155,0,0,1,.455.033A3.108,3.108,0,0,1,103.11,203.5Zm2.39-5.9a.9.9,0,1,1-.9.9A.9.9,0,0,1,105.5,197.6Z" transform="translate(-48 -152)" fill="#b9bcc7" fill-rule="evenodd"/>
                                    </g>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.facebook.com/" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10.176" height="21.9" viewBox="0 0 10.176 21.9">
                                    <path id="Path_6" data-name="Path 6" d="M42.94,27.133V37.714a.29.29,0,0,0,.286.286h3.932a.29.29,0,0,0,.286-.286V26.943H50.28a.3.3,0,0,0,.286-.262l.238-3.217a.281.281,0,0,0-.286-.31h-3.1V20.866a.966.966,0,0,1,.977-.977H50.59a.29.29,0,0,0,.286-.286V16.386a.29.29,0,0,0-.286-.286H46.872a3.936,3.936,0,0,0-3.932,3.932v3.122H40.986a.29.29,0,0,0-.286.286v3.241a.29.29,0,0,0,.286.286H42.94Z" transform="translate(-40.7 -16.1)" fill="#b9bcc7" fill-rule="evenodd"/>
                                </svg>
                            </a>
                        </li>
                        <li>
                            <a href="https://www.youtube.com/" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="26.283" height="18.469" viewBox="0 0 26.283 18.469">
                                    <path id="Path_7" data-name="Path 7" d="M27.268,10.767a10.149,10.149,0,0,0-1.086-4.931,3.486,3.486,0,0,0-2.054-1.194,93.56,93.56,0,0,0-9.993-.37,93.659,93.659,0,0,0-9.957.358,3.427,3.427,0,0,0-1.743.884C1.36,6.5,1.24,8.2,1.121,9.633a57.653,57.653,0,0,0,0,7.737,11.4,11.4,0,0,0,.358,2.388,3.749,3.749,0,0,0,.848,1.624,3.415,3.415,0,0,0,1.779.931,53.942,53.942,0,0,0,7.761.394c4.179.06,7.844,0,12.178-.334a3.439,3.439,0,0,0,1.827-.931,2.973,2.973,0,0,0,.728-1.194,12.632,12.632,0,0,0,.621-4.059C27.268,15.519,27.268,11.484,27.268,10.767ZM11.437,16.9V9.514L18.5,13.227C16.523,14.325,13.908,15.567,11.437,16.9Z" transform="translate(-0.991 -4.258)" fill="#b9bcc7"/>
                                </svg>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-top">
        <div class="container">
            <hr>
            <p class="copyright">Velocity Group USA © 2021</p>
        </div>
    </div>
</footer>

<!-- Bootstrap core JavaScript -->
<script src="{{url('funding/js/jquery.min.js')}}"></script>
<script src="{{url('funding/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{url('funding/js/skeletabs.js')}}"></script>
@stack('scripts')
<script>
    $('#close').click(function () {
        $('.alert-info').hide(150);
    });
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        var phno = formatPhoneNumber($("#cell_phone").val());
        //console.log($("#cell_phone").val());
        if(phno!=null){
            document.getElementById("cell_phone").value = phno;
        }
        return true;
    }
    function formatPhoneNumber(phoneNumberString) {
        var cleaned = ('' + phoneNumberString).replace(/\D/g, '')
        var match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/)
        if (match) {
            return '(' + match[1] + ') ' + match[2] + '-' + match[3]
        }
        return null
    }
@auth
		localStorage.setItem("ach","{{encrypt(Auth::user()->email)}}");
@else
    localStorage.clear();
    sessionStorage.clear();
@endauth
</script>
</body>
</html>