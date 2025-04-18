<?php

namespace App\Service;

use App\Models\Topic;
use App\Models\Prompt;
use App\Models\Chapter;
use App\Models\Subject;
use App\Models\PromptTags;
use App\Service\FileService;
use App\Models\PromptMapping;
use App\Enum\ActiveStatusEnum;
use App\Enum\PromptOutputTypeEnum;
use App\Enum\PriceAvailabilityEnum;
use App\Enum\PromptCustomInputEnum;
use App\Models\PromptDropdownValues;
use App\Enum\PromptOpenAIVersionEnum;
use App\Service\PromptMappingService;
use App\Enum\PromptCustomInputTypeEnum;
use App\Enum\ComponentCustomInputTypeEnum;
use Illuminate\Support\Facades\Storage;

class PromptService
{
    private PromptMappingService $promptMappingService;
    private FileService $fileService;
    public function __construct()
    {
        $this->promptMappingService = new PromptMappingService();
        $this->fileService = new FileService();
    }
    /**
     * save the board detail
     *
     * @param integer $userId
     * @param array $requestData
     * @param bool $failOnError
     * @return Prompt
     */
    public function save(array $requestData, bool $failOnError = true): Prompt
    { 
        $prompt = isset($requestData['prompt_id']) 
        ? Prompt::findOrFail($requestData['prompt_id'])  // Fetch existing Prompt if ID is provided
        : new Prompt;  
//dd($prompt);
        $dropdownValues = $requestData['dropdown_values'] ?? [];
       
        $prompt->name = $requestData['name'];
        $prompt->input_value = $requestData['input_value'] ?? null;
        $prompt->description = $requestData['description'];
        $prompt->output_type = PromptOutputTypeEnum::tryFrom($requestData['output_type']);
        $prompt->category_id = $requestData['category_id'];
        // $prompt->tag_id = $requestData['tag_id'];
        $prompt->open_ai_version = PromptOpenAIVersionEnum::tryFrom($requestData['open_ai_version']);
        $prompt->display_order = $requestData['display_order'];
        $prompt->is_active = ActiveStatusEnum::tryFrom($requestData['is_active']);
        $prompt->gtm_tracking_id = $requestData['gtm_tracking_id'];

        $prompt->price_availability = PriceAvailabilityEnum::tryFrom($requestData['price_availability']);
        $prompt->index_name = $requestData['index_name'];
        $prompt->help_text = $requestData['help_text'];
        $prompt->default_output = $requestData['default_output'];

        $prompt->app_module = $requestData['app_module'];
        $prompt->app_submodule = $requestData['app_submodule'];
        

        $prompt->math_input = $requestData['math_input'] ?? 0;
        $prompt->append_mapped_entity_content = $requestData['append_mapped_entity_content'] ?? 0;
        $prompt->single_input_label = $requestData['single_input_label'] ?? null;
        $prompt->single_input_placeholder = $requestData['single_input_placeholder'] ?? null;

        $prompt->multi_input_label = $requestData['multi_input_label'] ?? null;
        $prompt->multi_input_placeholder = $requestData['multi_input_placeholder'] ?? null;
        
        $prompt->dropdown_label = $requestData['dropdown_label'] ?? null;

        $prompt->prompt_user_input = $requestData['prompt_user_input'] ?? null;

        $prompt->meta_title = $requestData['meta_title'] ?? null;
        $prompt->meta_description = $requestData['meta_description'] ?? null;

        if (!empty($requestData['single_input_label']) || !empty($requestData['multi_input_label']) || !empty($requestData['dropdown_label']) || $requestData['math_input'] == 1) {
            $prompt->custom_input = PromptCustomInputEnum::Active;
        }

        if ($failOnError) {
            $prompt->saveOrFail();
        } else {
            $prompt->save();
        }

        // if (!empty($requestData['dropdown_label']) && !empty($dropdownValues)) {
        //     foreach ($dropdownValues as $dropdown) {
        //         $promptDropdown = new PromptDropdownValues;
        //         $promptDropdown->prompt_id = $prompt->id;
        //         $promptDropdown->key = $dropdown['key'];
        //         $promptDropdown->value = $dropdown['value'];
        //         $promptDropdown->display_order = $dropdown['dropdown_display_order'];

        //         if ($failOnError) {
        //             $promptDropdown->saveOrFail();
        //         } else {
        //             $promptDropdown->save();
        //         }
        //     }
        // }
        PromptTags::where('prompt_id', $prompt->id)->delete();
        if (isset($requestData['tags_id'])) {
                foreach ($requestData['tags_id'] as $tagId) {

                    $prompt_tags = new PromptTags;
                    $prompt_tags->tag_id = $tagId;
                    $prompt_tags->prompt_id = $prompt->id;
                    if ($failOnError) {
                        $prompt_tags->saveOrFail();
                    } else {
                        $prompt_tags->save();
                    }
                }
            }
        return $prompt;
    }

    /**
     * Update prompt
     *
     * @param Prompt $prompt
     * @param array $requestData
     * @param boolean $failOnError
     * @return void
     */
    public function update(Prompt $prompt, array $requestData, bool $failOnError = true)
    {
        $dropdownValues = $requestData['dropdown_values'] ?? [];
        //update prompt table
        $prompt->name = $requestData['name'];
        $prompt->input_value = $requestData['input_value'];
        $prompt->description = $requestData['description'];
        $prompt->output_type = PromptOutputTypeEnum::tryFrom($requestData['output_type']);
        $prompt->category_id = $requestData['category_id'];
        $prompt->display_order = $requestData['display_order'];
        $prompt->open_ai_version = PromptOpenAIVersionEnum::tryFrom($requestData['open_ai_version']);
        $prompt->is_active = ActiveStatusEnum::tryFrom($requestData['is_active']);
        $prompt->gtm_tracking_id = $requestData['gtm_tracking_id'];

        $prompt->price_availability = PriceAvailabilityEnum::tryFrom($requestData['price_availability']);
        $prompt->index_name = $requestData['index_name'];
        $prompt->help_text = $requestData['help_text'];
        $prompt->default_output = $requestData['default_output'];

        $prompt->math_input = $requestData['math_input'] ?? 0;
        $prompt->append_mapped_entity_content = $requestData['append_mapped_entity_content'] ?? 0;
        $prompt->single_input_label = $requestData['single_input_label'] ?? null;
        $prompt->single_input_placeholder = $requestData['single_input_placeholder'] ?? null;

        $prompt->multi_input_label = $requestData['multi_input_label'] ?? null;
        $prompt->multi_input_placeholder = $requestData['multi_input_placeholder'] ?? null;

        $prompt->dropdown_label = $requestData['dropdown_label'] ?? null;

        $prompt->prompt_user_input = $requestData['prompt_user_input'] ?? null;

        $prompt->meta_title = $requestData['meta_title'] ?? null;
        $prompt->meta_description = $requestData['meta_description'] ?? null;

        if (!empty($requestData['single_input_label']) || !empty($requestData['multi_input_label']) || !empty($requestData['dropdown_label']) || $requestData['math_input'] == 1) {
            $prompt->custom_input = PromptCustomInputEnum::Active;
        } else {
            $prompt->custom_input = PromptCustomInputEnum::Inactive;
        }

        if ($failOnError) {
            $promptDetail = $prompt->saveOrFail();
        } else {
            $promptDetail = $prompt->save();
        }

        // if (empty($requestData['dropdown_label'])) {
        //     PromptDropdownValues::where('prompt_id', $prompt->id)->delete();
        // }

        if (empty($requestData['dropdown_label']) && empty($dropdownValues)) {
            PromptDropdownValues::where('prompt_id', $prompt->id)->delete();
        }


        if (!empty($requestData['dropdown_label']) && !empty($dropdownValues)) {
            $requestIds = array_column($dropdownValues, 'id');
            $oldIds = PromptDropdownValues::where('prompt_id', $prompt->id)->pluck('id')->toArray();


            foreach ($dropdownValues as $dropdown) {
                $dropdownId = $dropdown['id'] ?? 0;
                $promptDropdown = new PromptDropdownValues;
                if ($dropdownId > 0) {
                    $promptDropdown = PromptDropdownValues::find($dropdownId);
                }
                $promptDropdown->prompt_id = $prompt->id;
                $promptDropdown->key = $dropdown['key'];
                $promptDropdown->value = $dropdown['value'];
                $promptDropdown->display_order = $dropdown['dropdown_display_order'];

                if ($failOnError) {
                    $promptDropdown->saveOrFail();
                } else {
                    $promptDropdown->save();
                }
            }
            $deleteIds = array_diff($oldIds, $requestIds);
            if (!empty($deleteIds)) {
                PromptDropdownValues::destroy($deleteIds);
            }
        }



        if ($promptDetail) {
            if (PromptTags::where('prompt_id', $prompt->id)->delete()) {
                foreach ($requestData['tags_id'] as $tagId) {
                    $prompt_tags = new PromptTags;
                    $prompt_tags->tag_id = $tagId;
                    $prompt_tags->prompt_id = $prompt->id;
                    if ($failOnError) {
                        $prompt_tags->saveOrFail();
                    } else {
                        $prompt_tags->save();
                    }
                }
            }
        }

        return $promptDetail;
    }

    /**
     * Get Board detail by status 
     * $status = -1 means get where withoutStatus all record
     *
     * @param integer $status
     * @return void
     */
    public function getByStatusAndCategory($status = -1, int $categoryId)
    {
        if ($status == -1) {
            return Prompt::withoutStatus()->where('category_id', $categoryId)->get();
        } else if (ActiveStatusEnum::tryFrom($status) == ActiveStatusEnum::Active) {
            return Prompt::where('category_id', $categoryId)->get();
        } else if (ActiveStatusEnum::tryFrom($status) == ActiveStatusEnum::Active) {
            return Prompt::inActive()->where('category_id', $categoryId)->get();
        }
    }

    //TODO: Can delete check
    /**
     * any dependant relation data exists or not 
     *
     * @param array|string $prompts
     * @return boolean
     */
    public function canDelete($prompts)
    {
        if (!is_array($prompts)) {
            $prompts = [$prompts];
        }

        $mappings = PromptMapping::whereIn('prompt_id', $prompts)->count();

        return ($mappings <= 0);
    }
    /**
     * get prompt by id and status
     * @param int $id
     * @param mixed $status
     * @return Prompt
     */
    public function getByIdAndStatus(int $id, $status = -1): Prompt
    {
        
        if ($status == -1) {
            return Prompt::withoutStatus()->find($id);
        } else if (ActiveStatusEnum::tryFrom($status) == ActiveStatusEnum::Active) {
            
            return Prompt::with('prompt_dropdown_values')->find($id);
        } else if (ActiveStatusEnum::tryFrom($status) == ActiveStatusEnum::Inactive) {
            return Prompt::inActive()->find($id);
        }
    }

    /**
     * Summary of generate subject request content
     * @param mixed $promptId
     * @param mixed $subjectId
     * @param mixed $withUserInput
     * @param mixed $userInput
     * @return array|null
     */
    public function generateSubjectRequestContent($promptId, $subjectId, $withUserInput = false, $userInput = [], $mathpixInputData = [])
    {
        $promptData = $this->getByIdAndStatus($promptId, ActiveStatusEnum::Active->value);

        if (empty($promptData)) {
            return null;
        }

        $subject = Subject::with('media')->find($subjectId);
        $content = $this->fileService->getSubjectMedia($subject);

        return $this->generateOpenAIRequest($promptData, $content, $withUserInput, $userInput, $mathpixInputData);
    }

    /**
     * Summary of generate chapter request content
     * @param mixed $promptId
     * @param mixed $subjectId
     * @param mixed $withUserInput
     * @param mixed $userInput
     * @return array|null
     */
    public function generateChapterRequestContent($promptId, $chapterId, $withUserInput = false, $userInput = [], $mathpixInputData = [])
    {
        $promptData = $this->getByIdAndStatus($promptId, ActiveStatusEnum::Active->value);

        if (empty($promptData)) {
            return null;
        }

        $chapter = Chapter::with('media')->find($chapterId);
        $content = $this->fileService->getChapterMedia($chapter);

        return $this->generateOpenAIRequest($promptData, $content, $withUserInput, $userInput, $mathpixInputData);
    }

    /**
     * Summary of generate topic request content
     * @param mixed $promptId
     * @param mixed $subjectId
     * @param mixed $withUserInput
     * @param mixed $userInput
     * @return array|null
     */
    public function generateTopicRequestContent($promptId, $topicId, $withUserInput = false, $userInput = [], $mathpixInputData = [])
    {
        
        $promptData = $this->getByIdAndStatus($promptId, ActiveStatusEnum::Active->value);
        
        if (empty($promptData)) {
            return null;
        }

        $topic = Topic::with('media')->find($topicId);
        $content = 'topic/' . $topic->file_source;
        //$content = Storage::disk('public')->get($filePath);;
       
        return $this->generateOpenAIRequest($promptData, $content, $withUserInput, $userInput, $mathpixInputData);
    }

    /**
     * Summary of generate open AI request
     * @param object|null $promptText
     * @param object|null $content
     * @param bool $withUserInput
     * @param array $userInput ["single_line_input" => value, "multi_line_input" => value, "single_list_input" => value]
     * @return array
     */
    private function generateOpenAIRequest($prompt, $content, $withUserInput = false, $userInput = [], $mathpixInputData = [])
    {
        /**
         * The $mathpixInputData may contain following information:
         * use_math_input - Is math input present or not
         * append_mapped_entity_content - If true, append chapter/topic data
         * file - The user uploaded file object
         * mathpix_response - The parsed response with raw request body for MathPix API
         */
// dd($userInput['dropdown_value']);
        $useMathInput = !empty($mathpixInputData) && $mathpixInputData["use_math_input"] === true;
        /** @var App\Support\Mathpix\MathpixResponse */
        $mathpixResponse = !empty($mathpixInputData) ? $mathpixInputData["mathpix_response"] : null;

        // if ($withUserInput || $useMathInput) {
        //     $promptText = $prompt->prompt_user_input ?? "";
        //     $promptText = $this->preparePromptWithUserInput($promptText, $userInput, $mathpixResponse?->getMathpixText());
        // } else {
             $promptText = $userInput['dropdown_value'] ?? "";
             if (isset($userInput['version'])) {
                $prompt->open_ai_version = $userInput['version'];
             }
             
        // }
        
        $fileContent = "";
        
        if (!empty($content) && Storage::disk('public')->exists($content)) {
            $fileContent = Storage::disk('public')->get($content);
        }
       

        $freshContentPrompt = config('teachai.prompt.fresh_content', '');

        // Removed fresh content prompt as per instructed by Harish Sir on 19-08-2023
        //$finalPromptText = $promptText . "\n" . $fileContent . "\n" . $freshContentPrompt;


        if ($useMathInput && !$mathpixInputData['append_mapped_entity_content']) {
            $finalPromptText = $promptText;
        } else {
            
            $finalPromptText = $promptText . "\n" . $fileContent;
        }
        
        $output = [
            "prompt" => $finalPromptText,
            "version" => $promptData->open_ai_version ?? PromptOpenAIVersionEnum::VER3_5,
        ];
    
        return $output;
    }

    /**
     * Summary of getPromptWithDropdownValues
     * @param mixed $id
     * @return mixed
     */
    public function getPromptWithInputs($id,$dropdown_id)
    {
        $prompt = Prompt::with(['prompt_dropdown_values' => function ($query) use ($dropdown_id) {
            $query->where('id', $dropdown_id);
        }])->find($id);

        $output = [];
        $output['custom_input_details'] = [];
        $output['prompt_detail'] = [];

        if (!empty($prompt)) {
            $dropdown = [];
            if (!empty($prompt->prompt_dropdown_values) && $prompt->prompt_dropdown_values->count() > 0) {
                foreach ($prompt->prompt_dropdown_values as $key => $value) {
                    $dropdown[$key]['key'] = $value->key;
                    $dropdown[$key]['value'] = $value->description;
                    $dropdown[$key]['version'] = $value->ai_version; // Access version directly from the dropdown table
                }
            }
            $output['custom_input_details'] = [
                'custom_input' => $prompt->custom_input->value,
                'single_input_label' => $prompt->single_input_label,
                'single_input_placeholder' => $prompt->single_input_placeholder,
                'multi_input_label' => $prompt->multi_input_label,
                'multi_input_placeholder' => $prompt->multi_input_placeholder,
                'dropdown_label' => $prompt->dropdown_label,
                'dropdown_values' => $dropdown,
                'math_input' => $prompt->math_input ?? 0, 
                'append_mapped_entity_content' => $prompt->append_mapped_entity_content ?? 0,
            ];
            $output['prompt_detail'] = [
                'id' => $prompt->id,
                "output_type" => $prompt->output_type,
                "open_ai_version" => $prompt->open_ai_version,
                "gtm_tracking_id" => $prompt->gtm_tracking_id,
                "default_output" => $prompt->default_output,
                "help_text" => $prompt->help_text,
            ];
        }

        return $output;
    }

    /** Prepares the prompt text with customer user input
     * 
     * @param string $prompt
     * @param array $userInput
     * @return mixed
     */
    private function preparePromptWithUserInput($prompt, array $userInput, $mathpixInputText = null)
    {
        if (empty($prompt)) {
            return $prompt;
        }
        $updatedPrompt = str_replace(array_column(PromptCustomInputTypeEnum::cases(), "value"), [
            $userInput['single_input_value'] ?? '',
            $userInput['multi_line_input_value'] ?? '',
            $userInput['dropdown_value'] ?? '',
            $mathpixInputText ?? '',
        ], $prompt);
        return $updatedPrompt;
    }

    /**
     * Summary of get prompt dropdown values by prompt id
     * @param mixed $promptId
     * @return mixed
     */
    public function getDropdownValueByPromptId($promptId)
    {
        $prompt = Prompt::with('prompt_dropdown_values:id,key,prompt_id')->where('id', $promptId)->first();
        if (!empty($prompt) && !empty($prompt->prompt_dropdown_values)) {
            return $prompt->prompt_dropdown_values;
        }
        return [];
    }
    
    /**
     * Summary of get dropdown values by key and prompt id
     * @param mixed $key
     * @param int $promptId
     * @return string|null
     */
    public function getDropdownValueByKeyAndPromptId($key, $promptId = 0)
    {
       
        if (empty($key) || $promptId == 0) {
            return null;
        } 
      
        $promptDropdownValue = PromptDropdownValues::where('id', $key);

        if ($promptId > 0) {
            $promptDropdownValue->where('prompt_id', $promptId);
        }
        $output = null;
        $promptDropdownValue = $promptDropdownValue->first();
        if (!empty($promptDropdownValue)) {
            $output = $promptDropdownValue?->description;
        }
        
        return $output;
    }

    /**
     * Generate prompt user input values
     * @param string $dropdownValue
     * @param string $singleInputValue
     * @param string $multiLineInputValue
     * @return array
     */
    public function buildUserInput($dropdownValue, $singleInputValue, $multiLineInputValue): array
    {
        return [
            "dropdown_value" => $dropdownValue,
            "single_input_value" => $singleInputValue,
            "multi_line_input_value" => $multiLineInputValue,
        ];
    }

    /**
     * create and edit time check dropdown value must be one blank validation check
     * @return bool
     */
    public function checkDropdownValueValidation($outputVersions, $promptId)
    {
        $DropdownValueArray = $this->getDropdownValueByPromptId($promptId);
       
        $emptyDropdownCount = 0;
        $notEmptydropdownCount = 0;
        if (!empty($outputVersions) && !empty($DropdownValueArray) && count($DropdownValueArray) > 0)
         { 
            foreach ($outputVersions as $outputVersion) {
                
                if (!empty($outputVersion['dropdown_key'])) {
                    
                    $notEmptydropdownCount++;
                } else {
                    $emptyDropdownCount++;
                }

            }
            if ($emptyDropdownCount < 1) {
               
                return true;
            }
        }
        return false;
    }

    /**
     * Get Prompts detail by status and category id and tag ids
     * $status = -1 means get where withoutStatus all record
     * 
     * @param mixed $status
     * @param int $categoryId
     * @param array $tagIds
     * @return mixed
     */
    public function getByStatusAndCategoryAndTagIds($status = -1, int $categoryId, array $tagIds)
    {
        if ($status == -1) {

            $prompts = Prompt::select(['prompts.id', 'prompts.name']);
            $prompts->withoutStatus();
            if (!empty($tagIds)) {
                $prompts->WhereHas('tags', function ($q) use ($tagIds) {
                    return $q->whereIn('prompt_tags.tag_id', $tagIds);
                });
            }
            if ($categoryId > 0) {
                $prompts->where('prompts.category_id', $categoryId);
            }
            $prompts->orderBy('prompts.display_order', 'ASC');
            return $prompts->get();
        } else if (ActiveStatusEnum::tryFrom($status) == ActiveStatusEnum::Active) {

            $prompts = Prompt::select(['prompts.id', 'prompts.name']);
            if (!empty($tagIds)) {
                $prompts->WhereHas('tags', function ($q) use ($tagIds) {
                    return $q->whereIn('prompt_tags.tag_id', $tagIds);
                });
            }
            if ($categoryId > 0) {
                $prompts->where('prompts.category_id', $categoryId);
            }
            $prompts->orderBy('prompts.display_order', 'ASC');
            return $prompts->get();
        } else if (ActiveStatusEnum::tryFrom($status) == ActiveStatusEnum::Active) {
            $prompts = Prompt::select(['prompts.id', 'prompts.name']);
            $prompts->inActive();
            if (!empty($tagIds)) {
                $prompts->WhereHas('tags', function ($q) use ($tagIds) {
                    return $q->whereIn('prompt_tags.tag_id', $tagIds);
                });
            }
            if ($categoryId > 0) {
                $prompts->where('prompts.category_id', $categoryId);
            }
            $prompts->orderBy('prompts.display_order', 'ASC');
            return $prompts->get();
        }
    }

    /**
     * get all promtp with id as key and value as name array for filter 
     * @return mixed
     */
    public function getAllForFilter()
    {
        return Prompt::withoutStatus()->pluck('name', 'id')->toArray();
    }

    /**
     * Returns the prompt text to generate a topic summary.
     * 
     * @return mixed|\Illuminate\Config\Repository
     */
    public function getGenerateTopicSummaryPrompt()
    {
        return config('teachai.book.prompt_generate_topic_summary');
    }

    /**
     * Returns the topic content generation prompt.
     * 
     * @return mixed|\Illuminate\Config\Repository
     */
    public function getGenerateTopicContentPrompt()
    {
        return config('teachai.book.prompt_generate_topic_content_using_outline', '');
    }

    /**
     * Returns the rectify content prompt.
     * 
     * @return mixed|\Illuminate\Config\Repository
     */
    public function getRectifyContentPrompt()
    {
        return config('teachai.book.prompt_rectify_content', '');
    }

    /**
     * Returns the default version to use with AI Completion requests.
     */
    public function getAICompletionModelVersion()
    {
        return PromptOpenAIVersionEnum::tryFrom(config('teachai.book.open_ai_version', '4'));
    }

    /**
     * Returns the prompt text to re-phrase a topic content.
     * @param mixed $promptText
     * @param mixed $location
     * @return array|string|null
     */
    public function getTopicRePhrasePrompt($promptText, $location = [])
    {

        // Check prompt text is empty or not.
        if (trim($promptText) == "") {
            return "";
        }

        $locationArray = [];
        // Check city is empty or not, If the not empty city then concatenate city.
        if (!empty($location['city'])) {
            $locationArray[] = $location['city'];
        }
        // Check state is empty, if not empty then concatenate with location.
        if (!empty($location['state'])) {
            $locationArray[] = $location['state'];
        }
        // Check country is empty, if not empty then concatenate with location.
        if (!empty($location['country'])) {
            $locationArray[] = $location['country'];
        }

        $locationText = '';
        if (!empty(implode(', ', $locationArray))) {
            // Prepare the location text.
            $locationText = " location as " . implode(', ', $locationArray);
        }

        $ageText = '';
        // Check age group is empty or not.
        if (!empty($location['age_group'])) {
            // Prepare the age group text.
            $ageText = " age as " . $location['age_group'];
        }

        return $this->prepareLocationPromptText($promptText, $locationText, $ageText);
    }


    /**
     * Summary of prepareLocationPromptText
     * @param string $promptText
     * @param string $locationText
     * @param string $ageText
     * @return array|string|null
     */
    public function prepareLocationPromptText(string $promptText, string $locationText, string $ageText)
    {
        $locationPresent = (strpos($promptText, ComponentCustomInputTypeEnum::Location->value) !== false && !empty($locationText));
        $agePresent = (strpos($promptText, ComponentCustomInputTypeEnum::AgeGroup->value) !== false && !empty($ageText));

        // If the location is present then replace.
        if ($locationPresent) {
            // Replace the location text.
            $promptText = str_replace(ComponentCustomInputTypeEnum::Location->value, $locationText . ($agePresent ? ' and ' : ''), $promptText);
        } else {
            $promptText = str_replace(ComponentCustomInputTypeEnum::Location->value, '', $promptText);
        }

        // If the age is present then replace.
        if ($agePresent) {
            // Replace the age group text.
            $promptText = str_replace(ComponentCustomInputTypeEnum::AgeGroup->value, $ageText, $promptText);
        } else {
            $promptText = str_replace(ComponentCustomInputTypeEnum::AgeGroup->value, '', $promptText);
        }

        // Check location or age group is present.
        if ($locationPresent || $agePresent) {
            // Remove the context start and end tags.
            $promptText = str_replace([ComponentCustomInputTypeEnum::ContextStart->value, ComponentCustomInputTypeEnum::ContextEnd->value], '', $promptText);
        } elseif (!$locationPresent && !$agePresent) {

            // If the location and age group are not present then remove the context start and end texts.

            // Create the pattern for remove text.
            // $pattern = '/^(' . preg_quote(ComponentCustomInputTypeEnum::ContextStart->value) . '.*' . preg_quote(ComponentCustomInputTypeEnum::ContextEnd->value, "/") . ')$/mi';

            // Create the pattern for remove text.
            $pattern = '/(' . preg_quote(ComponentCustomInputTypeEnum::ContextStart->value) . '.*' . preg_quote(ComponentCustomInputTypeEnum::ContextEnd->value, "/") . ')/im';

            $promptText = preg_replace($pattern, '', $promptText);
        }

        return $promptText;
    }
}
