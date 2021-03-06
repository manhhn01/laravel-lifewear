<?php

namespace App\Http\Resources\Front\Collections;

use App\Http\Resources\Front\ProductIndexResource;
use App\Http\Resources\PaginationCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\ResourceResponse;

class ProductPaginationCollection extends PaginationCollection
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
            'data' => ProductIndexResource::collection($this->collection),
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'per_page' => intval($this->perPage()),
            'products_count' => $this->total(),
        ];
    }
}
