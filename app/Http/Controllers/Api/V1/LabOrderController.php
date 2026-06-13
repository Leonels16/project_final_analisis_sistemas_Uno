<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LabOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class LabOrderController extends Controller
{
    public function __construct(private LabOrderService $service)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        try {
            $order = $this->service->createOrder($tenant, $request->only([
                'patient_id',
                'doctor_id',
                'exam_ids',
                'order_date',
                'status',
                'notes',
            ]));

            return response()->json(['data' => $order], 201);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        $filters = $request->only([
            'id',
            'patient_id',
            'patient_document',
            'from_date',
            'to_date',
        ]);

        $orders = $this->service->listOrders($tenant, array_filter($filters, fn ($value) => $value !== null && $value !== ''));

        return response()->json(['data' => $orders]);
    }
}
