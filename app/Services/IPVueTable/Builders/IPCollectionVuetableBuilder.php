<?php

namespace App\Services\IPVueTable\Builders;

use App\Services\IPVueTable\Resources\IPVueTableResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IPCollectionVuetableBuilder extends IPBaseBuilder
{
    /**
     * @var \Illuminate\Support\Collection
     */
    private $collection;

    /**
     * IPCollectionVuetableBuilder constructor.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Support\Collection $collection
     */
    public function __construct(\Illuminate\Http\Request $request, Collection $collection)
    {
        $this->request = $request;
        $this->collection = $collection;
    }

    /**
     *  * Make the vuetable data. The data is sorted, filtered and paginated.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|LengthAwarePaginator
     */
    public function make()
    {
        $results = $this
            ->filter()
            ->sort();

        if ($this->paging) {
            $results = $results->paginate();

            return $this->applyChangesTo($results);
        }

        return $results->get();
    }

    public function get()
    {
        return IPVueTableResource::collection($this->collection)
            ->additional($this->appends);
    }

    /**
     * Paginate the query.
     *
     * @return LengthAwarePaginator
     */
    public function paginate()
    {
        $perPage = ($this->request->input('per_page') > 0) ? $this->request->input('per_page') : 15;
        $count = $this->collection->count();
        $page = $this->request->input('page');
        $page = (empty($page) or $page < 1) ? 1 : $page;
        $offset = $perPage * ($page - 1);
        $offset = $offset < 1 ? 0 : $offset;
        $this->collection = $this->collection->slice(
            $offset,
            (int) $perPage
        )->values();

        $paginator = new LengthAwarePaginator($this->collection, $count, $perPage ?: 15);

        return $paginator;
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

        if ($field) {
            $comparer = function ($a, $b) use ($field, $direction) {
                if ($direction === 'desc') {
                    $first = $b;
                    $second = $a;
                } else {
                    $first = $a;
                    $second = $b;
                }

                $cmp = strnatcasecmp($first[$field], $second[$field]);

                if ($cmp != 0) {
                    return $cmp;
                }
                // all elements were equal
                return 0;
            };

            $this->collection = $this->collection->sort($comparer);
        }

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

        $filterText = Str::lower($this->request->input('filter'));
        $columns = $this->request->input('searchable');

        $this->collection = $this->collection->filter(
            function ($row) use ($columns, $filterText) {
                $data = $this->serialize($row);
                foreach ($columns as $column) {
                    if (! $value = Arr::get($data, $column)) {
                        continue;
                    }

                    if (is_array($value)) {
                        continue;
                    }

                    $value = Str::lower($value);

                    if (Str::contains($value, $filterText)) {
                        return true;
                    }
                }

                return false;
            }
        );

        return $this;
    }

    /**
     * Edit the results inside the pagination object.
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator $results
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function applyChangesTo($results)
    {
        if (empty($this->columnsToEdit) && empty($this->columnsToAdd)) {
            return $results;
        }

        $newData = $results
            ->getCollection()
            ->map(function ($item) {
                $item = $this->editItem($item);
                $item = $this->addItem($item);

                return $item;
            });

        return $this->appends($results->setCollection($newData));
    }

    /**
     * @param array $item
     * @throws \Exception
     *
     * @return array
     */
    public function addItem($item)
    {
        foreach ($this->columnsToAdd as $column => $value) {
            if (Arr::has($item, $column)) {
                continue;
                //throw new \Exception("Can not add the '{$column}' column, the results already have that column.");
            }

            $item = $this->applyColumn($item, $column, $value);
        }

        return $item;
    }

    public function editItem($item)
    {
        foreach ($this->columnsToEdit as $column => $value) {
            if (Arr::has($item, $column) === false) {
                //throw new \Exception("Column {$column} not exist in array");
            }

            $item = $this->applyColumn($item, $column, $value);
        }

        return $item;
    }

    /**
     * Change a model attribe
     *
     * @param  array $model
     * @param  string $attribute
     * @param  string|\Closure $value
     * @return array
     */
    public function applyColumn($item, $attribute, $value)
    {
        if ($value instanceof \Closure) {
            $item[$attribute] = $value($item);
        } else {
            $item[$attribute] = $value;
        }

        return $item;
    }

    /**
     * Serialize collection
     *
     * @param  mixed $collection
     * @return mixed|null
     */
    protected function serialize($collection)
    {
        return $collection instanceof Arrayable ? $collection->toArray() : (array) $collection;
    }

    public function with($column, $value)
    {
        $this->appends[$column] = $value;

        return $this;
    }
}
