<?php

use \Carbon\Carbon;


class Child extends App\Models\Model {
    public function getAll($school_id, $status = 'A') {
        try {
            $query = $this->db->prepare('
                select * from children
                where school_id = ?
                and child_status = ?
                order by child_name
            ');

            $query->execute([ $school_id, $status ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from children
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAssociatedChildren($user_id) {
        try {
            $query = $this->db->prepare('
                select * from child_user
                join children
                    on children.child_id = child_user.child_id
                join schools
                    on schools.school_id = children.school_id
                where child_user.user_id = ?
                and children.child_status = "A"
                and child_user.status = "A"
            ');

            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($child_id) {
        try {
            $query = $this->db->prepare('
                select * from children
                where child_id = ?
                limit 1
            ');

            $query->execute([ $child_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function getOneMore($child_id) {
        try {
            $query = $this->db->prepare('
                select c.*,s.country_id,c.school_id
                from children c
                join schools s on c.school_id = s.school_id
                where c.child_id = ?
            ');

            $query->execute([ $child_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getInvite($token_id) {
        try {
            $query = $this->db->prepare('
                select * from child_user
                where token_id = ?
            ');

            $query->execute([ $token_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSearch($school_id, $search) {
        try {
            $query = $this->db->prepare('
                select * from children
                where school_id = ?
                and child_name like ?
                order by child_status, child_name
            ');

            $query->execute([ $school_id, "%$search%" ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getParent($child_id, $user_id) {
        try {
            $query = $this->db->prepare('
                select * from child_user
                join users
                    on users.user_id = child_user.user_id
                where child_user.child_id = ?
                and child_user.user_id = ?
                and child_user.status = "A"
                limit 1
            ');

            $query->execute([ $child_id, $user_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getParents($child_id) {
        try {
            $query = $this->db->prepare('
                select * from child_user
                join users
                    on users.user_id = child_user.user_id
                where child_user.child_id = ?
            ');

            $query->execute([ $child_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getActiveParents($child_id) {
        try {
            $query = $this->db->prepare('
                select * from child_user
                join users
                    on users.user_id = child_user.user_id
                where child_user.child_id = ?
                and child_user.status = "A"
            ');

            $query->execute([ $child_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllParentsForSchool($school_id){
        try {

            $query = $this->db->prepare('
                select * from child_user
                join users
                    on users.user_id = child_user.user_id
                join children on child_user.child_id = children.child_id    
                where children.school_id = ?
            ');

            $query->execute([$school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAssociatedPlansForAYear($school_id, $child_id, $year, $type){
        try {
            $query = $this->db->prepare('
                SELECT * FROM ' . $type . '_plans 
                WHERE assoc = "school" AND school_fk = ? and year = ? AND deleted = 0
            ');

            $query->execute([$school_id, $year ]);

            $plans = $query->fetchAll(PDO::FETCH_OBJ);

            $query = $this->db->prepare('
                SELECT * FROM ' . $type . '_plans p 
                INNER JOIN ' . $type . '_plan_assoc pa ON p.' . $type . '_plan_id = pa.' . $type . '_plan_fk 
                INNER JOIN children c ON pa.assoc_fk = c.room_id 
                WHERE p.assoc = "room" AND c.child_id = ? AND p.year = ? AND deleted = 0
            ');

            $query->execute([$child_id, $year ]);

            $plans = array_merge($plans, $query->fetchAll(PDO::FETCH_OBJ));

            $query = $this->db->prepare('
                SELECT * FROM ' . $type . '_plans p 
                INNER JOIN ' . $type . '_plan_assoc pa ON p.' . $type . '_plan_id = pa.' . $type . '_plan_fk
                WHERE p.assoc = "child" AND pa.assoc_fk = ? AND p.year = ? AND deleted = 0
            ');

            $query->execute([$child_id, $year ]);

            return array_merge($plans, $query->fetchAll(PDO::FETCH_OBJ));
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAssociatedMonthlyPlansForAMonth($school_id, $child_id, $year, $month){
        try {
            $query = $this->db->prepare('
                SELECT * FROM monthly_plans 
                WHERE assoc = "school" AND school_fk = ? AND year = ? 
                AND month = ? AND deleted = 0
            ');

            $query->execute([$school_id, $year, $month ]);

            $plans = $query->fetchAll(PDO::FETCH_OBJ);

            $query = $this->db->prepare('
                SELECT * FROM monthly_plans mp 
                INNER JOIN monthly_plan_assoc mpa ON mp.monthly_plan_id = mpa.monthly_plan_fk 
                INNER JOIN children c ON mpa.assoc_fk = c.room_id 
                WHERE mp.assoc = "room" AND c.child_id = ? AND mp.year = ? 
                AND mp.month = ? AND deleted = 0
            ');

            $query->execute([$child_id, $year, $month ]);

            $plans = array_merge($plans, $query->fetchAll(PDO::FETCH_OBJ));

            $query = $this->db->prepare('
                SELECT * FROM monthly_plans mp 
                INNER JOIN monthly_plan_assoc mpa ON mp.monthly_plan_id = mpa.monthly_plan_fk
                WHERE mp.assoc = "child" AND mpa.assoc_fk = ? AND mp.year = ? 
                AND mp.month = ? AND deleted = 0
            ');

            $query->execute([$child_id, $year, $month ]);

            return array_merge($plans, $query->fetchAll(PDO::FETCH_OBJ));
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into children (child_name, school_id, room_id, child_gender, child_birthday, child_street, child_city, child_postal_code, child_phone, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $data['name'], $school_id, $data['room_id'], $data['gender'], $data['birthday'], $data['street'], $data['city'], $data['postal_code'], $data['phone'], Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createQuick($school_id, $room_id, $name) {
        try {
            $query = $this->db->prepare('
                insert into children (child_name, school_id, room_id, child_gender, child_birthday, child_street, child_city, child_postal_code, child_phone, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $name, $school_id, $room_id, null, null, null, null, null, null, Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createParent($child_id, $user_id, $status = 'P') {
        try {
            $token_id = StringHandler::getToken(16);

            $query = $this->db->prepare('
                insert into child_user (child_id, user_id, token_id, status)
                values (?, ?, ?, ?)
                on duplicate key update
                    token_id = values(token_id)
            ');

            $query->execute([ $child_id, $user_id, $token_id, $status ]);

            return $token_id;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setDetails($child_id, $data) {
        $dt = Carbon::parse($data['birthday']);

        try {
            $query = $this->db->prepare('
                update children
                set room_id = ?,
                    child_name = ?,
                    child_gender = ?,
                    child_birthday = ?,
                    child_street = ?,
                    child_city = ?,
                    child_postal_code = ?,
                    child_phone = ?,
                    updated_at = ?
                where child_id = ?
            ');

            return $query->execute([ $data['room_id'], $data['name'], $data['gender'], $dt->toDateString(), $data['street'], $data['city'], $data['postal_code'], $data['phone'], Carbon::now(), $child_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setStatus($child_id, $status = 'A') {
        try {
            $query = $this->db->prepare('
                update children
                set child_status = ?,
                    updated_at = ?
                where child_id = ?
            ');

            return $query->execute([ $status, Carbon::now(), $child_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setUserStatus($child_id, $user_id, $status) {
        try {
            $query = $this->db->prepare('
                update child_user
                set status = ?
                where child_id = ?
                and user_id = ?
            ');

            return $query->execute([ $status, $child_id, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setAvatar($child_id, $url) {
        try {
            $query = $this->db->prepare('
                update children
                set child_avatar_url = ?,
                    updated_at = ?
                where child_id = ?
            ');

            return $query->execute([ $url, Carbon::now(), $child_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setNotes($child_id, $data) {
        try {
            $query = $this->db->prepare('
                update children
                set child_notes = ?,
                    updated_at = ?
                where child_id = ?
            ');

            return $query->execute([ $data['notes'], Carbon::now(), $child_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($child_id) {
        try {
            $query = $this->db->prepare('
                delete from children
                where child_id = ?
            ');

            return $query->execute([ $child_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeParent($child_id, $user_id) {
        try {
            $query = $this->db->prepare('
                delete from child_user
                where child_id = ?
                and user_id = ?
            ');

            return $query->execute([ $child_id, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
