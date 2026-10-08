@if (session('success'))
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('success') }}</div>
@endif
@if (! ($tanpaError ?? false) && $errors->any())
    <div class="mb-4 p-3 rounded bg-red-100 text-red-800 text-sm">
        <ul class="list-disc ms-5">
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
@endif
