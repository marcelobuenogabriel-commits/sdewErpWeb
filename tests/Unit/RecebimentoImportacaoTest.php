<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RecebimentoImportacaoTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_a_basic_request(): void
    {
        $user = User::query()->first();
        if (!$user) {
            $this->markTestSkipped('No user found to authenticate the request.');
        }

        $payload = [
            'qtdRec' => 10,
            'codPro' => '123',
            'numNfc' => '456',
            'numOcp' => '789',
            'seqIpo' => 1,
            'seqIpc' => 1,
            'chvNel' => '00000000000000000000000000000000000000000000',
            'codPal' => 'PAL01',
            'codFor' => 100,
            'codPin' => 'PIN01',
        ];

        $response = $this->actingAs($user)->post(route('test'), $payload);

        $response->assertStatus(302);
    }
}