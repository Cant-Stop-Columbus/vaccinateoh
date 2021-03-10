<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="/"><i class="la la-map-marked-alt nav-icon"></i> Home / Map</a></li>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('user') }}'><i class='nav-icon la la-user'></i> Users</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('location') }}'><i class='nav-icon la la-map-marker'></i> Locations</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('availability') }}'><i class='nav-icon la la-syringe'></i> Availabilities</a></li>