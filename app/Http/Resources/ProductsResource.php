<?php

namespace App\Http\Resources;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Products */
class ProductsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->mergeWhen($this->created_by, function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'updated_by' => $this->mergeWhen($this->updated_by, function () {
                return [
                    'id' => $this->editor,
                    'name' => $this->editor,
                ];
            }),
//            'wishlist' => $this->mergeWhen(\Wishlist::has($this->getModel()), $this->wish),
        ];
    }
}