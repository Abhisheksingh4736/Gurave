<?php

namespace App\Http\Controllers\admincpapi;

use App\Models\User;
use App\Models\PlanInformation;
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

class PlanInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function PlanInformationList(Request $request)
    {
        $query = PlanInformation::query();
    
        if ($request->filled('applicable_type') && in_array($request->applicable_type, ['Teacher', 'Student'])) {
            $query->where('applicable_type', $request->applicable_type);
        }
    
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }
    
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%'); // Search with LIKE
        }
    
        // Get all filtered data
        $data = $query->get();  
    
        // Convert to array
        $planInformation_array = $data->toArray(); 
    
        $data_array = [];
        $page = $request->page ?? 1;
        $limit = $request->page_size ?? 10;
    
        if ($limit) {
            $offset = ($page - 1) * $limit;
            $data_array = array_slice($planInformation_array, $offset, $limit);
        } else {
            $data_array = $planInformation_array;
        }
    
        return response()->json([
            'status' => true,
            'data' => $data_array,
            'message' => 'Plan Information List',
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $limit,
                'total_pages' => ceil(count($planInformation_array) / $limit),
                'total_records' => count($planInformation_array),
            ]
        ], 200);
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
    public function PlanInformationStore(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name'                                        => 'required|string',
            'applicable_type'                             => 'required|in:Teacher,Student',
            'currency'                                    => 'required|in:Indian Rupees',
            'status'                                      => 'nullable|in:true,false',
            'popular'                                     => 'nullable|in:true,false',
            'display_order'                               => 'nullable|integer',
            'duration'                                    => 'required|in:yearly',
            'amount'                                      => 'nullable|integer',
            'full_view_access_to_all_courses'             => 'nullable|in:true,false',
            'full_create_own_courses'                     => 'nullable|in:true,false',
            'full_generate_custom_output'                 => 'nullable|in:true,false',
            'full_download_as_Doc_PPT'                    => 'nullable|in:true,false',
            'full_share_courses_to_other_users'           => 'nullable|in:true,false',
            'full_course_edit_rights_to_multiple_users'   => 'nullable|in:true,false',
            'full_custom_portal'                          => 'nullable|in:true,false',
            'full_view_access_limit'                      => 'nullable|integer',
            'full_view_access_text'                       => 'nullable|string',
            'full_image2text_limit'                       => 'nullable|integer',
            'full_image2text_text'                        => 'nullable|string',
            'full_tokens_limit'                           => 'nullable|integer',
            'full_tokens_text'                            => 'nullable|string',
            'full_pdf_upload_limit'                       => 'nullable|integer',
            'full_pdf_upload_text'                        => 'nullable|string',
            'trial_available'                             => 'nullable|in:true,false',
            'trial_days'                                  => 'nullable|integer',
            'trial_view_access_to_all_courses'            => 'nullable|in:true,false',
            'trial_create_own_courses'                    => 'nullable|in:true,false',
            'trial_generate_custom_output'                => 'nullable|in:true,false',
            'trial_download_as_Doc_PPT'                   => 'nullable|in:true,false',
            'trial_share_courses_to_other_users'          => 'nullable|in:true,false',
            'trial_course_edit_rights_to_multiple_users'  => 'nullable|in:true,false',
            'trial_custom_portal'                         => 'nullable|in:true,false',
            'trial_full_access_limit'                     => 'nullable|integer',
            'trial_image2text_limit'                      => 'nullable|integer',
            'trial_tokens_limit'                          => 'nullable|integer',
            'trial_pdf_upload_limit'                      => 'nullable|integer',
            'upload_file'                                 => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $imagePath = null;
            $imageUrl = null;

            if ($request->hasFile('upload_file')) {
                $image = $request->file('upload_file');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('public/planinformation', $imageName);
                $imageUrl = asset(env('STORAGE_URL') . '/planinformation/' . $imageName);
            }

            // Store data in database
            $validatedData['upload_file'] = $imageUrl; // Assign image URL

            $planInformation = PlanInformation::create($validatedData);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'data'    => $planInformation,
                'message' => "Plan Information stored successfully."
            ], 200);

        } catch (\Exception $exception) {
            DB::rollback();

            return response()->json([
                'status'  => 'error',
                'message' => 'Oops! Something went wrong. Please try again.',
                'error'   => $exception->getMessage()
            ], 500);
        }
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
    public function PlanInformationUpdate(Request $request, $id)
    {
        $user = Auth::user();
    
        $validatedData = $request->validate([
            'name'                                         => 'required|string',
            'applicable_type'                              => 'required|in:Teacher,Student',
            'currency'                                     => 'required|in:Indian Rupees',
            'status'                                       => 'nullable|in:true,false',
            'popular'                                      => 'nullable|in:true,false',
            'display_order'                                => 'nullable|integer',
            'duration'                                     => 'required|in:yearly',
            'amount'                                       => 'nullable|integer',
            'full_view_access_to_all_courses'              => 'nullable|in:true,false',
            'full_create_own_courses'                      => 'nullable|in:true,false',
            'full_generate_custom_output'                  => 'nullable|in:true,false',
            'full_download_as_Doc_PPT'                     => 'nullable|in:true,false',
            'full_share_courses_to_other_users'            => 'nullable|in:true,false',
            'full_course_edit_rights_to_multiple_users'    => 'nullable|in:true,false',
            'full_custom_portal'                           => 'nullable|in:true,false',
            'full_view_access_limit'                       => 'nullable|integer',
            'full_view_access_text'                        => 'nullable|string',
            'full_image2text_limit'                        => 'nullable|integer',
            'full_image2text_text'                         => 'nullable|string',
            'full_tokens_limit'                            => 'nullable|integer',
            'full_tokens_text'                             => 'nullable|string',
            'full_pdf_upload_limit'                        => 'nullable|integer',
            'full_pdf_upload_text'                         => 'nullable|string',
            'trial_available'                              => 'nullable|in:true,false',
            'trial_days'                                   => 'nullable|integer',
            'trial_view_access_to_all_courses'             => 'nullable|in:true,false',
            'trial_create_own_courses'                     => 'nullable|in:true,false',
            'trial_generate_custom_output'                 => 'nullable|in:true,false',
            'trial_download_as_Doc_PPT'                    => 'nullable|in:true,false',
            'trial_share_courses_to_other_users'           => 'nullable|in:true,false',
            'trial_course_edit_rights_to_multiple_users'   => 'nullable|in:true,false',
            'trial_custom_portal'                          => 'nullable|in:true,false',
            'trial_full_access_limit'                      => 'nullable|integer',
            'trial_image2text_limit'                       => 'nullable|integer',
            'trial_tokens_limit'                           => 'nullable|integer',
            'trial_pdf_upload_limit'                       => 'nullable|integer',
            'upload_file'                                  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);
    
        DB::beginTransaction();
        try {
            $planInformation = PlanInformation::findOrFail($id);
    
            if ($request->hasFile('upload_file')) {
                $image = $request->file('upload_file');
                $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('public/planinformation', $imageName);
                $imageUrl = asset(env('STORAGE_URL') . '/planinformation/' . $imageName);
                $validatedData['upload_file'] = $imageUrl;
            }
    
            $planInformation->update($validatedData);
    
            DB::commit();
    
            return response()->json([
                'status'  => 'success',
                'data'    => $planInformation,
                'message' => "Plan Information updated successfully."
            ], 200);
    
        } catch (\Exception $exception) {
            DB::rollback();
    
            return response()->json([
                'status'  => 'error',
                'message' => 'Oops! Something went wrong. Please try again.',
                'error'   => $exception->getMessage()
            ], 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $planInformation = PlanInformation::find($id);
    
        if (!$planInformation) {
            return response()->json(['status' => 'error', 'message' => 'Plan not found'], 404);
        }
    
        DB::beginTransaction();
        try {
            if ($planInformation->delete()) {
                DB::commit();
                return response()->json(['status' => 'success', 'data' => $planInformation, 'message' => "Plan Information deleted successfully"], 200);
            } else {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => "Oops!!!, something went wrong, please try again."]);
            }
        } catch (\Exception $exception) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Oops!!!, something went wrong, please try again.', 'error' => $exception->getMessage()]);
        }
    }
    

    public function get_PlanInformation($id)
    {
        $data = PlanInformation::find($id);

        if($data){
            return response()->json([
                'status'=> 'success',
                'data' => $data,
                'message' => 'Get Details Successfully',
            ]);
        }else {
            return response()->json([
                  'status' => 'error',
                  'message' => 'No data found for the given ID',
            ],404);
            
        }
    }
}
