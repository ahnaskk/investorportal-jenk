<!-- Main Header -->
<header class="main-header">


     <!--

        <div style="color: red; position: absolute;height: 20px;opacity: .7; margin-left: 40px;">
             Maintenance Mode. 
        </div> 

      -->


    

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
          <span class="sr-only">Toggle Navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        
<div class="navbar-custom-menu">
        <ul class="nav navbar-nav flex-row">
            @hasrole('admin|lender|editor')
             @if(@Permissions::isAllow('MailBox','View'))
          <!-- Messages: style can be found in dropdown.less-->
          <li class="dropdown messages-menu">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="">
              <svg xmlns="http://www.w3.org/2000/svg" width="21.423" height="14.461" viewBox="0 0 21.423 14.461">
                <path id="Icon_zocial-email" data-name="Icon zocial-email" d="M.052,16.118V4.219q0-.021.062-.393l7,5.991L.135,16.531a1.751,1.751,0,0,1-.083-.413ZM.982,3a.89.89,0,0,1,.351-.062H20.194A1.17,1.17,0,0,1,20.566,3L13.542,9.012l-.93.744-1.839,1.508L8.935,9.755l-.93-.744ZM1,17.337l7.044-6.755,2.727,2.21,2.727-2.21,7.044,6.755a.992.992,0,0,1-.351.062H1.333A.936.936,0,0,1,1,17.337ZM14.43,9.817l6.983-5.991a1.233,1.233,0,0,1,.062.393v11.9a1.583,1.583,0,0,1-.062.413Z" transform="translate(-0.052 -2.938)" fill="#9ba5be"/>
              </svg>
              </i>
            </a>

         
            <ul class="dropdown-menu"><!-- 
              <li class="header">You have 4 messages</li> -->
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu" id="notification_head">
                </ul>
               </li>
              <li class="footer"><a href="{{URL::to('admin/mailbox')}}">See All Messages</a></li>
            </ul>
            @endif
          @endhasrole


          </li>
          
            <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                           <span class="hidden-xs"><i class="" aria-hidden="true">
                           <svg xmlns="http://www.w3.org/2000/svg" width="19.452" height="19.452" viewBox="0 0 19.452 19.452">
                              <path id="Icon_awesome-user-alt" data-name="Icon awesome-user-alt" d="M9.726,10.942A5.471,5.471,0,1,0,4.255,5.471,5.472,5.472,0,0,0,9.726,10.942Zm4.863,1.216H12.5a6.613,6.613,0,0,1-5.539,0H4.863A4.863,4.863,0,0,0,0,17.02v.608a1.824,1.824,0,0,0,1.824,1.824h15.8a1.824,1.824,0,0,0,1.824-1.824V17.02A4.863,4.863,0,0,0,14.589,12.157Z" fill="#9ba5be"/>
                            </svg>
                           </i></span>
                    </a>
                    <ul class="dropdown-menu user-toggle">
                        <!-- Menu Footer-->
                        <li>
                          <div class="ath-name">
                            
                            <p><span class="name-line"><i class="fa fa-user-circle-o" aria-hidden="true"></i></span> {{isset(Auth::user()->name)?Auth::user()->name:''}}</p>
                               
                          </div>
                        </li>
                       
                        <li class="user-footer">
                            
                            <a class="btn btn-default btn-flat" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();" data-method="post">Sign out</a> 
                                    
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                    </form>
                       </li>

                    </ul>
                </li>
         </ul>
      </div>
      <div class="col-md-6 pull-right headerTime">
          <div class="navbar-custom-menu">
              <ul class="nav navbar-nav">
                  <li>{{ \FFM::datetime(\Carbon\Carbon::now('UTC')) }}</li>
              </ul>
          </div>
      </div>
     </nav>
</header>