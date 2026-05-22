<?php

namespace Sglms\InverseLogistics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sglms\InverseLogistics\Enums\ReturnStatus;
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
            'status' => ReturnStatus::Approved->value,
        ]);
    }

    public function reject(Request $request, string $returnId): JsonResponse
    {
        $this->service->rejectReturn(
            $returnId,
            $request->string('reason')->toString()
        );

        return response()->json([
            'status' => ReturnStatus::Rejected->value,
        ]);
    }

    public function index()
    {
        $returns = $this->service->listReturns();

        $returns = $returns->transform(function ($return) {
            $enrichedReturn = $this->service->getReturnWithProductQuantities($return->id);

            if ($enrichedReturn->request) {
                $enrichedReturn->products = $enrichedReturn->request->products;
            }

            return $enrichedReturn;
        });

        return view('inverse-logistics::index', compact('returns'));
    }
}
