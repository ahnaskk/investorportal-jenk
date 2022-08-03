@if(\Request::getRequestUri() != "/user/confirm-password")
<header class="main-header main-margin">
<div class="container-fluid">
<div class="row">
<nav class="navbar navbar-default navbar-fixed-top top-nav" role="navigation">
      <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle Navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="{{route ('admin::dashboard::index')}}" class="logo main-merchant"><img src="{{asset('/images/logo.png')}}" class="img-responsive"></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      @if((Auth::user()))

      <ul class="nav navbar-nav navbar-right menu-edit">




         <li class="active"><a href="{{route('investor::dashboard::index')}}"><span>Dashboard</span></a></li>

          <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                           <span class="hidden-xs">Report</span>
                    </a>
                    <ul class="dropdown-menu user-toggle user-footer">
                     <li><a href="{{route('investor::report::general')}}">General Report</a></li>


                        <!-- Menu Footer-->

                    </ul>
                </li>


                <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                           <span class="hidden-xs">Statements</span>
                    </a>
                    <ul class="dropdown-menu user-toggle user-footer">
                     <li><a href="{{route('investor::statements::weekly')}}">Weekly Statement</a></li>


                        <!-- Menu Footer-->

                    </ul>
                </li>
             <?PHP /*todo other investor document showing ?>
                <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                           <span class="hidden-xs">Documents</span>
                    </a>
                    <ul class="dropdown-menu user-toggle user-footer">
                     <li><a href="{{URL::to('investor/viewDocuments')}}">View Documents</a></li>


                        <!-- Menu Footer-->

                    </ul>
                </li>
                <?PHP */ ?>


<li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
              <i class="fa fa-envelope-o"></i>
              <span class="label label-success" id="notification_count">0</span>
            </a>
            <ul class="dropdown-menu"><!-- 
              <li class="header">You have 4 messages</li> -->
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu" id="notification_head">


                </ul>
              </li>
              <li class="footer"><a href="{{URL::to('investor/mailbox')}}">See All Messages</a></li>
            </ul>
          </li>

             <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                           <span class="hidden-xs"><i class="fa fa-user-circle-o" aria-hidden="true"></i></span>
                    </a>
                    <ul class="dropdown-menu user-toggle">
                        <!-- Menu Footer-->
                        <li>
                          <div class="ath-name">

                            <p><span class="name-line"><i class="fa fa-user-circle-o" aria-hidden="true"></i></span>
                             {{isset(Auth::user()->name)?Auth::user()->name:''}}

                            </p>

                          </div>
                        </li>

                        @if(Auth::user()->investor_type==5)

<!--                        <li><a href="{{route('investor::marketplace::index')}}">Marketplace</a></li>-->

                        @endif


                        <li class="user-footer">

<!--                             <a href="{{URL::to('/logout')}}" class="btn btn-default btn-flat user-sign-out">Sign out</a>-->



                             <a class="btn btn-default btn-flat" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();" data-method="post">Sign out</a> 


                                  <!-- logout section working here -->

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                    </form>






                        </li>



                    </ul>
                </li>



      </ul>
      @endif
    </div><!-- /.navbar-collapse -->
      </div><!-- /.container-collapse -->
  </nav>

</div>
</div>
</header>
@endif
