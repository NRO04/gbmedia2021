@extends("layouts.app")
@section('content')
<paymentlist :permissions="{{ json_encode($user_permission) }}"></paymentlist>
@endsection
@push("scripts")
<script>

$(document).ready(function(){
	collapseMenu();
});

</script>
@endpush
