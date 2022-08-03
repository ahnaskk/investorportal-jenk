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
      <a href="#" class="logo main-merchant"><img src="{{asset('/images/LOGO.png')}}" class="img-responsive"></a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      @if((Auth::user()))
      
      <ul class="nav navbar-nav navbar-right menu-edit">
        

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
                        <li class="user-footer">
                            <div class="ath-name">
                            <span class="name-line"><i class="fa fa-user-circle-o" aria-hidden="true"></i></span>
                            <p>{{isset(Auth::user()->name)?Auth::user()->name:''}}</p>
                               
                            </div>
                             <a href="{{URL::to('/logout')}}" class="btn btn-default btn-flat user-sign-out">Sign out</a>
                        </li>

                         <li><a href="{{route ('admin::dashboard::index')}}">Portfolio</a></

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