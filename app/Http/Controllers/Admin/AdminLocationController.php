<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Office;
use App\Models\OfficePhone;
use App\Models\OfficeEmail;
use App\Models\OfficeWebsite;

class AdminLocationController extends Controller
{
    public function index()
{
    $countries = Country::with([
        'offices.phones',
        'offices.emails',
        'offices.websites'
    ])
    ->orderBy('display_order')
    ->get();

    return response()->json([
        'countries' => $countries
    ]);
}
    /**
     * Store a new country with offices and contacts.
     */
    public function store(Request $request)
{
    $request->validate([
        'countries' => 'required|array',
        'countries.*.country_name' => 'required|string|max:255',
        'countries.*.icon_color' => 'nullable|string|max:50',
        'countries.*.display_order' => 'nullable|integer',
        'countries.*.is_active' => 'boolean',
        'countries.*.offices' => 'nullable|array',
        'countries.*.offices.*.office_name' => 'required|string|max:255',
        'countries.*.offices.*.address' => 'required|string',
        'countries.*.offices.*.city' => 'nullable|string|max:255',
        'countries.*.offices.*.province' => 'nullable|string|max:255',
        'countries.*.offices.*.phones' => 'nullable|array',
        'countries.*.offices.*.emails' => 'nullable|array',
        'countries.*.offices.*.websites' => 'nullable|array',
    ]);

    $createdCountries = [];

    foreach ($request->countries as $countryData) {
        $officesData = $countryData['offices'] ?? [];
        unset($countryData['offices']);

        $country = Country::create($countryData);

        foreach ($officesData as $officeData) {
            $phones = $officeData['phones'] ?? [];
            $emails = $officeData['emails'] ?? [];
            $websites = $officeData['websites'] ?? [];

            unset($officeData['phones'], $officeData['emails'], $officeData['websites']);

            $office = $country->offices()->create($officeData);

            foreach ($phones as $phone) {
                $office->phones()->create($phone);
            }
            foreach ($emails as $email) {
                $office->emails()->create($email);
            }
            foreach ($websites as $website) {
                $office->websites()->create($website);
            }
        }

        $createdCountries[] = $country;
    }

    return response()->json([
        'message' => 'Countries and offices created successfully',
        'countries' => $createdCountries
    ]);
}


    /**
     * Update a country and its offices/contacts.
     */
    public function update(Request $request, Country $country)
    {
        $request->validate([
            'country_name' => 'sometimes|required|string|max:255',
            'icon_color' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'offices' => 'nullable|array',
            'offices.*.id' => 'nullable|exists:offices,id',
            'offices.*.office_name' => 'required|string|max:255',
            'offices.*.address' => 'required|string',
        ]);

        $country->update($request->only('country_name', 'icon_color', 'display_order', 'is_active'));

        if ($request->has('offices')) {
            foreach ($request->offices as $officeData) {
                $phones = $officeData['phones'] ?? [];
                $emails = $officeData['emails'] ?? [];
                $websites = $officeData['websites'] ?? [];

                if (isset($officeData['id'])) {
                    $office = Office::find($officeData['id']);
                    $office->update($officeData);
                } else {
                    unset($officeData['id']);
                    $office = $country->offices()->create($officeData);
                }

                // Update phones
                foreach ($phones as $phoneData) {
                    if (isset($phoneData['id'])) {
                        OfficePhone::find($phoneData['id'])->update($phoneData);
                    } else {
                        $office->phones()->create($phoneData);
                    }
                }

                // Update emails
                foreach ($emails as $emailData) {
                    if (isset($emailData['id'])) {
                        OfficeEmail::find($emailData['id'])->update($emailData);
                    } else {
                        $office->emails()->create($emailData);
                    }
                }

                // Update websites
                foreach ($websites as $webData) {
                    if (isset($webData['id'])) {
                        OfficeWebsite::find($webData['id'])->update($webData);
                    } else {
                        $office->websites()->create($webData);
                    }
                }
            }
        }

        return response()->json(['message' => 'Country and offices updated successfully', 'country' => $country]);
    }

    /**
     * Delete a country and cascade delete offices and contacts.
     */
    public function destroy(Country $country)
    {
        $country->delete();

        return response()->json(['message' => 'Country and all related offices deleted successfully']);
    }
}
