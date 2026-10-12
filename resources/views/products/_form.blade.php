@if ($errors->any())
    <div class="alert alert-error">
        @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
    </div>
@endif

<div class="row">
    <div class="field">
        <label>Nome</label>
        <input name="name" value="{{ old('name', $product->name ?? '') }}" required>
    </div>
    <div class="field">
        <label>Categoria</label>
        <select name="category_id">
            <option value="">Sem categoria</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Tipo</label>
        <select name="type">
            @foreach (['physical' => 'Físico', 'digital' => 'Digital', 'service' => 'Serviço'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $product->type ?? 'physical') == $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="field">
        <label>Preço (R$)</label>
        <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" required>
    </div>
    <div class="field">
        <label>Peso (kg) — só físicos</label>
        <input name="weight" type="number" step="0.001" min="0" value="{{ old('weight', $product->weight ?? '') }}">
    </div>
    <div class="field">
        <label>Estoque</label>
        <input name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}">
    </div>
</div>

<div class="field">
    <label>Descrição</label>
    <textarea name="description" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
</div>

<div class="row">
    <div class="field">
        <label>Imagem</label>
        <input name="image" type="file" accept="image/*">
    </div>
    <div class="field">
        <label><input type="checkbox" name="active" value="1" @checked(old('active', $product->active ?? true))> Ativo</label>
    </div>
</div>

<div class="actions">
    <button class="btn btn-primary" type="submit">Salvar</button>
    <a class="btn" href="/products">Cancelar</a>
</div>
