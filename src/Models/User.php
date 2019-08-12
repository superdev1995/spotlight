<?php

use \Carbon\Carbon;


class User extends App\Models\Model {
    public function getOne($identification, $type = null) {
        try {
            $query = $this->db->prepare('
                select * from users
                where (
                    user_email = ?
                    and user_type = ?
                )
                or user_id = ?
                and user_status != "D"  
                limit 1
            ');

            $query->execute([ $identification, $type, $identification ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from users
                where user_status != "D" 
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getFrameworks($user_id) {
        try {
            $query = $this->db->prepare('
                select * from frameworks_teachers
                where teacher_fk = ?
                order by framework_fk
            ');

            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getTeacherCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from users
                where user_type = "T" and user_status != "D"  
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getParentCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from users
                where user_type = "P" and user_status != "D"  
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAll() {
        try {
            $query = $this->db->prepare('
                select * from users
                where user_status != "D"  
                order by created_at desc
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getTeachers() {
        try {
            $query = $this->db->prepare('
                select * from users
                where user_type = "T" and user_status != "D"  
                order by created_at desc
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getParents() {
        try {
            $query = $this->db->prepare('
                select * from users
                where user_type = "P" and user_status != "D"  
                order by created_at desc
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildren($user_id) {
        try {
            $query = $this->db->prepare('
                select * from child_user
                join children
                    on children.child_id = child_user.child_id
                where child_user.user_id = ?
                order by children.child_name
            ');

            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($data) {
        try {
            /**
             * Refrain from generating their own salts. We let PHP’s
             * password_hash() take care of that.
             */
            $query = $this->db->prepare('
                insert into users (user_email, user_password, user_first_name, user_last_name, user_type, user_consent_terms, user_consent_privacy, created_at, updated_at, 	user_consent_consent)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $data['email'], password_hash($data['password1'], PASSWORD_DEFAULT), $data['first_name'], $data['last_name'], $data['type'], Carbon::now(), Carbon::now(), Carbon::now(), Carbon::now(), Carbon::now()  ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setDetails($user_id, $data) {
        try {
            $query = $this->db->prepare('
                update users
                set user_first_name = "'.$data['first_name'].'",
                    user_last_name = "'.$data['last_name'].'",
                    user_notify_comment = "'.$data['notify_comment'].'",
                    user_notify_record = "'.$data['notify_record'].'",
                    user_notify_story = "'.$data['notify_story'].'",
                    user_notify_checklist = "'.$data['notify_checklist'].'",
                    updated_at = ?
                where user_id = ?
            ');

            return $query->execute([ Carbon::now(), $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setEmail($user_id, $email) {
        try {
            $query = $this->db->prepare('
                update users
                set user_email = ?,
                    updated_at = ?
                where user_id = ?
            ');

            return $query->execute([ $email, Carbon::now(), $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setPassword($user_id, $password) {
        try {
            $query = $this->db->prepare('
                update users
                set user_password = ?,
                    updated_at = ?
                where user_id = ?
            ');

            return $query->execute([ password_hash($password, PASSWORD_DEFAULT), Carbon::now(), $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setAvatar($user_id, $url) {
        try {
            $query = $this->db->prepare('
                update users
                set user_avatar_url = ?,
                    updated_at = ?
                where user_id = ?
            ');

            return $query->execute([ $url, Carbon::now(), $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setStatus($user_id, $status) {
        try {
            $query = $this->db->prepare('
                update users
                set user_status = ?,
                    updated_at = ?
                where user_id = ?
            ');

            return $query->execute([ $status, Carbon::now(), $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setStripeParentId($user_id, $stripe_parent_id) {
        try {
            $query = $this->db->prepare('
                update users
                set stripe_parent_id = ?,
                    updated_at = ?
                where user_id = ?
            ');

            return $query->execute([ $stripe_parent_id, Carbon::now(), $user_id]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($user_id) {
        try {
            $query = $this->db->prepare('
                delete from users
                where user_id = ?
            ');

            return $query->execute([$user_id]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
