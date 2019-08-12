<?php

use \Carbon\Carbon;


class calendarEvents extends App\Models\Model {
    public function getAllEvents($idUser) {
        try {

            $query = $this->db->prepare('
                select * from calendarEvents
                where (
                    idUser = ?
                )
            ');

            $query->execute([ $idUser ]);

            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function getAllParentsEvents($idUser) {
        try {

            $query = $this->db->prepare('
                select * from parentsevents
                where (
                    idParent = ?
                )
            ');

            $query->execute([ $idUser ]);

            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function getAllParentEvent($idEvent) {
        try {

            $query = $this->db->prepare('
                select * from parentsevents
                where (
                    idEvent = ?
                )
            ');

            $query->execute([ $idEvent ]);

            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createEvent($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$textColorEvent,$idUser,$shareAllParents) {
        try {
            $query = $this->db->prepare('
            INSERT INTO calendarEvents(title,start,end,description,color,textColor,idUser,shareAllParents) 
            VALUES (?,?,?,?,?,?,?,?)
            ');

            $create=$query->execute([ $titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$textColorEvent,$idUser,$shareAllParents ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createParentEvent($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$idUser,$idParent,$lastInsertId) {
        try {
            $query = $this->db->prepare('
            INSERT INTO parentsevents(title,start,end,description,color,idUserShare,idParent,idEvent) 
            VALUES (?,?,?,?,?,?,?,?)
            ');

            $create=$query->execute([ $titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$idUser,$idParent,$lastInsertId ]);

            return $create;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function delEvent($idEvent) {
        try {
            $query = $this->db->prepare('
            DELETE FROM calendarEvents WHERE idCalendarEvent=?
            ');

            $delete=$query->execute([ $idEvent ]);

            return $delete;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function delAllParentsEvent($idEvent) {
        try {
            $query = $this->db->prepare('
            DELETE FROM parentsevents WHERE idEvent=?
            ');

            $delete=$query->execute([ $idEvent ]);

            return $delete;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function updateEvent($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$textColorEvent,$idEvent,$shareAllParents) {
        try {
            $query = $this->db->prepare('
            UPDATE calendarEvents set
            title=?,
            start=?,
            end=?,
            description=?,
            color=?,
            textColor=?,
            shareAllParents=?
            WHERE idCalendarEvent=?
            ');

            $create=$query->execute([ $titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$textColorEvent,$shareAllParents,$idEvent ]);

            return $create;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function updateEventAllParents($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$idEvent) {
        try {
            $query = $this->db->prepare('
            UPDATE parentsevents set
            title=?,
            start=?,
            end=?,
            description=?,
            color=?
            WHERE idEvent=?
            ');

            $create=$query->execute([ $titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$idEvent ]);

            return $create;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function dropEvent($startEvent,$finishEvent,$idEvent) {
        try {
            $query = $this->db->prepare('
            UPDATE calendarEvents set
            start=?,
            end=?
            WHERE idCalendarEvent=?
            ');

            $change=$query->execute([ $startEvent,$finishEvent,$idEvent ]);

            return $change;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function dropEventAllParents($startEvent,$finishEvent,$idEvent) {
        try {
            $query = $this->db->prepare('
            UPDATE parentsevents set
            start=?,
            end=?
            WHERE idEvent=?
            ');

            $change=$query->execute([ $startEvent,$finishEvent,$idEvent ]);

            return $change;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

}