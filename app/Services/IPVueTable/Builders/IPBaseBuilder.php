<?php

namespace App\Services\IPVueTable\Builders;

use App\Services\IPVueTable\Resources\IPVueTableResource;
use Illuminate\Support\Collection;

abstract class IPBaseBuilder
{
    /**
     * The current request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    protected $paging = true;

    /**
     * Array of columns that should be added and the new content.
     *
     * @var array
     */
    protected $appends;

    /**
     * Array of columns that should be edited and the new content.
     *
     * @var array
     */
    protected $columnsToEdit = [];

    /**
     * Array of columns that should be added and the new content.
     *
     * @var array
     */
    protected $columnsToAdd = [];

    /**
     * Add a new column to edit with its new value.
     *
     * @param  string $column
     * @param  string|\Closure $content
     * @return $this
     */
    public function editColumn($column, $content)
    {
        $this->columnsToEdit[$column] = $content;

        return $this;
    }

    /**
     * Add a new column to the columns to add.
     *
     * @param string $column
     * @param string|\Closure $content
     */
    public function addColumn($column, $content)
    {
        $this->columnsToAdd[$column] = $content;

        return $this;
    }

    /**
     * Skip Paging from the Table
     */
    public function skipPaging()
    {
        $this->paging = false;

        return $this;
    }

    /**
     * @param $collection
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|Collection
     */
    public function appends($collection)
    {
        return IPVueTableResource::collection($collection)
            ->additional($this->appends);
    }
}
