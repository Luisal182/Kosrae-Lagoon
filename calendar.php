<?php

function generateCalendar(array $bookedDays): string
{
    $calendar = '<ul class="booked-dates">';
    $daysOfWeek = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];

    // Add day headers
    foreach ($daysOfWeek as $day) {
        $calendar .= '<li class="day-header">' . $day . '</li>';
    }

    // Add days of the month
    for ($day = 1; $day <= 31; $day++) {
        if (in_array($day, $bookedDays)) {
            $calendar .= '<li class="booked">' . $day . '</li>';
        } else {
            $calendar .= '<li>' . $day . '</li>';
        }
    }
    $calendar .= '</ul>';

    return $calendar;
}
