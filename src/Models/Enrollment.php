<?php

class Enrollment extends App\Models\Model {

    public function confirmEnrollment($id){
        try {
            $query = 'UPDATE `enrollments` SET `confirmed`= 1, `decline`= 0  WHERE `child_id`= :child_id ';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":child_id", $id);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function denyEnrollment($id){
        try {

            //$query_child = "UPDATE `children` SET `enrollment_id`= 0 WHERE `child_id`= :child_id";
            $query_child = "UPDATE `enrollments` SET `decline`= 1 WHERE `child_id`= :child_id";
            $stmt = $this->db->prepare($query_child);
            $stmt->bindParam(":child_id", $id);
            $stmt->execute();

            /*$query = "DELETE FROM `enrollments` WHERE `child_id`= :child_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":child_id", $id);
            $stmt->execute();*/

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function joinChildEnrollment() {
        try {
            $query = $this->db->prepare('
                 select * from enrollments
                join children
                    on enrollments.child_id = children.child_id
                    where `enrollments`.`confirmed` = 0
                    and children.enrollment_id = 1
            ');

            $query->execute();

            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function joinChildEnrollmentApproved() {
        try {
            $query = $this->db->prepare('
                select * from enrollments
                join children
                    on enrollments.child_id = children.child_id
                    where `enrollments`.`confirmed` = 1
            ');

            $query->execute();

            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildEnrollment($child_id) {
        try {
            $query = $this->db->prepare('
                select * from enrollments
                join children
                    on enrollments.child_id = children.child_id
                    where enrollments.child_id = '.$child_id.'
            ');

            $query->execute();

            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setNewEnrollment($table = null, $fields = array()) {
        $fields_count = count($fields);
        $i = 1;
        $put_coma = ", ";
        $table_fields = '';
        $field_binds = '';
        foreach ($fields as $field => $value) {

            if ($i == $fields_count) { $put_coma = ""; }
            $table_fields .= $field . $put_coma;
            $field_binds .= ":" . $field . $put_coma;
            $i++;

        }

        $stmt = $this->db->prepare("INSERT INTO " . $table . " (" . $table_fields . ") VALUES (" . $field_binds . ")");

        foreach ($fields as $field => $value) {
            $stmt->bindValue(":" . $field, $value);
        }
        //echo '<pre>';print_r($stmt);echo '</pre>';exit();
        $stmt->execute();

    }

    public function setChildIfExist($table = null, $fields = array(), $id = array()) {
        $table_fields = '';
        $i = 1;
        $put_coma = ", ";
        $fields['child_name'].=" ".$fields['surname'];
        unset($fields['surname']);
        $fields['enrollment_id']=1;
        $fields_count = count($fields);
        foreach ($fields as $field => $value) {
            if ($i == $fields_count) { $put_coma = ""; }
            $table_fields .= $field . ' = :' . $field . $put_coma;
            $i++;
        }
        $query = "UPDATE ".$table." SET ".$table_fields."  where ".$id['id_field']." = ".$id['id_value']." ";
        $stmt = $this->db->prepare($query);
        foreach ($fields as $field => $value) {
            $stmt->bindValue(":" . $field, $value);
        }
        $stmt->execute();
    }

    public function setEnrollmentAndChild($table = null, $fields = array(),$room_id) {

        $table_fields = '';
        $field_binds = '';
        $children_arr=json_decode($fields['children'],true);
        $i = 1;
        $put_coma = ", ";
        $children_arr['child_name'].=" ".$children_arr['surname'];
        unset($children_arr['surname']);

        $children_arr['enrollment_id']=1;
        $children_arr['room_id']=$room_id;
        $children_arr['school_id']=$_SESSION['school_id'];
        $children_arr['created_at'] = \Carbon\Carbon::now();
        $children_arr['updated_at'] = \Carbon\Carbon::now();


        $fields_count = count($children_arr);

        foreach ($children_arr as $child => $value) {

            if ($i == $fields_count) { $put_coma = ""; }
            $table_fields .= $child . $put_coma;
            $field_binds .= ":" . $child . $put_coma;
            $i++;

        }

        var_dump($children_arr);

        //if($children_arr->child_birthday) {
            $stmt = $this->db->prepare('INSERT INTO children (' . $table_fields . ') VALUES (' . $field_binds . ')');
            foreach ($children_arr as $child => $value) {
                $stmt->bindValue(":" . $child, $value);
            }
            $stmt->execute();
        //}


        $stmt_contact = $this->db->prepare("select `child_id` from `children` where `children`.`child_name`= :child_name  order by `child_id` desc LIMIT 1");
        $stmt_contact->bindParam(":child_name",$children_arr['child_name']);

        $stmt_contact->execute();
        $child_id = $stmt_contact->fetchAll();
        $stmt_contact->closeCursor();

        $child_ID=$child_id[0]['child_id'];


        unset($fields['children']);
        unset($fields['room_id']);
        $fields['child_id']=$child_ID;
        $fields_count = count($fields);
        $i = 1;
        $put_coma = ", ";
        $table_fields = '';
        $field_binds = '';


        foreach ($fields as $field => $value) {
            if ($i == $fields_count) {
                $put_coma = "";
            }
            $table_fields .= $field . $put_coma;
            $field_binds .= ":" . $field . $put_coma;
            $i++;
        }

        $stmt_contact = $this->db->prepare("INSERT INTO `".$table."` (".$table_fields.") VALUES (".$field_binds.")");
        foreach ($fields as $field => $value) {
            $stmt_contact->bindValue(":" . $field, $value);
        }

        //echo "<pre>";print_r($stmt_contact);echo "</pre>";exit();
        $stmt_contact->execute();
    }

    public function getEnrollments() {
        try {
             $query = $this->db->prepare('
                select * from children where enrollment_id IS NULL
            ');

            $query->execute();

            return $query->fetchAll();

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildrenForParent($user_id) {
        try {
            $query = $this->db->prepare('
                SELECT
                *
                from enrollments e
                right join child_user cu on(e.child_id = cu.child_id)
                join children ch on(ch.child_id = cu.child_id)
                where cu.user_id = '.$user_id.'
            ');
            
            $query->execute();

            return $query->fetchAll();

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getEnrollmentsCount($school_id) {
        try {
            $query_enrollments = $this->db->prepare("
                SELECT confirmed
                FROM enrollments
                INNER JOIN children ON enrollments.child_id = children.child_id
                WHERE enrollments.confirmed = 0
                AND children.enrollment_id = 1
                AND children.school_id = '$school_id'
            ");

            $query_enrollments->execute();
            $enrollmentsCount = $query_enrollments->fetchAll();
            $query_enrollments->closeCursor();

            return count($enrollmentsCount);

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }  
    }

    public function editEnrollment($table = null, $fields = array(),$child_id){
        $i = 1;
        $put_coma = ", ";
        $table_fields = '';
        unset($fields['child_id']);

        $fields_count=count($fields);
        $child_ID=$child_id;
        foreach ($fields as $field => $value) {
            if ($i == $fields_count) { $put_coma = ""; }
            $table_fields .= $field . ' = :' . $field . $put_coma;
            $i++;
        }
        $stmt_contact = $this->db->prepare("UPDATE `" . $table . "`  SET " . $table_fields . " WHERE `enrollments`.`child_id` = '" . $child_ID."'");
        foreach ($fields as $field => $value) {
            $stmt_contact->bindValue(":" . $field, $value);
        }
        $stmt_contact->execute();
    }

    public function setEnrollmentOnlyForParent($child_id, $enrollment_id){
        try {
            $query = "UPDATE `children` SET `enrollment_id` = :enrollment_id where `children`.`child_id` = :child_id";
            $stmt = $this->db->prepare($query);

            $query_child = "UPDATE `enrollments` SET `decline`= 0 WHERE `child_id`= :child_id";
            $stmt2 = $this->db->prepare($query_child);

            $stmt->bindValue(":enrollment_id", $enrollment_id);
            $stmt->bindValue(":child_id", $child_id);

            $stmt2->bindValue(":child_id", $child_id);
return $enrollment_id;
            $stmt->execute();
            $stmt2->execute();

        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

}