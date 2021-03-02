@extends('layouts.app')
@section('pageTitle', 'Chat')

@section('content')
<chats :user="{{ Auth::user() }}"></chats>
@endsection