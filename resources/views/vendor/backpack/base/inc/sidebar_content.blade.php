{{-- This file is used to store sidebar items, inside the Backpack admin panel --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>


{{-- Articles --}}
<li class="nav-item nav-dropdown open"><a class="nav-link nav-dropdown-toggle" href="#">Articles</a>
    <ul class="nav-dropdown-items">
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('article-post') }}"><i class="nav-icon la la-newspaper"></i> Posts</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('article-category') }}"><i class="nav-icon la la-tags"></i> Categories</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('article-comment') }}"><i class="nav-icon la la-comment-dots"></i> Comments</a></li>
    </ul>
</li>

{{-- Shop --}}
<li class="nav-item nav-dropdown open"><a class="nav-link nav-dropdown-toggle" href="#">Shop</a>
    <ul class="nav-dropdown-items">
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('products') }}"><i class="nav-icon la la-shopping-bag"></i> Products</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('product-category') }}"><i class="nav-icon la la-tags"></i> Categories</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('voucher') }}"><i class="nav-icon la la-ticket-alt"></i> Vouchers</a></li>
    </ul>
</li>


{{-- Authentication --}}
@if(backpack_user()->can('manage_users'))
<li class="nav-item nav-dropdown open"><a class="nav-link nav-dropdown-toggle" href="#">Authentication</a>
    <ul class="nav-dropdown-items">
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> Users</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-id-badge"></i> <span>Roles</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('doctor-verification') }}"><i class="nav-icon la la-user-nurse"></i> <span>Doctor Verifications</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ backpack_url('apotek-verification') }}"><i class="nav-icon la la-store-alt"></i> <span>Apotek Verifications</span></a></li>
    </ul>
</li>
@endif

{{-- Advanced --}}
<li class="nav-item nav-dropdown open"><a class="nav-link nav-dropdown-toggle" href="#">Advanced</a>
    <ul class="nav-dropdown-items">
        <li class='nav-item'><a class='nav-link' href='{{ backpack_url('log') }}'><i class='nav-icon la la-terminal'></i> Logs</a></li>   
    </ul>
</li>