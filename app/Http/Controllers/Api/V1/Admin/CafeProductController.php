<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Cafe\StoreCafeProductRequest;
use App\Http\Requests\V1\Cafe\UpdateCafeProductRequest;
use App\Http\Resources\V1\Cafe\CafeProductCollection;
use App\Http\Resources\V1\Cafe\CafeProductResource;
use App\Models\CafeProduct;
use App\Services\Cafe\CafeProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CafeProductController extends Controller
{
    public function __construct(
        private readonly CafeProductService $cafeProductService
    ) {
    }

    public function index(
        Request $request
    ): JsonResponse {
        $products = $this->cafeProductService
            ->all($request);

        return ApiResponse::success(
            new CafeProductCollection($products)
        );
    }

    public function store(
        StoreCafeProductRequest $request
    ): JsonResponse {
        $product = $this->cafeProductService
            ->createProduct(
                $request->validated()
            );

        return ApiResponse::success(
            new CafeProductResource($product),
            __('messages.created_successfully'),
            HttpStatusCode::CREATED
        );
    }

    public function show(
        CafeProduct $cafeProduct
    ): JsonResponse {
        $product = $this->cafeProductService
            ->editProduct($cafeProduct);

        return ApiResponse::success(
            new CafeProductResource($product)
        );
    }

    public function update(
        UpdateCafeProductRequest $request,
        CafeProduct $cafeProduct
    ): JsonResponse {
        $product = $this->cafeProductService
            ->updateProduct(
                $cafeProduct,
                $request->validated()
            );

        return ApiResponse::success(
            new CafeProductResource($product),
            __('messages.updated_successfully')
        );
    }

    public function destroy(
        CafeProduct $cafeProduct
    ): JsonResponse {
        $this->cafeProductService
            ->deleteProduct($cafeProduct);

        return ApiResponse::success(
            null,
            __('messages.deleted_successfully')
        );
    }
}
