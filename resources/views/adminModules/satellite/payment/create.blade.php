@extends("layouts.app")
@section('content')
<upload></upload>
@endsection
@push("scripts")
<script>

$(document).ready(function(){
	collapseMenu();
});

</script>
@endpush
