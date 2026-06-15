<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'orderId' => (string) $this->order_id,
            'status' => $this->status,
            'amount' => (float) $this->amount,
            'assignedAt' => optional($this->assigned_at)->toISOString(),
            'acceptedAt' => optional($this->accepted_at)->toISOString(),
            'pickedUpAt' => optional($this->picked_up_at)->toISOString(),
            'deliveredAt' => optional($this->delivered_at)->toISOString(),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
