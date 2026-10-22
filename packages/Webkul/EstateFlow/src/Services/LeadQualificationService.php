<?php
namespace Webkul\EstateFlow\Services;
use Webkul\EstateFlow\Models\Event;
class LeadQualificationService {
 public function score(array $answers): array { $score=0; $score+=!empty($answers['budget'])?25:0; $score+=!empty($answers['location'])?25:0; $score+=!empty($answers['property_type'])?20:0; $score+=!empty($answers['timeline'])?20:0; $score+=!empty($answers['wants_viewing'])?10:0; return ['score'=>$score,'status'=>$score>=75?'Hot':($score>=45?'Nurture':'New'),'needs_human_review'=>$score<75]; }
 public function record(int $leadId, ?int $userId, array $answers, string $channel='webform'): array { $result=$this->score($answers); Event::create(['lead_id'=>$leadId,'user_id'=>$userId,'event_type'=>'lead.qualified','channel'=>$channel,'payload'=>['answers'=>$answers,'result'=>$result]]); return $result; }
}
