<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'department_name' => $this->department_name,
            'max_clock_in_time' => $this->max_clock_in_time->format('H:i'),
            'max_clock_out_time' => $this->max_clock_out_time->format('H:i'),
            'created_at' => $this->created_at->format('d F Y, H:i:s'),
            'updated_at' => $this->updated_at->format('d F Y, H:i:s'),
        ];
    }
}
