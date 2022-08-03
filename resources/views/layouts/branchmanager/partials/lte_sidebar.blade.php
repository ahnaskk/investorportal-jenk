<!-- Left side column. contains the sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu tree" data-widget="tree">
            <li class="header">Actions</li>
            <!-- Optionally, you can add icons to the links -->
            <li class=""><a href="{{route('branch::dashboard')}}"><span>Dashboard</span></a></li>

            <li class="treeview">
                <a href="#"><span>Marketplace</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="{{route('branch::marketplace::index')}}">All Marketplace</a></li>
                    <li><a href="{{route('branch::marketplace::create')}}">Create Marketplace</a></li>
                </ul>
            </li>
            
            <li ><a href="{{URL::to('/logout')}}"><span>Logout</span></a></li>

        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>