<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                if (!isset($item['product_id']) || !isset($item['qty'])) {
                    continue;
                }

                $product = Product::find($item['product_id']);

                if ($product && $item['qty'] > $product->stock) {
                    $validator->errors()->add(
                        "items.$index.qty",
                        "Stok {$product->name} tidak mencukupi. Tersisa {$product->stock}."
                    );
                }
            }
        });
    }
}
