<?php

namespace App\Http\Controllers\admincpapi;

use App\Models\PaymentInvoiceConfiguration;
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

class PaymentInvoiceConfigurationController extends Controller
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
                    'gst_no'                        => 'nullable',
                    'gst_order_prefix'              => 'nullable', 
                    'gst_transaction_prefix'        => 'nullable', 
                    'gst_retry_limit'               => 'required|integer',
                    // 'gst_inclusive_tax'          => 'required|boolean',
                    // 'gst_tax_class'              => 'nullable|in:2,3,4,5',
                    'custom_order_prefix'           => 'nullable',
                    'custom_transaction_prefix'     => 'nullable',
                    'custom_retry_limit'            => 'nullable|integer',
                    'custom_razorpay_key_id'        => 'nullable',
                    'custom_razorpay_key_secret'    => 'nullable',
                    // 'custom_inclusive_tax'       => 'nullable|boolean',
                    // 'custom_tax_class'           => 'nullable|in:2,3,4,5',
                ]);

                $validatedData['gst_no'] = $request->gst_no; 
                $validatedData['gst_order_prefix'] = $request->gst_order_prefix;
                $validatedData['gst_transaction_prefix'] = $request->gst_transaction_prefix;
                $validatedData['gst_retry_limit'] = $request->gst_retry_limit;
                $validatedData['custom_order_prefix'] = $request->custom_order_prefix;
                $validatedData['custom_transaction_prefix'] = $request->custom_transaction_prefix;
                $validatedData['custom_retry_limit'] = $request->custom_retry_limit;
                $validatedData['custom_razorpay_key_id'] = $request->custom_razorpay_key_id;
                $validatedData['custom_razorpay_key_secret'] = $request->custom_razorpay_key_secret;
                $validatedData['gst_inclusive_tax'] = $request->gst_inclusive_tax;
                $validatedData['custom_inclusive_tax'] = $request->custom_inclusive_tax; 

                if ($request->gst_inclusive_tax == 1) {
                    
                    $validatedData['gst_tax_class'] =$request->gst_tax_class;    
                }else{
                    $validatedData['gst_tax_class'] =null;
                }
               
                 
                if ($request->custom_inclusive_tax == 1) {
                  
                    $validatedData['custom_tax_class'] =  $request->custom_tax_class; 
                }else{
                    $validatedData['custom_tax_class'] =null;
                }
                // dd($validatedData);
                $query = PaymentInvoiceConfiguration::first();

                if (!empty($query)) {
                    $query->update($validatedData);
                } else {
                    $query = PaymentInvoiceConfiguration::create($validatedData);
                }
                return response()->json([
                    'status' => true,
                    'data' => $query,
                    'message' => 'PaymentInvoice Configuration Created Successfully'
                ], 200);

    }

        public function get_paymentInvoiceconfiguration()
        {
            $data = PaymentInvoiceConfiguration::all();

            if ($data->isEmpty()) {
                return response()->json(['message' => 'No configurations found'], 200);
            }

            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'PaymentInvoice Configurations Successfully'
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
