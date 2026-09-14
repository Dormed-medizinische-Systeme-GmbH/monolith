<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\ContactFormRequest;
use App\Jobs\SendInquiryMails;
use Illuminate\Http\RedirectResponse;

class ContactFormController extends Controller
{
    public function store(ContactFormRequest $request): RedirectResponse
    {
        SendInquiryMails::dispatch(
            name: $request->validated('name'),
            email: $request->validated('email'),
            telefon: $request->validated('telefon'),
            plz: $request->validated('plz'),
            nachricht: $request->validated('nachricht'),
            praxis: $request->validated('praxis'),
            fachgebiet: $request->validated('fachgebiet'),
            wantsCallback: $request->wantsCallback(),
            rueckrufDatum: $request->validated('rueckruf_datum'),
        );

        return redirect()->route('website.danke');
    }
}
