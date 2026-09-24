<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cegah situs ini ditampilkan di dalam <iframe> milik domain lain —
        // proteksi standar terhadap serangan clickjacking.
        $response->headers->set('X-Frame-Options', 'DENY');

        // Cegah browser "menebak" tipe file secara otomatis (MIME sniffing) —
        // mencegah file upload disalahgunakan jadi dieksekusi sebagai script.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Batasi info referrer yang dikirim ke situs lain saat user klik link
        // keluar — jangan bocorkan full URL (yang bisa berisi token/parameter
        // sensitif) ke domain pihak ketiga.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Aplikasi ini TIDAK butuh akses kamera/mikrofon/lokasi browser sama
        // sekali — matikan eksplisit, mencegah penyalahgunaan kalau ada XSS.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // CSP dalam mode REPORT-ONLY dulu — browser cuma LAPOR pelanggaran ke
        // console, TIDAK memblokir apapun. Ini supaya kita bisa lihat dulu
        // resource apa saja yang sebenarnya dimuat sebelum benar-benar
        // menegakkan policy-nya (Alpine.js butuh 'unsafe-eval', perlu
        // dipastikan dulu tidak ada resource lain yang kelewat).
        $response->headers->set(
            'Content-Security-Policy-Report-Only',
            "default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';"
        );

        return $response;
    }
}
