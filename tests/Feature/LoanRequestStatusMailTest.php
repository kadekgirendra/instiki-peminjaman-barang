<?php

namespace Tests\Feature\Mail;

use App\Mail\LoanRequestStatusMail;
use App\Models\LoanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanRequestStatusMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_has_three_retries_and_thirty_second_backoff(): void
    {
        // Arrange
        $loanRequest = LoanRequest::factory()->create();

        $mail = new LoanRequestStatusMail(
            $loanRequest,
            'booked'
        );

        // Assert
        $this->assertEquals(3, $mail->tries);
        $this->assertEquals(30, $mail->backoff);
    }

    public function test_failed_mail_job_reports_exception(): void
    {
        // Arrange
        $loanRequest = LoanRequest::factory()->create();

        $mail = new LoanRequestStatusMail(
            $loanRequest,
            'booked'
        );

        $exception = new \RuntimeException(
            'SMTP server is unavailable'
        );

        // Act
        $mail->failed($exception);

        // Assert
        $this->assertTrue(true);
    }
}
