@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="card">
        <div class="row" style="justify-content:space-between;">
            <h1>{{ $product->name }}</h1>
            <div class="actions">
                <a class="btn" href="/products/{{ $product->id }}/edit">Editar</a>
                <a class="btn" href="/products">Voltar</a>
            </div>
        </div>

        @if ($product->image_path)
            <img src="{{ asset('storage/'.$product->image_path) }}" alt="" style="max-width:240px; border-radius:8px; margin-bottom:12px;">
        @endif

        <p class="muted">{{ $product->description }}</p>

        <table>
            <tr><th>SKU</th><td>{{ $product->sku }}</td></tr>
            <tr><th>Slug</th><td>{{ $product->slug }}</td></tr>
            <tr><th>Categoria</th><td>{{ $product->category?->name ?? '-' }}</td></tr>
            <tr><th>Tipo</th><td><span class="badge">{{ $product->type }}</span></td></tr>
            <tr><th>Preço base</th><td>R$ {{ number_format($product->price, 2, ',', '.') }}</td></tr>
            <tr><th>Impostos</th><td>R$ {{ number_format($tax, 2, ',', '.') }}</td></tr>
            <tr><th>Frete</th><td>R$ {{ number_format($shipping, 2, ',', '.') }}</td></tr>
            <tr><th>Preço final</th><td><b>R$ {{ number_format($finalPrice, 2, ',', '.') }}</b></td></tr>
            @if ($product->type == 'physical')
                <tr><th>Peso</th><td>{{ $product->weight }} kg</td></tr>
                <tr><th>Estoque</th><td>{{ $product->stock }}</td></tr>
            @endif
        </table>
    </div>

    @if ($product->type == 'physical')
        <div class="card">
            <h1>Ajustar estoque</h1>
            <form method="POST" action="/products/{{ $product->id }}/stock" class="row">
                @csrf
                <div class="field">
                    <label>Operação</label>
                    <select name="operation">
                        <option value="add">Entrada</option>
                        <option value="remove">Saída</option>
                    </select>
                </div>
                <div class="field">
                    <label>Quantidade</label>
                    <input name="quantity" type="number" min="1" value="1">
                </div>
                <div class="field" style="flex:0;">
                    <button class="btn btn-primary" type="submit">Aplicar</button>
                </div>
            </form>
        </div>
    @endif
@endsection
