<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="https://png.pngtree.com/png-vector/20211106/ourmid/pngtree-letter-m-logo-png-image_4008793.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">MANAGE DB</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="https://cdn-icons-png.freepik.com/512/1794/1794749.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth()->user()->name }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->


        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('vpn.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-th"></i>
                        <p>
                            Network
                            <span class="right badge badge-danger">New</span>
                        </p>
                    </a>
                </li>



                <li class="nav-header">TOOLS</li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-book"></i>
                        <p>
                            MikroTik
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @foreach ($mikrotik as $listmikrotik)
                            <li class="nav-item">
                                <a href="{{ route('masukmikrotik', [
                                    'ipmikrotik' => $listmikrotik->ipmikrotik,
                                    'portweb' => $listmikrotik->portweb,
                                ]) }}"
                                    class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ $listmikrotik->site }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
              
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-clone"></i>
                        <p>
                            OLT
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @foreach ($olt as $listolt)
                            <li class="nav-item">
                                <a href="{{"http://akses.aqtnetwork.my.id:". $listolt->portvpn}}" target="_blank"
                                    class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ $listolt->site }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

               <li class="nav-item">
    <a href="#" class="nav-link" onclick="confirmLogout(event)">
        <i class="nav-icon fas fa-door-open"></i>
        <p>Logout</p>
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
