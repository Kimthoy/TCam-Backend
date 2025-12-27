<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartnerWithUs\PartnerWithUsSection;
use App\Models\PartnerWithUs\PartnerWithUsCard;

class PartnerWithUsController extends Controller
{
    // ============================
    // Sections CRUD
    // ============================

    public function index()
    {
        $sections = PartnerWithUsSection::with('cards')->get();
        return response()->json($sections);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
        ]);

        $section = PartnerWithUsSection::create($data);

        return response()->json($section, 201);
    }

    public function show(PartnerWithUsSection $section)
    {
        $section->load('cards');
        return response()->json($section);
    }

    public function update(Request $request, PartnerWithUsSection $section)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
        ]);

        $section->update($data);

        return response()->json($section);
    }

    public function destroy(PartnerWithUsSection $section)
    {
        $section->delete();
        return response()->json(['message' => 'Section deleted successfully']);
    }

    // ============================
    // Cards CRUD
    // ============================

    public function storeCard(Request $request, PartnerWithUsSection $section)
    {
        $data = $request->validate([
            'icon' => 'required|string|max:50',
            'icon_color' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $card = $section->cards()->create($data);

        return response()->json($card, 201);
    }

    public function showCard(PartnerWithUsCard $card)
    {
        return response()->json($card);
    }

    public function updateCard(Request $request, PartnerWithUsCard $card)
    {
        $data = $request->validate([
            'icon' => 'required|string|max:50',
            'icon_color' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $card->update($data);

        return response()->json($card);
    }

    public function destroyCard(PartnerWithUsCard $card)
    {
        $card->delete();
        return response()->json(['message' => 'Card deleted successfully']);
    }
}
