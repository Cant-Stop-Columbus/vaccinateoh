@extends(backpack_view('blank'))

@php
    $widgets['before_content'][] = [
        'type'        => 'jumbotron',
        'heading'     => trans('backpack::base.welcome'),
        //'content'     => trans('backpack::base.use_sidebar'),
        //'button_link' => backpack_url('logout'),
        //'button_text' => trans('backpack::base.logout'),
    ];
@endphp

@section('content')
<p>Total Locations: {{ \App\Models\Location::count() }}</p>
<h2>Todo List</h2>
<p><em>There's work to be done if any of these numbers is greater than 0. Consider the gauntlet thrown down.</em></p>
<p>Missing County: <a href="{{ route('location.index', ['county' => '[0]']) }}">{{ \App\Models\Location::whereNull('county')->count() }}</a></p>
<p>Missing Appointment Types: <a href="{{ route('location.index', ['appointment_type_id' => '[0]']) }}">{{ \App\Models\Location::has('appointmentTypes', '<', 1)->count() }}</a></p>
<p>Missing Location Type: <a href="{{ route('location.index', ['location_type_id' => '[0]']) }}">{{ \App\Models\Location::whereNull('location_type_id')->count() }}</a></p>
<p>Missing Data Update Method: <a href="{{ route('location.index', [data_update_method_id' => '[0]']) }}">{{ \App\Models\Location::whereNull('data_update_method_id')->count() }}</a></p>
<p>Missing Collector User (with scraped/API update method): <a href="{{ route('location.index', ['collector_user_id' => 0, 'data_update_method_id' => '[2,3,4]']) }}">{{ \App\Models\Location::whereNull('collector_user_id')->whereIn('data_update_method_id',[2,3,4])->count() }}</a></p>
<p>Availability never updated: <a href="{{ route('location.index', ['never_updated' => true]) }}">{{ \App\Models\Location::withTrashed()->has('availabilities', '<', 1)->count() }}</a></p>
@endsection