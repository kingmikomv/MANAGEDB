<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="https://us.123rf.com/450wm/mopc95/mopc951609/mopc95160900019/65023633-abstract-red-letter-m-logo-design-template-icon-shape-element-you-can-use-logotype-in-energy.jpg"
            alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">MANAGE DB</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="https://cdn-icons-png.freepik.com/512/1794/1794749.png" class="img-circle elevation-2"
                    alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth()->user()->name }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Static Link -->
                <li class="nav-item">
                    <a href="{{ route('vpn.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-th"></i>
                        <p>Network <span class="right badge badge-danger">New</span></p>
                    </a>
                </li>

                <!-- Section Header -->
                <li class="nav-header">TOOLS</li>

                <!-- MikroTik Treeview -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-sitemap"></i>
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
                                ]) }}" class="nav-link">
                                    <i class="fas fa-server nav-icon"></i>
                                    <p>{{ $listmikrotik->site }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>

                <!-- OLT Treeview -->
                <li class="nav-item has-treeview">
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
                                <a href="{{ 'http://akses.aqtnetwork.my.id:' . $listolt->portvpn }}" target="_blank" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ $listolt->site }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                

               <li class="nav-item has-treeview">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-microchip"></i>
        <p>
            Alat
            <i class="fas fa-angle-left right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">

        <li class="nav-item">
            <a href="{{ route('modem.tambahmodem') }}" class="nav-link" target="_blank">
                <i class="far fa-circle nav-icon"></i>
                <p>Tambah Alat</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('modem.index') }}" class="nav-link">
                <i class="far fa-circle nav-icon"></i>
                <p>Data Alat</p>
            </a>
        </li>

    </ul>
</li>

                <!-- Logout -->
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
