<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<li class="nav-item"><a class="nav-link" href="/"><i class="la la-map-marked-alt nav-icon"></i> Home / Map</a></li>
<hr>
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>
<li class='nav-item'><a class='nav-link' href='{{ route('location.index', [
    'location_source_id' => '["0","1"]',
    'location_type_id' => '["1","3"]',
    'data_update_method_id' => '["2","3","4"]',
]) }}'><i class='nav-icon la la-exclamation-triangle'></i> Priority Locations</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('location') }}'><i class='nav-icon la la-map-marker'></i> Locations</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('availability') }}'><i class='nav-icon la la-syringe'></i> Availabilities</a></li>
<hr>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('appointmenttype') }}'><i class='nav-icon la la-list'></i> AppointmentTypes</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('dataupdatemethod') }}'><i class='nav-icon la la-list'></i> DataUpdateMethods</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('locationsource') }}'><i class='nav-icon la la-list'></i> LocationSources</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('locationtype') }}'><i class='nav-icon la la-list'></i> LocationTypes</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('county') }}'><i class='nav-icon la la-map-signs'></i> Counties</a></li>
<hr>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('user') }}'><i class='nav-icon la la-user'></i> Users</a></li>
<li class='nav-item'><a class='nav-link' href='{{ route('admin.location.import') }}'><i class='nav-icon la la-map-marker'></i> Location Import</a></li>
<li class='nav-item'><a class='nav-link' href='{{ route('admin.location.export') }}'><i class='nav-icon la la-download'></i> Location Export</a></li>