<?php

namespace App\Policies;

use App\Models\Peminjaman;
use App\Models\User;

class PeminjamanPolicy
{
    // Admin boleh semua; selain admin lanjut ke method di bawah.
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Peminjaman $peminjaman): bool
    {
        return $user->id === $peminjaman->user_id;
    }

    public function update(User $user, Peminjaman $peminjaman): bool
    {
        return $user->id === $peminjaman->user_id;
    }
}
