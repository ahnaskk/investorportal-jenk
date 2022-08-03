<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Logo -->
        <a href="{{route ('admin::dashboard::index')}}" class="logo">
            @php
            $image = Logo::logos();
            @endphp
            @if(Auth::user()->logo)
            <span class="logo-lg"><!-- <b>Investment</b>portal --><img src="{{ URL::asset($image) }}" width="50" height="50"></span>
            @else
            <span class="logo-mini"><b>I</b>p</span>
            <!-- <span class="logo-lg logo-master"><b>Investment</b>Portal</span> -->
            <span class="logo-lg logo-master master-pull-down"><img src="{{ URL::asset('images/velocity_logo_lg.png') }}" width="50" height="50"></span>
            @endif
        </a>
        <!-- /.search form -->
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu tree" data-widget="tree">
            <!--   <li class="header">Actions</li> -->
            <!-- Optionally, you can add icons to the links -->
            <li>
                <a href="{{route('admin::dashboard::index')}}">
                    <i class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 19 19">
                            <path id="Icon_open-dashboard" data-name="Icon open-dashboard" d="M9.5,0A9.5,9.5,0,1,0,19,9.5,9.528,9.528,0,0,0,9.5,0Zm0,2.375A7.125,7.125,0,1,1,2.375,9.5,7.115,7.115,0,0,1,9.5,2.375Zm0,2.375a1.188,1.188,0,1,0,1.188,1.188A1.176,1.176,0,0,0,9.5,4.75ZM5.558,7.125a1.188,1.188,0,0,0-.451,2l2.161,2.161a2.412,2.412,0,0,0-.143.594A2.375,2.375,0,1,0,9.5,9.5a2.412,2.412,0,0,0-.594.143L6.745,7.481A1.187,1.187,0,0,0,5.7,7.1a1.187,1.187,0,0,0-.143,0Zm7.5,0A1.188,1.188,0,1,0,14.25,8.313,1.176,1.176,0,0,0,13.063,7.125Z" fill="#bac1d2"/>
                        </svg>
                    </i>
                    <span>Dashboard</span>
                </a>
            </li>
            <!-- new roles section-on progress -->
            <!-- Investor Section -->
            @php ($modules = ['Investors','Generate PDF'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" id="cy_accounts"><i class="sideBarIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16.4" height="16.4" viewBox="0 0 16.4 16.4">
                        <path id="Icon_awesome-user-alt" data-name="Icon awesome-user-alt" d="M8.2,9.225A4.612,4.612,0,1,0,3.587,4.612,4.614,4.614,0,0,0,8.2,9.225Zm4.1,1.025H10.535a5.576,5.576,0,0,1-4.67,0H4.1A4.1,4.1,0,0,0,0,14.35v.512A1.538,1.538,0,0,0,1.537,16.4H14.862A1.538,1.538,0,0,0,16.4,14.862V14.35A4.1,4.1,0,0,0,12.3,10.25Z" fill="#bac1d2"/>
                    </svg>
                </i><span>Accounts</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Investors','View'))
                    <li><a href="{{route('admin::investors::index')}}" id="cy_all_accounts">All Accounts</a></li>
                    @endif
                    @if(@Permissions::isAllow('Investors','Create'))
                    <li><a href="{{route('admin::investors::create')}}" id="cy_create_account">Create Account</a></li>
                    @endif
                    @if(@Permissions::isAllow('Generate PDF','Create'))
                    <li><a href="{{route('admin::pdf_for_investors')}}">Generate Statement</a></li>
                    @endif
                    @if(@Permissions::isAllow('Generate PDF','View'))
                    <li>
                        <a href="{{route('admin::generated-pdf-csv')}}">Generated PDF/CSV</a>
                    </li>
                     @endif
                      @if(@Permissions::isAllow('FAQ','View'))
                    <li>
                        <a href="{{url('admin/investors/faq')}}">FAQ</a>
                    </li>
                    @endif
                   
                </ul>
            </li>
            @endif
            @php ($modules = ['Transactions'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" id="cy_transactions"><i class="sideBarIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16.4" height="16.4" viewBox="0 0 16.4 16.4">
                        <path id="Icon_awesome-user-alt" data-name="Icon awesome-user-alt" d="M8.2,9.225A4.612,4.612,0,1,0,3.587,4.612,4.614,4.614,0,0,0,8.2,9.225Zm4.1,1.025H10.535a5.576,5.576,0,0,1-4.67,0H4.1A4.1,4.1,0,0,0,0,14.35v.512A1.538,1.538,0,0,0,1.537,16.4H14.862A1.538,1.538,0,0,0,16.4,14.862V14.35A4.1,4.1,0,0,0,12.3,10.25Z" fill="#bac1d2"/>
                    </svg>
                </i><span>Transactions</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::checkAuth('Transaction Report'))
                    <li><a href="{{route('admin::investors::transactionreport')}}" id="cy_transactions" data-cy="cy_transaction">Transactions</a></li>
                    @endif
                    @if(@Permissions::isAllow('Transactions','Create'))
                    <li><a href="{{route('admin::merchants::investor_transactions')}}" id="cy_create_merchants" data-cy="cy_addtransaction">Add Transactions</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!-- end Investor section -->
            <!-- Branch Manager Section -->
            @php ($modules = ['Branch Manager'])
            @if(@Permissions::isModule($modules))
            <!-- <li class="treeview"> -->
            <!-- <a href="#" class="slid-drp open"><i class="fa fa-user"></i><span>Branch Manager</span> <i class="fa fa-angle-left pull-right"></i></a> -->
            <!-- <ul class="treeview-menu"> -->
            <!-- @if(@Permissions::isAllow('Branch Manager','View')) -->
            <!-- <li><a href="{{route('admin::branch_managers::index')}}">All Branch Manager</a></li> -->
            <!-- @endif -->
            <!-- @if(@Permissions::isAllow('Branch Manager','Create')) -->
            <!-- <li><a href="{{route('admin::branch_managers::create')}}">Create Branch Manager</a></li> -->
            <!-- @endif -->
            <!-- </ul> -->
            <!-- </li> -->
            @endif
            <!-- end branch manager section -->
            <!-- collection user -->
            @php ($modules = ['Collection Users'])
            @if(@Permissions::isModule($modules))
            <!-- <li class="treeview">
                <a href="#" class="slid-drp open">
                    <i class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20.357" height="14.25" viewBox="0 0 20.357 14.25">
                            <path id="Icon_awesome-users" data-name="Icon awesome-users" d="M3.054,8.357A2.036,2.036,0,1,0,1.018,6.321,2.038,2.038,0,0,0,3.054,8.357Zm14.25,0a2.036,2.036,0,1,0-2.036-2.036A2.038,2.038,0,0,0,17.3,8.357Zm1.018,1.018H16.286a2.03,2.03,0,0,0-1.435.592,4.653,4.653,0,0,1,2.389,3.48h2.1a1.017,1.017,0,0,0,1.018-1.018V11.411A2.038,2.038,0,0,0,18.321,9.375Zm-8.143,0A3.563,3.563,0,1,0,6.616,5.813,3.561,3.561,0,0,0,10.179,9.375Zm2.443,1.018h-.264a4.919,4.919,0,0,1-4.358,0H7.736a3.665,3.665,0,0,0-3.664,3.664v.916A1.527,1.527,0,0,0,5.6,16.5h9.161a1.527,1.527,0,0,0,1.527-1.527v-.916A3.665,3.665,0,0,0,12.621,10.393ZM5.506,9.967a2.03,2.03,0,0,0-1.435-.592H2.036A2.038,2.038,0,0,0,0,11.411v1.018a1.017,1.017,0,0,0,1.018,1.018h2.1A4.664,4.664,0,0,1,5.506,9.967Z" transform="translate(0 -2.25)" fill="#bac1d2"/>
                        </svg>
                    </i>
                    <span>Collection Users</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Collection Users','View'))
                    <li><a href="{{route('admin::collection_users::index')}}">All Collection Users</a></li>
                    @endif
                    @if(@Permissions::isAllow('Collection Users','Create'))
                    <li><a href="{{route('admin::collection_users::create')}}">Create Collection User</a></li>
                    @endif
                </ul>
            </li> -->
            @endif
            <!-- end collection user -->
            <!-- companies -->
            @php ($modules = ['Companies'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" id="cy_companies">
                    <i class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15.457" height="17.665" viewBox="0 0 15.457 17.665">
                            <path id="Icon_awesome-building" data-name="Icon awesome-building" d="M15.043,16.561h-.69V.828A.828.828,0,0,0,13.525,0H1.932A.828.828,0,0,0,1.1.828V16.561H.414A.414.414,0,0,0,0,16.975v.69H15.457v-.69A.414.414,0,0,0,15.043,16.561ZM4.416,2.622a.414.414,0,0,1,.414-.414H6.21a.414.414,0,0,1,.414.414V4a.414.414,0,0,1-.414.414H4.83A.414.414,0,0,1,4.416,4Zm0,3.312A.414.414,0,0,1,4.83,5.52H6.21a.414.414,0,0,1,.414.414v1.38a.414.414,0,0,1-.414.414H4.83a.414.414,0,0,1-.414-.414ZM6.21,11.041H4.83a.414.414,0,0,1-.414-.414V9.247a.414.414,0,0,1,.414-.414H6.21a.414.414,0,0,1,.414.414v1.38A.414.414,0,0,1,6.21,11.041Zm2.622,5.52H6.624v-2.9a.414.414,0,0,1,.414-.414h1.38a.414.414,0,0,1,.414.414Zm2.208-5.934a.414.414,0,0,1-.414.414H9.247a.414.414,0,0,1-.414-.414V9.247a.414.414,0,0,1,.414-.414h1.38a.414.414,0,0,1,.414.414Zm0-3.312a.414.414,0,0,1-.414.414H9.247a.414.414,0,0,1-.414-.414V5.934a.414.414,0,0,1,.414-.414h1.38a.414.414,0,0,1,.414.414Zm0-3.312a.414.414,0,0,1-.414.414H9.247A.414.414,0,0,1,8.833,4V2.622a.414.414,0,0,1,.414-.414h1.38a.414.414,0,0,1,.414.414Z" fill="#bac1d2"/>
                        </svg>
                    </i>
                    <span>Companies</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Companies','View'))
                    <li><a href="{{route('admin::sub_admins::index')}}" data-cy="all_companies">All Companies</a></li>
                    @endif
                    @if(@Permissions::isAllow('Companies','Create'))
                    <li><a href="{{route('admin::sub_admins::create')}}" id="cy_create_companies">Create Companies</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!--  end companies -->
            <!-- admin -->
            @php ($modules = ['Admins'])
            @if(@Permissions::isModule($modules))
<!--             <li class="treeview">
                <a href="#" class="slid-drp"><i class="sideBarIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20.498" height="16.4" viewBox="0 0 20.498 16.4">
                        <path id="Icon_awesome-user-cog" data-name="Icon awesome-user-cog" d="M19.555,11.957a3.757,3.757,0,0,0,0-1.365l.826-.477a.232.232,0,0,0,.106-.272A4.807,4.807,0,0,0,19.424,8a.234.234,0,0,0-.288-.045l-.826.477a3.761,3.761,0,0,0-1.182-.682V6.8a.233.233,0,0,0-.183-.227,4.833,4.833,0,0,0-2.12,0,.233.233,0,0,0-.183.227v.955a3.761,3.761,0,0,0-1.182.682l-.826-.477A.234.234,0,0,0,12.345,8a4.807,4.807,0,0,0-1.063,1.839.236.236,0,0,0,.106.272l.826.477a3.757,3.757,0,0,0,0,1.365l-.826.477a.232.232,0,0,0-.106.272,4.831,4.831,0,0,0,1.063,1.839.234.234,0,0,0,.288.045l.826-.477a3.761,3.761,0,0,0,1.182.682v.955a.233.233,0,0,0,.183.227,4.833,4.833,0,0,0,2.12,0,.233.233,0,0,0,.183-.227V14.8a3.761,3.761,0,0,0,1.182-.682l.826.477a.234.234,0,0,0,.288-.045,4.807,4.807,0,0,0,1.063-1.839.236.236,0,0,0-.106-.272Zm-3.668.871a1.554,1.554,0,1,1,1.554-1.554A1.555,1.555,0,0,1,15.888,12.829ZM7.175,8.2a4.1,4.1,0,1,0-4.1-4.1A4.1,4.1,0,0,0,7.175,8.2Zm6.445,7.255c-.074-.038-.147-.083-.218-.125l-.253.147a1.257,1.257,0,0,1-1.554-.234,5.841,5.841,0,0,1-1.288-2.229,1.253,1.253,0,0,1,.573-1.464l.253-.147q0-.125,0-.25l-.253-.147a1.251,1.251,0,0,1-.573-1.464c.029-.093.07-.186.1-.279-.122-.01-.24-.038-.365-.038H9.51a5.576,5.576,0,0,1-4.67,0H4.305A4.306,4.306,0,0,0,0,13.53v1.333A1.538,1.538,0,0,0,1.538,16.4H12.813a1.536,1.536,0,0,0,.871-.272,1.247,1.247,0,0,1-.064-.378Z" fill="#bac1d2"/>
                    </svg>
                </i><span>Admin</span> <i class="fa fa-angle-left pull-right">
                </i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Admins','View'))
                    <li><a href="{{ url('/admin/admin') }}">All Admin</a></li>
                    @endif
                    @if(@Permissions::isAllow('Admins','Create'))
                    <li><a href="{{ url('/admin/admin/create') }}">Create Admin</a></li>
                    @endif
                </ul>
            </li> -->
            @endif
            <!-- edn admin -->
            <!-- editor -->
            @php ($modules = ['Editors'])
            @if(@Permissions::isModule($modules))
<!--             <li class="treeview">
                <a href="#" class="slid-drp"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19.065" height="19.031" viewBox="0 0 19.065 19.031">
                        <path id="Path_155" data-name="Path 155" d="M21,19.748l-1.9-5.7a1.007,1.007,0,0,0-.228-.371l-11.4-11.4A.95.95,0,0,0,6.55,2,5.3,5.3,0,0,0,2,6.55a.95.95,0,0,0,.257.874l11.4,11.4a1.007,1.007,0,0,0,.371.228l5.7,1.9a1.131,1.131,0,0,0,.323.048A1,1,0,0,0,21,19.748Zm-4.931-5.036-.048-.086L9.04,7.7,9.6,7.139l6.993,7ZM6.455,4,8.26,5.8,5.8,8.26,4,6.455A3.2,3.2,0,0,1,6.455,4Zm.684,5.6L7.7,9.039l6.927,6.936.086.048-.57.57ZM15.8,17.629l1.834-1.834.95,2.755Z" transform="translate(-1.977 -1.971)" fill="#bac1d2"/>
                    </svg>
                </i><span>Editor</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Editors','View'))
                    <li><a href="{{route('admin::editors::show_editors')}}">All Editors</a></li>
                    @endif
                    @if(@Permissions::isAllow('Editors','Create'))
                    <li><a href="{{route('admin::editors::create_editors')}}">Create Editor</a></li>
                    @endif
                </ul>
            </li> -->
            @endif
            <!-- end editor -->
            <!-- lender -->
            @php ($modules = ['Lenders'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" id="cy_lender"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20.917" height="13.74" viewBox="0 0 20.917 13.74">
                        <g id="Group_59" data-name="Group 59" transform="translate(0 -10.98)">
                            <path id="Path_150" data-name="Path 150" d="M49.97,10.98v12.2l4.585,1.543v-12.2Z" transform="translate(-33.639)" fill="#bac1d2"/>
                            <path id="Path_151" data-name="Path 151" d="M33.31,24.72,37.9,23.177V10.98L33.31,12.523Z" transform="translate(-22.423)" fill="#bac1d2"/>
                            <path id="Path_152" data-name="Path 152" d="M16.66,23.177l4.582,1.543v-12.2L16.66,10.98Z" transform="translate(-11.215)" fill="#bac1d2"/>
                            <path id="Path_153" data-name="Path 153" d="M0,24.72l4.585-1.543V10.98L0,12.523Z" fill="#bac1d2"/>
                        </g>
                    </svg>
                </i><span>Lender</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Lenders','View'))
                    <li><a href="{{route('admin::lenders::show_lenders')}}" id="cy_all_lenders">All Lenders</a></li>
                    @endif
                    @if(@Permissions::isAllow('Lenders','Create'))
                    <li><a href="{{route('admin::lenders::create_lenders')}}" id="cy_create_lenders">Create Lender</a></li>
                    @endif
                    @if(@Permissions::isAllow('Lenders','Edit'))
                    <li><a href="{{route('admin::lender-activation')}}">Lender Settings</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!-- end lender -->
            <!-- viewer -->
            @php ($modules = ['Viewers'])
            @if(@Permissions::isModule($modules))
          <!--   <li class="treeview">
                <a href="#" class="slid-drp">
                    <i class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="19.136" height="13.047" viewBox="0 0 19.136 13.047">
                            <path id="Icon_material-remove-red-eye" data-name="Icon material-remove-red-eye" d="M11.068,6.75A10.287,10.287,0,0,0,1.5,13.274a10.279,10.279,0,0,0,19.136,0A10.287,10.287,0,0,0,11.068,6.75Zm0,10.873a4.349,4.349,0,1,1,4.349-4.349A4.351,4.351,0,0,1,11.068,17.623Zm0-6.959a2.609,2.609,0,1,0,2.609,2.609A2.606,2.606,0,0,0,11.068,10.664Z" transform="translate(-1.5 -6.75)" fill="#bac1d2"/>
                        </svg>
                    </i> -->
                    <!-- <i class="fa fa-user-o"></i> --><!-- <span>Viewer</span> <i class="fa fa-angle-left pull-right"></i> -->
           <!--      </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Viewers','View'))
                    <li><a href="{{route('admin::viewers::show-viewer')}}">All Viewers</a></li>
                    @endif
                    @if(@Permissions::isAllow('Viewers','View'))
                    <li><a href="{{route('admin::viewers::create-viewer')}}">Create Viewer</a></li>
                    @endif
                </ul>
            </li> -->
            @endif
            <!--  end viewer -->
            <!--  roles -->
            @php ($modules = ['Users','Roles','Modules','Firewall'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" data-cy="Roles_and_Permissions">
                    <i class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="18.604" viewBox="0 0 15 18.604">
                            <path id="Path_154" data-name="Path 154" d="M18.1,11.491H10.9a3.9,3.9,0,0,0-3.9,3.9v2.1a.3.3,0,0,0,.3.3H21.7a.3.3,0,0,0,.3-.3v-2.1a3.9,3.9,0,0,0-3.9-3.9Zm-.513-.6-.819-5.739a2.7,2.7,0,1,0-4.536,0l-.819,5.739ZM7.6,18.991a.6.6,0,0,0,.6.6H20.8a.6.6,0,0,0,.6-.6v-.6H7.6Z" transform="translate(-7 -0.987)" fill="#bac1d2"/>
                        </svg>
                    </i>
                    <span>Roles and Permissions</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Users','View'))
                    <li><a href="{{route('admin::roles::show-user-role')}}" data-cy="users_roles">Users and Roles</a></li>
                    @endif
                    @if(@Permissions::isAllow('Roles','View'))
                    <li><a href="{{route('admin::roles::show-role')}}" data-cy="roles_and_permissions">Roles and Permissions</a></li>
                    @endif
                    @if(@Permissions::isAllow('Modules','View'))
                    <li><a href="{{route('admin::roles::show-modules')}}" data-cy="cy_modules">Modules</a></li>
                    @endif
                    @if(@Permissions::isAllow('Firewall','View'))
                    <li><a href="{{route('admin::firewall::index')}}" data-cy="user_firewall">User Firewall</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!-- end roles -->
            <!-- merchants  -->
            @php ($modules = ['Merchants','Merchant Graph','Marketing Offers'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="" class="slid-drp" id="cy_merchants">
                    <i class="sideBarIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22">
                            <g id="Group_60" data-name="Group 60" transform="translate(0 -0.05)">
                                <rect id="Rectangle_338" data-name="Rectangle 338" width="22" height="22" transform="translate(0 0.05)" fill="none"/>
                                <path id="Path_156" data-name="Path 156" d="M54.2,272.406l1.909,1.9a.689.689,0,0,1-.351,1.165l-2.546.51a.689.689,0,0,0-.541.541l-.509,2.547a.69.69,0,0,1-1.164.352l-3-3,.742-.416,1.223-.015a2.069,2.069,0,0,0,1.753-1.012l.625-1.051,1.051-.625A2.064,2.064,0,0,0,54.2,272.406ZM39.109,272.4a2.071,2.071,0,0,0,.816.9l1.051.625.625,1.051a2.069,2.069,0,0,0,1.753,1.012l1.223.015.742.416-3,3a.69.69,0,0,1-1.164-.352l-.509-2.547a.689.689,0,0,0-.541-.541l-2.546-.51a.689.689,0,0,1-.352-1.163Zm5.835-10.539,1.373-.77a.687.687,0,0,1,.674,0l1.373.77,1.574.02a.688.688,0,0,1,.585.337l.8,1.353,1.354.8a.69.69,0,0,1,.337.584l.019,1.574.77,1.372a.688.688,0,0,1,0,.675l-.77,1.373-.019,1.574a.689.689,0,0,1-.337.584l-1.354.8-.8,1.354a.69.69,0,0,1-.585.337l-1.574.019-1.373.77a.69.69,0,0,1-.674,0l-1.373-.77-1.574-.019a.69.69,0,0,1-.585-.337l-.8-1.354-1.354-.8a.689.689,0,0,1-.337-.584l-.019-1.574-.77-1.373a.688.688,0,0,1,0-.675l.77-1.372.019-1.574a.69.69,0,0,1,.337-.584l1.354-.8.8-1.353a.688.688,0,0,1,.585-.337Zm.234,4.326.866-1.641a.689.689,0,0,1,1.219,0l.866,1.641,1.829.317a.689.689,0,0,1,.377,1.16l-1.294,1.331.264,1.837a.689.689,0,0,1-.987.716l-1.665-.819-1.665.819a.689.689,0,0,1-.987-.716l.264-1.837-1.294-1.331a.689.689,0,0,1,.377-1.16Z" transform="translate(-35.621 -258.947)" fill="#bac1d2" fill-rule="evenodd"/>
                            </g>
                        </svg>
                    </i><span>Merchants</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Merchants','View'))
                    <li><a href="{{route('admin::merchants::index')}}" id="cy_all_merchants">All Merchants</a></li>
                    @endif
                    @if(@Permissions::isAllow('Merchants','Create'))
                    <li><a href="{{route('admin::merchants::create')}}" id="cy_create_merchants">Create Merchants</a></li>
                    @endif
                    @if(@Permissions::isAllow('Merchant Graph','View'))
                    <li><a href="{{route('admin::percentageDeal')}}">Graph</a></li>
                    @endif
                    @if(@Permissions::isAllow('Merchants','Edit'))
                    <li><a href="{{ route('admin::change_merchant_status') }}">Change to Default</a></li>
                    @endif
                    @if(@Permissions::isAllow('Merchants','Edit'))
                    <li><a href="{{ route('admin::change_advanced_status') }}">Change to Advanced Status</a></li>
                    @endif
                    @if(@Permissions::isAllow('Generate statement','Create'))
                    <li><a href="{{route('admin::merchants-statements-create')}}" data-cy="cy_generate_statement">Generate Statement</a></li>
                    @endif
                    @if(@Permissions::isAllow('Generate statement','View'))
                    <li>
                        <a href="{{route('admin::merchants-statements')}}">Generated Statement</a>
                    </li>
                    @endif
                     @if(@Permissions::isAllow('FAQ','View'))
                        <li>
                            <a href="{{url('admin/merchants/faq')}}">FAQ</a>
                        </li>

                     @endif
                </ul>
            </li>
            @endif
            <!-- end merchants -->
            @if(@Permissions::isAllow('Marketing Offers','View'))

               <li class="treeview">
                <a href="#" class="slid-drp" data-cy="marketing_offers">
                    <i class="svg-icon">
                         <svg xmlns="http://www.w3.org/2000/svg" width="17" height="22.117" viewBox="0 0 17 22.117">
                            <g id="Group_66" data-name="Group 66" transform="translate(-10.684 -4.279)">
                                <path id="Path_169" data-name="Path 169" d="M30.606,36.077a.682.682,0,1,0,.386.354.684.684,0,0,0-.386-.354ZM28.24,32.8a.684.684,0,1,0-.385-.355A.684.684,0,0,0,28.24,32.8Z" transform="translate(-10.284 -16.351)" fill="#bac1d2"/>
                                <path id="Path_170" data-name="Path 170" d="M27.65,14.7,26.285,8.088a1.2,1.2,0,0,0-1.03-.953,5.969,5.969,0,0,0-.125-1.656c-.213-.874-.624-1.129-.932-1.189a1.026,1.026,0,0,0-.851.256,2.891,2.891,0,0,0-.645.786,8.265,8.265,0,0,0-.914,2.579,8.266,8.266,0,0,0-.114,2.734,2.892,2.892,0,0,0,.3.97,1.026,1.026,0,0,0,.693.555.858.858,0,0,0,.164.016c.469,0,.942-.385,1.361-1.108a1.465,1.465,0,1,1-2.712.4,3.824,3.824,0,0,1-.2-.772A8.507,8.507,0,0,1,21.375,8l-3.117.761A2.813,2.813,0,0,0,16.75,9.848l-5.875,9.291a1.237,1.237,0,0,0,.384,1.7L19.739,26.2a1.236,1.236,0,0,0,1.7-.384l5.875-9.291A2.814,2.814,0,0,0,27.65,14.7Zm-10.835.6a1.467,1.467,0,1,1,.871,1.881,1.467,1.467,0,0,1-.871-1.881Zm4.647,5.567a1.466,1.466,0,1,1-.871-1.881A1.465,1.465,0,0,1,21.463,20.873Zm1.416-4.192-7.057,3.268a.391.391,0,1,1-.329-.71l7.057-3.267a.391.391,0,0,1,.329.71Zm1.65-9.449-1.536.375q-.063.251-.115.516a7.413,7.413,0,0,0-.126,2.213,1.459,1.459,0,0,1,.922.2c-.379.729-.724.955-.865.927s-.331-.329-.425-.93a7.54,7.54,0,0,1,.11-2.486A7.539,7.539,0,0,1,23.32,5.7c.311-.522.6-.731.741-.7.216.042.562.76.468,2.236Z" fill="#bac1d2"/>
                            </g>
                        </svg>
                    </i>
                    <span>Marketing Offers</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Marketing Offers','View'))
                    <li><a href="{{URL::to('admin/addEditMerchantsOffers')}}" data-cy="create_mer_offers">Create Merchant Offers</a></li>
                    @endif
                    @if(@Permissions::isAllow('Marketing Offers','View'))
                    <li><a href="{{route('admin::merchantMarketOfferList')}}" data-cy="mer_offfer_list">Merchant Offers List</a></li>
                    @endif
                    @if(@Permissions::isAllow('Marketing Offers','View'))
                    <li><a href="{{route('admin::addEditInvestorsOffers')}}" data-cy="create_investor_offers">Create Investor Offers</a></li>
                    @endif
                    @if(@Permissions::isAllow('Marketing Offers','View'))
                    <li><a href="{{route('admin::investorMarketOfferList')}}" data-cy="investor_offers_list">Investors Offers List</a></li>
                    @endif
                </ul>
            </li>




<!-- 
            <li>
                <a href="{{URL::to('admin/market_offer')}}">
                    <i class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="22.117" viewBox="0 0 17 22.117">
                            <g id="Group_66" data-name="Group 66" transform="translate(-10.684 -4.279)">
                                <path id="Path_169" data-name="Path 169" d="M30.606,36.077a.682.682,0,1,0,.386.354.684.684,0,0,0-.386-.354ZM28.24,32.8a.684.684,0,1,0-.385-.355A.684.684,0,0,0,28.24,32.8Z" transform="translate(-10.284 -16.351)" fill="#bac1d2"/>
                                <path id="Path_170" data-name="Path 170" d="M27.65,14.7,26.285,8.088a1.2,1.2,0,0,0-1.03-.953,5.969,5.969,0,0,0-.125-1.656c-.213-.874-.624-1.129-.932-1.189a1.026,1.026,0,0,0-.851.256,2.891,2.891,0,0,0-.645.786,8.265,8.265,0,0,0-.914,2.579,8.266,8.266,0,0,0-.114,2.734,2.892,2.892,0,0,0,.3.97,1.026,1.026,0,0,0,.693.555.858.858,0,0,0,.164.016c.469,0,.942-.385,1.361-1.108a1.465,1.465,0,1,1-2.712.4,3.824,3.824,0,0,1-.2-.772A8.507,8.507,0,0,1,21.375,8l-3.117.761A2.813,2.813,0,0,0,16.75,9.848l-5.875,9.291a1.237,1.237,0,0,0,.384,1.7L19.739,26.2a1.236,1.236,0,0,0,1.7-.384l5.875-9.291A2.814,2.814,0,0,0,27.65,14.7Zm-10.835.6a1.467,1.467,0,1,1,.871,1.881,1.467,1.467,0,0,1-.871-1.881Zm4.647,5.567a1.466,1.466,0,1,1-.871-1.881A1.465,1.465,0,0,1,21.463,20.873Zm1.416-4.192-7.057,3.268a.391.391,0,1,1-.329-.71l7.057-3.267a.391.391,0,0,1,.329.71Zm1.65-9.449-1.536.375q-.063.251-.115.516a7.413,7.413,0,0,0-.126,2.213,1.459,1.459,0,0,1,.922.2c-.379.729-.724.955-.865.927s-.331-.329-.425-.93a7.54,7.54,0,0,1,.11-2.486A7.539,7.539,0,0,1,23.32,5.7c.311-.522.6-.731.741-.7.216.042.562.76.468,2.236Z" fill="#bac1d2"/>
                            </g>
                        </svg>
                    </i><span>Marketing Offers</span>
                </a>
            </li> -->
            @endif
            <!-- transactions -->
            @php ($modules = ['Transactions'])
            @if(@Permissions::isModule($modules))
        <!--     <li class="treeview">
                <a href="#" class="slid-drp"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18.875" height="18.875" viewBox="0 0 18.875 18.875">
                        <g id="Group_61" data-name="Group 61" transform="translate(-0.8 -0.8)">
                            <path id="Path_157" data-name="Path 157" d="M12.209,6a6.209,6.209,0,1,0,6.209,6.209A6.209,6.209,0,0,0,12.209,6Zm0,5.588A1.863,1.863,0,0,1,12.83,15.2v.733H11.588V15.2a1.863,1.863,0,0,1-1.242-1.751h1.242a.621.621,0,1,0,.621-.621,1.863,1.863,0,0,1-.621-3.614V8.484H12.83v.733a1.863,1.863,0,0,1,1.242,1.751H12.83a.621.621,0,1,0-.621.621Z" transform="translate(-1.971 -1.971)" fill="#bac1d2"/>
                            <path id="Path_158" data-name="Path 158" d="M13.133,3.3,14.654,2.29,16.175,3.3l.683-1.031L14.654.8l-2.2,1.472Z" transform="translate(-4.417 0)" fill="#bac1d2"/>
                            <path id="Path_159" data-name="Path 159" d="M2.272,12.45.8,14.654l1.472,2.2L3.3,16.175,2.29,14.654,3.3,13.133Z" transform="translate(0 -4.417)" fill="#bac1d2"/>
                            <path id="Path_160" data-name="Path 160" d="M14.654,28.182,13.133,27.17,12.45,28.2l2.2,1.472,2.2-1.472-.683-1.031Z" transform="translate(-4.417 -9.997)" fill="#bac1d2"/>
                            <path id="Path_161" data-name="Path 161" d="M27.17,13.133l1.012,1.521L27.17,16.175l1.031.683,1.472-2.2L28.2,12.45Z" transform="translate(-9.997 -4.417)" fill="#bac1d2"/>
                        </g>
                    </svg>
                </i><span>Transactions</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu"> -->
                    <!--  @if(@Permissions::isAllow('Transactions','View'))
                    <li><a href="{{URL::to('admin/bills')}}">All Transactions</a></li>
                    @endif -->
                    @if(@Permissions::isAllow('Transactions','Create'))
                    <!-- <li><a href="{{URL::to('admin/bills/create')}}">Create Bills</a></li> -->
                    @endif
                    @if(@Permissions::isAllow('Transactions','Download'))
                    <!-- <li><a href="{{URL::to('admin/bills/import_bill')}}">Import Bills</a></li> -->
                    @endif
                <!-- </ul> -->
            <!-- </li> -->
            @endif
            <!-- end transations -->
           
           @if(@Permissions::isAllow('Reconciliation','View'))
            <li>
                <a href="{{URL::to('admin/merchants/reconcilation-request')}}" data-cy="Reconciliation_Request"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11.997" height="15" viewBox="0 0 11.997 15">
                        <g id="Octicons" transform="translate(-0.002)">
                            <g id="git-pull-request">
                                <path id="Shape" d="M11,11.28V5a2.971,2.971,0,0,0-.94-2.06A3.034,3.034,0,0,0,8,2H7V0L4,3,7,6V4H8a1.045,1.045,0,0,1,.69.31A.943.943,0,0,1,9,5v6.28a2,2,0,1,0,2,0ZM10,14.2A1.2,1.2,0,1,1,11.2,13,1.21,1.21,0,0,1,10,14.2ZM4,3A2,2,0,1,0,1,4.72v6.56a2,2,0,1,0,2,0V4.72A1.988,1.988,0,0,0,4,3ZM3.2,13A1.21,1.21,0,0,1,2,14.2a1.2,1.2,0,1,1,0-2.4A1.217,1.217,0,0,1,3.2,13ZM2,4.2A1.2,1.2,0,1,1,2,1.8,1.217,1.217,0,0,1,3.2,3,1.217,1.217,0,0,1,2,4.2Z" fill="#bac1d2" fill-rule="evenodd"/>
                            </g>
                        </g>
                    </svg>
                </i><span>Reconciliation Request</span></a>
            </li>
            @endif
            <!-- velocity -->
            <!-- @php ($modules = ['Velocity Distributions'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17.411" height="13.433" viewBox="0 0 17.411 13.433">
                        <path id="Path_187" data-name="Path 187" d="M-233.688,1613.266l3.612-9.061h3.358l-1.711-4.372h-5.259l-2.851,6.907-2.661-6.907h-3.992s-1.711.19-.507,2.471,4.5,10.962,4.5,10.962Z" transform="translate(244.129 -1599.833)" fill="#bac1d2"/>
                    </svg>
                </i><span>Velocity Distributions</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Velocity Distributions','View'))
                    <li><a href="{{URL::to('admin/vdistribution')}}">All Distributions</a></li>
                    @endif
                </ul>
            </li>
            @endif -->
            <!-- end velociy -->
            <!-- batches -->
            @php ($modules = ['Merchant Batches'])
            @if(@Permissions::isModule($modules))
            <!-- <li class="treeview batches"> -->
            <!-- <a href="#" class="slid-drp"><i class=""> -->
            <!-- <svg xmlns="http://www.w3.org/2000/svg" width="17.282" height="14.761" viewBox="0 0 17.282 14.761"> -->
            <!-- <g id="layer1" transform="translate(0 -1022.696)"> -->
            <!-- <path id="path9967" d="M8.46,2.334a1.619,1.619,0,0,0-.27,3.215V6.838h.54V5.551a1.62,1.62,0,0,0-.27-3.217Zm-6.84,3.6A1.62,1.62,0,1,0,2.976,8.439L6.2,10.333c0-.005,0-.01,0-.015V9.7l-3-1.766A1.613,1.613,0,0,0,1.62,5.934Zm14.041,0a1.616,1.616,0,0,0-1.533,2.141L10.876,9.92V10.3a1.715,1.715,0,0,1-.036.264l3.545-2.01a1.62,1.62,0,1,0,1.276-2.617ZM6.522,7.018a.2.2,0,0,0-.147.191V8.583a.778.778,0,0,0,.361.652v1.046a1.788,1.788,0,0,0,.618,1.328.881.881,0,0,1-.393.653,5.5,5.5,0,0,1-1.046.517,3.171,3.171,0,0,0-1.159.743,2.29,2.29,0,0,0-.54,1.61v.135l.113.044a7.611,7.611,0,0,0,8.415,0l.113-.044v-.135a2.22,2.22,0,0,0-.54-1.576,3.164,3.164,0,0,0-1.148-.709,4.921,4.921,0,0,1-1.035-.517.971.971,0,0,1-.405-.855,1.6,1.6,0,0,0,.608-1.193V8.526a1.114,1.114,0,0,0-1.08-1.148H7.1a.65.65,0,0,1-.27-.044c-.045-.025-.056-.028-.056-.123A.207.207,0,0,0,6.522,7.018ZM8.535,12.27a.527.527,0,0,1,.527.527.548.548,0,0,1-.159.386s-.131.063-.075.273l.427,2.388-.72.36-.706-.364.435-2.384a.242.242,0,0,0-.077-.273.508.508,0,0,1-.179-.386A.527.527,0,0,1,8.535,12.27Zm4.549,1.483a2.791,2.791,0,0,1,.261.78l.772.454a1.629,1.629,0,1,0,.248-.48Zm-11.464.1a1.626,1.626,0,1,0,1.523,1.073l.574-.326a2.973,2.973,0,0,1,.206-.737l-1.044.593A1.62,1.62,0,0,0,1.62,13.854Z" transform="translate(0 1020.362)" fill="#bac1d2"/> -->
            <!-- </g> -->
            <!-- </svg> -->
            <!-- </i><span>Merchant Batches</span> <i class="fa fa-angle-left pull-right"></i></a> -->
            <!-- <ul class="treeview-menu"> -->
            <!-- @if(@Permissions::isAllow('Merchant Batches','View')) -->
            <!-- <li><a href="{{route('admin::merchant_batches::index')}}">All Batches</a></li> -->
            <!-- @endif -->
            <!-- @if(@Permissions::isAllow('Merchant Batches','Create')) -->
            <!-- <li><a href="{{route('admin::merchant_batches::create')}}">Create New Batch</a></li> -->
            <!-- @endif -->
            <!-- </ul> -->
            <!-- </li> -->
            @endif
            <!-- end batches -->
            <!-- payments -->
            @php ($modules = ['Payments'])
            @if(@Permissions::isModule($modules))
            <li class="treeview payments">
                <a href="#" class="slid-drp" data-cy="Payments"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14.875" height="14.874" viewBox="0 0 14.875 14.874">
                        <g id="Group_69" data-name="Group 69" transform="translate(-0.002)">
                            <path id="Path_176" data-name="Path 176" d="M0,30.145v5.3H3.336v-5.3Zm2.062,4.34a.394.394,0,1,1,.281-.114.394.394,0,0,1-.281.114Zm5.69.2H3.831V30.913l.044-.082a2.447,2.447,0,0,1,1.042-.982,3.229,3.229,0,0,1,2.643-.069,7.737,7.737,0,0,1,.791.377c.211.111.41.215.616.3a2.012,2.012,0,0,0,.266.077,1.193,1.193,0,0,1,.913.583,1.752,1.752,0,0,1,.112.808l1.872-.028.972-1.077a1.017,1.017,0,1,1,1.512,1.36l-1.465,1.635Zm-3.177-.744H7.693l5.079-.821,1.289-1.439a.273.273,0,0,0-.405-.364l-1.19,1.317-4.609.068-.011-.744,1.667-.025a1.193,1.193,0,0,0-.048-.52c-.028-.065-.276-.126-.41-.159a2.522,2.522,0,0,1-.377-.114c-.235-.1-.457-.216-.672-.329a7.075,7.075,0,0,0-.715-.342,2.487,2.487,0,0,0-2.032.037,1.786,1.786,0,0,0-.684.6ZM8.768,24.17a2.3,2.3,0,1,0,2.3,2.3,2.3,2.3,0,0,0-2.3-2.3Z" transform="translate(0 -20.575)" fill="#bac1d2"/>
                            <rect id="Rectangle_339" data-name="Rectangle 339" width="0.744" height="2.752" transform="translate(7.627)" fill="#bac1d2"/>
                            <rect id="Rectangle_340" data-name="Rectangle 340" width="0.744" height="1.909" transform="translate(9.164 0.843)" fill="#bac1d2"/>
                        </g>
                    </svg>
                </i><span>Payments</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Payments','View'))
                    <!-- <li><a href="{{route('admin::payments::openitems')}}">Open Items</a></li> -->
                    @endif
                    @if(@Permissions::isAllow('Payments','Create'))
                    <li><a href="{{route('admin::payments::lender-payment-generation')}}" data-cy="Generate_Payment_For_Lenders">Generate Payment For Lenders</a></li>
                    <li><a href="{{route('admin::payments::pending-transactions')}}" data-cy="Pending_Transactions">Pending Transactions</a></li>
                    @endif
                    @if(@Permissions::checkAuth('ACH'))
                    <li><a href="{{route('admin::payments::ach-payment.index')}}" dat-cy="Send_Merchant_ACH">Send Merchant ACH</a></li>
                    <li><a href="{{route('admin::payments::ach-requests.index')}}"data-cy="Merchant_ACH_Status_Check">Merchant ACH Status Check</a></li>
                    <li><a href="{{route('admin::payments::ach-fees.index')}}" data-cy="Merchant_ACH_Fees">Merchant ACH Fees</a></li>
                    @endif
                </ul>
            </li>
            @endif
            @php ($modules = ['Investor Ach'])
            @if(@Permissions::isModule($modules))
            <li class="treeview investor-payments">
                <a href="#" class="slid-drp" data-cy="investor_ach">
                    <i class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14.875" height="14.874" viewBox="0 0 14.875 14.874">
                            <g id="Group_69" data-name="Group 69" transform="translate(-0.002)">
                                <path id="Path_176" data-name="Path 176" d="M0,30.145v5.3H3.336v-5.3Zm2.062,4.34a.394.394,0,1,1,.281-.114.394.394,0,0,1-.281.114Zm5.69.2H3.831V30.913l.044-.082a2.447,2.447,0,0,1,1.042-.982,3.229,3.229,0,0,1,2.643-.069,7.737,7.737,0,0,1,.791.377c.211.111.41.215.616.3a2.012,2.012,0,0,0,.266.077,1.193,1.193,0,0,1,.913.583,1.752,1.752,0,0,1,.112.808l1.872-.028.972-1.077a1.017,1.017,0,1,1,1.512,1.36l-1.465,1.635Zm-3.177-.744H7.693l5.079-.821,1.289-1.439a.273.273,0,0,0-.405-.364l-1.19,1.317-4.609.068-.011-.744,1.667-.025a1.193,1.193,0,0,0-.048-.52c-.028-.065-.276-.126-.41-.159a2.522,2.522,0,0,1-.377-.114c-.235-.1-.457-.216-.672-.329a7.075,7.075,0,0,0-.715-.342,2.487,2.487,0,0,0-2.032.037,1.786,1.786,0,0,0-.684.6ZM8.768,24.17a2.3,2.3,0,1,0,2.3,2.3,2.3,2.3,0,0,0-2.3-2.3Z" transform="translate(0 -20.575)" fill="#bac1d2"/>
                                <rect id="Rectangle_339" data-name="Rectangle 339" width="0.744" height="2.752" transform="translate(7.627)" fill="#bac1d2"/>
                                <rect id="Rectangle_340" data-name="Rectangle 340" width="0.744" height="1.909" transform="translate(9.164 0.843)" fill="#bac1d2"/>
                            </g>
                        </svg>
                    </i>
                    <span>Investor ACH</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @if(@Permissions::checkAuth('Investor Ach'))
                    <li><a href="{{route('admin::payments::investor-ach-requests.index')}}" data-cy="Status_Check">Status Check</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Syndication Payment'))
                    <li><a href="{{route('admin::investors::syndication-payments')}}" data-cy="Syndication_Payments">Syndication Payments</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!-- end payments -->
            <!-- marketplace -->
            @if(@Permissions::isAllow('Marketplace','View'))
            <!-- <li> -->
            <!-- <a href="{{route('admin::marketplace')}}"><i class=""> -->
            <!-- <svg xmlns="http://www.w3.org/2000/svg" width="16.875" height="14.507" viewBox="0 0 16.875 14.507"> -->
            <!-- <path id="Path_175" data-name="Path 175" d="M9.7,2.005a.6.6,0,0,0-.459.211l-.061.044A5.509,5.509,0,0,0,3.857,3.5,4.829,4.829,0,0,0,2.392,7.94l-.019.017-.811.767a1.628,1.628,0,0,0,0,2.393,1.851,1.851,0,0,0,1.52.478,1.663,1.663,0,0,0,.466.769,1.829,1.829,0,0,0,1.133.491A1.646,1.646,0,0,0,5.2,13.927a1.829,1.829,0,0,0,1.133.491,1.647,1.647,0,0,0,.519,1.072,1.866,1.866,0,0,0,2.351.148l.4.377a1.871,1.871,0,0,0,2.542,0,1.654,1.654,0,0,0,.521-1.066,1.829,1.829,0,0,0,1.125-.491,1.647,1.647,0,0,0,.519-1.067,1.827,1.827,0,0,0,1.111-.489,1.663,1.663,0,0,0,.476-.807,1.85,1.85,0,0,0,1.493-.482,1.628,1.628,0,0,0,0-2.393l-.717-.679.137-.507a4.62,4.62,0,0,0-.446-3.488A5.107,5.107,0,0,0,11.9,2.009H10.675q-.083,0-.167,0ZM14.98,8.6l.588.578a.6.6,0,0,0,.122.093l.823.779a.5.5,0,0,1,0,.732.57.57,0,0,1-.774,0l-1.377-1.3a.646.646,0,0,0-.878,0l-.014.013a.565.565,0,0,0,0,.83l1.074,1.015a.5.5,0,0,1,0,.733.572.572,0,0,1-.717.048.648.648,0,0,0-.815.055.564.564,0,0,0-.051.771.5.5,0,0,1-.048.682.572.572,0,0,1-.725.042.647.647,0,0,0-.821.046.564.564,0,0,0-.053.776.505.505,0,0,1-.047.693.579.579,0,0,1-.787,0l-.387-.366.1-.1a1.628,1.628,0,0,0,0-2.393,1.829,1.829,0,0,0-1.133-.491,1.646,1.646,0,0,0-.519-1.072,1.829,1.829,0,0,0-1.133-.491A1.646,1.646,0,0,0,6.89,9.2a1.851,1.851,0,0,0-1.52-.478A1.664,1.664,0,0,0,4.9,7.957a1.841,1.841,0,0,0-1.331-.494A3.7,3.7,0,0,1,4.735,4.331,4.176,4.176,0,0,1,7.853,3.216L6.53,4.17A1.817,1.817,0,0,0,6.174,6.8a1.982,1.982,0,0,0,2.683.363l1.626-1.173h1.739ZM7.236,5.15,9.921,3.213h.587q.06,0,.119,0H11.9A3.9,3.9,0,0,1,15.312,5.14a3.414,3.414,0,0,1,.364,2.448L13,4.957a.6.6,0,0,0-.424-.174h-2.29a.6.6,0,0,0-.353.114L8.15,6.185a.774.774,0,0,1-1.035-.14A.61.61,0,0,1,7.236,5.15Zm1.307,8.008a.57.57,0,0,1,.772,0,.5.5,0,0,1,0,.732l-.811.767a.57.57,0,0,1-.774,0,.5.5,0,0,1,0-.732Zm-.878-.83-.813.769a.57.57,0,0,1-.774,0,.5.5,0,0,1-.012-.72l.013-.012L6.89,11.6l.013-.012a.57.57,0,0,1,.761.012A.5.5,0,0,1,7.666,12.327ZM6.012,10.767l-.811.767-.013.012a.57.57,0,0,1-.761-.012.5.5,0,0,1,0-.732l.811-.767a.57.57,0,0,1,.774,0,.5.5,0,0,1,.012.72ZM4.026,9.52l-.811.767a.57.57,0,0,1-.774,0,.5.5,0,0,1,0-.732l.811-.767a.57.57,0,0,1,.774,0A.5.5,0,0,1,4.026,9.52Z" transform="translate(-1.039 -2.005)" fill="#bac1d2"/> -->
            <!-- </svg> -->
            <!-- </i><span>Marketplace</span></a> -->
            <!-- </li> -->
            @endif
            <!-- end market place -->
            <!-- report section -->
            @php ($modules = ['Reports'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" id="cy_reports"><i class="" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15.875" height="18.852" viewBox="0 0 15.875 18.852">
                        <path id="Path_174" data-name="Path 174" d="M14.914,4.977h2.977a1.984,1.984,0,0,1,1.984,1.984V18.867a1.984,1.984,0,0,1-1.984,1.984H5.984A1.984,1.984,0,0,1,4,18.867V6.961A1.984,1.984,0,0,1,5.984,4.977H8.961a2.977,2.977,0,0,1,5.953,0Zm-1.984,0a.992.992,0,1,1-.992-.992A.992.992,0,0,1,12.93,4.977ZM5.984,8.945a.992.992,0,0,1,.992-.992H16.9a.992.992,0,0,1,0,1.984H6.977A.992.992,0,0,1,5.984,8.945Zm.992,2.977a.992.992,0,1,0,0,1.984H16.9a.992.992,0,0,0,0-1.984Zm0,3.969a.992.992,0,0,0,0,1.984H9.953a.992.992,0,0,0,0-1.984Z" transform="translate(-4 -2)" fill="#bac1d2" fill-rule="evenodd"/>
                    </svg>
                </i><span>Reports</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::checkAuth('Default Rate Report'))
                    <li><a href="{{route('admin::reports::default-rate-report')}}" data-cy="Default_Rate">Default Rate</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Default Rate Merchant Report'))
                    <li><a href="{{route('admin::reports::default-rate-merchant-report')}}" data-cy="Default_Rate_Merchants">Default Rate (Merchants)</a></li>
                    @endif
                    
                    @if(@Permissions::checkAuth('Profitability(65/20/15)'))
                    <li><a href="{{URL::to('admin/reports/profitability2')}}" data-cy="Profitability(65/20/15)">Profitability(65/20/15)</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Profitability(50/30/20)'))
                    <li><a href="{{URL::to('admin/reports/profitability3')}}" data-cy="Profitability(50/30/20)">Profitability(50/30/20)</a></li>
                    <li><a href="{{URL::to('admin/reports/profitability21')}}" data-cy="Profitability(50/30/20)-2021+">Profitability(50/30/20) - 2021+</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Profitability(50/50)'))
                    <li><a href="{{URL::to('admin/reports/profitability4')}}" data-cy="Profitability(50/50)">Profitability(50/50)</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Investment Report'))
                    <li><a href="{{route('admin::reports::investor')}}" id="cy_investmnt" data-cy="cy_Investment">Investment</a></li>
                    @endif
                    
                    @if(@Permissions::checkAuth('Upsell Commission Report'))
                   <li><a href="{{route('admin::reports::upsell-commission')}}" data-cy="Upsell_Commission">Upsell Commission</a></li>
                   @endif

                    @if(@Permissions::checkAuth('Investor Assignment Report'))
                    <li><a href="{{route('admin::reports::get-investor-assign-report')}}" id="cy_inv_assignment" data-cy="Investor_Assignment">Investor Assignment </a></li>
                    @endif
                    @if(@Permissions::checkAuth('Investor Reassignment Report'))
                    <li><a href="{{route('admin::reports::get-reassign-report')}}" data-cy="Investor_Reassignment">Investor Reassignment </a></li>
                    @endif
                    @if(@Permissions::checkAuth('Liquidity Report'))
                    <li @if(config('app.env')!='local') hidden @endif><a href="{{route('admin::reports::liquidity-report')}}" id="cy_liquidity_report" data-cy="cy_Liquidity">Liquidity </a></li>
                    @endif
                    @if(@Permissions::checkAuth('Payment Report'))
                    <li><a href="{{route('admin::reports::payments')}}" id="cy_payments" data-cy="cy_Payments">Payments</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Revenue Recognition Report'))
                    <!-- <li><a href="{{URL::to('admin/merchants/export2')}}">Revenue Recognition</a></li> -->
                    @endif
                    @if(@Permissions::checkAuth('Accrued Pre Return Report'))
                    <!-- <li><a href="{{route('admin::reports::investor_interest_accured_report')}}">Accrued Pref Return</a></li> -->
                    @endif
                    
                    @if(@Permissions::checkAuth('Equity Investor Report'))
                    <li><a href="{{route('admin::reports::equity-investor-report')}}" data-cy="Equity_Investor">Equity Investor</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Total Portfolio Earnings'))
                    <li><a href="{{route('admin::reports::dept-investor-report')}}" data-cy="Total_Portfolio_Earnings">Total Portfolio Earnings</a></li>
                    @endif
                    @if(@Permissions::checkAuth('OverPayment Report'))
                    <li><a href="{{route('admin::reports::overpayment-report')}}" data-cy="OverPayment_Report">OverPayment Report</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Velocity Profitability Report'))
                    <li><a href="{{route('admin::reports::velocity-profitability')}}" data-cy="Velocity_Profitability">Velocity Profitability</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Investor Liquidity Log'))
                    <li @if(config('app.env')!='local') hidden @endif><a href="{{route('admin::reports::InvestorLiquidityLog')}}" data-cy="Investor_Liquidity_Log">Investor Liquidity Log</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Investor RTR Balance'))
                    <li @if(config('app.env')!='local') hidden @endif><a href="{{route('admin::reports::InvestorRTRBalanceLog')}}" data-cy="Investor_RTR_Balance_Log">Investor RTR Balance Log</a></li>
                    @endif
                     @if(@Permissions::checkAuth('Agent Fee Report'))
                    <li><a href="{{route('admin::reports::agent-fee-report')}}" data-cy="Agent_Fee_Report">Agent Fee Report</a></li>
                    @endif
                    @if(@Permissions::checkAuth('Tax Report'))
                    <li><a href="{{route('admin::reports::TaxReport')}}" data-cy="Tax_Report">Tax Report</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!-- end report section -->
            <!-- log -->
            @php ($modules = ['Liquidity Log','Merchant Liquidity Log','Merchant Status Log','Activity Log', 'Permission Log'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" id="cy_logs"><i class="" data-cy="cy_logs">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17">
                        <g id="Group_71" data-name="Group 71" transform="translate(-0.5 -0.5)">
                            <path id="Path_177" data-name="Path 177" d="M3.217,5.435A2.178,2.178,0,0,1,1,3.217,2.178,2.178,0,0,1,3.217,1,2.178,2.178,0,0,1,5.435,3.217,2.178,2.178,0,0,1,3.217,5.435Zm0-2.957a.7.7,0,0,0-.739.739.7.7,0,0,0,.739.739.7.7,0,0,0,.739-.739A.7.7,0,0,0,3.217,2.478Z" transform="translate(-0.13 -0.13)" fill="#bac1d2"/>
                            <path id="Path_178" data-name="Path 178" d="M3.087,5.674A2.619,2.619,0,0,1,.5,3.087,2.619,2.619,0,0,1,3.087.5,2.619,2.619,0,0,1,5.674,3.087,2.619,2.619,0,0,1,3.087,5.674Zm0-4.435A1.83,1.83,0,0,0,1.239,3.087,1.83,1.83,0,0,0,3.087,4.935,1.83,1.83,0,0,0,4.935,3.087,1.83,1.83,0,0,0,3.087,1.239Zm0,2.957A1.137,1.137,0,0,1,1.978,3.087,1.137,1.137,0,0,1,3.087,1.978,1.137,1.137,0,0,1,4.2,3.087,1.137,1.137,0,0,1,3.087,4.2Zm0-1.478a.349.349,0,0,0-.37.37.349.349,0,0,0,.37.37.349.349,0,0,0,.37-.37A.349.349,0,0,0,3.087,2.717Zm0,14.413a2.217,2.217,0,1,1,0-4.435,2.217,2.217,0,1,1,0,4.435Zm0-2.957a.739.739,0,1,0,0,1.478.739.739,0,1,0,0-1.478Z" fill="#bac1d2"/>
                            <path id="Path_179" data-name="Path 179" d="M3.087,19.717a2.587,2.587,0,0,1,0-5.174,2.587,2.587,0,0,1,0,5.174Zm0-4.435a1.848,1.848,0,1,0,0,3.7,1.848,1.848,0,0,0,0-3.7Zm0,2.957a1.109,1.109,0,0,1,0-2.217,1.109,1.109,0,0,1,0,2.217Zm0-1.478a.37.37,0,1,0,0,.739.37.37,0,1,0,0-.739Zm0-3.326A2.217,2.217,0,1,1,3.087,9a2.217,2.217,0,1,1,0,4.435Zm0-2.957a.739.739,0,1,0,0,1.478.739.739,0,0,0,0-1.478Z" transform="translate(0 -2.217)" fill="#bac1d2"/>
                            <path id="Path_180" data-name="Path 180" d="M3.087,13.674a2.587,2.587,0,0,1,0-5.174,2.587,2.587,0,0,1,0,5.174Zm0-4.435a1.848,1.848,0,1,0,0,3.7,1.848,1.848,0,0,0,0-3.7Zm0,2.957a1.109,1.109,0,0,1,0-2.217,1.109,1.109,0,0,1,0,2.217Zm0-1.478a.37.37,0,1,0,0,.739.37.37,0,1,0,0-.739Z" transform="translate(0 -2.087)" fill="#bac1d2"/>
                            <path id="Path_181" data-name="Path 181" d="M18.609,5.478H9.739A.7.7,0,0,1,9,4.739.7.7,0,0,1,9.739,4h8.87a.7.7,0,0,1,.739.739A.7.7,0,0,1,18.609,5.478Z" transform="translate(-2.217 -0.913)" fill="#bac1d2"/>
                            <path id="Path_182" data-name="Path 182" d="M18.478,5.217H9.609A1.137,1.137,0,0,1,8.5,4.109,1.137,1.137,0,0,1,9.609,3h8.87a1.137,1.137,0,0,1,1.109,1.109A1.137,1.137,0,0,1,18.478,5.217ZM9.609,3.739a.349.349,0,0,0-.37.37.349.349,0,0,0,.37.37h8.87a.349.349,0,0,0,.37-.37.349.349,0,0,0-.37-.37Z" transform="translate(-2.087 -0.652)" fill="#bac1d2"/>
                            <path id="Path_183" data-name="Path 183" d="M14.174,13.478H9.739a.739.739,0,0,1,0-1.478h4.435a.739.739,0,1,1,0,1.478Z" transform="translate(-2.217 -3)" fill="#bac1d2"/>
                            <path id="Path_184" data-name="Path 184" d="M14.043,13.217H9.609a1.109,1.109,0,1,1,0-2.217h4.435a1.109,1.109,0,1,1,0,2.217ZM9.609,11.739a.37.37,0,0,0,0,.739h4.435a.37.37,0,0,0,0-.739Z" transform="translate(-2.087 -2.739)" fill="#bac1d2"/>
                            <g id="Group_70" data-name="Group 70" transform="translate(6.413 14.174)">
                                <path id="Path_185" data-name="Path 185" d="M16.391,21.478H9.739a.739.739,0,1,1,0-1.478h6.652a.739.739,0,0,1,0,1.478Z" transform="translate(-8.63 -19.261)" fill="#bac1d2"/>
                                <path id="Path_186" data-name="Path 186" d="M16.261,21.217H9.609a1.109,1.109,0,1,1,0-2.217h6.652a1.109,1.109,0,1,1,0,2.217ZM9.609,19.739a.37.37,0,1,0,0,.739h6.652a.37.37,0,1,0,0-.739Z" transform="translate(-8.5 -19)" fill="#bac1d2"/>
                            </g>
                        </g>
                    </svg>
                </i><span>Logs</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Liquidity Log','View'))
                    <li><a href="{{route('admin::reports::liquidity-log')}}" id="cy_liquity_log" data-cy="Liquidity_Log">Liquidity Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Merchant Liquidity Log','View'))
                    <li><a href="{{route('admin::reports::liquidity-log-merchant')}}" id="cy_mer_liq_log" data-cy="Merchant_Liquidity_Log">Merchant Liquidity Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Merchant Status Log','View'))
                    <li><a href="{{route('admin::merchant_status_log')}}" data-cy="mer_status_logs">Merchant Status Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Activity Log','View'))
                    <li><a href="{{route('admin::activity-log.get.index')}}" data-cy="cy_user_activity_logs">User Activity Log</a></li>
                    <li><a href="{{url('admin/investor-transaction-log')}}" data-cy="cy_investor_trans_logs">Investor Transaction Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Message','View'))
                    <li><a href="{{route('admin::messages::lists')}}" data-cy="cy_message_logs">Messages Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Mail Log','View'))
                    <li><a href="{{URL::to('admin/merchants/mail-log')}}" data-cy="cy_mail_logs">Mail Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Permission Log','View'))
                    <li @if(config('app.env')!='local') hidden @endif><a href="{{URL::to('admin/permission-log')}}">Permission Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Log','View'))
                    <li @if(config('app.env')!='local') hidden @endif><a href="{{URL::to('Log')}}">Laravel Log</a></li>
                    @endif
                    @if(@Permissions::isAllow('Visitor','View'))
                    <li @if(config('app.env')!='local') hidden @endif><a href="{{URL::to('Visitor')}}">Visitor</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!-- end log -->
            <!-- bank details -->
            @php ($modules = ['Bank Details'])
            @if(@Permissions::isModule($modules))
            <!-- <li class="treeview"> -->
            <!-- <a href="#" class="slid-drp"><i class=""> -->
            <!-- <svg id="Group_67" data-name="Group 67" xmlns="http://www.w3.org/2000/svg" width="22.334" height="22.334" viewBox="0 0 22.334 22.334"> -->
            <!-- <path id="Path_171" data-name="Path 171" d="M0,0H22.334V22.334H0Z" fill="none"/> -->
            <!-- <path id="Path_172" data-name="Path 172" d="M5.861,10.375v6.514H8.653V10.375Zm5.584,0v6.514h2.792V10.375ZM4,21.542H21.681V18.751H4ZM17.028,10.375v6.514H19.82V10.375ZM12.841,2,4,6.653V8.514H21.681V6.653Z" transform="translate(-2.139 -1.069)" fill="#bac1d2"/> -->
            <!-- </svg> -->
            <!-- </i><span>Bank Details</span> <i class="fa fa-angle-left pull-right"></i></a> -->
            <!-- <ul class="treeview-menu"> -->
            <!-- @if(@Permissions::isAllow('Bank Details','Create')) -->
            <!-- <li><a href="{{route('admin::create_bank')}}">Create Account</a></li> -->
            <!-- @endif -->
            <!-- @if(@Permissions::isAllow('Bank Details','View')) -->
            <!-- <li><a href="{{route('admin::view-bank')}}">View Accounts</a></li> -->
            <!-- @endif -->
            <!-- </ul> -->
            <!-- </li>  -->
            @endif
            <!-- end bank details -->
            <!-- reconcile -->
            @php ($modules = ['Reconcile'])
            @if(@Permissions::isModule($modules))
            <!-- <li class="treeview"> -->
            <!-- <a href="#" class="slid-drp"> -->
            <!-- <i class="svg-icon"> -->
            <!-- <svg xmlns="http://www.w3.org/2000/svg" width="18.44" height="18.44" viewBox="0 0 18.44 18.44"> -->
            <!-- <path id="Path_173" data-name="Path 173" d="M96.271,29.559a1.046,1.046,0,0,0-.316-.768L93.6,26.44a1.046,1.046,0,0,0-.768-.316,1.076,1.076,0,0,0-.814.362l.215.209q.181.175.243.243a2.69,2.69,0,0,1,.169.215.909.909,0,0,1,.147.288,1.15,1.15,0,0,1,.04.311,1.08,1.08,0,0,1-1.085,1.085,1.15,1.15,0,0,1-.311-.04.909.909,0,0,1-.288-.147,2.691,2.691,0,0,1-.215-.169q-.068-.062-.243-.243l-.209-.215a1.1,1.1,0,0,0-.373.825,1.046,1.046,0,0,0,.316.768l2.328,2.339a1.042,1.042,0,0,0,.768.305,1.088,1.088,0,0,0,.768-.294l1.661-1.65A1.033,1.033,0,0,0,96.271,29.559Zm-7.943-7.966a1.046,1.046,0,0,0-.316-.768l-2.328-2.339a1.046,1.046,0,0,0-.768-.316,1.091,1.091,0,0,0-.768.305l-1.661,1.65a1.033,1.033,0,0,0-.316.757,1.046,1.046,0,0,0,.316.768L84.836,24a1.042,1.042,0,0,0,.768.305,1.092,1.092,0,0,0,.814-.35l-.215-.209q-.181-.175-.243-.243a2.688,2.688,0,0,1-.169-.215A.909.909,0,0,1,85.644,23a1.15,1.15,0,0,1-.04-.311A1.08,1.08,0,0,1,86.689,21.6a1.15,1.15,0,0,1,.311.04.909.909,0,0,1,.288.147,2.689,2.689,0,0,1,.215.169q.068.062.243.243l.209.215A1.1,1.1,0,0,0,88.327,21.593ZM98.44,29.559a3.083,3.083,0,0,1-.96,2.294L95.819,33.5a3.26,3.26,0,0,1-4.6-.023l-2.328-2.339a3.12,3.12,0,0,1-.938-2.294,3.178,3.178,0,0,1,.994-2.362l-.994-.994a3.274,3.274,0,0,1-4.655.045l-2.35-2.35A3.138,3.138,0,0,1,80,20.881a3.083,3.083,0,0,1,.96-2.294l1.661-1.65a3.26,3.26,0,0,1,4.6.023L89.548,19.3a3.12,3.12,0,0,1,.938,2.294,3.178,3.178,0,0,1-.994,2.362l.994.994a3.274,3.274,0,0,1,4.655-.045l2.35,2.35A3.138,3.138,0,0,1,98.44,29.559Z" transform="translate(-80 -16)" fill="#bac1d2"/> -->
            <!-- </svg> -->
            <!-- </i> -->
            <!-- <span>Reconcile</span> <i class="fa fa-angle-left pull-right"></i> -->
            <!-- </a> -->
            <!-- <ul class="treeview-menu"> -->
            <!-- @if(@Permissions::isAllow('Reconcile','Create')) -->
            <!-- <li><a href="{{URL::to('admin/reconcile/create')}}">Create</a></li> -->
            <!-- @endif -->
            <!-- @if(@Permissions::isAllow('Reconcile','View')) -->
            <!-- <li><a href="{{URL::to('admin/reports/reconcile')}}">List</a></li> -->
            <!-- @endif -->
            <!-- </ul> -->
            <!-- </li>  -->
            @endif
            <!-- end reconcile -->
        
            @php ($modules = ['Template Management'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" data-cy="Template_Management">
                    <i class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16.333" height="16.333" viewBox="0 0 16.333 16.333">
                            <path id="layout_6" data-name="layout 6" d="M15.883,2H4.45A2.45,2.45,0,0,0,2,4.45V15.883a2.45,2.45,0,0,0,2.45,2.45H15.883a2.45,2.45,0,0,0,2.45-2.45V4.45A2.45,2.45,0,0,0,15.883,2ZM3.633,4.45a.817.817,0,0,1,.817-.817H15.883a.817.817,0,0,1,.817.817v.817H3.633ZM11.8,10.983H3.633V6.9H11.8ZM4.45,16.7a.817.817,0,0,1-.817-.817V12.617H11.8V16.7Zm12.25-.817a.817.817,0,0,1-.817.817h-2.45V6.9H16.7Z" transform="translate(-2 -2)" fill="#bac1d2"/>
                        </svg>
                    </i>
                    <span>Template Management</span> <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Template Management','View'))
                    <li><a href="{{route('admin::template::index')}}" data-cy="View_Template">View Template</a></li>
                    @endif
                </ul>
            </li>
            @endif
            <!-- end template -->
            <!-- settings -->
            @php ($modules = ['Settings'])
            @if(@Permissions::isModule($modules))
            <li class="treeview">
                <a href="#" class="slid-drp" id="cy_settings"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16.333" height="16.333" viewBox="0 0 16.333 16.333">
                        <g id="Group_65" data-name="Group 65" transform="translate(-10 -10)">
                            <path id="Path_167" data-name="Path 167" d="M25.591,15.568H24.44l.813-.813a.742.742,0,0,0,0-1.051L22.621,11.08a.765.765,0,0,0-1.051,0l-.806.813V10.742A.742.742,0,0,0,20.023,10H16.311a.742.742,0,0,0-.742.742v1.151l-.813-.813a.742.742,0,0,0-1.051,0L11.08,13.712a.742.742,0,0,0,0,1.051l.813.806H10.742a.742.742,0,0,0-.742.742v3.712a.742.742,0,0,0,.742.742h1.151l-.813.813a.742.742,0,0,0,0,1.051l2.632,2.624a.765.765,0,0,0,1.051,0l.806-.813v1.151a.742.742,0,0,0,.742.742h3.712a.742.742,0,0,0,.742-.742V24.44l.813.813a.742.742,0,0,0,1.051,0l2.624-2.632a.742.742,0,0,0,0-1.051l-.813-.806h1.151a.742.742,0,0,0,.742-.742V16.311A.742.742,0,0,0,25.591,15.568Zm-.742,3.712H23.412a.742.742,0,0,0-.7.494,4.789,4.789,0,0,1-.193.468.742.742,0,0,0,.145.846l1.017,1.017-1.574,1.574-1.017-1.017a.742.742,0,0,0-.846-.145,4.79,4.79,0,0,1-.468.193.742.742,0,0,0-.494.7v1.437H17.053V23.412a.742.742,0,0,0-.494-.7,4.789,4.789,0,0,1-.468-.193.742.742,0,0,0-.846.145l-1.017,1.017-1.574-1.574,1.017-1.017a.742.742,0,0,0,.145-.846,4.788,4.788,0,0,1-.193-.468.742.742,0,0,0-.7-.494H11.485V17.053h1.437a.742.742,0,0,0,.7-.494,4.789,4.789,0,0,1,.193-.468.742.742,0,0,0-.145-.846l-1.017-1.017,1.574-1.574,1.017,1.017a.742.742,0,0,0,.846.145,4.789,4.789,0,0,1,.468-.193.742.742,0,0,0,.494-.7V11.485H19.28v1.437a.742.742,0,0,0,.494.7,4.788,4.788,0,0,1,.468.193.742.742,0,0,0,.846-.145l1.017-1.017,1.574,1.574-1.017,1.017a.742.742,0,0,0-.145.846,4.789,4.789,0,0,1,.193.468.742.742,0,0,0,.7.494h1.437Z" fill="#bac1d2"/>
                            <path id="Path_168" data-name="Path 168" d="M26.97,24a2.97,2.97,0,1,0,2.97,2.97A2.97,2.97,0,0,0,26.97,24Zm0,4.455a1.485,1.485,0,1,1,1.485-1.485A1.485,1.485,0,0,1,26.97,28.455Z" transform="translate(-8.803 -8.803)" fill="#bac1d2"/>
                        </g>
                    </svg>
                </i><span>Settings</span> <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('Settings Advanced','View'))
                    <li><a href="{{ url('admin/settings') }}" id="cy_advanced_settings" >Advance Settings</a></li>
                    @endif
                    @if(@Permissions::isAllow('Settings Advanced','View'))
                    <li><a href="{{ url('admin/settings/system_settings') }}" id="cy_advanced_settings" data-cy="system_settings">System Settings</a></li>
                    @endif
                    @if(@Permissions::isAllow('Settings Re-assign','View'))
                    <li><a href="{{ url('admin/re-assign') }}" data-cy="Re-assign">Re-assign</a></li>
                    @endif
                    @if(@Permissions::isAllow('Settings Sub Status','View'))
                    <li><a href="{{ route('admin::sub_status::index') }}" data-cy="all_status">All Status</a></li>
                    @endif
                    @if(@Permissions::isAllow('Settings Duplicate DB','View'))
                    <!--  <li><a href="{{ route('admin::duplicate-db') }}">Generate Duplicate DB</a></li>  -->
                    @endif
                    @if(@Permissions::isAllow('Settings Label','View'))
                    <li><a href="{{ route('admin::label::index') }}" data-cy="label">Label</a></li>
                    @endif
                    
                    @if(@Permissions::isAllow('Settings Sub Status Flag','View'))
                    <li><a href="{{ route('admin::sub_status_flag::index') }}" data-cy="sub_status_flag">Sub Status Flag</a></li>
                    @endif

                     @if(@Permissions::isAllow('Settings Calender For Holidays','View'))
                    <li><a href="{{ route('admin::fullcalender') }}" data-cy="calander_for_holidays">Calender for Holidays</a></li>
                    @endif
                    @if(@Permissions::isAllow('Settings Liquidity Adjuster','View'))
                    <li><a href="{{ route('admin::admins::liquidity_adjuster') }}" data-cy="liq_adjuster">Liquidity Adjuster</a>
                    @endif
                    @if(@Permissions::isAllow('Settings Two Factor Authentication','View')) 
                    <li><a href="{{ route('admin::two-factor-authentication') }}" data-cy="two_factor_auth">Two Factor Authentication</a></li>
                    @endif
                     @if(@Permissions::isAllow('Settings Carry Forwards','View')) 
                        @if (config('settings.app_env') == 'local')
                    <li><a href="{{ route('admin::get-profit-carryforwards') }}" data-cy="carry_forwards">Carry Forwards</a></li>
                            @endif
                        @endif    
                </ul>
            </li>
            @endif
            <?PHP $dont_merge_with_master=1; ?>

            @if($dont_merge_with_master)
            @php ($modules = ['PennyAdjustmentReport'])
            @if(@Permissions::isModule($modules))
            <li class="treeview" @if(config('app.env')!='local') hidden @endif>
                <a href="#" class="slid-drp"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16.333" height="16.333" viewBox="0 0 16.333 16.333">
                        <g id="Group_65" data-name="Group 65" transform="translate(-10 -10)">
                            <path id="Path_167" data-name="Path 167" d="M25.591,15.568H24.44l.813-.813a.742.742,0,0,0,0-1.051L22.621,11.08a.765.765,0,0,0-1.051,0l-.806.813V10.742A.742.742,0,0,0,20.023,10H16.311a.742.742,0,0,0-.742.742v1.151l-.813-.813a.742.742,0,0,0-1.051,0L11.08,13.712a.742.742,0,0,0,0,1.051l.813.806H10.742a.742.742,0,0,0-.742.742v3.712a.742.742,0,0,0,.742.742h1.151l-.813.813a.742.742,0,0,0,0,1.051l2.632,2.624a.765.765,0,0,0,1.051,0l.806-.813v1.151a.742.742,0,0,0,.742.742h3.712a.742.742,0,0,0,.742-.742V24.44l.813.813a.742.742,0,0,0,1.051,0l2.624-2.632a.742.742,0,0,0,0-1.051l-.813-.806h1.151a.742.742,0,0,0,.742-.742V16.311A.742.742,0,0,0,25.591,15.568Zm-.742,3.712H23.412a.742.742,0,0,0-.7.494,4.789,4.789,0,0,1-.193.468.742.742,0,0,0,.145.846l1.017,1.017-1.574,1.574-1.017-1.017a.742.742,0,0,0-.846-.145,4.79,4.79,0,0,1-.468.193.742.742,0,0,0-.494.7v1.437H17.053V23.412a.742.742,0,0,0-.494-.7,4.789,4.789,0,0,1-.468-.193.742.742,0,0,0-.846.145l-1.017,1.017-1.574-1.574,1.017-1.017a.742.742,0,0,0,.145-.846,4.788,4.788,0,0,1-.193-.468.742.742,0,0,0-.7-.494H11.485V17.053h1.437a.742.742,0,0,0,.7-.494,4.789,4.789,0,0,1,.193-.468.742.742,0,0,0-.145-.846l-1.017-1.017,1.574-1.574,1.017,1.017a.742.742,0,0,0,.846.145,4.789,4.789,0,0,1,.468-.193.742.742,0,0,0,.494-.7V11.485H19.28v1.437a.742.742,0,0,0,.494.7,4.788,4.788,0,0,1,.468.193.742.742,0,0,0,.846-.145l1.017-1.017,1.574,1.574-1.017,1.017a.742.742,0,0,0-.145.846,4.789,4.789,0,0,1,.193.468.742.742,0,0,0,.7.494h1.437Z" fill="#bac1d2"/>
                            <path id="Path_168" data-name="Path 168" d="M26.97,24a2.97,2.97,0,1,0,2.97,2.97A2.97,2.97,0,0,0,26.97,24Zm0,4.455a1.485,1.485,0,1,1,1.485-1.485A1.485,1.485,0,0,1,26.97,28.455Z" transform="translate(-8.803 -8.803)" fill="#bac1d2"/>
                        </g>
                    </svg>
                </i><span>Penny Adjustment</span> <i class="fa fa-angle-left pull-right"></i> </a>
                <ul class="treeview-menu">
                    @if(@Permissions::isAllow('PennyAdjustmentReport','View'))
                    <li><a href="{{ route('PennyAdjustment::LiquidityDifference') }}">Liquidity Difference</a></li>
                    <li><a href="{{ route('PennyAdjustment::MerchantValueDifference') }}">Merchant Value Difference</a></li>
                    <li><a href="{{ route('PennyAdjustment::CompanyAmountDifference') }}">Company Amount Difference</a></li>
                    <li><a href="{{ route('PennyAdjustment::ZeroParticipantAmount') }}">Zero Participant Amount</a></li>
                    <li><a href="{{ route('PennyAdjustment::FinalParticipantShare') }}">Final Participant Share Difference</a></li>
                    <li><a href="{{ route('PennyAdjustment::MerchantInvestorShareDifference') }}">Merchant Investor Share Difference</a></li>
                    <li><a href="{{ route('PennyAdjustment::MerchantsFundAmountCheck') }}">Merchants Fund Amount Check</a></li>
                    <li><a href="{{ route('PennyAdjustment::InvestmentAmountCheck') }}">Investment Amount Check</a></li>
                    <li><a href="{{ route('PennyAdjustment::PennyInvestment') }}">Penny Investment</a></li>
                    <li><a href="{{ route('PennyAdjustment::MerchantRTRAndInvestorRtr') }}">Merchant RTR & Investor Rtr</a></li>
                    @endif
                </ul>
            </li>
            @endif
            @endif
            <li>
                <a href="{{ route('logout') }}" onclick="event.preventDefault();
                document.getElementById('logout-form').submit();" data-method="post"><i class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15.744" height="15.75" viewBox="0 0 15.744 15.75">
                        <g id="Group_64" data-name="Group 64" transform="translate(-0.009)">
                            <path id="Path_165" data-name="Path 165" d="M16.188,0H8.969A1.975,1.975,0,0,0,7,1.969V5.906h5.906a1.969,1.969,0,0,1,0,3.938H7v3.938A1.975,1.975,0,0,0,8.969,15.75h7.219a1.975,1.975,0,0,0,1.969-1.969V1.969A1.975,1.975,0,0,0,16.188,0Z" transform="translate(-2.403)" fill="#bac1d2"/>
                            <path id="Path_166" data-name="Path 166" d="M2.241,11.28H10.5a.656.656,0,1,0,0-1.312H2.241l.853-.847a.659.659,0,0,0-.932-.932L.193,10.158a.681.681,0,0,0,0,.932l1.969,1.969a.676.676,0,0,0,.932,0,.662.662,0,0,0,0-.932Z" transform="translate(0 -2.749)" fill="#bac1d2"/>
                        </g>
                    </svg>
                </i><span>Logout</span></a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
