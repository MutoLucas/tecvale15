@extends('layouts.app')

@section('title', 'Editar produto')

@section('content')
    <div class="card">
        <h1>Editar: {{ $product->name }}</h1>
        <form method="POST" action="/products/{{ $product->id }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('products._form')
        </form>
    </div>
@endsection
