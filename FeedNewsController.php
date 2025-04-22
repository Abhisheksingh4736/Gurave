<?php

namespace App\Http\Controllers\admincpapi;

use Illuminate\Http\Request;
use App\Helpers\Utility;
use App\Models\FeedNews;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FeedNewsController extends Controller
{
    // public function store(Request $request)
    // {
    //     $user = Auth::user();
      
    //     DB::beginTransaction();
    
    //     try {
    //         $imagePath = null;
    //         if ($request->hasFile('detailed_image')) {
    //             $image = $request->file('detailed_image');
    //             $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
    //             $imagePath = $image->storeAs('/feednews', $imageName);
    //             $imagePath1 = asset('storage/app/public/feednews/' . $imageName);
    //         } else {
    //             $imageUrl = null; // Handle no image uploaded
    //         }

    //         if ($request->hasFile('short_banner')) {

    //             $file = $request->file('short_banner');
    //             $fileName = time() . '-'. $file->getClientOriginalName(); // Get the original filename
    //             $filePath = $file->storeAs('/feednews', $fileName); // Store the file
    
    //              $filePath1 = asset('storage/app/public/feednews/' . $fileName);
                
    //             $requestData['short_banner'] = $fileName;
    //         }

    //         $requestData = [
    //             'subject_id'        => $request->subject_id,
    //             'standard_id'       => $request->standard_id,
    //             'topic_id'          => $request->topic_id,
    //             'short_title'       => $request->short_title,
    //             'short_description' => $request->short_description,
    //             'short_banner'      => $request->short_banner,
    //             'detailed_title'    => $request->detailed_title,
    //             'detailed_article'  => $request->detailed_article,
    //             'detailed_image'    => $imageUrl,
    //             'faqs'              => json_encode($request->faqs),
    //             'is_active'         => $request->status ?? ActiveStatusEnum::Active->value,
    //         ];
    
    //         $feedNews = FeedNews::create($requestData);
    
    //         DB::commit();
    //         return response()->json([
    //             'data' => $feedNews,
    //             'message' => 'Feed News Created Successfully!.'  
    //         ],200);
    
    //     } catch (\Exception $exception) {
    //         DB::rollback();
    
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Oops! Something went wrong. Please try again.',
    //             'error'   => $exception->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
{
    $user = Auth::user();
    
    DB::beginTransaction();

    try {
        // Validate basic fields (faqs as string)
        $validated = $request->validate([
            'subject_id'        => 'required|integer',
            'standard_id'       => 'required|integer',
            'topic_id'          => 'required|integer',
            'short_title'       => 'required|string',
            'short_description' => 'required|string',
            'short_banner'      => 'nullable|file|mimes:jpg,jpeg,png,webp',
            'detailed_title'    => 'required|string',
            'detailed_article'  => 'required|string',
            'detailed_image'    => 'nullable|file|mimes:jpg,jpeg,png,webp',
            'faqs'              => 'required|string', // Accept JSON string
            'status'            => 'nullable|in:0,1'
        ]);

        // Decode faqs JSON string manually
        $faqs = json_decode($request->faqs, true);
        if (!is_array($faqs)) {
            throw new \Exception("Invalid format for FAQs. Must be a valid JSON array.");
        }

        // Handle short banner
        $shortBannerUrl = null;
            if ($request->hasFile('short_banner')) {
                $shortBanner = $request->file('short_banner');
                $shortBannerName = time() . '-' . $shortBanner->getClientOriginalName();
                $shortBanner->storeAs('/feednews', $shortBannerName);
                $shortBannerUrl = asset('storage/app/public/feednews/' . $shortBannerName);
            }

            $detailedImageUrl = null;
            if ($request->hasFile('detailed_image')) {
                $detailedImage = $request->file('detailed_image');
                $detailedImageName = uniqid() . '.' . $detailedImage->getClientOriginalExtension();
                $detailedImage->storeAs('/feednews', $detailedImageName);
                $detailedImageUrl = asset('storage/app/public/feednews/' . $detailedImageName);
            }

        // Prepare insert data
        $requestData = [
            'subject_id'        => $validated['subject_id'],
            'standard_id'       => $validated['standard_id'],
            'topic_id'          => $validated['topic_id'],
            'short_title'       => $validated['short_title'],
            'short_description' => $validated['short_description'],
            'short_banner'      => $shortBannerUrl,
            'detailed_title'    => $validated['detailed_title'],
            'detailed_article'  => $validated['detailed_article'],
            'detailed_image'    => $detailedImageUrl,
            'faqs'              => json_encode($faqs),
            'is_active'         => $validated['status'] ?? 1,
        ];

        $feedNews = FeedNews::create($requestData);

        DB::commit();

        return response()->json([
            'data' => $feedNews,
            'message' => 'Feed News Created Successfully!'
        ], 200);

    } catch (\Exception $exception) {
        DB::rollBack();

        return response()->json([
            'status'  => 'error',
            'message' => 'Oops! Something went wrong. Please try again.',
            'error'   => $exception->getMessage()
        ], 400);
    }
}

}
