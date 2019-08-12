<?php

use \Carbon\Carbon;

class TeacherTimesheet extends App\Models\Model {
    public function getOne($date, $user_id) {
        try {
            $query = $this->db->prepare("
                SELECT * FROM teacher_timesheets
                 WHERE user_id = :user_id
                   AND timesheet_date = :timesheet_date
            ");
            
            $query->execute([
                ":user_id" => $user_id,
                ":timesheet_date" => $date
            ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        
        return null;
    }

    public function create(
        $date,
        $user_id,
        $timesheet_in,
        $timesheet_out = null,
        $break_start = null,
        $break_end = null
    ) {
        $clauses = [
            "timesheet_date = :timesheet_date",
            "user_id = :user_id",
            "timesheet_in = :timesheet_in",
            "updated_at = :now",
        ];

        $params = [
            ":timesheet_date" => $date,
            ":user_id" => $user_id,
            ":timesheet_in" => $timesheet_in,
            ":now" => Carbon::now(),
        ];
        
        if ($timesheet_out) {
            $clauses[] = "timesheet_out = :timesheet_out";
            $params[":timesheet_out"] = $timesheet_out;
        }
        
        if ($break_start) {
            $clauses[] = "break_start = :break_start";
            $params[":break_start"] = $break_start;
        }
        
        if ($break_end) {
            $clauses[] = "break_end = :break_end";
            $params[":break_end"] = $break_end;
        }
        
        $clausesSql = implode(", ", $clauses);
        
        try {
            return $this->db->prepare("
                INSERT INTO teacher_timesheets
                        SET created_at = :now,
                            {$clausesSql}
                         ON DUPLICATE KEY UPDATE
                            {$clausesSql}
            ")->execute($params);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        
        return null;
    }

    public function absent($date, $user_id, $reason, $comment, $evidence_url) {
        try {
            return $this->db->prepare("
                INSERT INTO teacher_timesheets
                        SET timesheet_date = :timesheet_date,
                            user_id = :user_id,
                            missing = :missing,
                            missing_comment = :missing_comment,
                            missing_evidence_url = :missing_evidence_url,
                            created_at = :now,
                            updated_at = :now
                         ON DUPLICATE KEY UPDATE
                            missing = :missing,
                            missing_comment = :missing_comment,
                            missing_evidence_url = :missing_evidence_url,
                            updated_at = :now
                            
            ")->execute([
                ":user_id" => $user_id,
                ":timesheet_date" => $date,
                ":missing" => $reason,
                ":missing_comment" => $comment,
                ":missing_evidence_url" => $evidence_url,
                ":now" => Carbon::now(),
            ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        
        return null;
    }

    public function purge($date, $user_id) {
        try {
            return $this->db->prepare("
                DELETE FROM teacher_timesheets
                      WHERE timesheet_date = :timesheet_date
                        AND user_id = :user_id
            ")->execute([
                ":user_id" => $user_id,
                ":timesheet_date" => $date,
            ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
        
        return null;
    }
}
