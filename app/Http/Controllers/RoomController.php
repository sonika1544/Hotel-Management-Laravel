<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Models\RoomStatus;
use App\Models\Transaction;
use App\Models\Type;
use App\Models\Facility;
use App\Repositories\Interface\ImageRepositoryInterface;
use App\Repositories\Interface\RoomRepositoryInterface;
use App\Repositories\Interface\RoomStatusRepositoryInterface;
use App\Repositories\Interface\TypeRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    private $roomRepository;
    private $typeRepository;
    private $roomStatusRepository;

    public function __construct(
        RoomRepositoryInterface $roomRepository,
        TypeRepositoryInterface $typeRepository,
        RoomStatusRepositoryInterface $roomStatusRepository
    ) {
        $this->roomRepository = $roomRepository;
        $this->typeRepository = $typeRepository;
        $this->roomStatusRepository = $roomStatusRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->roomRepository->getRoomsDatatable($request);
        }

        $types = $this->typeRepository->getTypeList($request);
        $roomStatuses = $this->roomStatusRepository->getRoomStatusList($request);

        return view('room.index', [
            'types' => $types,
            'roomStatuses' => $roomStatuses,
        ]);
    }

    public function create()
    {
        $types = $this->typeRepository->getTypeList(request());
        $roomstatuses = RoomStatus::all();
        $facilities = Facility::where('is_active', true)->get();
        
        // Group facilities by category
        $facilitiesByCategory = $facilities->groupBy('category');
        $categories = Facility::getCategories();
        
        $view = view('room.create', [
            'types' => $types,
            'roomstatuses' => $roomstatuses,
            'facilities' => $facilities,
            'facilitiesByCategory' => $facilitiesByCategory,
            'categories' => $categories
        ])->render();

        return response()->json([
            'view' => $view,
        ]);
    }

    public function store(StoreRoomRequest $request)
    {
        try {
            \DB::beginTransaction();

            // Create the room
            $room = Room::create($request->except('facilities'));
            
            // Attach facilities if provided
            if ($request->has('facilities')) {
                $room->facilities()->attach($request->facilities);
                \Log::info('Facilities attached to room: ' . $room->id, ['facilities' => $request->facilities]);
            }

            \DB::commit();

            return response()->json([
                'message' => 'Room '.$room->number.' created successfully',
                'room' => $room->load('facilities')
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating room: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error creating room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Room $room)
    {
        $room->load(['type', 'roomStatus', 'facilities']);
        
        if (request()->ajax()) {
            return view('room.show', compact('room'));
        }
        
        return view('room.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $room->load(['type', 'roomStatus', 'facilities']);
        $types = $this->typeRepository->getTypeList(request());
        $roomStatuses = $this->roomStatusRepository->getRoomStatusList(request());
        $facilities = Facility::all();

        if (request()->ajax()) {
            return view('room.edit', compact('room', 'types', 'roomStatuses', 'facilities'));
        }

        return view('room.edit', compact('room', 'types', 'roomStatuses', 'facilities'));
    }

    public function update(StoreRoomRequest $request, Room $room)
    {
        try {
            \DB::beginTransaction();

            $data = $request->validated();
            
            // Update room
            $room = $this->roomRepository->update($room->id, $data);

            // Sync facilities if provided
            if (isset($data['facilities'])) {
                $room->facilities()->sync($data['facilities']);
            }

            \DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Room has been updated successfully!',
                    'room' => $room
                ]);
            }

            return redirect()->route('room.index')->with('success', 'Room has been updated!');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating room: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Error updating room: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error updating room: ' . $e->getMessage());
        }
    }

    public function destroy(Room $room)
    {
        try {
            // Check if room is being used in any transactions
            if ($room->transactions()->exists()) {
                throw new \Exception('Cannot delete room that has transactions');
            }

            // Delete room
            $this->roomRepository->destroy($room->id);

            if (request()->ajax()) {
                return response()->json([
                    'message' => 'Room has been deleted successfully!'
                ]);
            }

            return redirect()->route('room.index')->with('success', 'Room has been deleted!');
        } catch (\Exception $e) {
            \Log::error('Error deleting room: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'message' => 'Error deleting room: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error deleting room: ' . $e->getMessage());
        }
    }

    public function datatable(Request $request)
    {
        return $this->roomRepository->getRoomsDatatable($request);
    }
}
