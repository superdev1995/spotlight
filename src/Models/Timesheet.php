<?php

use \Carbon\Carbon;


class Timesheet extends App\Models\Model {
    public function getOne($date, $child_id) {
        try {
            $query = $this->db->prepare('
                select * from timesheets
                
                where timesheets.child_id = ?
                and timesheets.timesheet_date = ?
            ');

            $query->execute([ $child_id, $date ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOption($child_id) {
        try {
            $query = $this->db->prepare('
                select Other_ID, NCS_Hour_Claim, ECCE_Hour_Claim, program_type, DOB from timesheets
                where child_id = ?
                ORDER BY timesheet_date DESC 
                LIMIT 1
            ');

            $query->execute([ $child_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($child_id, $in, $out, $data, $other, $NCS, $ECCE, $programme, $DOB, $initial) {

        if(empty($out)){
            try {
                $query = $this->db->prepare('
                insert into timesheets (timesheet_date, child_id, timesheet_in, created_at, updated_at,
                Other_ID, NCS_Hour_Claim, ECCE_Hour_Claim, program_type, DOB, initial)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                on duplicate key update
                    timesheet_in = values(timesheet_in),
                    timesheet_out = values(timesheet_out),
                    updated_at = values(updated_at),
                    Other_ID = values(Other_ID),
                    NCS_Hour_Claim = values(NCS_Hour_Claim),
                    ECCE_Hour_Claim = values(ECCE_Hour_Claim),
                    program_type = values(program_type),
                    DOB = values(DOB),
                    initial= values(initial)
            ');

                return $query->execute([ $data['timesheet_date'], $child_id, $in, Carbon::now(), Carbon::now(), $other, $NCS, $ECCE, $programme, $DOB, $initial ]);
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
        } else {
            try {
                $query = $this->db->prepare('
                insert into timesheets (timesheet_date, child_id, timesheet_in, timesheet_out, created_at, updated_at,
                Other_ID, NCS_Hour_Claim, ECCE_Hour_Claim, program_type, DOB, initial)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                on duplicate key update
                    timesheet_in = values(timesheet_in),
                    timesheet_out = values(timesheet_out),
                    updated_at = values(updated_at),
                    Other_ID = values(Other_ID),
                    NCS_Hour_Claim = values(NCS_Hour_Claim),
                    ECCE_Hour_Claim = values(ECCE_Hour_Claim),
                    program_type = values(program_type),
                    DOB = values(DOB),
                    initial= values(initial)
            ');

                return $query->execute([ $data['timesheet_date'], $child_id, $in, $out, Carbon::now(), Carbon::now(), $other, $NCS, $ECCE, $programme, $DOB, $initial ]);
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
        }

    }

    public function absent($date, $child_id, $reason, $comment, $evidence_url, $other, $NCS, $ECCE, $programme, $DOB,$initial) {
        try {
            $query = $this->db->prepare('
            insert into timesheets (timesheet_date, child_id, missing, missing_comment, missing_evidence_url, created_at, updated_at, 
            Other_ID, NCS_Hour_Claim, ECCE_Hour_Claim, program_type, DOB, initial)
            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            on duplicate key update
                missing = values(missing),
                missing_comment = values(missing_comment),
                missing_evidence_url = values(missing_evidence_url),
                updated_at = values(updated_at),
                Other_ID = values(Other_ID),
                NCS_Hour_Claim = values(NCS_Hour_Claim),
                ECCE_Hour_Claim = values(ECCE_Hour_Claim),
                program_type = values(program_type),
                DOB = values(DOB),
                initial= values(initial)
        ');

            return $query->execute([ $date, $child_id, $reason, $comment, $evidence_url, Carbon::now(), Carbon::now(), $other, $NCS, $ECCE, $programme, $DOB, $initial ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($date, $child_id) {
        try {
            $query = $this->db->prepare('
                delete from timesheets
                where timesheet_date = ?
                and child_id = ?
            ');

            return $query->execute([ $date, $child_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllBetweenDate($data) {
        try {
            $query = $this->db->prepare('
                select * from timesheets
                where child_id = ?
                and timesheet_date between ? and ?
            ');

            $query->execute([$data['child_id'],$data['start_date'],$data['end_date'] ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
