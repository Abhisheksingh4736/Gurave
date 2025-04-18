<?php

namespace App\Http\Controllers\admincpapi;

use App\DataTables\PromptsDataTable;
use App\Enum\ActiveStatusEnum;
use App\Enum\CategoryApplicableEnum;
use App\Enum\PriceAvailabilityEnum;
use App\Helpers\Utility;
use App\Http\Controllers\Controller;
use App\Http\Requests\admincp\PromptStoreRequest;
use App\Http\Requests\admincp\PromptUpdateRequest;
use App\Http\Traits\WebResponse;
use App\Models\Prompt;
use App\Models\OutputType;
use App\Models\custom_fields;
use App\Service\CategoryService;
use App\Service\FileService;
use App\Service\PromptService;
use App\Service\TagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Service\PromptDropdownService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\PromptDropdownValues;
use App\Models\PromptTags;


class PromptController extends Controller
{
    // use  WebResponse;

    private PromptService $promptService;
    private CategoryService $categoryService;
    private PromptDropdownService $promptDropdownService;
    private TagService $tagService;
    private FileService $fileService;

    /**
     * Default constructor
     *
     * @param PromptService $promptService
     */
    public function __construct(PromptService $promptService, CategoryService $categoryService, TagService $tagService, FileService $fileService,PromptDropdownService $promptDropdownService)
    {
        $this->promptService = $promptService;
        $this->categoryService = $categoryService;
        $this->tagService = $tagService;
        $this->fileService = $fileService;
        $this->promptDropdownService = $promptDropdownService;
    }

    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        try {
            // $page = $request->input('page', 1);
            // $page_size = $request->input(1000, 10000);
    
            $data = Prompt::with('categoryss');
             if ($request->filled('status')) {
                $data->where('is_active', $request->status);
             }
             if ($request->filled('name')) {
                $data->where('name', 'LIKE', '%' . trim($request->name) . '%');
             }
             if ($request->filled('output_type')) {
                $data->where('output_type', $request->output_type);
             }
             if ($request->filled('category_id')) {
                $data->where('category_id', (int)$request->category_id);
             }
             if ($request->filled('category_name')) {
                $query->whereHas('category', function ($subQuery) use ($request) {
                    $subQuery->where('name', 'LIKE', '%' . trim($request->category_name) . '%');
                });
             }
             if ($request->filled('open_ai_version')) {
                $data->where('open_ai_version', $request->open_ai_version);
             }
             if ($request->filled('input_value')) {
                $data->where('input_value', 'LIKE', '%' . trim($request->input_value) . '%');
             }
             if ($request->filled('description')) {
                $data->where('description', 'LIKE', '%' . trim($request->description) . '%');
             }
             if ($request->filled('display_order')) {
                $data->where('display_order', (int)$request->display_order);
             }
             if ($request->filled('price_availability')) {
                $data->where('price_availability', (int)$request->price_availability);
             }
     
            $data = $data->orderBy('id', 'desc')->get();
    
            $prompt_array = array();
            if (is_object($data)) {
                foreach ($data as $key => $data1) {
                    $prompt_array[] = $data[$key];
                }
            }
    
            $data_array = array();
            $page = (isset($request->page)) ? $request->page : '';
            $limit = (isset($request->page_size)) ? $request->page_size : '';
           
            $pagination = array();
            if ($page != '' && $limit != '') {
                $offset = ($page - 1) * $limit;
                for ($i = 0; $i < $limit; $i++) {
                    if (isset($prompt_array[$offset])) {
                           $data_array[] = $prompt_array[$offset];
                          
                     }
                    $offset++;
                }
                
            } else {
    
                $data_array = $prompt_array;
                
            } 
           
            return response()->json(['status' => true,'message' => 'prompt List', 'data' => $data_array,
                'pagination' => [
                    'current_page' => (int) $page,
                    'per_page' => $limit,
                    'total_pages' => $limit ? ceil(count($prompt_array) / $limit) : ceil(count($prompt_array)),
                    'total_records' => count($prompt_array),
                ]
            ]);
 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching Prompt details.',
                'errors' => $e->getMessage()
            ]);
        }
    }
    
    public function get_output_type()
    {
        $data =OutputType::select('id','name','status','is_active')->get();
        if(count($data)>0)
        {  
         return array('Result'=>$data , 'API_Status' => 1, 'Message' => " Output Type List");
        }
        else
        {
         return array('Result'=>[], 'API_Status' => 0, 'Message' => "No Data Found");
        }  
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categoryList = $this->categoryService->getByStatus(ActiveStatusEnum::Active->value, CategoryApplicableEnum::Prompt);
        $tagList = $this->tagService->getByStatus(ActiveStatusEnum::Active->value, ActiveStatusEnum::Active);
        $fileType = config('teachai.admin.prompt.file.type');
        $fileSize = Utility::convertBytesToMegabytes(config('teachai.admin.prompt.file.size'));
        $fileSizeFormat = Utility::formatBytes(config('teachai.admin.prompt.file.size'));

        $fileTypeMeta = config('teachai.admin.prompt.meta-file.type');
        $fileSizeMeta = Utility::convertBytesToMegabytes(config('teachai.admin.prompt.meta-file.size'));
        $fileSizeFormatMeta = Utility::formatBytes(config('teachai.admin.prompt.meta-file.size'));

        return view('admincp.prompt.add', [
            'categorys' => $categoryList,
            'tags' => $tagList,
            'fileType' => $fileType,
            'fileSize' => $fileSize,
            'fileSizeFormat' => $fileSizeFormat,

            'fileTypeMeta' => $fileTypeMeta,
            'fileSizeMeta' => $fileSizeMeta,
            'fileSizeFormatMeta' => $fileSizeFormatMeta,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromptStoreRequest $promptStoreRequest)
    {
    
       if(!$promptStoreRequest->prompt_id){

            $validator = Validator::make($promptStoreRequest->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255', // Adjust the max length as needed
                    Rule::unique('prompts', 'name')->whereNull('deleted_at'),
                ],
                // Add other field validations here as needed
            ]);
            
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

       }


        DB::beginTransaction();
        try {
            $requestData = [
                'prompt_id' => $promptStoreRequest->prompt_id ?? null, 
                'name' => $promptStoreRequest->name,
                'output_type' => $promptStoreRequest->output_type,
                'input_value' => $promptStoreRequest->input_value ?? null,
                'description' => $promptStoreRequest->description,
                'category_id' => $promptStoreRequest->category_id,
                'tags_id' => $promptStoreRequest->tags_id,
                'open_ai_version' => $promptStoreRequest->open_ai_version ?? null,
                'display_order' => $promptStoreRequest->display_order,
                'is_active' => '1',
                'gtm_tracking_id' => $promptStoreRequest->gtm_tracking_id ?? null,
                'default_output' => $promptStoreRequest->default_output ?? null,
                'price_availability' => $promptStoreRequest->price_availability ?? PriceAvailabilityEnum::Paid->value,
                'index_name' => $promptStoreRequest->index_name ?? null,
                'help_text' => $promptStoreRequest->help_text ?? null,
                'math_input' => $promptStoreRequest->math_input ?? 0,
                'append_mapped_entity_content' => $promptStoreRequest->append_mapped_entity_content ?? 0,
                'single_input_label' => $promptStoreRequest->single_input_label ?? null,
                'single_input_placeholder' => $promptStoreRequest->single_input_placeholder ?? null,
                'multi_input_label' => $promptStoreRequest->multi_input_label ?? null,
                'multi_input_placeholder' => $promptStoreRequest->multi_input_placeholder ?? null,
                'dropdown_label' => $promptStoreRequest->dropdown_label ?? null,
                'dropdown_values' => $promptStoreRequest->dropdown_values ?? [],
                'prompt_user_input' => $promptStoreRequest->prompt_user_input ?? null,
                'app_module' => $promptStoreRequest->app_module ?? '',
                'app_submodule' => $promptStoreRequest->app_submodule ?? '',
                'meta_title' => $promptStoreRequest->meta_title ?? null,
                'meta_description' => $promptStoreRequest->meta_description ?? null,
            ];
            // dd($requestData);
            // Handle uploaded files if any
            if (!empty($promptStoreRequest->uploaded_files)) {
                $base64String = explode(',', $promptStoreRequest->uploaded_files)[1] ?? null;
                if ($base64String) {
                    $imageData = base64_decode($base64String);
                    $requestData['uploaded_files'] = $this->fileService->moveToPromptCollection($imageData); // Adjust file service as needed
                }
            }
    
            $prompt = $this->promptService->save($requestData);
    
            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $prompt->load('alltags') // Include related tags if necessary
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Oops!!!, something went wrong, please try again.',
                'error' => $exception->getMessage()
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Oops!!!, something went wrong, please try again.',
                'error' => $exception->getMessage()
            ]);
        }
    }
    

    public function detailsastore(Request $request)
    {
       
        // Validate the request
        $validator = $this->validatePromptData($request->all());

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422); // Unprocessable Entity
        }

        DB::beginTransaction();

        try {
            
            // Use the validated data
            $validatedData = $validator->validated(); // Get the validated data
  
            // Use the service to store the data
            $this->promptDropdownService->storePromptData(
                $validatedData['prompt_id'], 
                $validatedData['application']
            );

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Prompt details stored successfully.',
            ]);
        } catch (\Throwable $exception) {
            // Roll back the transaction in case of an error
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Oops! Something went wrong. Please try again.',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function detailsupdate(Request $request)
    {
       
        // Validate the request
        $validator = $this->validatePromptData($request->all());
      
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422); // Unprocessable Entity
        }

        DB::beginTransaction();

        try {
            // Use the validated data
            $validatedData = $validator->validated(); // Get the validated data
           
            // Use the service to store the data
            $this->promptDropdownService->updatePromptData(
                $validatedData['prompt_id'], 
                $validatedData['application']
            );
              
            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Prompt details Update successfully.',
            ]);
        } catch (\Throwable $exception) {
            // Roll back the transaction in case of an error
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Oops! Something went wrong. Please try again.',
                'error' => $exception->getMessage(),
            ]);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Prompt $prompt)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prompt $prompt)
    {
        //dd($prompt); 
        // $categoryList = $this->categoryService->getActiveWithId($prompt->category_id, CategoryApplicableEnum::Prompt);
        // $selectTags = $prompt->tags->pluck('id')->toArray();
        // $tagList = $this->tagService->getActiveWithId($selectTags);
        // $fileType = config('teachai.admin.prompt.file.type');
        // $fileSize = Utility::convertBytesToMegabytes(config('teachai.admin.prompt.file.size'));
        // $fileSizeFormat = Utility::formatBytes(config('teachai.admin.prompt.file.size'));

        // $fileTypeMeta = config('teachai.admin.prompt.meta-file.type');
        // $fileSizeMeta = Utility::convertBytesToMegabytes(config('teachai.admin.prompt.meta-file.size'));
        // $fileSizeFormatMeta = Utility::formatBytes(config('teachai.admin.prompt.meta-file.size'));

        // $media = $this->fileService->getPromptMedia($prompt);
        // $metaMedia = $this->fileService->getPromptMetaMedia($prompt);
        // return view('admincp.prompt.edit', [
        //     'prompt' => $prompt,
        //     'categorys' => $categoryList,
        //     'tags' => $tagList,
        //     'selectTags' => $selectTags,
        //     'media' => $media,
        //     'metaMedia' => $metaMedia,
        //     'fileType' => $fileType,
        //     'fileSize' => $fileSize,
        //     'fileSizeFormat' => $fileSizeFormat,

        //     'fileTypeMeta' => $fileTypeMeta,
        //     'fileSizeMeta' => $fileSizeMeta,
        //     'fileSizeFormatMeta' => $fileSizeFormatMeta,
        // ]);

         // Fetch the Prompt data
   /*       $custom=DB::table('custom_fields')->get();
         dd( $custom); */
         /* $custom = CustomFields::get();
         dd( $custom); */
         
            $prompt = Prompt::with([
                'prompt_dropdown_values.customFields','alltags' // Eager load related dropdown values and their custom fields
            ])->find($prompt->id);
            //  dd($prompt);
            if (!$prompt) { 
                return response()->json([
                    'status' => 'error',
                    'data' => [],
                    'message' => 'Prompt not found'
                ], 404);
            }else{
                return response()->json(['status' => true, 'data' =>  $prompt, 'message' => "Prompt Data"]); 
            }
           
             
            // Build the JSON response
           /*  $response = [
                'name' => $prompt[0]->name,
                'input_value' => $prompt[0]->input_value,
                'description' => $prompt[0]->description,
                'output_type' => $prompt[0]->output_type,
                'category_id' => $prompt[0]->category_id,
                'open_ai_version' => $prompt[0]->open_ai_version,
                'display_order' => $prompt[0]->display_order,
                'is_active' => $prompt[0]->is_active,
                'gtm_tracking_id' => $prompt[0]->gtm_tracking_id,
                'price_availability' => $prompt[0]->price_availability,
                'index_name' => $prompt[0]->index_name,
                'help_text' => $prompt[0]->help_text,
                'default_output' => $prompt[0]->default_output,
                'math_input' => $prompt[0]->math_input,
                'append_mapped_entity_content' => $prompt[0]->append_mapped_entity_content,
                'single_input_label' => $prompt[0]->single_input_label,
                'single_input_placeholder' => $prompt[0]->single_input_placeholder,
                'multi_input_label' => $prompt[0]->multi_input_label,
                'multi_input_placeholder' => $prompt[0]->multi_input_placeholder,
                'dropdown_label' => $prompt[0]->dropdown_label,
                'prompt_user_input' => $prompt[0]->prompt_user_input,
                'meta_title' => $prompt[0]->meta_title,
                'meta_description' => $prompt[0]->meta_description,
                'created_by' => $prompt[0]->created_by,
                'updated_by' => $prompt[0]->updated_by,
                'updated_at' => $prompt[0]->updated_at,
                'created_at' => $prompt[0]->created_at,
                'id' => $prompt[0]->id,
                'tags' => $prompt[0]->alltags->map(function ($alltags) {
                    return [
                        'id' => $alltags->id,
                        'tag_id' => $alltags->tag_id,
                    ];
                 }),
                'application' => $prompt[0]->prompt_dropdown_values->map(function ($prompt_dropdown_values) {
                    return [
                        'key' => $prompt_dropdown_values->key,
                        'value' => $prompt_dropdown_values->value,
                        'display_order' => $prompt_dropdown_values->display_order,
                        'maths' => $prompt_dropdown_values->maths,
                        'personalization' => $prompt_dropdown_values->personalization,
                        'ai_version' => $prompt_dropdown_values->ai_version,
                        'ai_model' => $prompt_dropdown_values->ai_model,
                        'custom_fields' => $prompt_dropdown_values->customFields->map(function ($customField) {
                            return [
                                'title' => $customField->title,
                                'description' => $customField->description,
                            ];
                        }),
                    ];
                }),
            ];

            return response()->json($response, 200); */

    }

    public function fetchPromptData(Request $request)
    {
        if($request->app_module =="learn"){

           
            $prompt = Prompt::with([
                'prompt_dropdown_values','alltags' 
            ])->whereIn('app_module',[$request->app_module, 'teach'])->get();
        
        } else {
            $prompt = Prompt::with([
                'prompt_dropdown_values','alltags' 
            ])->where('app_module',$request->app_module)->get();
        }        
            
            if (!$prompt) { 
                return response()->json([
                    'status' => 'error',
                    'data' => [],
                    'message' => 'Prompt not found'
                ], 404);
            }else{
                return response()->json(['status' => true, 'data' =>  $prompt, 'message' => "Prompt Data"]); 
            }      

    }
 

    /**
     * Update the specified resource in storage.
     */
    public function update(PromptUpdateRequest $promptUpdateRequest, Prompt $prompt)
    {
        // dd($promptUpdateRequest->all());

        DB::beginTransaction();
        try {
            $requestData = [
                'name' => $promptUpdateRequest->name,
                'output_type' => $promptUpdateRequest->output_type,
                'input_value' => $promptUpdateRequest->input_value ?? null,
                'description' => $promptUpdateRequest->description,
                'category_id' => $promptUpdateRequest->category_id,
                'tags_id' => $promptUpdateRequest->tags_id,
                'open_ai_version' => $promptUpdateRequest->open_ai_version ?? null,
                'display_order' => $promptUpdateRequest->display_order,
                'is_active' => '1',
                'gtm_tracking_id' => $promptUpdateRequest->gtm_tracking_id ?? null,
                'default_output' => $promptUpdateRequest->default_output ?? null,

                'price_availability' => $promptUpdateRequest->price_availability ?? PriceAvailabilityEnum::Paid->value,
                'index_name' => $promptUpdateRequest->index_name ?? null,
                'help_text' => $promptUpdateRequest->help_text ?? null,

                "math_input" => $promptUpdateRequest->math_input ?? 0,
                "append_mapped_entity_content" => $promptUpdateRequest->append_mapped_entity_content ?? 0,
                "single_input_label" => $promptUpdateRequest->single_input_label ?? null,
                "single_input_placeholder" => $promptUpdateRequest->single_input_placeholder ?? null,
                "multi_input_label" => $promptUpdateRequest->multi_input_label ?? null,
                "multi_input_placeholder" => $promptUpdateRequest->multi_input_placeholder ?? null,
                "dropdown_label" => $promptUpdateRequest->dropdown_label ?? null,
                "dropdown_values" => $promptUpdateRequest->dropdown_values ?? [],
                "prompt_user_input" => $promptUpdateRequest->prompt_user_input ?? null,
                "app_module" => $promptUpdateRequest->app_module ?? '',
                "app_submodule" => $promptUpdateRequest->app_submodule ?? '',
                "meta_title" => $promptUpdateRequest->meta_title ?? null,
                "meta_description" => $promptUpdateRequest->meta_description ?? null,
            ];
            $this->promptService->update($prompt, $requestData);

            if ($promptUpdateRequest->remove_file) {
                $this->fileService->deletePromptMedia($prompt);
            }
            if ($promptUpdateRequest->uploaded_files) {
                $this->fileService->moveToPromptCollection($prompt, $promptUpdateRequest->uploaded_files);
            }

            if ($promptUpdateRequest->remove_meta_file) {
                $this->fileService->deletePromptMetaMedia($prompt);
            }
            if ($promptUpdateRequest->uploaded_meta_files) {
                $this->fileService->moveToPromptMetaCollection($prompt, $promptUpdateRequest->uploaded_meta_files);
            }
            DB::commit();
            return response()->json(['status' => 'success','data' => $prompt,'message' => "Prompt updated successfully"]);
        } catch (\Exception $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error','message' => 'Oops!!!, something went wrong, please try again.','error' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json(['status' => 'error','message' => 'Oops!!!, something went wrong, please try again.','error' => $exception->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prompt $prompt)
    {
        DB::beginTransaction();
        try {
            if ($prompt->delete()) {
                DB::commit();
                return response()->json(['status' => 'success','data' => $prompt, 'message' => "Prompt Deleted successfully"]);

            }else{
                DB::rollback();
                return response()->json(['status' => 'error','data' => [],'message' => "Oops!!!, something went wrong, please try again."]);
            }
        } catch (\Exception $exception) {
            DB::rollback();  
         
            return response()->json(['status' => 'error', 'message' => 'Oops!!!, something went wrong, please try again.','error' => $exception->getMessage()]);
        
        } catch (\Throwable $exception) {
        
            DB::rollback();   
          
            return response()->json(['status' => 'error', 'message' => 'Oops!!!, something went wrong, please try again.', 'error' => $exception->getMessage()]);
        }
    }

    // public function get_prompt($id)
    // {
    //     $data = Prompt::find($id);

    //     if($data){
    //         return response()->json([
    //             'status'=> 'success',
    //             'data' => $data,
    //             'message' => 'Get Details Successfully',
    //         ]);
    //     }else {
    //         return response()->json([
    //               'status' => 'error',
    //               'message' => 'No data found for the given ID',
    //         ],404);
            
    //     }
    // }


public function get_prompt($id)
{
    // Retrieve the Prompt with its related tags
    $data = Prompt::with('tags')->find($id);

    if ($data) {
        return response()->json([
            'status' => 'success',
            'data' => $data, // Directly return the prompt with tags
            'message' => 'Get Details Successfully',
        ]);
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'No data found for the given ID',
        ], 404);
    }
}


    
    /**
     * Delete Multiple
     *
     * @param Request $request
     * @return void
     */
    // public function deleteMultiple(Request $request)
    // {
    //     try {
    //         $prompt = $request->rows;
    //         // if (!$this->promptService->canDelete($prompt)) {
    //         //     return $this->jsonError(__('message.error.cant-delete-bulk', ['attribute' => 'board']), 400);
    //         // }

    //         if (Prompt::destroy($prompt)) {
    //             return $this->jsonSuccess(__('message.success.delete'));
    //         }
    //     } catch (\Throwable $th) {
    //         return $this->goBackWithError(__('message.error.unknown-error'));
    //     }
    // }

    public function deleteMultiple(Request $request)
    {
        DB::beginTransaction();
        try {
            $promptIds = $request->rows;
    
            // Fetch the Prompt to be deleted
            $promptToDelete = Prompt::whereIn('id', $promptIds)->get();
    
            // Perform deletion
            if (Prompt::whereIn('id', $promptIds)->withoutStatus()->delete()) {
                DB::commit();
    
                return response()->json([
                    'status' => 'success',
                    'data' => $promptToDelete, // Return deleted prompt data
                    'message' => "Prompt deleted successfully."
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'data' => [],
                    'message' => "Error deleting Prompt, operation returned false."
                ]);
            }
        } catch (\Exception $exception) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'An exception occurred.',
                'error' => $exception->getMessage()
            ]);
        } catch (\Throwable $exception) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'A throwable occurred.',
                'error' => $exception->getMessage()
            ]);
        }
    }



    /**
     * Get list by status filter and category id
     *
     * @param Request $request
     * @return void
     */
    public function listByCategoryId(Request $request)
    {
        return $this->jsonSuccess($this->promptService->getByStatusAndCategory($request->status, $request->category_id));
    }

    /**
     * Get list by status filter and category id and tag ids
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function listByCategoryIdAndTagIds(Request $request)
    {
        $status = $request->status ?? -1;
        $categoryId = $request->category_id ?? 0;
        $tagIds = $request->tag_ids ?? [];
        return $this->jsonSuccess($this->promptService->getByStatusAndCategoryAndTagIds($status, $categoryId, $tagIds));
    }

    public function listDropdownValueByPromptId(Request $request)
    {
        return $this->jsonSuccess($this->promptService->getDropdownValueByPromptId($request->prompt_id));
    }
     // Custom validation function
     private function validatePromptData(array $data)
     {
         return Validator::make($data, [
             'prompt_id' => 'required|integer',
             'application' => 'required|array',
             'application.*.key' => 'required|string',
             'application.*.value' => 'required|string',
             'application.*.display_order' => 'required|integer',
             'application.*.description' => 'required|string',
             'application.*.maths' => 'required|boolean',
             'application.*.personalization' => 'required|boolean',
             'application.*.ai_version' => 'required|string',
             'application.*.ai_model' => 'required|string',
             'application.*.custom_fields' => 'required|array',
             'application.*.custom_fields.*.title' => 'required|string',
             'application.*.custom_fields.*.description' => 'required|string',
             'application.*.flag' => 'required|string',
            //  'application.*.icons' => 'required|string',
             'application.*.dropdown_id' => 'required|integer', 
         ]);
     }

     public function prompt_store_basedon_id(Request $request)
     {
           DB::beginTransaction();
           try {
                   $prompt= Prompt::with('prompt_dropdown_values','prompt_dropdown_values.customFields','alltags')->where('id',$request->prompt_id)->first();
                if($prompt)
                 {
                    
                   $newPrompt = $prompt->replicate();  
                   $newPrompt->created_by = auth()->id();  
                   $newPrompt->save();
                
                  DB::transaction(function () use ($prompt,$newPrompt) {

                    if($prompt->alltags)
                      {
                        foreach($prompt->alltags as $tagId)
                         {
                                $prompt_tags = new PromptTags;
                                $prompt_tags->tag_id = $tagId->tag_id;
                                $prompt_tags->prompt_id = $newPrompt->id;
                                $prompt_tags->save();
                         }
                      }

                   foreach ($prompt->prompt_dropdown_values as $data) {
                       
                       $dropdownValue = PromptDropdownValues::create([
                           'prompt_id' => $newPrompt->id,
                           'key' => $data['key'],
                           'value' => $data['value'],
                           'display_order' => $data['display_order'],
                           'description' => $data['description'],
                           'maths' => $data['maths'],
                           'personalization' => $data['personalization'],
                           'ai_version' => $data['ai_version'],
                           'ai_model' => $data['ai_model'],
                           'icons' => $data['icons'] ?? null,
                       ]);
                       
                     if ($data->customFields) {
                       $keys = [];  
                       foreach ($data->customFields as $field) {
                           custom_fields::create([
                               'prompt_id' => $newPrompt->id,
                               'dropdown_value_id' => $dropdownValue->id,
                               'title' => $field['title'],
                               'description' => $field['description'], 
                           ]);
                           $keys[] = $field['title'];
                       }
                      
                       $concatenatedKeys = implode(',', $keys);
                       PromptDropdownValues::where('id', $dropdownValue->id)->update(['custom_fields' => $concatenatedKeys]);
                     }
 
                 }
               });
               
               DB::commit();
               return response()->json(['status' => 'success', 'message' => 'Prompt details stored successfully.',],200);
            }else{
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Prompt Not Found'],404);
            }
           } catch (\Throwable $exception) {
               
               DB::rollBack();
               return response()->json(['status' => 'error', 'message' => 'Oops! Something went wrong. Please try again.','error' => $exception->getMessage(),],500);
           }

     }
}
