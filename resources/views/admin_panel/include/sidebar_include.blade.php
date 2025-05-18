<div class="sidebar bg--dark">
    <button class="res-sidebar-close-btn"><i class="la la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="#" class="sidebar__main-logo">
                <img src="{{ asset('logo_white.png') }}" alt="image">
            </a>
        </div>
        <div class="sidebar__menu-wrapper" id="sidebar__menuWrapper">
            @if(Auth::check() && Auth::user()->usertype == 'admin')
            <ul class="sidebar__menu">
                <li class="sidebar-menu-item ">
                    <a href="{{ route('home') }}" class="nav-link ">
                        <i class="menu-icon la la-home"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a  href="javascript:void(0)">
                        <i class="menu-icon fas fa-truck"></i>
                        <span class="menu-title">Products</span>
                    </a>
                    <div class="sidebar-submenu ">
                        <ul>
                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('category') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Products </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('brand') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Verity</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('unit') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Units</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('In-unit') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Units In</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>


                <li class="sidebar-menu-item sidebar-dropdown">
                    <a  href="javascript:void(0)">
                        <i class="menu-icon fas fa-user-friends"></i>
                        <span class="menu-title">Customer</span>
                    </a>
                    <div class="sidebar-submenu ">
                        <ul>
                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('customer') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Customers</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('Customer-balance') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Customers Balance</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('customer-ledger') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Cutomers Ledger</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('customer-recovery') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Cutomers Recoveries</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('customer-sale') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title"> Customer sale </span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>  

                
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a  href="javascript:void(0)">
                        <i class="menu-icon fas fa-user-check"></i>
                        <span class="menu-title">Vendor</span>
                    </a>
                    <div class="sidebar-submenu ">
                        <ul>
                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('supplier') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Vendor</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('Supplier-balance') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Vendor Balance</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('supplier-ledger') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Vendor Ledger</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('supplier-payment') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Vendor Payments</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>  

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a  href="javascript:void(0)">
                        <i class="menu-icon fas fa-truck"></i>
                        <span class="menu-title">Truck Entry</span>
                    </a>
                    <div class="sidebar-submenu ">
                        <ul>
                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('Truck-Entry') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Add Truck Entry </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('Truck-Entries') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Trucks Enter</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a  href="javascript:void(0)">
                        <i class="menu-icon fas fa-shopping-basket"></i>
                        <span class="menu-title"> Sale</span>
                    </a>
                    <div class="sidebar-submenu ">
                        <ul>
                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('show-trucks') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title"> Available Truck </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('trucks-sold') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title"> Sold Truck </span>
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item">
                    <a href="{{ route('cash-sale') }}" class="nav-link">
                        <i class="menu-icon fas fa-receipt"></i>
                        <span class="menu-title">Cash Sale</span>
                    </a><i class=""></i>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('daily-sale') }}" class="nav-link">
                        <i class="menu-icon fas fa-calendar-alt"></i>
                        <span class="menu-title">Daily Sale</span>
                    </a><i class=""></i>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('daily-sale-truck-wise') }}" class="nav-link">
                        <i class="menu-icon fas fa-calendar-alt"></i>
                        <span class="menu-title">Daily Sale Truck </span>
                    </a><i class=""></i>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('daily-recovery') }}" class="nav-link">
                        <i class="menu-icon fas fa-calendar-alt"></i>
                        <span class="menu-title">Daily Recoveries</span>
                    </a><i class=""></i>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('customer-payments') }}" class="nav-link">
                        <i class="menu-icon fas fa-calendar-alt"></i>
                        <span class="menu-title">Customer Payments</span>
                    </a><i class=""></i>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('Vendor-payments') }}" class="nav-link">
                        <i class="menu-icon fas fa-calendar-alt"></i>
                        <span class="menu-title">Vendors Payments</span>
                    </a><i class=""></i>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a  href="javascript:void(0)">
                        <i class="menu-icon fas fa-shopping-basket"></i>
                        <span class="menu-title"> Reports</span>
                    </a>
                    <div class="sidebar-submenu ">
                        <ul>
                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('customer-ledger-report') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title"> Customer Ledger Report </span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('Vendor-ledger-report') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title"> Vendor Ledger Report</span>
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>

               <!-- <li class="sidebar-menu-item sidebar-dropdown">
                    <a class="" href="javascript:void(0)">
                        <i class="menu-icon las la-users"></i>
                        <span class="menu-title">Manage Staff</span>
                    </a>
                    <div class="sidebar-submenu ">
                        <ul>
                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('staff') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">All Staff</span>
                                </a>
                            </li>

                            <li class="sidebar-menu-item ">
                                <a class="nav-link" href="{{ route('StaffSalary') }}">
                                    <i class="menu-icon las la-dot-circle"></i>
                                    <span class="menu-title">Staff Salary</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>  -->

                <!-- <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="">
                        <i class="menu-icon lab la-product-hunt"></i>
                        <span class="menu-title">Manage Product</span>
                    </a>
                    <div class="sidebar-submenu  ">
                        <ul>
                            <li class="sidebar-menu-item  ">
                                <a href="{{ route('category') }}" class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">Categories</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item  ">
                                <a href="{{ route('subcategory') }}" class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">Sub Categories</span>
                                </a>
                            </li>
                            {{-- <li class="sidebar-menu-item  ">
                                <a href="{{ route('brand') }}" class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">Brands</span>
                                </a>
                            </li> --}}
                            <li class="sidebar-menu-item  ">
                                <a href="{{ route('unit') }}" class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">Units</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item  ">
                                <a href="{{ route('all-product') }}"
                                    class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">Products</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <!-- <li class="sidebar-menu-item ">
                    <a href="{{ route('all-order') }}" class="nav-link ">
                        <i class="menu-icon la la-warehouse"></i>
                        <span class="menu-title">Order</span>
                    </a>
                </li> -->
                <!-- <li class="sidebar-menu-item ">
                    <a href="{{ route('product-alerts') }}" class="nav-link ">
                        <i class="menu-icon las la-bell"></i>
                        <span class="menu-title">Stock Alerts</span>
                        {{-- @php
                        $lowStockProductsCount = DB::table('products')
                        ->whereRaw('CAST(stock AS UNSIGNED) <= CAST(alert_quantity AS UNSIGNED)')
                            ->count();
                            @endphp


                            @if($lowStockProductsCount > 0)
                            <small>&nbsp;<i class="fa fa-circle text--danger" aria-hidden="true" aria-label="Returned" data-bs-original-title="Returned"></i></small>
                            @endif --}}
                    </a>
                </li> -->

                




                 <!-- <li class="sidebar-menu-item">
                    <a href="{{ route('supplier') }}" class="nav-link">
                        <i class="menu-icon la la-user-friends"></i>
                        <span class="menu-title">Supplier</span>
                    </a>
                </li> 
                 <li class="sidebar-menu-item">
                    <a href="{{ route('supplier') }}" class="nav-link">
                        <i class="menu-icon la la-user-friends"></i>
                        <span class="menu-title">Vendor</span>
                    </a>
                </li>  -->
                <!-- <li class="sidebar-menu-item">
                    <a href="{{ route('vendor') }}" class="nav-link">
                        <i class="menu-icon la la-user-friends"></i>
                        <span class="menu-title">Vendor</span>
                    </a>
                </li>

                <li class="sidebar-menu-item">
                    <a href="{{ route('vendor') }}" class="nav-link">
                        <i class="menu-icon la la-user-friends"></i>
                        <span class="menu-title">Give Order to Vendor</span>
                    </a>
                </li>

                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="">
                        <i class="menu-icon la la-shopping-bag"></i>
                        <span class="menu-title">Purchase</span>
                    </a>
                    <div class="sidebar-submenu  ">
                        <ul>
                            <li class="sidebar-menu-item  ">
                                <a href="{{ route('Purchase') }}"
                                    class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">All Purchases</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item  ">
                                <a href="{{ route('all-purchase-return') }}"
                                    class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">Purchases Return</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="sidebar-menu-item  ">
                    <a href="{{ route('all-purchase-return-damage-item') }}"
                        class="nav-link">
                        <i class="menu-icon la la-dot-circle"></i>
                        <span class="menu-title">Claim Returns</span>
                    </a>
                </li>

                <li class="sidebar-menu-item">
                    <a href="{{ route('customer') }}" class="nav-link">
                        <i class="menu-icon la la-users"></i>
                        <span class="menu-title">Customer</span>
                    </a>
                </li>

                
                <li class="sidebar-menu-item sidebar-dropdown">
                    <a href="javascript:void(0)" class="">
                        <i class="menu-icon la la-shopping-cart"></i>
                        <span class="menu-title">Sale</span>
                    </a>
                    <div class="sidebar-submenu">
                        <ul>
                            <li class="sidebar-menu-item">
                                <a href="{{ route('all-sales') }}" class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">All Sales</span>
                                </a>
                            </li>
                            <li class="sidebar-menu-item  ">
                                <a href="#"
                                    class="nav-link">
                                    <i class="menu-icon la la-dot-circle"></i>
                                    <span class="menu-title">Sales Return</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>  -->
            </ul>
            @endif

            <div class="text-center mb-3 text-uppercase">
                <span class="text--warning">Jan</span>
                <span class="text--primary">Muhammad</span>
                <span class="text--warning">CO</span>
            </div>
        </div>
    </div>
</div>