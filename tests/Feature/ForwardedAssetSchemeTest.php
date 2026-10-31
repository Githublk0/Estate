<?php

use Illuminate\Support\Facades\URL;

it('uses HTTPS for assets on the forwarded admin login page', function () {
    URL::forceRootUrl(null);

    $response = $this->withServerVariables([
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'REMOTE_ADDR' => '127.0.0.1',
    ])
        ->get('/admin/login')
        ->assertOk();

    $html = $response->getContent();

    preg_match_all(
        '/(?:href|src)="([^"]*\\/admin\\/build\\/assets\\/[^"]+)"/',
        $html,
        $matches
    );

    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $url) {
        $this->assertStringStartsWith('https://', $url);
    }

    $this->assertMatchesRegularExpression('#/admin/build/assets/[^"]+\\.css#', $html);
    $this->assertMatchesRegularExpression('#/admin/build/assets/[^"]+\\.js#', $html);
    $this->assertMatchesRegularExpression('#/admin/build/assets/logo[^"]+\\.svg#', $html);
});
