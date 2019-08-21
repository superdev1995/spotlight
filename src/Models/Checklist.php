<?php

use \Carbon\Carbon;


class Checklist extends App\Models\Model {
    public function getAll($child_id, $month) {
        try {
            $query = $this->db->prepare('
                select * from checklists
                join children
                    on children.school_id = checklists.school_id
                where checklists.checklist_month_min <= ?
                and checklists.checklist_month_max >= ?
                and checklists.checklist_status = "A"
                and children.child_id = ?
                order by checklist_month_min, checklist_name
            ');

            $query->execute([ $month, $month, $child_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllSchool($school_id) {
        try {
            $query = $this->db->prepare('
                select * from checklists
                where school_id = ?
                and checklist_status = "A"
                order by checklist_month_min asc, checklist_name asc
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($checklist_id) {
        try {
            $query = $this->db->prepare('
                select * from checklists
                where checklist_id = ?
            ');

            $query->execute([ $checklist_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllGlobal($country_id) {
        try {
            $query = $this->db->prepare('
                select * from checklists
                where (
                    country_id = ?
                    or country_id is null
                )
                and school_id is null
                order by checklist_month_min, checklist_name
            ');

            $query->execute([ $country_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllMilestones($checklist_id, $category_id) {
        try {
            $query = $this->db->prepare('
                select * from checklist_milestones
                where checklist_id = ?
                and category_id = ?
                and milestone_status = "A"
                order by milestone_sort
            ');

            $query->execute([ $checklist_id, $category_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getMilestone($milestone_id) {
        try {
            $query = $this->db->prepare('
                select * from checklist_milestones
                where milestone_id = ?
            ');

            $query->execute([ $milestone_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getRedFlags($month) {
        try {
            $query = $this->db->prepare('
                select * from checklist_red_flags
                where red_flag_month_min <= ?
                and red_flag_month_max >= ?
                order by red_flag_sort
            ');

            $query->execute([ $month, $month ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCategories() {
        try {
            $query = $this->db->prepare('
                select * from checklist_categories
                order by category_sort
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getObservations($child_id) {
        try {
            $query = $this->db->prepare('
                select *, checklist_child.created_at as checklist_created_at from checklist_child
                join users
                    on users.user_id = checklist_child.user_id
                where checklist_child.child_id = ?
            ');

            $query->execute([ $child_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllObservations($school_id) {
        try {
            $query = $this->db->prepare('
                select checklist_child.milestone_id, checklist_child.child_id, checklist_milestones.milestone_description from checklist_child
                join checklist_milestones
                    on checklist_milestones.milestone_id = checklist_child.milestone_id
                join children
                    on children.child_id = checklist_child.child_id
                where children.school_id = ?
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(distinct(token_id)) from checklist_child
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildCount($child_id) {
        try {
            $query = $this->db->prepare('
                select distinct token_id from checklist_child
                where child_id = ?
            ');

            $query->execute([ $child_id ]);

            return $query->rowCount();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into checklists (school_id, checklist_name, checklist_month_min, checklist_month_max, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $school_id, $data['name'], $data['month_min'], $data['month_max'], Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createMilestone($checklist_id, $data) {
        $sort = $data['sort'] ? $data['sort'] : 0;

        try {
            $query = $this->db->prepare('
                insert into checklist_milestones (milestone_description, checklist_id, category_id, milestone_sort, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $data['description'], $checklist_id, $data['category_id'], $sort, Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createObservation($token_id, $milestone_id, $child_id, $user_id, $observation, $red_flag = 0) {
        try {
            $query = $this->db->prepare('
                insert into checklist_child (token_id, milestone_id, child_id, user_id, observation, red_flag, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $token_id, $milestone_id, $child_id, $user_id, $observation, $red_flag, Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setChecklist($checklist_id, $data) {
        try {
            $query = $this->db->prepare('
                update checklists
                set checklist_name = ?,
                    checklist_month_min = ?,
                    checklist_month_max = ?,
                    updated_at = ?
                where checklist_id = ?
            ');

            return $query->execute([ $data['name'], $data['month_min'], $data['month_max'], Carbon::now(), $checklist_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setMilestone($milestone_id, $data) {
        try {
            $query = $this->db->prepare('
                update checklist_milestones
                set milestone_description = ?,
                    category_id = ?,
                    updated_at = ?
                where milestone_id = ?
            ');

            return $query->execute([ $data['description'], $data['category_id'], Carbon::now(), $milestone_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setStatus($checklist_id, $status) {
        try {
            $query = $this->db->prepare('
                update checklists
                set checklist_status = ?,
                    updated_at = ?
                where checklist_id = ?
            ');

            return $query->execute([ $status, Carbon::now(), $checklist_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setMilestoneStatus($milestone_id, $status) {
        try {
            $query = $this->db->prepare('
                update checklist_milestones
                set milestone_status = ?,
                    updated_at = ?
                where milestone_id = ?
            ');

            return $query->execute([ $status, Carbon::now(), $milestone_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setMilestoneId($child_id, $milestone_id, $new_milestone_id) {
        try {
            $query = $this->db->prepare('
                update checklist_child
                set milestone_id = ?
                where milestone_id = ?
                and child_id = ?
                and red_flag = 0
            ');

            return $query->execute([ $new_milestone_id, $milestone_id, $child_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
