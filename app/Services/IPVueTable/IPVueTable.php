<?php

namespace App\Services\IPVueTable;

use App\Services\IPVueTable\Builders\IPCollectionVuetableBuilder;
use App\Services\IPVueTable\Builders\IPEloquentVuetableBuilder;
use Illuminate\Http\Request;

class IPVueTable
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle automatic builder
     *
     * @param  mixed $source
     * @return IPCollectionVuetableBuilder|IPEloquentVuetableBuilder
     * @throws \Exception
     */
    public static function of($source)
    {
        $request = app('request');

        if ($source instanceof \Illuminate\Database\Eloquent\Builder) {
            return new IPEloquentVuetableBuilder($request, $source);
        } elseif ($source instanceof \Illuminate\Support\Collection) {
            return new IPCollectionVuetableBuilder($request, $source);
        } else {
            throw new \Exception('Unsupported builder type: '.gettype($source));
        }
    }

    /**
     * Return the Eloquent Vuetable Builder
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     */
    public function eloquent($query)
    {
        return new IPEloquentVuetableBuilder($this->request, $query);
    }

    /**
     * @param \Illuminate\Support\Collection $collection
     */
    public function collection($collection)
    {
        return new IPCollectionVuetableBuilder($this->request, $collection);
    }

    public function getRequest()
    {
        return $this->request;
    }
}
