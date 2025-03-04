<?php

namespace App\Http\Controllers\admincpapi;

use App\Models\OpenAIConfiguration;
use App\Helpers\Utility;
use App\Models\AdminUser;
use App\Enum\IsPreviewEnum;
use App\Service\FileService;
use Illuminate\Http\Request;
use App\Enum\ActiveStatusEnum;
use App\Http\Traits\WebResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;

class OpenAIConfigurationController extends Controller
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }
   
  public function store(Request $request) 
    {
        $request->validate([
            'ai_service'                 => 'required',
            'base_url'                   => 'required',
            'version'                    => 'required',
            'key'                        => 'required',
            'assistant_api_version'      => 'required',
            'whisper_deployment'         => 'required',
            'whisper_api_version'        => 'required',
            'tts_hd_base_url'            => 'required',
            'tts_hd_key'                 => 'required',
            'tts_hd_api_version'         => 'required',
            'tts_hd_voice'               => 'required',
            'tts_hd_output_audio_format' => 'required',
            'tts_hd_model_deployment'    => 'required',
            'tts_hd_model_voice'         => 'required',
            'model_deployment_3_5'       => 'required',
            'model_deployment_3_5_16k'   => 'required',
            'model_deployment_4'         => 'required',
            'model_deployment_4_32k'     => 'required',
            'token_multiplier_3_5'       => 'required',
            'token_multiplier_3_5_16k'   => 'required',
            'token_multiplier_4'         => 'required',
            'token_multiplier_4_32k'     => 'required',
            'timeout_3_5'                => 'required',
            'timeout_3_5_16k'            => 'required',
            'timeout_4'                  => 'required',
            'timeout_4_32k'              => 'required',
            'rate_limit'                 => 'required|integer',
            'expires_in'                 => 'required|integer'
        ]);

         $query = OpenAIConfiguration::first();

        if (!empty($query)) {
            $query->update(request()->all());
        } else {
            $query = OpenAIConfiguration::create(request()->all());
        }

        return response()->json($query, 200);

    }

    public function get_openaiconfiguration()
    {
        $data = OpenAIConfiguration::all();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'No configurations found'], 200);
        }

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'OpenAI Configurations Successfully'
        ], 200);
    }
  
}
