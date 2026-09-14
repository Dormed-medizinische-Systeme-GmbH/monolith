<?php

test('returns a successful response', function () {
    $response = $this->get(route('website.index'));

    $response->assertOk();
});
