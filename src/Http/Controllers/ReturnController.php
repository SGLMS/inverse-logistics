<?php

namespace Sglms\InverseLogistics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sglms\InverseLogistics\Models\ILReturn;
use Sglms\InverseLogistics\Services\InverseLogisticsManager;

class ReturnController extends Controller
{
    public function __construct(
        protected InverseLogisticsManager $service
    ) {}

    public function store(Request $request): JsonResponse
    {
        $return = $this->service->createReturn(
            $request->all()
        );

        return response()->json($return);
    }

    public function approve(string $returnId): JsonResponse
    {
        $this->service->approveReturn($returnId);

        return response()->json([
            'status' => 'approved',
        ]);
    }

    public function reject(Request $request, string $returnId): JsonResponse
    {
        $this->service->rejectReturn(
            $returnId,
            $request->string('reason')->toString()
        );

        return response()->json([
            'status' => 'rejected',
        ]);
    }

    public function index()
    {
        $returns = $this->service->listReturns();

        $returns->each(function ($return) {
            if($return->request) {
                $return->products = $return->request->products;
            }
            $return->requestProductQuantities = $this->service->getRequestProductQuantities($return->id);
            $return->returnProductQuantities = $this->service->getReturnProductQuantities($return->id);
        });

        return view('inverse-logistics::welcome', compact('returns'));
    }
}
