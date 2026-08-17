<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\Select\SelectService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Selects",
 *     description="Select options for dropdowns and forms"
 * )
 */
class SelectController extends Controller
{
    private $selectSrvice;

    public function __construct(private SelectService $selectService)
    {
    }
    public function getSelects(Request $request)
    {
        $selectData = $this->selectService->getSelects($request->allSelects);

        return response()->json($selectData);
    }


}
