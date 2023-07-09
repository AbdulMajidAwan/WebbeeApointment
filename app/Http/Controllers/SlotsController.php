<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Models\Service;
use App\Models\Slot;

class SlotsController extends Controller
{
    public function getAllSlots()
    {
        try {
            $services = Service::all();
            // Prepare the response data
            $response = [];
            foreach ($services as $service) {
                $slots = Slot::with('appointments')->where('service_id', $service->id)->get();
                // Add the service and its slots with appointments to the response
                $service['slots'] = $slots;
                $response[] = [
                    'service' => $service
                ];
            }
            // Return the response
            return response()->json($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve slots.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
