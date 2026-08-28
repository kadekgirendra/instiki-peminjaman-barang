<x-mail::message>
@if ($isApproved)
# Pengajuan Peminjaman Disetujui

Halo {{ $loanRequest->user->name }},

Pengajuan peminjaman kamu untuk tanggal **{{ $loanRequest->start_date->translatedFormat('j F Y') }}** sampai **{{ $loanRequest->end_date->translatedFormat('j F Y') }}** telah **disetujui** oleh admin.

Silakan ambil barang sesuai jadwal yang sudah ditentukan.
@else
# Pengajuan Peminjaman Ditolak

Halo {{ $loanRequest->user->name }},

Mohon maaf, pengajuan peminjaman kamu untuk tanggal **{{ $loanRequest->start_date->translatedFormat('j F Y') }}** sampai **{{ $loanRequest->end_date->translatedFormat('j F Y') }}** telah **ditolak** oleh admin.

Silakan hubungi admin untuk informasi lebih lanjut, atau ajukan permintaan baru.
@endif

<x-mail::button :url="route('transactions.index')">
Lihat Detail Pengajuan
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
