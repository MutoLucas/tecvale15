@extends('layouts.app')

@section('title', 'Categorias')

@section('content')
    <div class="card">
        <h1>Nova categoria</h1>
        <form method="POST" action="/categories" class="row">
            @csrf
            <div class="field"><label>Nome</label><input name="name" required></div>
            <div class="field" style="flex:0;"><button class="btn btn-primary" type="submit">Criar</button></div>
        </form>
    </div>

    <div class="card">
        <h1>Categorias</h1>
        <table>
            <thead><tr><th>Nome</th><th>Slug</th><th>Produtos</th><th></th></tr></thead>
            <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td class="muted">{{ $category->slug }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td>
                        <form method="POST" action="/categories/{{ $category->id }}" onsubmit="return confirm('Remover categoria? Os produtos serão desativados.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Remover</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">Nenhuma categoria.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
