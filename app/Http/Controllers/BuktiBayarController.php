<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BuktiBayarController extends Controller
{
    public function show(Peminjaman $peminjaman): StreamedResponse
    {
        Gate::authorize('view', $peminjaman);

        abort_if(! $peminjaman->bukti_bayar, 404);

        return Storage::disk('local')->response(
            $peminjaman->bukti_bayar,
            null,
            ['X-Content-Type-Options' => 'nosniff']
        );
    }
}
