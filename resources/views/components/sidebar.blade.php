<div class="sidebar-brand">
    <a href="{{ url('/') }}" class="brand-link">
        <span class="brand-text fw-bold ms-2">My App</span>
    </a>
</div>
<div class="sidebar-wrapper">
    <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview">
            <li class="nav-item">
                <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-house"></i>
                    <p>Home</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon bi bi-grid"></i>
                    <p>
                        Menu
                        <i class="nav-arrow bi bi-chevron-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Item 1</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon bi bi-circle"></i>
                            <p>Item 2</p>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>