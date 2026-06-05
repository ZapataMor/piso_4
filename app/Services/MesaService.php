<?php

namespace App\Services;

use App\Models\Mesa;

/**
 * Operaciones de negocio sobre mesas. Centraliza la generación del
 * qr_token para que el controlador no conozca ese detalle.
 */
class MesaService
{
    public function __construct(private QrService $qr) {}

    public function create(array $data): Mesa
    {
        $data['qr_token'] = $this->qr->generateToken();

        return Mesa::create($data);
    }

    public function update(Mesa $mesa, array $data): Mesa
    {
        $mesa->update($data);

        return $mesa;
    }

    public function regenerateToken(Mesa $mesa): Mesa
    {
        $mesa->update(['qr_token' => $this->qr->generateToken()]);

        return $mesa;
    }

    public function delete(Mesa $mesa): void
    {
        $mesa->delete(); // soft delete
    }
}
