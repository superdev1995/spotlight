<?php

use \Carbon\Carbon;


class WaitingLists extends App\Models\Model {

    public function getAll($school_id) {
        try {
            $query = $this->db->prepare('
                select * from waitinglist
                where school_id =?
            ');

            $query->execute([ $school_id]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($school_id, $data) {
        try {
            $query = $this->db->prepare('
            insert into waitinglist (school_id, child_name, gender, start_date, DOB, room_id, contact_name, phone, email, source, booking_methode, enquiry_reson, book_parent, book_parent_name, book_parent_note, book_parent_date, book_parent_time, created_at, updated_at) 
            values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ');

            $query->execute([ 
                $school_id, 
                $data['child_name'], 
                $data['gender'], 
                $data['start_date'],
                $data['DOB'],
                $data['room'],
                $data['contact_name'],
                $data['phone'],
                $data['email'],
                $data['source'],
                $data['booking_methode'],
                $data['enquiry_reson'],
                $data['book_parent'],
                $data['book_parent_name'],
                $data['book_parent_note'],
                $data['book_parent_date'],
                $data['book_parent_time'],
                Carbon::now(),
                Carbon::now(),
                ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function update($data) {
        try {
            $query = $this->db->prepare('
            update waitinglist 
                set child_name=?,
                  gender=?,
                  start_date=?,
                  DOB=?,
                  room_id=?,
                  contact_name=?,
                  phone=?,
                  email=?,
                  source=?,
                  booking_methode=?,
                  enquiry_reson=?,
                  book_parent=?,
                  book_parent_name=?,
                  book_parent_note=?,
                  book_parent_date=?,
                  book_parent_time=?,
                  updated_at=? 
                WHERE waitingList_id = ?
            ');

            return $query->execute([ 
                $data['child_name'], 
                $data['gender'], 
                $data['start_date'],
                $data['DOB'],
                $data['room'],
                $data['contact_name'],
                $data['phone'],
                $data['email'],
                $data['source'],
                $data['booking_methode'],
                $data['enquiry_reson'],
                $data['book_parent'],
                $data['book_parent_name'],
                $data['book_parent_note'],
                $data['book_parent_date'],
                $data['book_parent_time'],
                Carbon::now(),
                $data['waitingList_id'],
                ]);

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSearch($school_id,$search) {
        try {
            $query = $this->db->prepare('
                select * from waitinglist
                where school_id = ? 
                and source like ?
            ');

            $query->execute([ $school_id,$search]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSearchOther($school_id) {
        try {
            $query = $this->db->prepare('
                select * from waitinglist
                where school_id = ? 
                and source not in ("web","social","mouth","staff","parent","open","leaflets")
            ');

            $query->execute([ $school_id]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($waitingList_id) {
        try {
            $query = $this->db->prepare('
            delete from waitinglist 
            where waitingList_id = ?
            ');

            $query->execute([ $waitingList_id]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    

}
