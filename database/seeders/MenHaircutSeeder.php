<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Service;
use App\Models\OpeningHour;
use App\Models\Breaks;
use App\Models\PublicHoliday;
use App\Models\Appointment;
use App\Models\BookingLimit;
use App\Models\User;

class MenHaircutSeeder extends Seeder
{
    public function run()
    {
        // Create Men Haircut service
        $menHaircutService = new Service();
        $menHaircutService->service_name = 'Men Haircut';
        $menHaircutService->duration_minutes = 10; // Duration in minutes
        $menHaircutService->save();

        // Insert booking_limits
        $booking_limits = new BookingLimit();
        $booking_limits->service_id = $menHaircutService->id;
        $booking_limits->max_booking_count =3;
        $booking_limits->save();

        // Insert user information
        $user = new User();
        $user->username = 'A.Majid';
        $user->email = 'johndoe@example.com';
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
            // Add opening hours for other days
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

                    // Apply breaks for the current day
                    $currentBreaks = $breaks;
                    foreach ($currentBreaks as $break) {
                        $breakStart = Carbon::parse($break->break_start_time);
                        $breakEnd = Carbon::parse($break->break_start_time);

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

                        // Check if the maximum booking count has been reached for the current time slot
                        $isMaxBookingCountReached = Appointment::where('service_id', $menHaircutService->id)
                                ->where('appointment_date', $currentDate->format('Y-m-d'))
                                ->where('appointment_time', $startTime->format('H:i:s'))
                                ->count() >= $booking_limits->max_booking_count;

                        // Seed the time slot into the database if conditions are met
                        if (!$isPublicHoliday && !$isMaxBookingCountReached) {
                            $appointment = new Appointment();
                            $appointment->service_id = $menHaircutService->id;
                            $appointment->user_id = $user->id; // Assign the user ID
                            $appointment->appointment_date = $currentDate->format('Y-m-d');
                            $appointment->appointment_time = $startTime->format('H:i:s');
                            $appointment->save();
                        }
                        $startTime->addMinutes($menHaircutService->duration_minutes); // Increase by service duration
                    }
                }
            }
            $currentDate->addDay(); // Move to the next day
        }
    }
}
