<?php

declare(strict_types=1);

namespace App\Services\Cafe;

use App\Models\CafeProduct;
use App\QueryFilters\CafeProductSearchFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CafeProductService
{
    public function all(
        Request $request
    ): LengthAwarePaginator {
        $perPage = min(
            max((int) $request->integer('perPage', 15), 1),
            100
        );

        return QueryBuilder::for(CafeProduct::class)
            ->withCount([
                'ingredients',
            ])
            ->allowedFilters(
                AllowedFilter::custom(
                    'search',
                    new CafeProductSearchFilter()
                ),

                AllowedFilter::exact(
                    'isActive',
                    'is_active'
                ),
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function createProduct(
        array $data
    ): CafeProduct {
        return DB::transaction(function () use ($data): CafeProduct {
            $product = CafeProduct::create([
                'name' =>
                    $data['name'],

                'description' =>
                    $data['description'] ?? null,

                'selling_price' =>
                    $data['sellingPrice'],

                'is_active' =>
                    $data['isActive'] ?? true,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            if (
                array_key_exists('ingredients', $data)
            ) {
                $this->syncIngredients(
                    $product,
                    $data['ingredients']
                );
            }

            return $this->loadProduct(
                $product
            );
        });
    }

    public function editProduct(
        CafeProduct $product
    ): CafeProduct {
        return $this->loadProduct(
            $product
        );
    }

    public function updateProduct(
        CafeProduct $product,
        array $data
    ): CafeProduct {
        return DB::transaction(function () use (
            $product,
            $data
        ): CafeProduct {
            $product->update([
                'name' =>
                    $data['name']
                    ?? $product->name,

                'description' =>
                    array_key_exists(
                        'description',
                        $data
                    )
                        ? $data['description']
                        : $product->description,

                'selling_price' =>
                    $data['sellingPrice']
                    ?? $product->selling_price,

                'is_active' =>
                    $data['isActive']
                    ?? $product->is_active,

                'notes' =>
                    array_key_exists('notes', $data)
                        ? $data['notes']
                        : $product->notes,
            ]);

            if (
                array_key_exists('ingredients', $data)
            ) {
                $this->syncIngredients(
                    $product,
                    $data['ingredients']
                );
            }

            return $this->loadProduct(
                $product->refresh()
            );
        });
    }

    public function deleteProduct(
        CafeProduct $product
    ): bool {
        return DB::transaction(
            fn (): bool =>
                (bool) $product->delete()
        );
    }

    private function syncIngredients(
        CafeProduct $product,
        array $ingredients
    ): void {
        $product->ingredients()->delete();

        if ($ingredients === []) {
            return;
        }

        $rows = collect($ingredients)
            ->map(
                fn (array $ingredient): array => [
                    'inventory_item_id' =>
                        $ingredient['inventoryItemId'],

                    'quantity' =>
                        $ingredient['quantity'],
                ]
            )
            ->all();

        $product->ingredients()->createMany(
            $rows
        );
    }

    private function loadProduct(
        CafeProduct $product
    ): CafeProduct {
        return $product->load([
            'ingredients.inventoryItem',
        ]);
    }
}
