<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\BookingLimit;
use App\Models\Slot;

class AppointmentController extends Controller
{
    /*
     * Create Appointment
     * Rec Body
       INPUT
        Raw data Json formate
         {
           "slot_id": 196,
               "appointment_details": [
                   {
                     "email": "john@example.com",
                     "first_name": "John",
                     "last_name": "Doe"
                   },
                   {
                       "email": "jane@example.com",
                     "first_name": "Jane",
                     "last_name": "Smith"
                   }
               ]
           }
       */
    public function create(Request $request)
    {
        try {
            $rules = [
                'slot_id' => 'required',
                'appointment_details' => 'required|array',
                'appointment_details.*.email' => 'required|email',
                'appointment_details.*.first_name' => 'required|string',
                'appointment_details.*.last_name' => 'required|string',
            ];

            // Create a new validator instance
            $validator = Validator::make($request->all(), $rules);

            // Check if validation fails
            if ($validator->fails()) {
                // Return the validation errors as the response
                return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $slotId = $request->input('slot_id');
            // Check if the slot exists
            if (!Slot::where('id', $slotId)->exists()) {
                return response()->json(['error' => 'Invalid slot ID'], Response::HTTP_NOT_FOUND);
            }

            $serviceId = Slot::findOrFail($slotId)->service_id;
            $bookingLimit = BookingLimit::where('service_id', $serviceId)->first();

            if (!$bookingLimit) {
                return response()->json(['error' => 'Booking limit not found for the service'], Response::HTTP_NOT_FOUND);
            }

            $appointmentDetails = $request->input('appointment_details');
            // Create a new appointment for each person
            foreach ($appointmentDetails as $appointmentDetail) {

                $appointmentCount = Appointment::where('slot_id', $slotId)->count();
                if ($appointmentCount >= $bookingLimit->max_booking_count) {
                    return response()->json(['error' => 'Maximum appointment limit reached for this slot'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                $appointment = new Appointment();
                $appointment->slot_id = $slotId;
                $appointment->email = $appointmentDetail['email'];
                $appointment->first_name = $appointmentDetail['first_name'];
                $appointment->last_name = $appointmentDetail['last_name'];
                // Save the appointment to the database
                $appointment->save();
            }
            return response()->json(['message' => 'Appointment created successfully'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            print_r($e->getMessage());die;
            // Handle the exception and return an error response
            return response()->json(['error' => 'An error occurred while creating the appointment'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
