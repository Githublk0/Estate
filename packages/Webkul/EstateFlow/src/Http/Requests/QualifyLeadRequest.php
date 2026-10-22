<?php
namespace Webkul\EstateFlow\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class QualifyLeadRequest extends FormRequest { public function authorize(): bool { return true; } public function rules(): array { return ['lead_id'=>['required','integer','min:1'],'budget'=>['nullable','string','max:120'],'location'=>['nullable','string','max:160'],'property_type'=>['nullable','string','max:80'],'timeline'=>['nullable','string','max:80'],'wants_viewing'=>['nullable','boolean'],'channel'=>['nullable','string','max:40']]; } }
