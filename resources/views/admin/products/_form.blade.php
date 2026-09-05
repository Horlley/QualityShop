<div class="form-grid">
    <div><label for="sku">SKU</label><input id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" maxlength="30" required></div>
    <div><label for="name">Nome</label><input id="name" name="name" value="{{ old('name', $product->name ?? '') }}" maxlength="120" required></div>
    <div><label for="category">Categoria</label><input id="category" name="category" value="{{ old('category', $product->category ?? '') }}" maxlength="80" required></div>
    <div><label for="price">Preço</label><input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" required></div>
    <div><label for="stock">Estoque</label><input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required></div>
    <div class="checkbox-field"><input type="hidden" name="active" value="0"><input id="active" name="active" type="checkbox" value="1" @checked((bool) old('active', $product->active ?? true))><label for="active">Produto ativo no catálogo</label></div>
    <div class="full-field"><label for="description">Descrição</label><textarea id="description" name="description" rows="4" maxlength="500">{{ old('description', $product->description ?? '') }}</textarea></div>
</div>
<div class="button-row"><button class="button" type="submit">Salvar produto</button><a class="button button-secondary" href="{{ route('admin.products.index') }}">Cancelar</a></div>
