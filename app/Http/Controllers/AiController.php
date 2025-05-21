<?php

namespace App\Http\Controllers;

use App\Models\Ai;
use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiController extends APIController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ai::query();

        if ($request->has('portal_id')) {
            $query->where('portal_id', $request->portal_id);
        }

        $ais = $query->paginate(10);

        return $this->getSuccess(['result' => $ais]);
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
        $validator = Validator::make($request->all(), [
            'provider' => 'nullable|string',
            'activity_id' => 'nullable|string',
            'file_id' => 'nullable|string',
            'in_comment' => 'boolean',
            'status' => 'nullable|string',
            'result' => 'nullable|string',
            'symbols_count' => 'nullable|integer',
            'price' => 'nullable|float',
            'domain' => 'nullable|string',
            'user_id' => 'nullable|numeric',
            'user_name' => 'nullable|string',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|numeric',
            'entity_name' => 'nullable|string',
            'user_result' => 'nullable|json',
            'user_comment' => 'nullable|string',
            'owner_comment' => 'nullable|string',
            'user_mark' => 'nullable|string',
            'owner_mark' => 'nullable|string',
            'app' => 'nullable|string',
            'department' => 'nullable|string|in:sales,service,tmc',
            'type' => 'nullable|string|in:resume,recommendation',
            'model' => 'nullable|string',
            'portal_id' => 'nullable|exists:portals,id',
            'transcription_id' => 'nullable|exists:transcriptions,id'
        ]);

        if ($validator->fails()) {
            return $this->getError('Validation error', $validator->errors());
        }
        $portal = Portal::where('domain', $request->domain)->first();
        if ($portal) {
            $request->merge(['portal_id' => $portal->id]);
        }

        $ai = Ai::create($request->all());

        return $this->getSuccess(['result' => $ai]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ai = Ai::find($id);

        if (!$ai) {
            return $this->getError('AI not found', []);
        }

        return $this->getSuccess(['result' => $ai]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ai $ai)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $ai = Ai::find($id);

        if (!$ai) {
            return $this->errorResponse('AI not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'provider' => 'nullable|string',
            // 'activity_id' => 'nullable|string',
            // 'file_id' => 'nullable|string',
            'in_comment' => 'boolean',
            'status' => 'nullable|string',
            'result' => 'nullable|string',
            'symbols_count' => 'nullable|integer',
            'price' => 'nullable|float',
            'domain' => 'nullable|string',
            // 'user_id' => 'nullable|numeric',
            // 'user_name' => 'nullable|string',
            // 'entity_type' => 'nullable|string',
            // 'entity_id' => 'nullable|numeric',
            // 'entity_name' => 'nullable|string',
            'user_result' => 'nullable|json',
            'user_comment' => 'nullable|string',
            'owner_comment' => 'nullable|string',
            'user_mark' => 'nullable|string',
            'owner_mark' => 'nullable|string',
            'tokens_count' => 'nullable|float',
            // 'app' => 'nullable|string',
            // 'department' => 'nullable|string|in:sales,service,tmc',
            // 'type' => 'nullable|string|in:resume,recommendation',
            // 'model' => 'nullable|string|in:gpt-4o,gpt-4o-mini',
            'portal_id' => 'nullable|exists:portals,id',
            'transcription_id' => 'nullable|exists:transcriptions,id'
        ]);

        if ($validator->fails()) {
            return $this->getError('Validation error', $validator->errors());
        }

        $ai->update($request->all());

        return $this->getSuccess(['result' => $ai]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ai = Ai::find($id);

        if (!$ai) {
            return $this->getError('AI not found', []);
        }

        $ai->delete();

        return $this->getSuccess(['result' => null]);
    }

    /**
     * Get AIs by portal ID.
     */
    public function getByPortal($portalId)
    {
        $ais = Ai::where('portal_id', $portalId)->paginate(10);

        return $this->getSuccess(['result' => $ais]);
    }
}
