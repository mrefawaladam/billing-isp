<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CRUDService
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records with pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * Get single record by ID
     */
    public function getById($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update($id, array $data): bool
    {
        $record = $this->getById($id);

        if (!$record) {
            return false;
        }

        return $record->update($data);
    }

    /**
     * Delete record
     */
    public function delete($id): bool
    {
        $record = $this->getById($id);

        if (!$record) {
            return false;
        }

        return $record->delete();
    }

    /**
     * Bulk delete
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Check if record exists
     */
    public function exists($id): bool
    {
        return $this->model->where('id', $id)->exists();
    }
}

