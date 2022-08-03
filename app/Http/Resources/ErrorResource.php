<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ErrorResource extends JsonResource
{
    public function __construct($errors)
    {
        $this->resource = $errors;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => [],
            'errors' => $this->resource,
        ];
    }

    public function with($request)
    {
        return [
            'status'=> false,
            'maintenance_mode'=> true,
            'login_dashboard'=>(Auth::user()) ? Auth::user()->login_board : ''
        ];
    }
}
