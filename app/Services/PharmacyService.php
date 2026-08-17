<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineStock;
use App\Models\Prescription;
use App\Models\PrescriptionDispense;
use App\Models\PrescriptionDispenseItem;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyService
{
    public function categories(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 50), 5), 50);

        return MedicineCategory::query()
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function createCategory(array $data): MedicineCategory
    {
        return DB::transaction(function () use ($data) {
            $category = MedicineCategory::create([
                ...$data,
                'category_code' => null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $category->update([
                'category_code' => sprintf('CAT-%04d', $category->id),
            ]);

            return $category->fresh();
        });
    }

    public function updateCategory(MedicineCategory $category, array $data): MedicineCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function archiveCategory(MedicineCategory $category): void
    {
        $category->update(['is_active' => false]);
    }

    public function medicines(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));

        return Medicine::query()
            ->with('category:id,category_code,name')
            ->withSum('stocks as stock_total', 'quantity_available')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('medicine_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%");
                });
            })
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when(! empty($filters['category_id']), fn ($query) => $query->where('medicine_category_id', $filters['category_id']))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function createMedicine(array $data): Medicine
    {
        $this->validateActiveCategory($data['medicine_category_id'] ?? null);

        return DB::transaction(function () use ($data) {
            $medicine = Medicine::create([
                ...$data,
                'medicine_code' => null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $medicine->update([
                'medicine_code' => sprintf('MED-%s-%05d', now()->format('Y'), $medicine->id),
            ]);

            return $medicine->fresh(['category:id,category_code,name'])->loadSum('stocks as stock_total', 'quantity_available');
        });
    }

    public function updateMedicine(Medicine $medicine, array $data): Medicine
    {
        $this->validateActiveCategory($data['medicine_category_id'] ?? null);
        $medicine->update($data);

        return $medicine->fresh(['category:id,category_code,name'])->loadSum('stocks as stock_total', 'quantity_available');
    }

    public function archiveMedicine(Medicine $medicine): void
    {
        $medicine->update(['is_active' => false]);
    }

    public function stocks(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));

        return MedicineStock::query()
            ->with('medicine:id,medicine_code,name,dosage_form,strength')
            ->where('quantity_available', '>', 0)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('batch_number', 'like', "%{$search}%")
                        ->orWhereHas('medicine', fn ($query) => $query
                            ->where('medicine_code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($filters['medicine_id']), fn ($query) => $query->where('medicine_id', $filters['medicine_id']))
            ->when(($filters['expiry_status'] ?? null) === 'expired', fn ($query) => $query->whereDate('expiry_date', '<', today()))
            ->when(($filters['expiry_status'] ?? null) === 'expiring', fn ($query) => $query->whereBetween('expiry_date', [today(), today()->addDays(30)]))
            ->when(($filters['expiry_status'] ?? null) === 'valid', fn ($query) => $query->whereDate('expiry_date', '>', today()->addDays(30)))
            ->orderBy('expiry_date')
            ->paginate($perPage);
    }

    public function receiveStock(array $data, User $user): MedicineStock
    {
        $medicine = Medicine::query()
            ->whereKey($data['medicine_id'])
            ->where('is_active', true)
            ->first();

        if (! $medicine) {
            throw ValidationException::withMessages([
                'medicine_id' => ['The selected medicine is not active.'],
            ]);
        }

        $stock = MedicineStock::create([
            ...Arr::except($data, ['medicine_id']),
            'medicine_id' => $medicine->id,
            'quantity_available' => $data['quantity_received'],
            'received_by' => $user->id,
        ]);

        return $stock->fresh('medicine:id,medicine_code,name,dosage_form,strength');
    }

    public function alerts(): array
    {
        $expired = MedicineStock::query()
            ->with('medicine:id,medicine_code,name')
            ->where('quantity_available', '>', 0)
            ->whereDate('expiry_date', '<', today())
            ->orderBy('expiry_date')
            ->limit(20)
            ->get();

        $expiring = MedicineStock::query()
            ->with('medicine:id,medicine_code,name')
            ->where('quantity_available', '>', 0)
            ->whereBetween('expiry_date', [today(), today()->addDays(30)])
            ->orderBy('expiry_date')
            ->limit(20)
            ->get();

        $medicines = Medicine::query()
            ->withSum('stocks as stock_total', 'quantity_available')
            ->where('is_active', true)
            ->get()
            ->filter(fn (Medicine $medicine) => (float) ($medicine->stock_total ?? 0) <= (float) $medicine->reorder_level)
            ->values();

        return [
            'expired_count' => $expired->count(),
            'expiring_count' => $expiring->count(),
            'low_stock_count' => $medicines->count(),
            'expired' => $expired->map(fn ($stock) => $this->stockAlertItem($stock))->values(),
            'expiring' => $expiring->map(fn ($stock) => $this->stockAlertItem($stock))->values(),
            'low_stock' => $medicines->map(fn ($medicine) => [
                'id' => $medicine->id,
                'medicine_code' => $medicine->medicine_code,
                'name' => $medicine->name,
                'stock_total' => (string) ($medicine->stock_total ?? 0),
                'reorder_level' => $medicine->reorder_level,
            ])->values(),
        ];
    }

    public function prescriptions(array $filters): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 10), 5), 50);
        $search = trim((string) ($filters['search'] ?? ''));
        $query = Prescription::query()->with($this->prescriptionRelations());

        $query->when($search !== '', function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('prescription_code', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($query) => $query
                        ->where('patient_code', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%"));
            });
        })->when(! empty($filters['patient_id']), fn ($query) => $query->where('patient_id', $filters['patient_id']));

        return $query->orderByDesc('issued_at')->paginate($perPage);
    }

    public function loadPrescription(Prescription $prescription): Prescription
    {
        return $prescription->load($this->prescriptionRelations());
    }

    public function dispense(Prescription $prescription, array $data, User $user): Prescription
    {
        DB::transaction(function () use ($prescription, $data, $user) {
            $prescription->loadMissing('items');
            $prescriptionItems = $prescription->items->keyBy('id');
            $currentDispensed = PrescriptionDispenseItem::query()
                ->whereIn('prescription_item_id', $prescriptionItems->keys())
                ->selectRaw('prescription_item_id, SUM(quantity_dispensed) as total')
                ->groupBy('prescription_item_id')
                ->pluck('total', 'prescription_item_id');

            foreach ($data['items'] as $itemData) {
                $prescriptionItem = $prescriptionItems->get($itemData['prescription_item_id']);
                if (! $prescriptionItem) {
                    throw ValidationException::withMessages([
                        'items' => ['Each dispensing item must belong to this prescription.'],
                    ]);
                }

                $quantity = (float) $itemData['quantity_dispensed'];
                $alreadyDispensed = (float) ($currentDispensed[$prescriptionItem->id] ?? 0);
                if ($prescriptionItem->quantity !== null && $alreadyDispensed + $quantity > (float) $prescriptionItem->quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["The requested quantity exceeds the prescribed quantity for {$prescriptionItem->medicine_name}."],
                    ]);
                }
            }

            $dispense = PrescriptionDispense::create([
                'dispense_code' => null,
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id,
                'dispensed_by' => $user->id,
                'status' => 'PARTIAL',
                'notes' => $data['notes'] ?? null,
                'dispensed_at' => now(),
            ]);

            $dispense->update([
                'dispense_code' => sprintf('DSP-%s-%06d', now()->format('Y'), $dispense->id),
            ]);

            foreach ($data['items'] as $itemData) {
                $stock = MedicineStock::query()->lockForUpdate()->find($itemData['medicine_stock_id']);
                $quantity = (float) $itemData['quantity_dispensed'];

                if (! $stock || (float) $stock->quantity_available < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ['One or more selected batches no longer have sufficient stock.'],
                    ]);
                }

                if ($stock->expiry_date->isBefore(today())) {
                    throw ValidationException::withMessages([
                        'items' => ["Batch {$stock->batch_number} is expired and cannot be dispensed."],
                    ]);
                }

                PrescriptionDispenseItem::create([
                    'prescription_dispense_id' => $dispense->id,
                    'prescription_item_id' => $itemData['prescription_item_id'],
                    'medicine_id' => $stock->medicine_id,
                    'medicine_stock_id' => $stock->id,
                    'quantity_dispensed' => $itemData['quantity_dispensed'],
                    'unit_price' => $stock->selling_price,
                ]);

                $stock->decrement('quantity_available', $itemData['quantity_dispensed']);
                $currentDispensed[$itemData['prescription_item_id']] = ($currentDispensed[$itemData['prescription_item_id']] ?? 0) + $quantity;
            }

            $allComplete = $prescriptionItems->isNotEmpty() && $prescriptionItems->every(function (PrescriptionItem $item) use ($currentDispensed) {
                return $item->quantity !== null && (float) ($currentDispensed[$item->id] ?? 0) >= (float) $item->quantity;
            });

            $dispense->update(['status' => $allComplete ? 'COMPLETED' : 'PARTIAL']);
        });

        return $prescription->fresh($this->prescriptionRelations());
    }

    private function prescriptionRelations(): array
    {
        return [
            'patient:id,patient_code,first_name,last_name,allergies',
            'prescribedBy:id,user_id',
            'prescribedBy.user:id,name',
            'items.dispenseItems',
            'dispenses' => fn ($query) => $query->latest('dispensed_at')->with('dispensedBy:id,name'),
        ];
    }

    private function validateActiveCategory(?int $categoryId): void
    {
        if ($categoryId && ! MedicineCategory::query()->whereKey($categoryId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'medicine_category_id' => ['The selected medicine category is not active.'],
            ]);
        }
    }

    private function stockAlertItem(MedicineStock $stock): array
    {
        return [
            'id' => $stock->id,
            'medicine_code' => $stock->medicine?->medicine_code,
            'medicine_name' => $stock->medicine?->name,
            'batch_number' => $stock->batch_number,
            'expiry_date' => $stock->expiry_date?->toDateString(),
            'quantity_available' => $stock->quantity_available,
        ];
    }
}
