@extends('layout')

@section('title', 'Inscription')

@section('content')
    <h1>Inscription</h1>
    
    <form action="{{ route('signup') }}" method="post">
        @csrf
        
        @if($errors->any())
            <aside class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </aside>
        @endif
        
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
        </div>
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="password-confirmation">Confirmation du mot de passe</label>
            <input type="password" class="form-control" id="password-confirmation" name="password_confirmation">
        </div>
        <button class="btn btn-primary">Enregistrer</button>
    </form>
@endsection