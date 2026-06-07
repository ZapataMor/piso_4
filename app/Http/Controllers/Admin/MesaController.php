<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMesaRequest;
use App\Http\Requests\UpdateMesaRequest;
use App\Models\Mesa;
use App\Services\MesaService;
use App\Services\QrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class MesaController extends Controller
{
    public function __construct(
        private readonly MesaService $mesas,
        private readonly QrService $qr,
    ) {}

    public function create(): View
    {
        return view('admin.mesas.create', ['estados' => TableStatus::cases()]);
    }

    public function store(StoreMesaRequest $request): RedirectResponse
    {
        $mesa = $this->mesas->create($request->validated());

        return redirect()
            ->route('admin.mesas.edit', $mesa)
            ->with('status', "Mesa #{$mesa->numero} creada y QR generado.");
    }

    public function edit(Mesa $mesa): View
    {
        return view('admin.mesas.edit', [
            'mesa' => $mesa,
            'estados' => TableStatus::cases(),
            'qrSvg' => $this->qr->inlineSvgForMesa($mesa, 240),
        ]);
    }

    public function update(UpdateMesaRequest $request, Mesa $mesa): RedirectResponse
    {
        $this->mesas->update($mesa, $request->validated());

        return redirect()
            ->route('admin.mesas.edit', $mesa)
            ->with('status', "Mesa #{$mesa->numero} actualizada.");
    }

    public function destroy(Mesa $mesa): RedirectResponse
    {
        $this->mesas->delete($mesa);

        return redirect()
            ->route('admin.mesas.index')
            ->with('status', "Mesa #{$mesa->numero} eliminada.");
    }

    /** Descarga el QR de la mesa como archivo SVG. */
    public function downloadQr(Mesa $mesa): Response
    {
        $svg = $this->qr->svgForMesa($mesa, 600);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="mesa-'.$mesa->numero.'-qr.svg"',
        ]);
    }

    /** Regenera el token (invalida el QR anterior). */
    public function regenerateQr(Mesa $mesa): RedirectResponse
    {
        $this->mesas->regenerateToken($mesa);

        return redirect()
            ->route('admin.mesas.edit', $mesa)
            ->with('status', 'QR regenerado. El código anterior dejó de ser válido.');
    }
}
