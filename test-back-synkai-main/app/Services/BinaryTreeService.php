<?php

namespace App\Services;

use App\Models\BinaryPlacement;
use App\Models\User;

/**
 * API explícita de negocio para colocación en el binario (delega en BinaryService).
 */
class BinaryTreeService
{
    public function __construct(
        protected BinaryService $binaryService
    ) {}

    /**
     * Inserta al usuario en el primer hueco libre (BFS) bajo su patrocinador.
     */
    public function insertarEnArbol(User $user): ?BinaryPlacement
    {
        return $this->binaryService->placeUserInFirstFreeSlot($user);
    }
}
