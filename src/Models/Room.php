<?php

use \Carbon\Carbon;


class Room extends App\Models\Model {
    public function getAll($school_id) {
        try {
            $query = $this->db->prepare('
                select * from rooms
                where school_id = "'.$school_id.'"
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getByIds($school_id, $ids) {
        try {
            $questions = str_repeat('?,', count($ids) - 1) . '?';
            $query = $this->db->prepare("
                select * from rooms
                where school_id = ? and rooms.room_id IN ($questions)
            ");

            $data = [ $school_id ];
            $data = array_merge($data, $ids);

            $query->execute($data);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($room_id) {
        try {
            $query = $this->db->prepare('
                select * from rooms
                where room_id = "'.$room_id.'"
                limit 1
            ');

            $query->execute([ $school_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getFirst($school_id) {
        try {
            $query = $this->db->prepare('
                select * from rooms
                where school_id = "'.$school_id.'"
                order by created_at
                limit 1
            ');

            $query->execute([ $school_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildren($room_id) {
        try {
            $query = $this->db->prepare('
                select * from children
                where room_id = "'.$room_id.'"
            ');

            $query->execute([ $room_id]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildrenByIds($room_id, $ids) {
        try {
            $questions = str_repeat('?,', count($ids) - 1) . '?';
            $query = $this->db->prepare("
                select * from children
                where room_id = ? and children.child_id IN ($questions)
            ");

            $data = [ $room_id ];
            $data = array_merge($data, $ids);

            $query->execute($data);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildrenCount($room_id) {
        try {
            $query = $this->db->prepare('
                select * from children
                where room_id = "'.$room_id.'"
            ');

            $query->execute([ $room_id]);

            return $query->rowCount();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into rooms (school_id, room_name, room_description, created_at, updated_at)
                values (?, ?, ?, ?, ?)
            ');

            $query->execute([ $school_id, $data['name'], $data['description'], Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setDetails($room_id, $data) {
        try {
            $query = $this->db->prepare('
                update rooms
                set room_name = ?,
                    room_description = ?,
                    updated_at = ?
                where room_id = ?
            ');

            return $query->execute([ $data['name'], $data['description'], Carbon::now(), $room_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($room_id) {
        try {
            $query = $this->db->prepare('
                delete from rooms
                where room_id = ?
            ');

            return $query->execute([ $room_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
