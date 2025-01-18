<?php

namespace App\Http\Controllers;

use App\Models\BxRq;
use App\Models\Portal;
use Illuminate\Http\Request;

class BxRqController extends Controller
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
        // Валидация входящих данных
        $validated = $request->validate([
            'domain' => 'required|string', // Домен портала
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'type' => 'nullable|string',
            'bitrix_id' => 'required|string',
            'xml_id' => 'required|string',
            'entity_type_id' => 'required|string',
            'country_id' => 'nullable|string',
            'is_active' => 'boolean',
            'sort' => 'nullable|integer',
        ]);
    
        // Найти портал по домену
        $portal = Portal::where('domain', $validated['domain'])->first();
    
        if (!$portal) {
            return response()->json(['error' => 'Portal not found'], 404);
        }
    
        // Создать новую запись BxRq и связать с порталом
        $bxRq = new BxRq($validated);
        $bxRq->portal()->associate($portal); // Установить связь
        $bxRq->save();
    
        // Вернуть созданную запись
        return response()->json($bxRq, 201);
    }
    
    public function get(Request $request)
{
    // Получить домен из запроса
    $domain = $request->get('domain');

    // Найти портал по домену
    $portal = Portal::where('domain', $domain)->first();

    // Проверить, найден ли портал
    if (!$portal) {
        return response()->json(['error' => 'Portal not found'], 404);
    }

    // Получить связанные записи BxRq
    $bxRqs = $portal->bxRqs;

    // Вернуть связанные записи
    return response()->json($bxRqs, 200);
}


    /**
     * Display the specified resource.
     */
    public function show(BxRq $bxRq)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BxRq $bxRq)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BxRq $bxRq)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BxRq $bxRq)
    {
        //
    }
}
