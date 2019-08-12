<?php
/**
 * Author: dadirlam@hotmail.com
 */
class Calendar {

     public static function MakeCalendar($month, $year) {

          // Days and week vars 
          $running_day = date('w',mktime(0,0,0,$month,1,$year));
          $days_in_month = date('t',mktime(0,0,0,$month,1,$year));
          $days_in_this_week = 1;
          $day_counter = 0;
          $row_counter = 0;

          $calendar['headings'] = [
               'Sunday',
               'Monday',
               'Tuesday',
               'Wednesday',
               'Thursday',
               'Friday',
               'Saturday'
          ];

          // Print "blank" days until the first of the current week 
          for($x = 0; $x < $running_day; $x++) {
               $calendar['rows'][$row_counter][] = ['date' => '', 'day' => ''];
               $days_in_this_week++;
          }

          for($list_day = 1; $list_day <= $days_in_month; $list_day++) {
               $date = $year.'-'.$month.'-'.str_pad($list_day, 2, '0', STR_PAD_LEFT);
               $calendar['rows'][$row_counter][] = ['date' => $date, 'day' => $list_day];

               if($running_day == 6) {
                    if(($day_counter + 1) != $days_in_month) {
                         $row_counter++;
                         $running_day = -1;
                         $days_in_this_week = 0;
                    }
               }
               $days_in_this_week++; $running_day++; $day_counter++;
          }

          if ($days_in_this_week < 8) {
               for ($x = 1; $x <= (8 - $days_in_this_week); $x++) {
                    $calendar['rows'][$row_counter][] = ['date' => '', 'day' => ''];
               }
          }

          return $calendar;
     }
}