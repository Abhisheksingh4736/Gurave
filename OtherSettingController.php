<?php

namespace App\Http\Controllers\admincpapi;

use App\Models\OtherSetting;
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

class OtherSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token_value_per_rupee'  => 'required|integer|min:1',
            'buy_token_worth_rupees' => 'nullable|string',
            'allow_share'            => 'required|boolean',
            'allow_download'         => 'required|boolean',
        ]);

        $query = OtherSetting::first();

        if (!empty($query)) {
            $query->update(request()->all());
        } else {
            $query = OtherSetting::create(request()->all());
        }

        return response()->json([
            'status' => true,
            'data' => $query,
            'message' => 'OtherSetting Created Successfully'
        ], 200);
    }



    public function get_OtherSettingConfiguration()
        {
            $data = OtherSetting::all();

            if ($data->isEmpty()) {
                return response()->json(['message' => 'No configurations found'], 200);
            }

            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'OtherSetting List Successfully'
            ], 200);
        }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
