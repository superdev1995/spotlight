<?php

use \Carbon\Carbon;


class Broadcast extends App\Models\Model {
    public function getAll($user_id) {
        try {
            $query = $this->db->prepare('
                select users.*, broadcasts.* from broadcasts
                left join broadcast_user
                    on broadcast_user.user_id = broadcasts.user_id
                join users
                    on users.user_id = broadcasts.user_id
                where broadcasts.user_id = ?
                or broadcast_user.user_id = ?
                group by broadcasts.broadcast_id
                order by broadcasts.created_at desc
            ');

            $query->execute([ $user_id, $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($broadcast_id) {
        try {
            $query = $this->db->prepare('
                select users.*, broadcasts.* from broadcasts
                join users
                    on users.user_id = broadcasts.user_id
                where broadcasts.broadcast_id = ?
                limit 1
            ');

            $query->execute([ $broadcast_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getUsers($broadcast_id) {
        try {
            $query = $this->db->prepare('
                select users.* from broadcast_user
                join users
                    on users.user_id = broadcast_user.user_id
                where broadcast_user.broadcast_id = ?
                order by users.user_first_name
            ');

            $query->execute([ $broadcast_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($user_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into broadcasts (user_id, broadcast_subject, broadcast_message, created_at, updated_at)
                values (?, ?, ?, ?, ?)
            ');

            $query->execute([ $user_id, $data['subject'], $data['message'], Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createUser($broadcast_id, $user_id) {
        try {
            $query = $this->db->prepare('
                insert into broadcast_user (broadcast_id, user_id)
                values (?, ?)
            ');

            return $query->execute([ $broadcast_id, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
