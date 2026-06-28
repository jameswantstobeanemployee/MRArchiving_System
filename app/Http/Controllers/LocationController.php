<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Shelf;
use App\Models\FolderBox;
use App\Models\AuditLog;
use App\Models\ArchivedChart;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // ===================== ROOMS =====================
    public function roomsIndex()
    {
        $rooms = Room::withCount('shelves')->orderBy('name')->paginate(20);

        $orphanedCount = ArchivedChart::whereNull('physical_location_id')
            ->where('status', '!=', 'destroyed')
            ->count();

        return view('locations.rooms.index', compact('rooms', 'orphanedCount'));
    }

    public function roomCreate()
    {
        return view('locations.rooms.create');
    }

    public function roomStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20|unique:rooms,code',
            'building'    => 'nullable|string|max:100',
            'floor'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $room = Room::create($data);
        AuditLog::record('create_room', 'rooms', $room->id, null, $room->toArray());
        return redirect()->route('locations.rooms.index')->with('success', 'Room created.');
    }

    public function roomEdit(Room $room)
    {
        return view('locations.rooms.edit', compact('room'));
    }

    public function roomUpdate(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:20|unique:rooms,code,' . $room->id,
            'building'    => 'nullable|string|max:100',
            'floor'       => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $old = $room->toArray();
        $room->update($data);
        AuditLog::record('update_room', 'rooms', $room->id, $old, $room->toArray());
        return redirect()->route('locations.rooms.index')->with('success', 'Room updated.');
    }

    public function roomShow(Room $room)
    {
        $room->load(['shelves.folderBoxes']);
        return view('locations.rooms.show', compact('room'));
    }

    public function roomDestroy(Room $room)
    {
        $affectedCharts = ArchivedChart::whereHas('physicalLocation.shelf', function ($q) use ($room) {
            $q->where('room_id', $room->id);
        })->count();

        ArchivedChart::whereHas('physicalLocation.shelf', function ($q) use ($room) {
            $q->where('room_id', $room->id);
        })->update(['physical_location_id' => null]);

        foreach ($room->shelves as $shelf) {
            foreach ($shelf->folderBoxes as $box) {
                AuditLog::record('delete_box', 'folder_boxes', $box->id, $box->toArray(), null);
                $box->delete();
            }
            AuditLog::record('delete_shelf', 'shelves', $shelf->id, $shelf->toArray(), null);
            $shelf->delete();
        }

        AuditLog::record('delete_room', 'rooms', $room->id, $room->toArray(), null);
        $room->delete();

        return redirect()->route('locations.rooms.index')
            ->with('success', "Room deleted. {$affectedCharts} chart(s) have been orphaned and need to be reassigned.");
    }

    // ===================== SHELVES =====================
    public function shelfCreate(Room $room)
    {
        return view('locations.shelves.create', compact('room'));
    }

    public function shelfStore(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:30|unique:shelves,code',
            'section'     => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $data['room_id'] = $room->id;
        $shelf = Shelf::create($data);
        AuditLog::record('create_shelf', 'shelves', $shelf->id, null, $shelf->toArray());
        return redirect()->route('locations.rooms.show', $room)->with('success', 'Shelf added.');
    }

    public function shelfEdit(Room $room, Shelf $shelf)
    {
        return view('locations.shelves.edit', compact('shelf'));
    }

    public function shelfUpdate(Request $request, Room $room, Shelf $shelf)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'required|string|max:30|unique:shelves,code,' . $shelf->id,
            'section'     => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $old = $shelf->toArray();
        $shelf->update($data);
        AuditLog::record('update_shelf', 'shelves', $shelf->id, $old, $shelf->toArray());
        return redirect()->route('locations.rooms.show', $room)->with('success', 'Shelf updated.');
    }

    public function shelfDestroy(Room $room, Shelf $shelf)
    {
        $roomId = $shelf->room_id;

        $affectedCharts = ArchivedChart::whereHas('physicalLocation', function ($q) use ($shelf) {
            $q->where('shelf_id', $shelf->id);
        })->count();

        ArchivedChart::whereHas('physicalLocation', function ($q) use ($shelf) {
            $q->where('shelf_id', $shelf->id);
        })->update(['physical_location_id' => null]);

        foreach ($shelf->folderBoxes as $box) {
            AuditLog::record('delete_box', 'folder_boxes', $box->id, $box->toArray(), null);
            $box->delete();
        }

        AuditLog::record('delete_shelf', 'shelves', $shelf->id, $shelf->toArray(), null);
        $shelf->delete();

        return redirect()->route('locations.rooms.show', $roomId)
            ->with('success', "Shelf deleted. {$affectedCharts} chart(s) have been orphaned and need to be reassigned.");
    }

    // ===================== BOXES =====================
    public function boxCreate(Shelf $shelf)
    {
        $defaultCapacity = \App\Models\SystemSetting::getValue('box_default_capacity', 50);
        return view('locations.boxes.create', compact('shelf', 'defaultCapacity'));
    }

    public function boxStore(Request $request, Shelf $shelf)
    {
        $data = $request->validate([
            'box_number'  => 'required|string|max:20',
            'box_code'    => 'required|string|max:50|unique:folder_boxes,box_code',
            'capacity'    => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string',
        ]);
        $data['shelf_id'] = $shelf->id;
        $box = FolderBox::create($data);
        AuditLog::record('create_box', 'folder_boxes', $box->id, null, $box->toArray());
        return redirect()->route('locations.rooms.show', $shelf->room_id)->with('success', 'Box added.');
    }

    public function boxEdit(FolderBox $box)
    {
        return view('locations.boxes.edit', compact('box'));
    }

    public function boxUpdate(Request $request, FolderBox $box)
    {
        $data = $request->validate([
            'box_number'  => 'required|string|max:20',
            'box_code'    => 'required|string|max:50|unique:folder_boxes,box_code,' . $box->id,
            'capacity'    => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $old = $box->toArray();
        $box->update($data);
        AuditLog::record('update_box', 'folder_boxes', $box->id, $old, $box->toArray());
        return redirect()->route('locations.rooms.show', $box->shelf->room_id)->with('success', 'Box updated.');
    }

    public function boxShow(FolderBox $box)
    {
        $box->load(['shelf.room', 'activeCharts.patient']);
        return view('locations.boxes.show', compact('box'));
    }

    public function boxDestroy(FolderBox $box)
    {
        $roomId = $box->shelf->room_id;

        $affectedCharts = $box->activeCharts()->count();

        ArchivedChart::where('physical_location_id', $box->id)
            ->update(['physical_location_id' => null]);

        AuditLog::record('delete_box', 'folder_boxes', $box->id, $box->toArray(), null);
        $box->delete();

        return redirect()->route('locations.rooms.show', $roomId)
            ->with('success', "Box deleted. {$affectedCharts} chart(s) have been orphaned and need to be reassigned.");
    }

    public function getShelvesByRoom(Room $room)
    {
        $shelves = $room->activeShelves()->orderBy('name')->get(['id', 'name', 'code']);
        return response()->json($shelves);
    }

    public function getBoxesByShelf(Shelf $shelf)
    {
        $boxes = $shelf->activeFolderBoxes()->orderBy('box_number')->get()->map(fn($b) => [
            'id'              => $b->id,
            'box_number'      => $b->box_number,
            'box_code'        => $b->box_code,
            'capacity'        => $b->capacity,
            'current_count'   => $b->current_count,
            'fill_percentage' => $b->fill_percentage,
            'status'          => $b->status,
            'can_accept'      => $b->canAcceptChart(),
        ]);

        return response()->json($boxes);
    }
}