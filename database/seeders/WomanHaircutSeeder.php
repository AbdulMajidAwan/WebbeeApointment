<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BookingLimit;
use App\Models\Breaks;
use App\Models\OpeningHour;
use App\Models\PublicHoliday;
use App\Models\Service;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WomanHaircutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Men Haircut service
        $menHaircutService = new Service();
        $menHaircutService->service_name = 'Women Haircut';
        $menHaircutService->duration_minutes = 60; //60+10 minute cleanin
        $menHaircutService->save();

        // Insert booking_limits
        $booking_limits = new BookingLimit();
        $booking_limits->service_id = $menHaircutService->id;
        $booking_limits->max_booking_count =3;
        $booking_limits->save();

        // Insert user information
        $user = new User();
        $user->username = 'Ana';
        $user->email = 'Ana@example.com';
        $user->save();

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
            [
                'day_of_week' => 'Wednesday',
                'opening_time' => '08:00',
                'closing_time' => '20:00',
            ],
            [
                'day_of_week' => 'Thursday',
                'opening_time' => '08:00',
                'closing_time' => '20:00',
            ],
            [
                'day_of_week' => 'Friday',
                'opening_time' => '08:00',
                'closing_time' => '20:00',
            ],
            [
                'day_of_week' => 'Saturday',
                'opening_time' => '10:00',
                'closing_time' => '22:00',
            ]
        ];

        foreach ($openingHours as $hours) {
            $openingHour = new OpeningHour();
            $openingHour->service_id = $menHaircutService->id;
            $openingHour->day_of_week = $hours['day_of_week'];
            $openingHour->opening_time = $hours['opening_time'];
            $openingHour->closing_time = $hours['closing_time'];
            $openingHour->save();
        }

        // Save breaks
        $breaks = [
            [
                'break_start_time' => '12:00',
                'break_end_time' => '13:00',
                'type' => 'lunch',
            ],
            [
                'break_start_time' => '15:00',
                'break_end_time' => '16:00',
                'type' => 'cleaning',
            ],
            // Add breaks for other times
        ];

        foreach ($breaks as $break) {
            $breakObj = new Breaks();
            $breakObj->service_id = $menHaircutService->id;
            $breakObj->break_start_time = $break['break_start_time'];
            $breakObj->break_end_time = $break['break_end_time'];
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

        // Retrieve opening hours and breaks for Men Haircut service
        $openingHours = OpeningHour::where('service_id', $menHaircutService->id)->get();
        $breaks = Breaks::where('service_id', $menHaircutService->id)->get();

        // Generate time slots
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                // Find opening hours for the current day
                $currentOpeningHours = $openingHours->where('day_of_week', $currentDate->englishDayOfWeek)->first();

                if ($currentOpeningHours) {
                    $startTime = Carbon::parse($currentOpeningHours->opening_time);
                    $endTime = Carbon::parse($currentOpeningHours->closing_time);


                    // Proceed with generating time slots
                    while ($startTime->lte($endTime)) {
                        // Check if the current time slot falls on a public holiday
                        $isPublicHoliday = PublicHoliday::whereDate('date', $currentDate)->exists();

                        // Check if the current time slot falls within any break
                        $isWithinBreak = false;
                        foreach ($breaks as $break) {
                            $breakStart = Carbon::parse($break->break_start_time);
                            $breakEnd = Carbon::parse($break->break_end_time);

                            if ($startTime->between($breakStart, $breakEnd) || $endTime->between($breakStart, $breakEnd)) {
                                $isWithinBreak = true;
                                break;
                            }
                        }
                        // Seed the time slot into the database if conditions are met
                        if (!$isPublicHoliday && !$isWithinBreak) {
                            $slot = new Slot();
                            $slot->service_id = $menHaircutService->id;
                            $slot->slot_date = $currentDate->format('Y-m-d');
                            $slot->slot_start_time = $startTime->format('H:i:s');
                            $slot->slot_end_time =$startTime->copy()->addMinutes($menHaircutService->duration_minutes)->format('H:i:s');
                            $slot->save();
                        }
                        //$startTime->addMinutes(15); // Increase by the cleanup break duration
                        $startTime->addMinutes($menHaircutService->duration_minutes); // Increase by service duration
                    }
                }
            }
            $currentDate->addDay(); // Move to the next day

        }
    }
}
