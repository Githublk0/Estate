<?php

use Illuminate\Support\Facades\URL;

it('uses a same-origin relative action on the forwarded login form', function () {
    URL::forceRootUrl(null);

    $forwardedHost = env('CODESPACE_NAME', 'test-codespace').'-44483.app.github.dev';

    $serverVariables = [
        'HTTP_HOST' => 'localhost:44483',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => '44483',
        'HTTP_X_FORWARDED_HOST' => $forwardedHost,
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'REMOTE_ADDR' => '127.0.0.1',
    ];

    $html = $this->withServerVariables($serverVariables)
        ->get('/admin/login')
        ->assertOk()
        ->getContent();

    $matched = preg_match('#<(?:v-)?form\b[^>]*\baction="([^"]*)"[^>]*>#i', $html, $matches);

    $this->assertSame(1, $matched);
    $this->assertSame('/admin/login', $matches[1]);
    $this->assertStringContainsString('href="/admin/forget-password"', $html);
});

it('keeps a failed forwarded login redirect on the same origin', function () {
    URL::forceRootUrl(null);

    $forwardedHost = env('CODESPACE_NAME', 'test-codespace').'-44483.app.github.dev';

    $response = $this->withServerVariables([
        'HTTP_HOST' => 'localhost:44483',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => '44483',
        'HTTP_X_FORWARDED_HOST' => $forwardedHost,
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_REFERER' => "https://{$forwardedHost}/admin/login",
        'REMOTE_ADDR' => '127.0.0.1',
    ])->post('/admin/login', [
        'email' => 'codespaces-regression@example.invalid',
        'password' => bin2hex(random_bytes(16)),
    ]);

    $response->assertRedirect();

    $location = $response->headers->get('Location');

    $this->assertNotSame(false, $location);
    $this->assertSame('/admin/login', $location);
});

it('keeps the redirect for an authenticated login-page visit on the same origin', function () {
    URL::forceRootUrl(null);

    $this->actingAs(getDefaultAdmin())
        ->withServerVariables([
            'HTTP_HOST' => 'localhost:44483',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '44483',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '127.0.0.1',
        ])
        ->get('/admin/login')
        ->assertRedirect('/admin/dashboard');
});
