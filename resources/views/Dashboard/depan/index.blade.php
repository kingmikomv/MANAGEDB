<!DOCTYPE html>
<html lang="en">


<x-head />


<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <!-- Preloader -->
        <x-preload />
        <!-- Navbar -->
        <x-nav />
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <x-sidebar :mikrotik="$mikrotik"/>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <x-cheader />
            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Info boxes -->
                        <!-- /.row -->

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">DEPAN</h5>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                   
                                </div>
                              
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>

                </div>
            </section>

        </div>

        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <x-footer />
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <!-- jQuery -->
    <x-script />
</body>

</html>
