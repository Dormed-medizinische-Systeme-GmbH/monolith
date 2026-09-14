<?php

declare(strict_types=1);

use App\Jobs\SendInquiryMails;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake();
});

/**
 * @return array<string, mixed>
 */
function validInquiry(array $overrides = []): array
{
    return array_merge([
        'name' => 'Dr. Meier',
        'email' => 'meier@praxis.test',
        'plz' => '44135',
        'nachricht' => 'Bitte um ein Angebot.',
        'datenschutz' => 'ja',
    ], $overrides);
}

test('eine gueltige Anfrage stellt den Mailversand in die Queue', function (): void {
    $this->post('http://'.config('domains.website').'/kontakt', validInquiry())
        ->assertRedirect(route('website.danke'));

    Queue::assertPushed(SendInquiryMails::class, function (SendInquiryMails $job): bool {
        return $job->email === 'meier@praxis.test' && $job->plz === '44135';
    });
});

test('ohne Datenschutz-Zustimmung passiert nichts', function (): void {
    $this->post('http://'.config('domains.website').'/kontakt', validInquiry(['datenschutz' => null]))
        ->assertSessionHasErrors('datenschutz');

    Queue::assertNothingPushed();
});

test('Pflichtfelder werden geprueft', function (string $field): void {
    $this->post('http://'.config('domains.website').'/kontakt', validInquiry([$field => null]))
        ->assertSessionHasErrors($field);

    Queue::assertNothingPushed();
})->with(['name', 'email', 'plz']);

test('eine ungueltige Postleitzahl wird abgewiesen', function (): void {
    $this->post('http://'.config('domains.website').'/kontakt', validInquiry(['plz' => '441']))
        ->assertSessionHasErrors('plz');
});

test('der Rueckruf-Wunsch kommt am Job an', function (): void {
    $this->post('http://'.config('domains.website').'/kontakt', validInquiry(['rueckruf' => 'ja']))
        ->assertRedirect(route('website.danke'));

    Queue::assertPushed(fn (SendInquiryMails $job): bool => $job->wantsCallback === true);
});
