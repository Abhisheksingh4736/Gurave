<?php

namespace App\Http\Controllers\Api\V2;

use App\DataTables\ClassRoomsDataTable;
use App\Enum\ActiveStatusEnum;
use App\Http\Requests\admincp\ClassRoomStoreRequest;
use App\Http\Requests\admincp\ClassRoomUpdateRequest;
use App\Models\ClassRooms;
use App\Models\Standard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ClassRoomController extends Controller
{
    
        public function index()
    {
        try {
            $classRoom = ClassRooms::orderBy('id', 'desc')->get();

            return response()->json(['status' => 'success','data' => $classRoom,'message' => 'Class Room List Successfully.'], 200);

        } catch (\Exception $exception) {
            return response()->json(['status'  => 'error','message' => 'Oops! Unable to fetch Class Room list.','error'   => $exception->getMessage()], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admincp.classRoom.add');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'standard_id' => 'required|exists:standards,id',
            'status' => 'in:active,inactive'
        ]);

        $exists = ClassRooms::where('standard_id', $validatedData['standard_id'])->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Standard already exists.',
            ], 400);
        }
         DB::beginTransaction();
        try {

            $data = ClassRooms::create($validatedData);
    
            DB::commit();
    
            return response()->json([
                'status'  => 'success',
                'data'    => $data,
                'message' => "Class Room Stored Successfully."
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
    public function show(ClassRoom $classRoom)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassRoom $classRoom)
    {
        return view('admincp.classRoom.edit', ['classRoom' => $classRoom]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
    
        $validatedData = $request->validate([
            'standard_id' => 'sometimes|exists:standards,id',
            'status' => 'sometimes|in:active,inactive'
        ]);
    
        DB::beginTransaction();
        try {
            $classRoom = ClassRooms::findOrFail($id);
    
            $classRoom->update($validatedData);
    
            DB::commit();
    
            return response()->json([
                'status'  => 'success',
                'data'    => $classRoom,
                'message' => 'Class Room Updated Successfully.'
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
    // public function destroy(ClassRooms $classRoom)
    // {
    //     if (!$this->classRoomService->canDelete($classRoom->id)) {
    //         return $this->jsonError(__('message.error.cant-delete-single', ['attribute' => 'board']), 400);
    //     }

    //     if ($classRoom->delete()) {
    //         return $this->jsonSuccess(__('message.success.delete'));
    //     }

    //     return $this->jsonError(__('message.error.unknown-error'), 500);
    // }

        public function destroy(ClassRooms $classRoom)
        {
            DB::beginTransaction();

            try {
                if ($classRoom->delete()) {
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'data' => $classRoom,
                        'message' => 'Standard Mapping deleted successfully.'
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'data' => [],
                        'message' => 'Oops! Something went wrong, please try again.'
                    ]);
                }

            } catch (\Throwable $exception) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Oops! Something went wrong, please try again.',
                    'error' => $exception->getMessage()
                ]);
            }
        }


    /**
     * Delete Multiple
     *
     * @param Request $request
     * @return void
     */
    public function deleteMultiple(Request $request)
    {
        try {
            $classRoom = $request->rows;
            if (!$this->classRoomService->canDelete($classRoom)) {
                return $this->jsonError(__('message.error.cant-delete-bulk', ['attribute' => 'board']), 400);
            }

            if (ClassRooms::destroy($classRoom)) {
                return $this->jsonSuccess(__('message.success.delete'));
            }
        } catch (\Throwable $th) {
            return $this->goBackWithError(__('message.error.unknown-error'));
        }
    }

    public function ListBackened(Request $request)
        {
            try {
                $classRoom = ClassRooms::with(['standard' => function ($query) {
                    $query->select('id', 'name');
                }])
                ->where('status', 'active')
                ->orderBy('id', 'desc')
                ->get();
            

                return response()->json([
                    'status'  => 'success',
                    'data'    => $classRoom,
                    'message' => 'Standard Active Status List Successfully.'
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to fetch class room list.',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }

}
