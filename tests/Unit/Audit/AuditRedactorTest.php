<?php

namespace Tests\Unit\Audit;

use App\Services\Audit\AuditRedactor;
use PHPUnit\Framework\TestCase;

class AuditRedactorTest extends TestCase
{
    public function test_it_never_retains_secrets_or_personal_identifiers(): void
    {
        $result = (new AuditRedactor)->redact([
            'password' => 'NeverStoreThis',
            'api_token' => 'token-value',
            'email' => 'person@example.test',
            'phone' => '+254700000000',
            'name' => 'Allowed Name',
        ]);

        $this->assertSame('[REDACTED]', $result['password']);
        $this->assertSame('[REDACTED]', $result['api_token']);
        $this->assertSame('[MASKED]', $result['email']);
        $this->assertSame('[MASKED]', $result['phone']);
        $this->assertSame('Allowed Name', $result['name']);
    }

    public function test_it_recursively_redacts_metadata(): void
    {
        $result = (new AuditRedactor)->redact(['request' => ['secret' => 'hidden', 'count' => 4]]);

        $this->assertSame('[REDACTED]', $result['request']['secret']);
        $this->assertSame(4, $result['request']['count']);
    }
}
