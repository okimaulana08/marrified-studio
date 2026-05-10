<?php

declare(strict_types=1);

use App\Support\CustomCss;

it('returns empty string for empty input', function () {
    expect(CustomCss::sanitize(''))->toBe('');
});

it('preserves safe css unchanged', function () {
    $css = ".section-title { color: red; letter-spacing: 0.3em; }";
    expect(CustomCss::sanitize($css))->toBe($css);
});

it('strips closing style tags', function () {
    $css = '.x { color: red; }</style><script>alert(1)</script>.y { color: blue; }';
    $clean = CustomCss::sanitize($css);
    expect($clean)->not->toContain('</style');
    expect($clean)->not->toContain('<script');
    expect($clean)->toContain('.y');
});

it('strips @import directives', function () {
    $css = "@import url('https://evil.com/x.css');\n.body { color: blue; }";
    $clean = CustomCss::sanitize($css);
    expect($clean)->not->toContain('@import');
    expect($clean)->toContain('.body');
});

it('strips expression() css', function () {
    $css = '.x { width: expression(alert(1)); }';
    expect(CustomCss::sanitize($css))->not->toContain('expression(');
});

it('strips javascript: urls', function () {
    $css = ".x { background: url(javascript:alert(1)); }";
    expect(CustomCss::sanitize($css))->not->toContain('javascript:');
});

it('strips data text-html urls', function () {
    $css = ".x { background: url(data:text/html,<script>1</script>); }";
    $clean = CustomCss::sanitize($css);
    expect($clean)->not->toContain('data:text/html');
});

it('strips iframe object embed tags', function () {
    $css = '<iframe src=evil><object data=x><embed src=y>.real { color: blue; }';
    $clean = CustomCss::sanitize($css);
    expect($clean)->not->toContain('<iframe');
    expect($clean)->not->toContain('<object');
    expect($clean)->not->toContain('<embed');
    expect($clean)->toContain('.real');
});

it('strips IE behavior property', function () {
    $css = '.x { behavior: url(evil.htc); }';
    expect(CustomCss::sanitize($css))->not->toContain('behavior:');
});

it('clamps to max length', function () {
    $css = str_repeat('a', 40000);
    $clean = CustomCss::sanitize($css);
    expect(strlen($clean))->toBe(CustomCss::MAX_LENGTH);
});

it('is case-insensitive for danger patterns', function () {
    $css = '@IMPORT url(x); <SCRIPT>nope</SCRIPT> .ok { color: red; }';
    $clean = CustomCss::sanitize($css);
    expect($clean)->not->toContain('@IMPORT');
    expect($clean)->not->toContain('<SCRIPT');
    expect($clean)->toContain('.ok');
});
