<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Exam;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\Tenant;
use App\Repositories\LabOrderRepository;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class LabOrderService
{
    public function __construct(private LabOrderRepository $repository)
    {
    }

    public function createOrder(Tenant $tenant, array $payload): LabOrder
    {
        $this->validatePayload($payload);

        $patient = Patient::query()
            ->where('tenant_id', $tenant->id)
            ->find($payload['patient_id']);

        if ($patient === null) {
            throw new InvalidArgumentException('Paciente no encontrado.');
        }

        $doctor = Doctor::query()
            ->where('tenant_id', $tenant->id)
            ->find($payload['doctor_id']);

        if ($doctor === null) {
            throw new InvalidArgumentException('Médico no encontrado.');
        }

        $examIds = $this->normalizeExamIds($payload['exam_ids']);
        $this->validateExamIds($tenant->id, $examIds);

        $orderData = [
            'tenant_id' => $tenant->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'order_date' => $payload['order_date'],
            'status' => $payload['status'] ?? 'Pendiente',
            'notes' => $payload['notes'] ?? null,
        ];

        return $this->repository->createOrder($orderData, $examIds);
    }

    public function listOrders(Tenant $tenant, array $filters): Collection
    {
        return $this->repository->findOrders($tenant, $filters);
    }

    private function validatePayload(array $payload): void
    {
        if (empty($payload['patient_id'])) {
            throw new InvalidArgumentException('El campo patient_id es obligatorio.');
        }

        if (empty($payload['doctor_id'])) {
            throw new InvalidArgumentException('El campo doctor_id es obligatorio.');
        }

        if (empty($payload['order_date']) || trim((string) $payload['order_date']) === '') {
            throw new InvalidArgumentException('El campo order_date es obligatorio.');
        }

        if (! isset($payload['exam_ids']) || ! is_array($payload['exam_ids']) || count($payload['exam_ids']) === 0) {
            throw new InvalidArgumentException('Debe indicar al menos un examen en exam_ids.');
        }
    }

    private function normalizeExamIds(array $examIds): array
    {
        return array_values(array_filter(array_map('intval', $examIds), fn ($id) => $id > 0));
    }

    private function validateExamIds(int $tenantId, array $examIds): void
    {
        $existing = Exam::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $examIds)
            ->pluck('id')
            ->all();

        if (count($existing) !== count($examIds)) {
            throw new InvalidArgumentException('Uno o más exámenes no existen para el tenant actual.');
        }
    }
}
