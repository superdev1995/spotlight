<?php
class TeacherTimesheetInsights extends App\Models\Model {
    public function getAttendanceInsights($user_id, $date_start, $date_end) {
        try{
            $query = $this->db->prepare("
                SELECT * FROM teacher_timesheets
                 WHERE user_id = :user_id
                   AND timesheet_date BETWEEN :date_start AND :date_end
            ");
    
            $query->execute([
                ":user_id" => $user_id,
                ":date_start" => $date_start,
                ":date_end" => $date_end,
            ]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        
        return [];
    }
    
    public function getAttendanceInsightsMonthly($user_id, $year, $month) {
        try {
            $query = $this->db->prepare("
                SELECT * FROM teacher_timesheets
                 WHERE user_id = :user_id
                   AND YEAR(timesheet_date) = :year
                   AND MONTH(timesheet_date) = :month
            ");
    
            $query->execute([
                ":user_id" => $user_id,
                ":year" => $year,
                ":month" => $month,
            ]);
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch(PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        
        return [];
    }
}
