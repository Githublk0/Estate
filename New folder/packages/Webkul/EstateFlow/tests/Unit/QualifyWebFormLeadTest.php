<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
use Webkul\EstateFlow\Services\LeadQualificationService;
class QualifyWebFormLeadTest extends TestCase
{
    public function test_missing_answers_are_safe_and_require_review(): void
    {
        $result = (new LeadQualificationService())->score([]);
        $this->assertSame(0, $result['score']);
        $this->assertSame('New', $result['status']);
        $this->assertTrue($result['needs_human_review']);
    }
}
