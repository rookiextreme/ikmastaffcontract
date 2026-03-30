<?php

namespace App\Services\Performance;

use App\Models\PerformanceEvaluation;

class PerformanceLogService
{
    public function write(
        PerformanceEvaluation $evaluation,
        ?int $actorId,
        ?string $actorRole,
        string $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        array $meta = []
    ) {
        return $evaluation->logs()->create([
            'actor_id'    => $actorId,
            'actor_role'  => $actorRole,
            'action'      => $action,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'meta'        => $meta ?: null,
        ]);
    }
}
