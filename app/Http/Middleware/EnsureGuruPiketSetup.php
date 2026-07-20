<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan setiap sesi guru piket sudah mengisi "Nama Lengkap Piket"
 * sebelum bisa mengakses halaman scan.
 *
 * Data disimpan di SESSION (bukan database per user_id) sehingga dua
 * device berbeda yang login dengan akun guru_piket yang SAMA akan
 * memiliki piket_petugas_id yang BERBEDA dan INDEPENDEN satu sama lain.
 */
class EnsureGuruPiketSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        // Lewati middleware ini untuk route setup itu sendiri (hindari redirect loop)
        if ($request->routeIs('piket.setup') || $request->routeIs('piket.setup.post')) {
            return $next($request);
        }

        // Cek apakah sesi ini sudah punya piket_petugas_id
        if (!session()->has('piket_petugas_id')) {
            return redirect()->route('piket.setup')
                ->with('info', 'Silakan isi Nama Lengkap Anda sebagai Petugas Piket terlebih dahulu.');
        }

        return $next($request);
    }
}