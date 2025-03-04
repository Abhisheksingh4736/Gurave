<?php

namespace App\Http\Controllers\admincpapi;

use App\Enum\ActiveStatusEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AssessmentHeader;
use App\Models\AssessmentDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssessmentController extends Controller
{
    public function store(Request $request)
    {
        //dd("ihihk");
        $validator = Validator::make($request->all(), [
            'assessment_name' => 'required|string|max:255',
            'assessment_type' => 'required|string|max:255',
            'assessment_duration' => 'required|integer',
            'assessment_time' => 'required',
            'board_id' => 'required|integer',
            'medium_id' => 'required|integer',
            'standard_id' => 'required|integer',
            'course_id' => 'nullable|integer',
            'chapter_id' => 'nullable|array',
            'chapter_id.*' => 'integer',
            'topic_id' => 'nullable|array',
            'topic_id.*' => 'integer',
            'assessment_level' => 'required|in:beginner,intermediate,expert',
            'created_by' => 'required|integer',
            'total_marks' => 'required|integer',
            'details' => 'required|array',
            'details.*.question_type' => 'required|string|max:255',
            'details.*.no_of_question' => 'required|integer|min:1',
            'details.*.correct_marks' => 'required|integer',
            'details.*.incorrect_marks' => 'required|integer',
            'details.*.total_marks' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request) {
            $header = AssessmentHeader::create(array_merge($request->only([
                'assessment_name', 'assessment_type', 'assessment_duration', 'assessment_time',
                'board_id', 'medium_id', 'standard_id', 'course_id', 'assessment_level', 'total_marks'
            ]), [
                'chapter_id' => isset($request->chapter_id) ? implode(',', $request->chapter_id) : null,
                'topic_id' => isset($request->topic_id) ? implode(',', $request->topic_id) : null,
                // 'total_marks' => $request->total_marks,
            ]));
            
            foreach ($request->details as $detail) {
                AssessmentDetail::create(array_merge($detail, ['assessment_header_id' => $header->id]));
            }
        });
        
        return response()->json([
            'status' => true,
            'message' => 'Assessment created successfully'
        ], 200);
        
    }

    public function update(Request $request, $id)
    {
        $header = AssessmentHeader::findOrFail($id);
        $header->update($request->only([
            'assessment_name', 'assessment_type', 'assessment_duration', 'assessment_time',
            'board_id', 'medium_id', 'standard_id', 'course_id', 'assessment_level', 'total_marks'
        ]) + [
            'chapter_id' => isset($request->chapter_id) ? implode(',', $request->chapter_id) : null,
            'topic_id' => isset($request->topic_id) ? implode(',', $request->topic_id) : null,
            // 'total_marks' => $request->total_marks,
        ]);

        AssessmentDetail::where('assessment_header_id', $id)
        ->whereNotIn('id', array_column($request->details, 'id'))
        ->delete();

        foreach ($request->details as $detail) {
            if (isset($detail['id'])) {
                AssessmentDetail::where('id', $detail['id'])->update($detail);
            } else {
                AssessmentDetail::create(array_merge($detail, ['assessment_header_id' => $header->id]));
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Assessment updated successfully'
        ], 200);
    }

    public function index()
    {
        $assessments = AssessmentHeader::with([
            'board:id,name', 
            'medium:id,name', 
            'standard:id,name', 
            'course:id,name', 
            'details'
        ])
        ->get()
        ->map(function ($assessment) {
            return [
                'id' => $assessment->id,
                'assessment_name' => $assessment->assessment_name,
                'assessment_type' => $assessment->assessment_type,
                'assessment_duration' => $assessment->assessment_duration,
                'assessment_time' => $assessment->assessment_time,
                'board' => $assessment->board ? ['id' => $assessment->board->id, 'name' => $assessment->board->name] : null,
                'medium' => $assessment->medium ? ['id' => $assessment->medium->id, 'name' => $assessment->medium->name] : null,
                'standard' => $assessment->standard ? ['id' => $assessment->standard->id, 'name' => $assessment->standard->name] : null,
                'course' => $assessment->course ? ['id' => $assessment->course->id, 'name' => $assessment->course->name] : null,
                'chapters' => $assessment->chapters,
                'topics' => $assessment->topics,
                'total_marks' => $assessment->total_marks,
                'details' => $assessment->details->map(fn($detail) => [
                    'id' => $detail->id,
                    'question_type' => $detail->question_type,
                    'no_of_question' => $detail->no_of_question,
                    'correct_marks' => $detail->correct_marks,
                    'incorrect_marks' => $detail->incorrect_marks,
                    'total_marks' => $detail->total_marks
                ])
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $assessments,
            'message' => 'Assessment Data List'
        ], 200);
    }

    public function show($id)
    {
        $assessment = AssessmentHeader::with([
            'board:id,name', 
            'medium:id,name', 
            'standard:id,name', 
            'course:id,name', 
            'details'
        ])
        ->findOrFail($id);

        return response()->json([
            'id' => $assessment->id,
            'assessment_name' => $assessment->assessment_name,
            'assessment_type' => $assessment->assessment_type,
            'assessment_duration' => $assessment->assessment_duration,
            'assessment_time' => $assessment->assessment_time,
            'board' => $assessment->board ? ['id' => $assessment->board->id, 'name' => $assessment->board->name] : null,
            'medium' => $assessment->medium ? ['id' => $assessment->medium->id, 'name' => $assessment->medium->name] : null,
            'standard' => $assessment->standard ? ['id' => $assessment->standard->id, 'name' => $assessment->standard->name] : null,
            'course' => $assessment->course ? ['id' => $assessment->course->id, 'name' => $assessment->course->name] : null,
            'chapters' => $assessment->chapters,
            'topics' => $assessment->topics,
            'total_marks' => $assessment->total_marks,
            'details' => $assessment->details->map(fn($detail) => [
                'id' => $detail->id, 
                'question_type' => $detail->question_type,
                'no_of_question' => $detail->no_of_question,
                'correct_marks' => $detail->correct_marks,
                'incorrect_marks' => $detail->incorrect_marks,
                'total_marks' => $detail->total_marks
            ])
        ]);
    }

    
}
