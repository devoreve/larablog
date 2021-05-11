@extends('layout')

@section('title', 'Liste des utilisateurs')

@section('content')

<h1>Liste des utilisateurs</h1>

<form id="search-user" class="form-inline mb-3">
    <label for="search" class="sr-only">Filtrer</label>
    <input type="search" id="search" name="search" class="form-control" placeholder="Nom utilisateur">
</form>

<table id="user-list" class="table table-striped">
    <thead>
        <tr>
            <th>Pseudo</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Date de création</th>
        </tr>
    </thead>
    <tbody>
        @include('partials.users.index', ['users' => $users])
    </tbody>
</table>



@endSection