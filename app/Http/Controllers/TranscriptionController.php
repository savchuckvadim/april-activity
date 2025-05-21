<?php

namespace App\Http\Controllers;

use App\Models\Portal;
use App\Models\Transcription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TranscriptionController extends APIController
{
    /**
     * Display a listing of the transcriptions.
     */
    public function index(Request $request)
    {
        $query = Transcription::query();

        // Filter by portal_id if provided
        if ($request->has('portal_id')) {
            $query->where('portal_id', $request->portal_id);
        }

        $transcriptions = $query->latest()->paginate(15);

        return $this->getSuccess($transcriptions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created transcription in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'sometimes|required|string|max:255',
            'activity_id' => 'sometimes|required|integer',
            'file_id' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:255',
            'duration' => 'sometimes|required|string',
            'domain' => 'sometimes|required|string|max:255',
            'user_id' => 'sometimes|required|integer',
            'user_name' => 'required|required|string|max:255',
            'entity_type' => 'required|string|max:255',
            'entity_id' => 'required|string',
            'entity_name' => 'required|string',
            'app' => 'sometimes|required|string',
            'department' => 'sometimes|required|string',



        ]);

        if ($validator->fails()) {
            return $this->getError('validation error', [$validator->errors()],);
        }
        $portal = Portal::where('domain', $request->domain)->first();
        if ($portal) {
            $request->merge(['portal_id' => $portal->id]);
        }

        $transcription = Transcription::create($request->all());

        return $this->getSuccess($transcription);
    }

    /**
     * Display the specified transcription.
     */
    public function show($id)
    {
        $transcription = Transcription::findOrFail($id);

        return $this->getSuccess($transcription);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transcription $transcription)
    {
        //
    }

    /**
     * Update the specified transcription in storage.
     */
    public function update(Request $request, $id)
    {
        $transcription = Transcription::findOrFail($id);

        $validator = Validator::make($request->all(), [
            // 'provider' => 'sometimes|required|string|max:255',
            // 'activity_id' => 'sometimes|required|integer',
            // 'file_id' => 'sometimes|required|string|max:255',
            'in_comment' => 'sometimes|boolean',
            'status' => 'sometimes|required|string|max:255',
            'text' => 'sometimes|string',
            'symbols_count' => 'sometimes|integer',
            'price' => 'sometimes|numeric',
            'duration' => 'sometimes|required|string',
            'domain' => 'sometimes|required|string|max:255',

            'report_result' => 'nullable|string',
            'user_result' => 'nullable|json',
            'user_comment' => 'nullable|string',
            'owner_comment' => 'nullable|string',
            'user_mark' => 'nullable|string',
            'owner_mark' => 'nullable|string',
            // 'user_id' => 'sometimes|required|integer',
            // 'user_name' => 'sometimes|required|string|max:255',
            // 'entity_type' => 'sometimes|string|max:255',
            // 'entity_id' => 'sometimes|integer',
            // 'entity_name' => 'sometimes|string|max:255',

            // 'portal_id' => 'sometimes|required|exists:portals,id'
        ]);

        if ($validator->fails()) {
            return $this->getError('validation error', [$validator->errors()],);
        }

        $transcription->update($request->all());

        return $this->getSuccess($transcription);
    }

    /**
     * Remove the specified transcription from storage.
     */
    public function destroy($id)
    {
        $transcription = Transcription::findOrFail($id);
        $transcription->delete();

        return $this->getSuccess(['result' => 'Transcription deleted successfully'],);
    }

    /**
     * Get transcriptions by portal ID.
     */
    public function getByPortal($portalId)
    {
        $transcriptions = Transcription::where('portal_id', $portalId)
            ->latest()
            ->paginate(15);

        return $this->getSuccess([
            'result' => $transcriptions,
        ]);
    }
}
