<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'department_id' => $this->department_id,
            'name' => $this->name,
            'address' => $this->address,
            'department' => $this->whenLoaded('department', function () {
                return [
                    'department_name' => $this->department->department_name,
                    'max_clock_in_time' => $this->department->max_clock_in_time->format('H:i:s'),
                    'max_clock_out_time' => $this->department->max_clock_out_time->format('H:i:s'),
                ];
            }),
            'created_at' => $this->created_at->format('d F Y, H:i:s'),
            'updated_at' => $this->updated_at->format('d F Y, H:i:s'),
        ];
    }
}
