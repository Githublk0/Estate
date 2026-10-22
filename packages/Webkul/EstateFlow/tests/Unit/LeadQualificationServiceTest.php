<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase; use Webkul\EstateFlow\Services\LeadQualificationService;
class LeadQualificationServiceTest extends TestCase { public function test_complete_answers_are_hot(): void { $result=(new LeadQualificationService())->score(['budget'=>'700k','location'=>'Austin','property_type'=>'2 bed','timeline'=>'30 days','wants_viewing'=>true]); $this->assertSame(100,$result['score']); $this->assertSame('Hot',$result['status']); $this->assertFalse($result['needs_human_review']); } }
