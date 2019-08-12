<?php

use \Carbon\Carbon;


class School extends App\Models\Model {
    public function getOne($school_id) {
        try {
            $query = $this->db->prepare('
                select * from schools
                where school_id = ?
                limit 1
            ');

            $query->execute([ $school_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getOneUS($school_id) {
        try {
            $query = $this->db->prepare('
                select * from us_schools
                where school_id = ?
                limit 1
            ');

            $query->execute([ $school_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from schools
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSubscriberCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from schools
                where (
                    stripe_id != null
                    or stripe_id != ""
                )
                and school_billing_date >= ?
            ');

            $query->execute([ Carbon::today() ]);

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getByStripeId($stripe_id) {
        try {
            $query = $this->db->prepare('
                select * from schools
                where stripe_id = ?
                limit 1
            ');

            $query->execute([ $stripe_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAll($user_id) {
        try {
            $query = $this->db->prepare('
                select * from schools
                join school_user
                    on school_user.school_id = schools.school_id
                left join school_categories
                    on school_categories.category_id = schools.category_id
                where school_user.user_id = ?
                and school_user.status = "A"
            ');

            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getAllUS($user_id) {
        try {
            $query = $this->db->prepare('
                select * from schools
                join school_user
                    on school_user.school_id = schools.school_id
                join school_categories
                    on school_categories.category_id = schools.category_id
                join us_schools
                	on us_schools.school_id=schools.school_id
                where school_user.user_id = ?
                and school_user.status = "A"
            ');

            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllSchools() {
        try {
            $query = $this->db->prepare('
                select * from schools
                order by created_at desc
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getUser($school_id, $user_id) {
        try {
            $query = $this->db->prepare('
                select * from school_user
                where school_id = ?
                and user_id = ?
                and status = "A"
                limit 1
            ');

            $query->execute([ $school_id, $user_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getUsers($school_id) {
        try {
            $query = $this->db->prepare('
                select * from users
                join school_user
                    on users.user_id = school_user.user_id
                where school_user.school_id = ?
                order by users.user_first_name
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAdministrators($school_id) {
        try {
            $query = $this->db->prepare('
                select * from users
                join school_user
                    on users.user_id = school_user.user_id
                where school_user.school_id = ?
                and school_user.role = "1"
                order by users.user_first_name
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSubscriptionStatus($school_id) {
        try {
            $query = $this->db->prepare('
                select * from schools
                where school_id = ?
                and school_billing_date >= ?
            ');

            $query->execute([ $school_id, Carbon::today() ]);

            return $query->rowCount();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getExpiringTrials() {
        try {
            $query = $this->db->prepare('
                select * from schools
                where (stripe_id = "" or stripe_id is null)
                and school_billing_date = ?
            ');

            $query->execute([ Carbon::today()->addDays(3) ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getExpiredTrials() {
        try {
            $query = $this->db->prepare('
                select * from schools
                where (stripe_id = "" or stripe_id is null)
                and school_billing_date = ?
            ');

            $query->execute([ Carbon::today()->subDays(3) ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getActiveUsers($school_id) {
        try {
            $query = $this->db->prepare('
                select * from users
                join school_user
                    on users.user_id = school_user.user_id
                where school_user.school_id = ?
                and school_user.status = "A"
                order by users.user_first_name
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAdmins($school_id) {
        try {
            $query = $this->db->prepare('
                select * from school_user
                where school_id = ?
                and role = "1"
                and status = "A"
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAdminAll($school_id) {
        try {
            $query = $this->db->prepare('
                select * from school_user
                INNER JOIN users ON school_user.user_id = users.user_id
                where school_id = ?
                and role = "1"
                and status = "A"
                LIMIT 1
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


    public function checkIfAdmin($user_id) {
        try {
            $query = $this->db->prepare('
                select * from school_user
                where user_id = ?
                and role = "1"
            ');

            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAdminEmail($school_id) {
        try {
            $query = $this->db->prepare('
                select u.user_email from school_user s inner join users u on u.user_id = s.user_id
                where s.school_id = ?
                and role = "1"
                and status = "A" limit 1
            ');

            $query->execute([ $school_id ]);
            
            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getInvite($token_id) {
        try {
            $query = $this->db->prepare('
                select * from school_user
                where token_id = ?
            ');

            $query->execute([ $token_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCategories($country_id = null) {
        try {
            $query = $this->db->prepare('
                select * from school_categories
                '.($country_id ? 'where country_id = ?' : '').'
                order by category_sort
            ');

            $query->execute([ $country_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($data) {
        $today = Carbon::now();
        $expiry = $today->addDays(30);

        try {
            $query = $this->db->prepare('
                insert into schools (school_billing_date, school_name, school_type, category_id, school_street, school_city, school_postal_code, country_id, school_phone, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $expiry, $data['name'], $data['type'], $data['category_id'], $data['street'], $data['city'], $data['postal_code'], $data['country'], $data['phone'], Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createCountryNotAvailable($data) {
        $today = Carbon::now();
        $expiry = $today->addDays(30);

        try {
            $query = $this->db->prepare('
                insert into schools (school_billing_date, school_name, school_type, category_id, school_street, school_city, school_postal_code, country_id, school_phone, created_at, updated_at, school_custom_category)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $expiry, $data['name'], $data['type'], $data['category_id'], $data['street'], $data['city'], $data['postal_code'], $data['country'], $data['phone'], Carbon::now(), Carbon::now(), $data['custom_school_category'] ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function createUS($data) {
        $today = Carbon::now();
        $expiry = $today->addDays(30);
        try {
            $query = $this->db->prepare('
                insert into schools (school_billing_date, school_name, school_type, category_id, school_street, school_city, school_postal_code, country_id, school_phone, created_at, updated_at, country_subdivision_id)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $expiry, $data['name'], $data['type'], $data['category_id'], $data['street'], $data['city'], $data['postal_code'], $data['country'], $data['phone'], Carbon::now(), Carbon::now(), $data['country_subdivision'] ]);
            $school_id=$this->db->lastInsertId();
            if ($data['rage_age']=='0'){
                $data['rage_age']=$data['other_age'];
                $this->logger->debug('rage_age (must be 0): ' . $data['rage_age']);
            } else {
                $this->logger->debug('rage_age (must not be 0): ' . $data['rage_age']);
            }
            if ($data['category_id']=='39'){
                //$data['category_id']=$data['other_type'];
                $data['category_id']=$data['custom_school_category'];
                $this->logger->debug('category_id (must be 39): ' . $data['category_id']);
            } else {
                $this->logger->debug('category_id (must not be 39): ' . $data['category_id']);
            }
            $query2 = $this->db->prepare('
                insert into us_schools (hours,curriculum,age_range,category,school_id, custom_curriculum)
                values (?, ?, ?, ?, ?, ?)
            ');
            /*if (!isset($data['custom_curriculum']) || empty($data['custom_curriculum'])) {
                $data['custom_curriculum'] = '';
            }*/
            //$data['custom_curriculum'] = '';
            $query2->execute([ $data['time'], $data['curriculum'], $data['rage_age'], $data['category_id'], $school_id, $data['custom_curriculum'] ]);
            return $school_id;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createUser($school_id, $user_id, $role = 0, $status = 'P') {
        try {
            $token_id = StringHandler::getToken(16);

            $query = $this->db->prepare('
                insert into school_user (school_id, user_id, token_id, role, status)
                values (?, ?, ?, ?, ?)
                on duplicate key update
                    token_id = values(token_id)
            ');

            $query->execute([ $school_id, $user_id, $token_id, $role, $status ]);

            return $token_id;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setUserStatus($school_id, $user_id, $status) {
        try {
            $query = $this->db->prepare('
                update school_user
                set status = ?
                where school_id = ?
                and user_id = ?
            ');

            return $query->execute([ $status, $school_id, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setBillingDate($stripe_id, $date) {
        try {
            $query = $this->db->prepare('
                update schools
                set school_billing_date = ?
                where stripe_id = ?
            ');

            return $query->execute([ $date, $stripe_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setUserRole($school_id, $user_id, $role) {
        try {
            $query = $this->db->prepare('
                update school_user
                set role = ?
                where school_id = ?
                and user_id = ?
            ');

            return $query->execute([ $role, $school_id, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

     public function setIdStripe($school_id, $id_account) {

        try {
            $query = $this->db->prepare('
                update schools
                set stripe_connect_id = ?
                where school_id = ?
            ');
            return $query->execute([ $id_account , $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
     }

    public function setDetails($school_id, $data) {
        try {

            $query = $this->db->prepare('
                update schools
                set school_name = ?,
                    school_phone = ?,
                    school_type = ?,
                    category_id = ?,
                    school_street = ?,
                    school_city = ?,
                    school_postal_code = ?,
                    updated_at = ?
                where school_id = ?
            ');
            if ($data['country']=='US'){
                $query2 = $this->db->prepare('
                    update us_schools
                    set hours = ?,
                        curriculum = ?,
                        age_range = ?,
                        category = ?,
                    where school_id = ?
                ');
                $query2->execute([ $data['hours'], $data['Curriculum'], $data['age_range'], $data['category'],$school_id ]);
            }
            return $query->execute([ $data['name'], $data['phone'], $data['type'], $data['category_id'], $data['street'], $data['city'], $data['postal_code'], Carbon::now(), $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setAvatar($school_id, $url) {
        try {
            $query = $this->db->prepare('
                update schools
                set school_avatar_url = ?,
                    updated_at = ?
                where school_id = ?
            ');

            return $query->execute([ $url, Carbon::now(), $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setStripeId($school_id, $stripe_id) {
        try {
            $query = $this->db->prepare('
                update schools
                set stripe_id = ?,
                    updated_at = ?
                where school_id = ?
            ');

            return $query->execute([ $stripe_id, Carbon::now(), $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setVatId($school_id, $vat_id) {
        try {
            $query = $this->db->prepare('
                update schools
                set school_vat_id = ?,
                    updated_at = ?
                where school_id = ?
            ');

            return $query->execute([ $vat_id, Carbon::now(), $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeUser($school_id, $user_id) {
        try {
            $query = $this->db->prepare('
                delete from school_user
                where school_id = ?
                and user_id = ?
            ');

            return $query->execute([ $school_id, $user_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function getParent( $user_id) {
        try {
            $query = $this->db->prepare('
                select * from users
                INNER JOIN child_user
                  ON child_user.user_id = users.user_id 
                INNER JOIN children
                  ON children.child_id = child_user.child_id
                INNER JOIN schools
                  ON schools.school_id = children.school_id
                WHERE users.user_id = ?
            ');
            $query->execute([ $user_id ]);
            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function getSchoolID( $user_id, $child_id ) {
        try {
            $query = $this->db->prepare('
                SELECT school_id from children
				INNER JOIN child_user
				ON child_user.child_id = children.child_id
				INNER JOIN users
				ON users.user_id = child_user.user_id
				WHERE users.user_id = ?
				AND children.child_id = ?
            ');
            $query->execute([ $user_id, $child_id ]);
            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
