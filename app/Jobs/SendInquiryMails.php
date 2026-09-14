<?php

namespace App\Jobs;

use App\Mail\ContactFormCompanyMail;
use App\Mail\ContactFormCustomerMail;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends both contact-form mails and records the outcome in mail.log.
 * This is the only thing a contact-form submission triggers.
 *
 * Any mail failure lets the job retry as a whole (see retryUntil()) rather
 * than treating a single failed send as final - the accepted trade-off is
 * that a retry may re-send the company mail a second time if it already
 * went out but the customer mail then failed; a duplicate internal
 * notification is a much smaller problem than a lost inquiry.
 */
class SendInquiryMails implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $name,
        public string $email,
        public ?string $telefon,
        public string $plz,
        public ?string $nachricht,
        public ?string $praxis,
        public ?string $fachgebiet,
        public bool $wantsCallback,
        public ?string $rueckrufDatum,
    ) {}

    public function retryUntil(): DateTime
    {
        return now()->addHours(3);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 120, 300, 600];
    }

    public function handle(): void
    {
        Mail::send(new ContactFormCompanyMail(
            name: $this->name,
            email: $this->email,
            telefon: $this->telefon,
            plz: $this->plz,
            nachricht: $this->nachricht,
            praxis: $this->praxis,
            fachgebiet: $this->fachgebiet,
            wantsCallback: $this->wantsCallback,
            rueckrufDatum: $this->rueckrufDatum,
        ));

        Mail::send(new ContactFormCustomerMail(name: $this->name, email: $this->email));

        $this->logSubmission(mailSucceeded: true);
    }

    public function failed(Throwable $exception): void
    {
        $this->logSubmission(mailSucceeded: false);

        Log::error('SendInquiryMails permanently failed.', [
            'name' => $this->name,
            'email' => $this->email,
            'exception' => $exception->getMessage(),
        ]);
    }

    private function logSubmission(bool $mailSucceeded): void
    {
        $timestamp = now()->format('d.m.Y H:i:s');
        $status = $mailSucceeded ? '✓' : '✗';

        Log::channel('mail')->info(
            "[{$timestamp}] Anfrage von {$this->name} | Mailversand an {$this->email} {$status}"
        );
    }
}
