<?php

namespace App\Services;

use App\Models\Mesa;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Generación de tokens y códigos QR para mesas. El QR codifica la URL
 * pública /mesa/{token}. Se usa el backend SVG de BaconQrCode (PHP puro,
 * no requiere las extensiones imagick/gd).
 */
class QrService
{
    /** Token aleatorio y único para una mesa (40 hex). */
    public function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(20));
        } while (Mesa::withTrashed()->where('qr_token', $token)->exists());

        return $token;
    }

    /** SVG completo (con prólogo XML) — para descarga. */
    public function svg(string $data, int $size = 320): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd));

        return $writer->writeString($data);
    }

    public function svgForMesa(Mesa $mesa, int $size = 320): string
    {
        return $this->svg($mesa->public_url, $size);
    }

    /** SVG sin prólogo XML — para incrustar inline en una vista HTML. */
    public function inlineSvgForMesa(Mesa $mesa, int $size = 320): string
    {
        return preg_replace('/^<\?xml.*?\?>\s*/s', '', $this->svgForMesa($mesa, $size));
    }
}
