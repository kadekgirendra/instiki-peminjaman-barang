<?php

namespace App\Mail;

use App\Models\LoanRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Dikirim ke mahasiswa saat pengajuan peminjamannya di-approve/reject admin.
 * implements ShouldQueue -> WAJIB, ini yang bikin Laravel taruh proses
 * "kirim email" ke tabel `jobs` dulu, bukan langsung dikirim saat itu juga.
 * Tanpa ShouldQueue, Mail::send() akan tetap sinkron (menunggu selesai),
 * persis seperti sebelum ada queue sama sekali.
 */
class LoanRequestStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public LoanRequest $loanRequest,
        public string $status // 'booked' (disetujui) atau 'rejected' (ditolak)
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->status === 'booked'
            ? 'Pengajuan Peminjaman Anda Disetujui'
            : 'Pengajuan Peminjaman Anda Ditolak';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.loan-request-status',
            with: [
                'loanRequest' => $this->loanRequest,
                'isApproved' => $this->status === 'booked',
            ],
        );
    }
}
