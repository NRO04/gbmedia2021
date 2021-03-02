@extends("layouts.app")
@section('content')
<ownerpayroll :owner="{{ $owner }}" :permissions="{{ json_encode($user_permission) }}"></ownerpayroll>
@endsection
@push("scripts")
<script>

$(document).ready(function(){
	collapseMenu();
});

</script>
@endpush
