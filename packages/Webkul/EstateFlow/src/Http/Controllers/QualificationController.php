<?php
namespace Webkul\EstateFlow\Http\Controllers;
use Illuminate\Routing\Controller; use Webkul\EstateFlow\Http\Requests\QualifyLeadRequest; use Webkul\EstateFlow\Services\LeadQualificationService;
class QualificationController extends Controller { public function __invoke(QualifyLeadRequest $request, LeadQualificationService $service) { $data=$request->validated(); $leadId=(int)$data['lead_id']; unset($data['lead_id']); $channel=$data['channel']??'webform'; unset($data['channel']); return response()->json($service->record($leadId,auth()->id(),$data,$channel)); } }
