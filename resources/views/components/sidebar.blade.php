<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand text-primary">
                <img src="{{ asset('assets/images/logo-dark.svg') }}" class="img-fluid logo-lg" alt="logo">
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a class="pc-link">
                        <span class="pc-micon"><i class="ti ti-menu-2"></i></span>
                        <span class="pc-mtext">Master Data</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{ route('categories.index') }}">Categories</a>
                        </li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('flavours.index') }}">Flavours</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('sizes.index') }}">Sizes</a></li>
                    </ul>
                </li>

                <li class="pc-item">
                    <a href="{{ route('products.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-box"></i></span>
                        <span class="pc-mtext">Products</span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('flash-sales.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-shopping-cart-discount"></i></span>
                        <span class="pc-mtext">Flash Sale</span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('benefits.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-gift"></i></span>
                        <span class="pc-mtext">Benefits</span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('partners.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-users"></i></span>
                        <span class="pc-mtext">Partners</span>
                    </a>
                </li>

                {{-- <li class="pc-item pc-hasmenu">
                    <a class="pc-link">
                        <span class="pc-micon"><i class="ti ti-users"></i></span>
                        <span class="pc-mtext">Partners</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link" href="{{ route('resellers.index') }}">Resellers</a></li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('flavours.index') }}">Affiliates</a></li>
                    </ul>
                </li> --}}

                <li class="pc-item">
                    <a href="{{ route('promotions.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-speakerphone"></i></span>
                        <span class="pc-mtext">Promotions</span>
                    </a>
                </li>

                <li class="pc-item">
                    <a href="{{ route('vouchers.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-receipt-tax"></i></span>
                        <span class="pc-mtext">Vouchers</span>
                    </a>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a class="pc-link">
                        <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                        <span class="pc-mtext">Articles</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link"
                                href="{{ route('category-articles.index') }}">Category</a>
                        </li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('articles.index') }}">Article</a></li>
                    </ul>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a class="pc-link">
                        <span class="pc-micon"><i class="ti ti-brand-youtube"></i></span>
                        <span class="pc-mtext">Tutorials</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link"
                                href="{{ route('category-tutorials.index') }}">Category</a>
                        </li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('tutorials.index') }}">Tutorial</a>
                        </li>
                    </ul>
                </li>

                <li class="pc-item pc-hasmenu">
                    <a class="pc-link">
                        <span class="pc-micon"><i class="ti ti-question-mark"></i></span>
                        <span class="pc-mtext">FAQs</span>
                        <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                    </a>
                    <ul class="pc-submenu">
                        <li class="pc-item"><a class="pc-link"
                                href="{{ route('category-faqs.index') }}">Category</a>
                        </li>
                        <li class="pc-item"><a class="pc-link" href="{{ route('faqs.index') }}">FAQ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
