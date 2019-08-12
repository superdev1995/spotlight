<?php

use \Carbon\Carbon;


class Policy extends App\Models\Model {
    public function getAll($school_id) {
        try {
            $query = $this->db->prepare('
                select *, policies.policy_id as policy_id from policies
                left join policy_school
                    on policy_school.policy_id = policies.policy_id
                    and policy_school.school_id = ?
				where policies.policy_default = 0 or policies.policy_default = ?
                order by policy_public DESC
            ');

            $query->execute([ $school_id, $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllPoliciesForParent($school_id, $user_id){
        try {
            $query = $this->db->prepare('
                select *, policies.policy_id as policy_id from policies
                left join policy_school
                    on policy_school.policy_id = policies.policy_id
                    and policy_school.school_id = ?
                left join policy_parent
                    on policy_parent.policy_id = policies.policy_id
                    and policy_parent.school_id = policy_school.school_id
                    and policy_parent.user_id = ?
				where policies.policy_default = 0 or policies.policy_default = ?
                order by policy_parent.consulted ASC, updated_at DESC
            ');

            $query->execute([ $school_id, $user_id, $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($policy_id, $school_id) {
        try {
            $query = $this->db->prepare('
                select *, policies.policy_id as policy_id from policies
                left join policy_school
                    on policy_school.policy_id = policies.policy_id
                    and policy_school.school_id = ?
                where policies.policy_id = ?
                limit 1
            ');

            $query->execute([ $school_id, $policy_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from policy_school
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($policy_id, $school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into policy_school (policy_id, school_id, body, file_url, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?)
            ');

            return $query->execute([ $policy_id, $school_id, $data['description'], $data['file_url'], Carbon::now(), Carbon::now() ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function update($policy_id, $school_id, $data) {
        try {
            $query = $this->db->prepare('
                update policy_school
                set body = ?,
                    file_url = ?,
                    updated_at = ?
                where policy_id = ?
                and school_id = ?
            ');

            return $query->execute([ $data['description'], $data['file_url'], Carbon::now(), $policy_id, $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($policy_id, $school_id) {
        try {
            $query = $this->db->prepare('
                delete from policy_school
                where policy_id = ?
                and school_id = ?;
            ');

            $result = $query->execute([ $policy_id, $school_id ]);
            if(!$result)
                return $result;

            $query = $this->db->prepare('
                delete from policies
                where policy_id = ?
                and policy_default = ?;
            ');

            $result = $query->execute([ $policy_id, $school_id ]);
            if(!$result)
                return $result;

            $query = $this->db->prepare('
                delete from policy_parent
                where policy_id = ?
                and school_id = ?;
            ');

            return $query->execute([ $policy_id, $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function shareCheck($school_id) {
        try {
            $query = $this->db->prepare('
                select policy_id, policy_public from policy_school
				where school_id = ?
                order by policy_id
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function share($policy_id, $school_id) {
            try {
                $query = $this->db->prepare('
                update policy_school
                set policy_public = 1
                where policy_id = ?
                and school_id = ?
            ');
                return $query->execute([ $policy_id, $school_id ]);
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
        }
    }
	
	public function unshare($policy_id, $school_id) {
            try {
                $query = $this->db->prepare('
                update policy_school
                set policy_public = 0
                where policy_id = ?
                and school_id = ?
            ');
                return $query->execute([ $policy_id, $school_id ]);
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
        }
    }
	
	public function createPolicy($data, $school_id){
        try{
            $query = $this->db->prepare('
                insert into policies (policy_name, policy_description, policy_required, policy_default)
                values(?, ?, ?, ?)
                ');

            return $query->execute([$data['polName'], $data['polDesc'], 1, $school_id]);
        }catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createPolicyParent($school_id, $policy_id, $user_id){
        try{
            $query = $this->db->prepare('
                insert into policy_parent (school_id, policy_id, user_id)
                values(?, ?, ?)
                ');

            return $query->execute([$school_id, $policy_id, $user_id]);
        }catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function editSinglePolicyParent($school_id, $policy_id, $user_id, $consulted){
        try{
            $query = $this->db->prepare('
                update policy_parent
                set consulted = ?
                where school_id = ? 
                and policy_id = ?
                and user_id = ?
            ');

            return $query->execute([$consulted, $school_id, $policy_id, $user_id]);
        }catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function editPolicyParent($school_id, $policy_id, $consulted){
        try{
            $query = $this->db->prepare('
                update policy_parent
                set consulted = ?
                where school_id = ?  
                and policy_id = ?
                ');

            return $query->execute([$consulted, $school_id, $policy_id]);
        }catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getPolicyParents($school_id, $policy_id){
        try {
            $query = $this->db->prepare('
                select * from users
                inner join policy_parent
                    on users.user_id = policy_parent.user_id
                where users.user_type = "P" 
                and policy_parent.school_id = ?
                and policy_parent.policy_id = ?
            ');

            $query->execute([ $school_id, $policy_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
