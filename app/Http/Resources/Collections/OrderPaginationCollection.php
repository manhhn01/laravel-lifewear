<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\OrderResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\ResourceResponse;

class OrderPaginationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => OrderResource::collection($this->collection),
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'per_page' => intval($this->perPage()),
            'orders_count' => $this->total(),
        ];
    }

    public function toResponse($request)
    {
        return (new ResourceResponse($this))->toResponse($request);
    }
}
