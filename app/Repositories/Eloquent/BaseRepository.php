<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseRepository
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * Base Repository constructor.
     * 
     * @param Model $model
     */

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function find(string $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    public function findBy(string $column, mixed $value, array $relations = []): ?Model
    {
        return $this->model->with($relations)->where($column, $value)->first();
    }

    public function create(array $payload): ?Model
    {
        return $this->model->create($payload);
    }

    public function update(string $id, array $payload): bool
    {
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $model->update($payload);
    }

    public function updateOrCreate(array $search, array $payload): ?Model
    {
        return $this->model->updateOrCreate($search, $payload);
    }

    public function delete(string $id): bool
    {
        $model = $this->find($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }
}
