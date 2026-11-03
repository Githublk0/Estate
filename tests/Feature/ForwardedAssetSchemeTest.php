<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;

uses(DatabaseTransactions::class);

it('uses same-origin paths for admin build assets without a named route', function () {
    URL::forceRootUrl('https://localhost:44483');

    request()->setRouteResolver(fn () => null);

    $url = vite()->asset('images/logo.svg');

    $this->assertStringStartsWith('/admin/build/assets/logo-', $url);
    $this->assertNull(parse_url($url, PHP_URL_HOST));
    $this->assertStringNotContainsString('https://localhost:44483', $url);
});

it('uses same-origin asset paths on the forwarded admin login page', function (string $forwardedHost) {
    URL::forceRootUrl(null);

    $html = $this->withServerVariables([
        'HTTP_HOST' => 'localhost:44483',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => '44483',
        'HTTP_X_FORWARDED_HOST' => $forwardedHost,
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'REMOTE_ADDR' => '127.0.0.1',
    ])
        ->get('/admin/login')
        ->assertOk()
        ->getContent();

    preg_match_all(
        '/(?:href|src)="([^"]*\\/admin\\/build\\/assets\\/[^"]+)"/',
        $html,
        $matches
    );

    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $url) {
        $this->assertStringStartsWith('/admin/build/assets/', $url);
        $this->assertNull(parse_url($url, PHP_URL_HOST));
    }

    $this->assertMatchesRegularExpression('#href="/admin/build/assets/[^"]+\\.css"#', $html);
    $this->assertMatchesRegularExpression('#src="/admin/build/assets/[^"]+\\.js"#', $html);
    $this->assertMatchesRegularExpression('#src="/admin/build/assets/logo-[^"]+\\.svg"#', $html);
    $this->assertMatchesRegularExpression('#href="/admin/build/assets/favicon-[^"]+\\.ico"#', $html);

    $manifest = json_decode(
        file_get_contents(public_path('admin/build/manifest.json')),
        true,
        flags: JSON_THROW_ON_ERROR
    );
    $stylesheet = file_get_contents(public_path('admin/build/'.$manifest['src/Resources/assets/css/app.css']['file']));

    $this->assertMatchesRegularExpression('#url\\(icomoon-[^)]*\\.woff#', $stylesheet);
    $this->assertDoesNotMatchRegularExpression(
        '#https?://localhost(?::44483)?/admin/build/assets/#',
        $html.$stylesheet
    );
})->with([
    'Codespaces forwarded host' => env('CODESPACE_NAME', 'test-codespace').'-44483.app.github.dev',
    'forged forwarded host' => 'attacker.example',
]);

it('uses same-origin asset paths on the forwarded admin dashboard', function () {
    URL::forceRootUrl(null);

    $admin = getDefaultAdmin();

    securityTestPipeline();

    $html = $this->actingAs($admin)
        ->withServerVariables([
            'HTTP_HOST' => 'localhost:44483',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '44483',
            'HTTP_X_FORWARDED_HOST' => env('CODESPACE_NAME', 'test-codespace').'-44483.app.github.dev',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '127.0.0.1',
        ])
        ->get('/admin/dashboard')
        ->assertOk()
        ->getContent();

    preg_match_all(
        '/(?:href|src)="([^"]*\\/admin\\/build\\/assets\\/[^"]+)"/',
        $html,
        $matches
    );

    expect($matches[1])->not->toBeEmpty();

    foreach ($matches[1] as $url) {
        $this->assertStringStartsWith('/admin/build/assets/', $url);
        $this->assertNull(parse_url($url, PHP_URL_HOST));
    }

    $this->assertMatchesRegularExpression('#href="/admin/build/assets/[^"]+\\.css"#', $html);
    $this->assertMatchesRegularExpression('#src="/admin/build/assets/[^"]+\\.js"#', $html);
    $this->assertMatchesRegularExpression('#src="/admin/build/assets/logo-[^"]+\\.svg"#', $html);
    $this->assertMatchesRegularExpression('#href="/admin/build/assets/favicon-[^"]+\\.ico"#', $html);
});
