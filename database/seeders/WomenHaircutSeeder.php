<?php
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Service;
use App\Models\OpeningHour;
use App\Models\Break;
use App\Models\PublicHoliday;
use App\Models\Appointment;

class WomanHaircutSeeder extends Seeder
{
    public function run()
    {
        // Create Woman Haircut service
        $womanHaircutService = new Service();
        $womanHaircutService->service_name = 'Woman Haircut';
        $womanHaircutService->duration = 60; // Duration in minutes
        $womanHaircutService->save();

        // Save opening hours
        $openingHours = [
            [
                'day_of_week' => 'Monday',
                'opening_time' => '08:00',
                'closing_time' => '20:00',
            ],
            [
                'day_of_week' => 'Tuesday',
                'opening_time' => '08:00',
                'closing_time' => '20:00',
            ],
            // Add opening hours for other days
        ];

        foreach ($openingHours as $hours) {
            $openingHour = new OpeningHour();
            $openingHour->service_id = $womanHaircutService->id;
            $openingHour->day_of_week = $hours['day_of_week'];
            $openingHour->opening_time = $hours['opening_time'];
            $openingHour->closing_time = $hours['closing_time'];
            $openingHour->save();
        }

        // Save breaks
        $breaks = [
            [
                'day_of_week' => 'Monday',
                'start_time' => '12:00',
                'end_time' => '13:00',
                'type' => 'lunch',
            ],
            [
                'day_of_week' => 'Monday',
                'start_time' => '15:00',
                'end_time' => '16:00',
                'type' => 'cleaning',
            ],
            // Add breaks for other days
        ];

        foreach ($breaks as $break) {
            $breakObj = new Break();
            $breakObj->service_id = $womanHaircutService->id;
            $breakObj->day_of_week = $break['day_of_week'];
            $breakObj->start_time = $break['start_time'];
            $breakObj->end_time = $break['end_time'];
            $breakObj->type = $break['type'];
            $breakObj->save();
        }

        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays(7);

        // Find the third day from now and mark it as a public holiday
        $publicHolidayDate = $startDate->copy()->addDays(2);
        $publicHoliday = new PublicHoliday();
        $publicHoliday->date = $publicHolidayDate;
        $publicHoliday->save();

        // Retrieve opening hours and breaks for Woman Haircut service
        $openingHours = OpeningHour::where('service_id', $womanHaircutService->id)->get();
        $breaks = Break::where('service_id', $womanHaircutService->id)->get();

        // Generate time slots
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                // Find opening hours for the current day
                $currentOpeningHours = $openingHours->where('day_of_week', $currentDate->englishDayOfWeek)->first();

                if ($currentOpeningHours) {
                    $startTime = Carbon::parse($currentOpeningHours->opening_time);
                    $endTime = Carbon::parse($currentOpeningHours->closing_time);

                    // Apply breaks for the current day
                    $currentBreaks = $breaks->where('day_of_week', $currentDate->englishDayOfWeek);
                    foreach ($currentBreaks as $break) {
                        $breakStart = Carbon::parse($break->start_time);
                        $breakEnd = Carbon::parse($break->end_time);

                        // Adjust time slots to exclude breaks
                        if ($startTime->between($breakStart, $breakEnd)) {
                            $startTime = $breakEnd->copy();
                        }

                        if ($endTime->between($breakStart, $breakEnd)) {
                            $endTime = $breakStart->copy();
                        }
                    }

                    // Proceed with generating time slots
                    while ($startTime->lte($endTime)) {
                        // Check if the current time slot falls on a public holiday
                        $isPublicHoliday = PublicHoliday::whereDate('date', $currentDate)->exists();

                        // Seed the time slot into the database
                        if (!$isPublicHoliday) {
                            for ($i = 0; $i < 3; $i++) {
                                $appointment = new Appointment();
                                $appointment->service_id = $womanHaircutService->id;
                                $appointment->appointment_date = $currentDate->format('Y-m-d');
                                $appointment->appointment_time = $startTime->format('H:i:s');
                                $appointment->save();
                            }
                        }

                        $startTime->addHour(); // Increase by service duration
                    }
                }
            }

            $currentDate->addDay(); // Move to the next day
        }
    }
}
