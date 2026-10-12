@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
    <div class="stats">
        <div class="card stat"><span>Total de produtos</span><b>{{ $totalProducts }}</b></div>
        <div class="card stat"><span>Valor em estoque</span><b>R$ {{ number_format($totalStockValue, 2, ',', '.') }}</b></div>
        <div class="card stat"><span>Estoque baixo (&lt; 5)</span><b>{{ $lowStockCount }}</b></div>
    </div>

    <div class="card">
        <div class="row" style="justify-content:space-between; margin-bottom:16px;">
            <h1 style="margin:0;">Produtos</h1>
            <div class="actions">
                <a class="btn" href="/products/export">Exportar CSV</a>
                <a class="btn btn-primary" href="/products/create">+ Novo produto</a>
            </div>
        </div>

        <form method="GET" action="/products" class="row">
            <div class="field"><label>Buscar</label><input name="search" value="{{ request('search') }}" placeholder="Nome, SKU ou descrição"></div>
            <div class="field">
                <label>Categoria</label>
                <select name="category_id">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Tipo</label>
                <select name="type">
                    <option value="">Todos</option>
                    <option value="physical" @selected(request('type') == 'physical')>Físico</option>
                    <option value="digital" @selected(request('type') == 'digital')>Digital</option>
                    <option value="service" @selected(request('type') == 'service')>Serviço</option>
                </select>
            </div>
            <div class="field">
                <label>Ordenar</label>
                <select name="sort">
                    <option value="">Mais recentes</option>
                    <option value="name" @selected(request('sort') == 'name')>Nome</option>
                    <option value="price_asc" @selected(request('sort') == 'price_asc')>Menor preço</option>
                    <option value="price_desc" @selected(request('sort') == 'price_desc')>Maior preço</option>
                </select>
            </div>
            <div class="field" style="flex:0;">
                <label><input type="checkbox" name="low_stock" value="1" @checked(request('low_stock'))> Estoque baixo</label>
                <button class="btn" type="submit">Filtrar</button>
            </div>
        </form>

        <table>
            <thead>
            <tr><th>SKU</th><th>Nome</th><th>Categoria</th><th>Tipo</th><th>Preço</th><th>Preço final</th><th>Estoque</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                <tr>
                    <td class="muted">{{ $product->sku }}</td>
                    <td>
                        <a href="/products/{{ $product->id }}">{{ $product->name }}</a>
                        @unless ($product->active) <span class="badge badge-off">inativo</span> @endunless
                    </td>
                    <td>{{ $product->category?->name ?? '-' }}</td>
                    <td><span class="badge">{{ $product->type }}</span></td>
                    <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                    <td><b>{{ $product->final_price_formatted }}</b></td>
                    <td>
                        @if ($product->type == 'physical')
                            {{ $product->stock }}
                            @if ($product->stock < 5) <span class="badge badge-warn">baixo</span> @endif
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="actions">
                        <a class="btn btn-sm" href="/products/{{ $product->id }}/edit">Editar</a>
                        <form method="POST" action="/products/{{ $product->id }}" onsubmit="return confirm('Excluir produto?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Nenhum produto encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="pagination">{{ $products->links('pagination.simple') }}</div>
    </div>
@endsection
