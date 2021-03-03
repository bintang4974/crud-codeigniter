<body>
    <div id="app">
        <div class="main-wrapper">
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <form class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                        <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
                    </ul>
                </form>
            </nav>
            <div class="main-sidebar">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="index.html">Stisla</a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="index.html">St</a>
                    </div>
                    <ul class="sidebar-menu">
                        <li class="menu-header">Dashboard</li>

                        <li>
                            <a class="nav-link" href="<?= base_url('dashboard') ?>"><i class="fas fa-tachometer-alt"></i> <span>Dahsboard</span></a>
                        </li>
                        <li>
                            <a class="nav-link" href="<?= base_url('siswa') ?>"><i class="fas fa-user-alt"></i> <span>Siswa</span></a>
                        </li>
                        <li>
                            <a class="nav-link text-danger" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
                        </li>
                    </ul>
                </aside>
            </div>