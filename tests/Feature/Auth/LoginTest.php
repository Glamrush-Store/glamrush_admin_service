<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in user', function () {
    $user = User::factory()->create([
        'email' => 'johndoe@gmail.com'
    ]);


    $response = $this->postJson('/api/v1/account/login',[
            'email' => $user->email,
            'password' => 'password',
            'device_id' => 'jdndjneornfoirnmfoemofi',
            'device_name' => 'iphone'
    ]);


    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'access_token',
                'token_type',
            ],
            'errors',
        ]);
});


it('fails on wrong login credentials', function () {
    $user = User::factory()->create([
        'email' => 'johndoe@gmail.com'
    ]);


    $response = $this->postJson('/api/v1/account/login',[
        'email' => $user->email,
        'password' => 'wrong_password',
        'device_id' => 'jdndjneornfoirnmfoemofi',
        'device_name' => 'iphone'
    ]);


    $response->assertStatus(400)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [],
            'errors',
        ]);
});
