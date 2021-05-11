@extends('layout')

@section('title', 'Connexion')

@section('content')
    <h1>Connexion</h1>
    
    <form action="{{ route('signin') }}" method="post">
        @csrf
        
        @if($errors->has('credentials'))
            <aside class="alert alert-danger">
                {{ $errors->first('credentials') }}
            </aside>
        @endif
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember_me" id="remember-me">
            <label class="form-check-label" for="remember-me">
                Se souvenir de moi
            </label>
        </div>
        <button class="btn btn-primary">Enregistrer</button>
    </form>
@endsection