<?php

namespace Tests\Http\Controllers\Api;

use function Pest\Laravel\getJson;

it('able to see spend wallet balance', function () {
    getJson('/api/v1/wallets/spend/balance')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'balance',
        ]);
});
it('able to see default wallet balance', function () {
    getJson('/api/v1/wallets/default/balance')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'balance',
        ]);
});
it('able to see currency wallet balance', function () {
    getJson('/api/v1/wallets/currency/balance')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'balance',
        ]);
});