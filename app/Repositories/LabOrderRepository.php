<?php

namespace App\Repositories;

use App\Models\LabOrder;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class LabOrderRepository
{
    public function createOrder(array $data, array $examIds): LabOrder
    {
        return DB::transaction(function () use ($data, $examIds) {
            $order = LabOrder::query()->create($data);
            $order->exams()->sync($examIds);
            return $order->load('patient', 'doctor', 'exams');
        });
    }

    public function findOrders(Tenant $tenant, array $filters): Collection
    {
        $query = LabOrder::query()
            ->where('tenant_id', $tenant->id)
            ->with(['patient', 'doctor', 'exams']);

        return $this->applyFilters($query, $filters)->get();
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(isset($filters['id']), fn (Builder $query) => $query->where('id', $filters['id']))
            ->when(isset($filters['patient_id']), fn (Builder $query) => $query->where('patient_id', $filters['patient_id']))
            ->when(isset($filters['patient_document']), fn (Builder $query) => $query->whereHas('patient', fn (Builder $query) => $query->where('identification_number', $filters['patient_document'])))
            ->when(isset($filters['from_date']), fn (Builder $query) => $query->whereDate('order_date', '>=', $filters['from_date']))
            ->when(isset($filters['to_date']), fn (Builder $query) => $query->whereDate('order_date', '<=', $filters['to_date']));
    }
}
