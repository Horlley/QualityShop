<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'sku' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'category' => ['sometimes', 'required', 'string', 'max:80'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'price' => ['sometimes', 'required', 'numeric', 'decimal:0,2', 'min:0', 'max:999999.99'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0', 'max:99999'],
            'active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute não pode ficar vazio.',
            'sku.unique' => 'O SKU informado já está em uso.',
            'price.decimal' => 'O preço deve ter no máximo duas casas decimais.',
            'price.min' => 'O preço não pode ser negativo.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
            'active.boolean' => 'O campo active deve ser verdadeiro ou falso.',
        ];
    }
}
