<?php

namespace App\Services\IPVueTable\Builders;

use App\Services\IPVueTable\Resources\IPVueTableResource;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class IPEloquentVuetableBuilder extends IPBaseBuilder
{
    /**
     * Query used to make the table data.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $query;

    public function __construct(Request $request, $query)
    {
        $this->request = $request;
        $this->query = $query;
    }

    /**
     *  Make the vuetable data. The data is sorted, filtered and paginated.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|LengthAwarePaginator|Collection
     */
    public function make()
    {
        $results = $this
            ->sort()
            ->filter();

        if ($this->paging) {
            $results = $results->paginate();

            return $this->applyChangesTo($results);
        }

        return $results->get();
    }

    /**
     * Paginate the query.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate()
    {
        $perPage = $this->request->input('per_page');

        return $this->query->paginate($perPage ?: 15);
    }

    public function get()
    {
        return IPVueTableResource::collection($this->query->get())
            ->additional($this->appends);
    }

    /**
     * Add the order by statement to the query.
     *
     * @return $this
     */
    public function sort()
    {
        if (! $this->request->input('sort')) {
            return $this;
        }

        list($field, $direction) = explode('|', $this->request->input('sort'));

        $this->query->orderBy($field, $direction);

        return $this;
    }

    /**
     * Add the where clauses to the query.
     *
     * @return $this
     */
    public function filter()
    {
        if (! $this->request->input('searchable') || ! $this->request->input('filter')) {
            return $this;
        }

        $filterText = "%{$this->request->input('filter')}%";

        $this->query->where(function ($query) use ($filterText) {
            foreach ($this->request->input('searchable') as $column) {
                $query->orWhere($column, 'like', $filterText);
            }
        });

        return $this;
    }

    /**
     * Edit the results inside the pagination object.
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator $results
     */
    public function applyChangesTo($results)
    {
        if (empty($this->columnsToEdit) && empty($this->columnsToAdd)) {
            return $results;
        }

        $newData = $results
            ->getCollection()
            ->map(function ($model) {
                $model = $this->editModelAttibutes($model);
                $model = $this->addModelAttibutes($model);

                return $model;
            });

        return $this->appends($results->setCollection($newData));
    }

    /**
     * Edit the model attributes acording to the columnsToEdit attribute.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function editModelAttibutes($model)
    {
        foreach ($this->columnsToEdit as $column => $value) {
            if ($model->hasCast($column)) {
                //throw new \Exception("Can not edit the '{$column}' attribute, it has a cast defined in the model.");
            }

            $model = $this->changeAttribute($model, $column, $value);
        }

        return $model;
    }

    /**
     * Add the model attributes acording to the columnsToAdd attribute.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function addModelAttibutes($model)
    {
        foreach ($this->columnsToAdd as $column => $value) {
            if ($model->relationLoaded($column) || $model->getAttributeValue($column) != null) {
                //throw new \Exception("Can not add the '{$column}' column, the results already have that column.");
            }

            $model = $this->changeAttribute($model, $column, $value);
        }

        return $model;
    }

    /**
     * Change a model attribe
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string $attribute
     * @param  string|Closure $value
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function changeAttribute($model, $attribute, $value)
    {
        if ($value instanceof Closure) {
            $model->setAttribute($attribute, $value($model));
        } else {
            $model->setAttribute($attribute, $value);
        }

        if ($model->relationLoaded($attribute)) {
            $model->setRelation($attribute, 'removed');
        }

        return $model;
    }

    public function with($column, $value)
    {
        $this->appends[$column] = $value;

        return $this;
    }
}
