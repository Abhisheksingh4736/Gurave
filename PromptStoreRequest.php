<?php

namespace App\Http\Requests\admincp;

use App\Enum\PromptOpenAIVersionEnum;
use App\Enum\PromptOutputTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromptStoreRequest extends FormRequest
{
    private const NAME_MAX_LENGTH = 100;
    private const DESCRIPTION_MAX_LENGTH = 150;
    private const INPUT_VALUE_MAX_LENGTH = 65500;
    private const STRING_MAX_LENGTH = 255;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        $checkDropdownLabel = (request()->has('dropdown_values') && (isset(request()->get('dropdown_values')[0]['key']) && !empty(request()->get('dropdown_values')[0]['key']) || isset(request()->get('dropdown_values')[0]['value']) && !empty(request()->get('dropdown_values')[0]['value'])));

        $validation = [
            'name' => 'required|max:' . self::NAME_MAX_LENGTH,
            'output_type' => 'required|in:' . implode(',', PromptOutputTypeEnum::values()),
            'input_value' => 'required|max:' . self::INPUT_VALUE_MAX_LENGTH,
            'description' => 'required|max:' . self::DESCRIPTION_MAX_LENGTH,
            'category_id' => 'required|numeric|exists:categories,id',
            'tags_id' => 'required|array',
            'open_ai_version' => 'required|in:' . implode(',', PromptOpenAIVersionEnum::values()),
            'display_order'   => 'required|numeric|min:1',

            'single_input_label' => [Rule::requiredIf(function () {
                return !empty(request()->get('single_input_placeholder'));
            })],

            // 'single_input_placeholder' => [Rule::requiredIf(function () {
            //     return !empty(request()->get('single_input_label'));
            // })],

            'multi_input_label' => [Rule::requiredIf(function () {
                return !empty(request()->get('multi_input_placeholder'));
            })],

            'dropdown_label' => ($checkDropdownLabel) ? 'required' : 'nullable',

            'prompt_user_input' => [Rule::requiredIf(function () {
                return (!empty(request()->get('single_input_label')) || !empty(request()->get('multi_input_label')) || !empty(request()->get('dropdown_label')));
            })],
            'meta_title' => 'max:' . self::STRING_MAX_LENGTH,
            'meta_description' => 'max:' . self::STRING_MAX_LENGTH,
        ];

        if (request()->has('dropdown_label') && !empty(request()->get('dropdown_label'))) {
            $dropdownValue = [
                'dropdown_values.*' => 'required',
                'dropdown_values.*.key' => 'required',
                'dropdown_values.*.value' => 'required',
                'dropdown_values.*.dropdown_display_order' => 'required|numeric',
            ];
            $validation = array_merge($validation, $dropdownValue);
        }
        return $validation;
    }


    /**
     * Define the error messages here.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'name.required' => __('validation.required', ['attribute' => __('label.common.attribute-name', ['attribute' => __('label.common.name')])]),
            'name.max' => __('validation.max.string', ['attribute' => __('label.common.attribute-name', ['attribute' => __('label.common.name')]), 'value' => self::NAME_MAX_LENGTH]),
            'name.unique' => __('validation.unique', ['attribute' => __('label.common.attribute-name', ['attribute' => __('label.common.name')])]),
            'output_type.required' => __('validation.required', ['attribute' => __('label.common.select-attribute', ['attribute' => __('label.module.output-type')])]),
            'input_value.required' => __('validation.required', ['attribute' => __('label.common.enter-input-values', ['attribute' => __('label.common.input-values')])]),
            'input_value.max' => __('validation.max.string', ['attribute' => __('label.common.enter-input-values', ['attribute' => __('label.common.input-values')])]),
            'description.required' => __('validation.required', ['attribute' => __('label.common.enter-description', ['attribute' => __('label.module.description')])]),
            'description.max' => __('validation.max.string', ['attribute' => __('label.common.enter-description', ['attribute' => __('label.module.description')])]),
            'category_id.required' => __('validation.required', ['attribute' => __('label.common.select-attribute', ['attribute' => __('label.module.category')])]),
            'tags_id.required' => __('validation.required', ['attribute' => __('label.common.select-attribute', ['attribute' => __('label.module.tags')])]),
            'open_ai_version.required' => __('validation.required', ['attribute' => __('label.common.select-attribute', ['attribute' => __('label.module.open-ai-version')])]),

            'meta_title.max' => __('validation.max.string', ['attribute' =>  __('label.common.meta_title')]),
            'meta_description.max' => __('validation.max.string', ['attribute' =>  __('label.common.meta_description')]),
        ];

        if (request()->has('dropdown_label') && !empty(request()->get('dropdown_label'))) {
            $dropdownMessage = [];
            foreach ($this->get('dropdown_values') as $key => $value) {
                $dropdownMessage['dropdown_values.' . $key . '.key.required'] = __("validation.required", ["attribute" => "dropdown value at row $key Key "]);
                $dropdownMessage['dropdown_values.' . $key . '.value.required'] = __("validation.required", ["attribute" => "dropdown value at row $key Value"]);
                $dropdownMessage['dropdown_values.' . $key . '.dropdown_display_order.required'] = __("validation.required", ["attribute" => "dropdown value at row $key display order"]);
                $dropdownMessage['dropdown_values.' . $key . '.dropdown_display_order.numeric'] = __("validation.numeric", ["attribute" => "dropdown value at row $key display order"]);
            }
            $messages = array_merge($messages, $dropdownMessage);
        }

        return $messages;
    }
}
