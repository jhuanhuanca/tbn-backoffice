<?php

namespace App\Services;

use App\Models\Rank;
use App\Models\User;

/**
 * Motor de rango por PV mensual de calificación (umbrales en config) + orden en tabla ranks.
 */
class RankService
{
    /**
     * Asigna el mayor rango cuyo umbral de PV mensual cumple el usuario (slug en mlm.residual.rank_thresholds_pv).
     */
    public function sincronizarRangoPorCalificacion(User $user): void
    {
        $pv = (string) ($user->monthly_qualifying_pv ?? '0');
        $thresholds = config('mlm.residual.rank_thresholds_pv', []);
        if ($thresholds === []) {
            return;
        }

        uasort($thresholds, fn ($a, $b) => $b <=> $a);

        $slug = null;
        foreach ($thresholds as $candidateSlug => $min) {
            if (bccomp($pv, (string) $min, 2) >= 0) {
                $slug = (string) $candidateSlug;
                break;
            }
        }

        if ($slug === null) {
            if (config('mlm.rank.allow_downgrade_on_monthly_eval', true)) {
                $fallback = Rank::query()->orderBy('sort_order')->first();
                if ($fallback && (int) $user->rank_id !== (int) $fallback->id) {
                    $user->forceFill(['rank_id' => $fallback->id])->save();
                }
            }

            return;
        }

        $rank = Rank::query()->where('slug', $slug)->first();
        if (! $rank) {
            return;
        }

        if ((int) $user->rank_id === (int) $rank->id) {
            return;
        }

        $user->forceFill(['rank_id' => $rank->id])->save();
    }

    /**
     * Re-evalúa todos los socios (chunk) — programar vía consola / schedule.
     */
    public function reevaluarTodosLosRangos(): int
    {
        $updated = 0;
        User::query()->chunkById(500, function ($users) use (&$updated) {
            foreach ($users as $user) {
                $before = $user->rank_id;
                $this->sincronizarRangoPorCalificacion($user);
                if ((int) $user->fresh()->rank_id !== (int) $before) {
                    $updated++;
                }
            }
        });

        return $updated;
    }

    public function slugEfectivoParaResidual(User $sponsor): string
    {
        $pv = (string) ($sponsor->monthly_qualifying_pv ?? '0');
        $thresholds = config('mlm.residual.rank_thresholds_pv', []);
        if ($thresholds === []) {
            return 'default';
        }

        uasort($thresholds, fn ($a, $b) => $b <=> $a);

        foreach ($thresholds as $slug => $min) {
            if (bccomp($pv, (string) $min, 2) >= 0) {
                return (string) $slug;
            }
        }

        return 'default';
    }
}
