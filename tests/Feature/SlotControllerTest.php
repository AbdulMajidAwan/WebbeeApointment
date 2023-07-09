<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Service;
use App\Models\Slot;

class SlotControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testGetAllSlots()
    {
        // Create sample data for testing
        $service = Service::factory()->create();
        $slot1 = Slot::factory()->create(['service_id' => $service->id]);
        $slot2 = Slot::factory()->create(['service_id' => $service->id]);

        // Perform the API request
        $response = $this->get('/api/slots');

        // Assert the response status code
        $response->assertStatus(200);

        // Assert the response data structure
        $response->assertJsonStructure([
            '*' => [
                'service',
                'slots' => [
                    '*' => [
                        'id',
                        'start_time',
                        'end_time',
                        'appointments',
                    ],
                ],
            ],
        ]);

        // Assert the response data content
        $response->assertJson([
            [
                'service' => [
                    'id' => $service->id,
                    // Include other service properties for assertion
                ],
                'slots' => [
                    [
                        'id' => $slot1->id,
                        // Include other slot properties for assertion
                    ],
                    [
                        'id' => $slot2->id,
                        // Include other slot properties for assertion
                    ],
                ],
            ],
        ]);
    }
}
