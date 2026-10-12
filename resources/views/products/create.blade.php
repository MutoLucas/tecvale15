@extends('layouts.app')

@section('title', 'Novo produto')

@section('content')
    <div class="card">
        <h1>Novo produto</h1>
        <form method="POST" action="/products" enctype="multipart/form-data">
            @csrf
            @include('products._form')
        </form>
    </div>
@endsection
