<?php

use \Carbon\Carbon;


class Infection extends App\Models\Model {
    
    public function getAllSample() {
        try {
            $query = $this->db->prepare('
                select * from infection_letter_sample
                order by letter_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getEditLater($user_id){
        
        try {
            $query = $this->db->prepare('
                select * from infection_letter
                where user_id = ? 
                and send_to = \'SE\'
                order by created_at desc
            ');
            
            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getOne($letter_id){
        try {
            $query = $this->db->prepare('
                select * from infection_letter
                where letter_id = ?
            ');

            $query->execute([ $letter_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getOneSample($letter_id){
        try {
            $query = $this->db->prepare('
                select * from infection_letter_sample
                where letter_sample_id = ?
            ');

            $query->execute([ $letter_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    
    public function create($user_id, $school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into infection_letter (school_id, user_id, send_to,letter_name, letter_body, created_at)
                values ( ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $school_id, $user_id, $data['sendto'], $data['letter_n'], $data['description'], Carbon::now() ]);
            

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function edit($data) {
        try {
            $query = $this->db->prepare('
                update infection_letter 
                set send_to = ?,
                    letter_name = ?,
                    letter_body = ?,
                    created_at = ?
                where letter_id = ?
            ');
            
            return $query->execute([ $data['sendto'], $data['letter_n'], $data['description'], Carbon::now(), $data['edit_s'] ]);
            
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    
    
}


