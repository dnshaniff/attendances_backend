<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'name' => $this->employee->name,
                    'department' => [
                        'department_name' => $this->employee->department->department_name,
                        'max_clock_in_time' => $this->employee->department->max_clock_in_time->format('H:i:s'),
                        'max_clock_out_time' => $this->employee->department->max_clock_out_time->format('H:i:s'),
                    ],
                ];
            }),
            'clock_in' => $this->clock_in->format('d F Y, H:i:s'),
            'clock_out' => $this->clock_out ? $this->clock_out->format('d F Y, H:i:s') : null,
            'status' => $this->calculateStatus(),
            'created_at' => $this->created_at->format('d F Y, H:i:s'),
            'updated_at' => $this->updated_at->format('d F Y, H:i:s'),
        ];
    }

    protected function calculateStatus(): string
    {
        $late = $this->attendanceHistory()
            ->where('attendance_type', 1)
            ->exists();

        $early = $this->attendanceHistory()
            ->where('attendance_type', 2)
            ->exists();

        if (!$this->clock_out) {
            return $late ? 'Late' : '';
        }

        if ($late) {
            return 'Late';
        }

        if ($early) {
            return 'Early Leave';
        }

        return '';
    }
}
