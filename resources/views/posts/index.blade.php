@extends('layout')

@section('title', 'blog')

@section('content')

    <h1>Liste des articles du blog</h1>
    
    <nav>
        {{ $posts->links() }}
    </nav>

    @include('posts.list', ['posts' => $posts])
    
@endsection