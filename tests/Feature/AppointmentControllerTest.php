<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Slot;
use App\Models\Appointment;
use App\Models\BookingLimit;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateAppointment()
    {
        // Create sample data for testing
        $slot = Slot::factory()->create();
        $bookingLimit = BookingLimit::factory()->create(['service_id' => $slot->service_id]);

        // Define the request payload
        $requestData = [
            'slot_id' => $slot->id,
            'appointment_details' => [
                [
                    'email' => 'test1@example.com',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
                [
                    'email' => 'test2@example.com',
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                ],
            ],
        ];

        // Perform the API request
        $response = $this->post('/api/appointments', $requestData);

        // Assert the response status code
        $response->assertStatus(201);

        // Assert the response data
        $response->assertJson([
            'message' => 'Appointment created successfully',
        ]);

        $this->assertDatabaseHas('appointments', [
            'slot_id' => $slot->id,
            'email' => 'test1@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        $this->assertDatabaseHas('appointments', [
            'slot_id' => $slot->id,
            'email' => 'test2@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }
}
