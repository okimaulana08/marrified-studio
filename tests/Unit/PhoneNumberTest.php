<?php

declare(strict_types=1);

use App\Support\PhoneNumber;

it('normalizes Indonesian local form starting with 0', function () {
    expect(PhoneNumber::normalize('08123456789'))->toBe('628123456789');
});

it('normalizes form with +62 prefix', function () {
    expect(PhoneNumber::normalize('+628123456789'))->toBe('628123456789');
});

it('keeps already-normalized 62xxx form', function () {
    expect(PhoneNumber::normalize('628123456789'))->toBe('628123456789');
});

it('strips spaces and dashes', function () {
    expect(PhoneNumber::normalize('+62 812-3456-7890'))->toBe('6281234567890');
});

it('strips parentheses', function () {
    expect(PhoneNumber::normalize('(0812) 3456 7890'))->toBe('6281234567890');
});

it('adds 62 to bare local form starting with 8', function () {
    expect(PhoneNumber::normalize('81234567890'))->toBe('6281234567890');
});

it('returns null for null input', function () {
    expect(PhoneNumber::normalize(null))->toBeNull();
});

it('returns null for empty string', function () {
    expect(PhoneNumber::normalize(''))->toBeNull();
});

it('returns null for too-short input', function () {
    expect(PhoneNumber::normalize('123'))->toBeNull();
});

it('builds wa link without message', function () {
    expect(PhoneNumber::waLink('08123456789'))->toBe('https://wa.me/628123456789');
});

it('builds wa link with url-encoded message', function () {
    $link = PhoneNumber::waLink('08123456789', 'Halo, ini pesan');
    expect($link)->toBe('https://wa.me/628123456789?text=Halo%2C%20ini%20pesan');
});

it('returns null wa link when phone is invalid', function () {
    expect(PhoneNumber::waLink(null, 'pesan'))->toBeNull();
    expect(PhoneNumber::waLink('xx', 'pesan'))->toBeNull();
});
