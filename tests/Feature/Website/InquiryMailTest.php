<?php

declare(strict_types=1);

use App\Jobs\SendInquiryMails;
use App\Mail\ContactFormCompanyMail;
use App\Mail\ContactFormCustomerMail;
use Illuminate\Support\Facades\Mail;

/*
| Die Mail-Views werden hier wirklich gerendert. Das ist der Punkt: ein
| `Queue::fake()` im Formular-Test fuehrt den Job nie aus, und ein falscher
| route()-Name in einer Mail-Vorlage faellt dann erst beim ersten echten
| Kundenkontakt auf.
*/

test('der Job versendet beide Mails und rendert sie fehlerfrei', function (): void {
    Mail::fake();

    (new SendInquiryMails(
        name: 'Dr. Meier',
        email: 'meier@praxis.test',
        telefon: '0231 1234',
        plz: '44135',
        nachricht: 'Bitte um ein Angebot.',
        praxis: 'Praxis Meier',
        fachgebiet: 'Allgemeinmedizin',
        wantsCallback: true,
        rueckrufDatum: '2026-10-01',
    ))->handle();

    Mail::assertSent(ContactFormCompanyMail::class);
    Mail::assertSent(ContactFormCustomerMail::class);
});

test('die Firmenmail geht an das eigene Postfach und antwortet an den Kunden', function (): void {
    $mail = new ContactFormCompanyMail(
        name: 'Dr. Meier',
        email: 'meier@praxis.test',
        telefon: null,
        plz: '44135',
        nachricht: null,
        praxis: null,
        fachgebiet: null,
        wantsCallback: false,
        rueckrufDatum: null,
    );

    $envelope = $mail->envelope();

    expect($envelope->to[0]->address)->toBe(config('mail.from.address'));
    expect($envelope->replyTo[0]->address)->toBe('meier@praxis.test');

    $mail->assertSeeInHtml('Dr. Meier');
    $mail->assertSeeInHtml('44135');
});

test('die Kundenmail rendert inklusive ihrer Links', function (): void {
    (new ContactFormCustomerMail(name: 'Dr. Meier', email: 'meier@praxis.test'))
        ->assertSeeInHtml('Dr. Meier');
});
