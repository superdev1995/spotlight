<?php

use \Carbon\Carbon;

class Frameworks extends App\Models\Model {
    
    public function getAll() {
        try {
            $query = $this->db->prepare('
                select * from frameworks
                where frameworks.school_id IS NOT NULL 
                order by framework_month_min, framework_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllByName($name) {
        try {
            $query = $this->db->prepare('
                select framework_id, framework_name from frameworks
                where framework_name = ?
            ');

            $query->execute([$name]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllSchool($school_id) {
        try {
            $query = $this->db->prepare('
                select * from frameworks
                where school_id = ?
                and framework_status = "A"
                order by framework_month_min asc, framework_name asc
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($framework_id) {
        try {
            $query = $this->db->prepare('
                select * from frameworks
                where framework_id = ?
            ');

            $query->execute([ $framework_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from frameworks
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllCategories() {
        try {
            $query = $this->db->prepare('
                select * from framework_categories
                order by category_sort
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


    public function getCategories($framework_id) {
        try {
            $query = $this->db->prepare('
                select * from framework_categories
                where framework_categories.framework_id=?
                order by framework_categories.category_group, framework_categories.category_sort
            ');

            $query->execute([ $framework_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCategory($category_id) {
        try {
            $query = $this->db->prepare('
                select * from framework_categories
                where framework_categories.category_id=?
            ');

            $query->execute([ $category_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCategoriesByMonth($country_id, $months) {
        try {
            $query = $this->db->prepare('
                select * from framework_categories
                join frameworks
                     on frameworks.framework_id = framework_categories.framework_id
                where frameworks.country_id = ?
                and (
                    (
                        frameworks.framework_month_min <= ?
                        and frameworks.framework_month_max >= ?
                    ) or (
                        frameworks.framework_month_min is null
                        and frameworks.framework_month_max is null
                    )
                )
                order by framework_categories.category_group, framework_categories.category_sort
            ');

            $query->execute([ $country_id, $months, $months ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllGoals($framework_id, $category_id) {
        try {
            $query = $this->db->prepare('
            select * from framework_goals
                join framework_categories
                    on framework_categories.category_id = framework_goals.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                where framework_categories.framework_id = ? and framework_goals.category_id = ?
                and goal_status = "A"
                order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
            ');

            $query->execute([ $framework_id,$category_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getGoal($group_id) {
        try {
            $query = $this->db->prepare('
                select * from framework_goals
                where goal_id = ? 
                order by goal_sort
            ');

            $query->execute([ $goal_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


    public function getGoals($category_id) {
        try {
            $query = $this->db->prepare('
                select * from framework_goals
                where category_id = ?
                order by goal_sort
            ');

            $query->execute([ $category_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getTexts($category_id) {
        try {
            $query = $this->db->prepare('
                select * from framework_texts
                where category_id = ?
                order by text_sort
            ');

            $query->execute([$category_id]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getStoryGoals($story_id) {
        try {
            $query = $this->db->prepare('
                select * from goal_story
                join framework_goals
                    on framework_goals.goal_id = goal_story.goal_id
                join framework_categories
                    on framework_categories.category_id = framework_goals.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                where goal_story.story_id = ?
                order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
            ');

            $query->execute([ $story_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


    public function create($school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into frameworks (school_id, framework_name, framework_month_min, framework_month_max,country_id)
                values (?, ?, ?, ?,?)
            ');

            $query->execute([ $school_id, $data['name'], $data['month_min'], $data['month_max'], $data['country_id']]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createCategory($framework_id, $data) {
        try {
            $query = $this->db->prepare('
                insert ignore into framework_categories (framework_id, category_name, category_group,category_description)
                values (?, ?, ?,?)
            ');

            $query->execute([$framework_id, $data['category_name'], $data['group_name'],$data['category_description']]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createGoalOne($category_id, $desc,$counter) {
        
     
            try {
                
                $query = $this->db->prepare('
                    insert into framework_goals (category_id, goal_description, goal_sort)
                    values (?, ?, ?)
                ');

                 $query->execute([ $category_id, $desc, $counter ]);
             
                
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
      
        return true;
    }
    public function createGoal($category_id, $data) {
        $counter=0;
        do{
            try {
                
                $query = $this->db->prepare('
                    insert into framework_goals (category_id, goal_description, goal_sort)
                    values (?, ?, ?)
                ');

                 $query->execute([ $category_id, $data['goal'.$counter], $counter ]);
                $counter=$counter+1;
                
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
        }while($counter < $data['count']);
        return true;
    }
    public function setGoals($data){
        $counter=0;
        do{
            try {
                
                $query = $this->db->prepare('
                    UPDATE `framework_goals` 
                    SET goal_description = ?
                    WHERE goal_id = 13207
                    
                ');

                 $query->execute([$data['goal_description'.$counter], $data['goal_id'.$counter]]);
                $counter=$counter+1;
                
            } catch (PDOException $e) {
                $this->logger->error($e->getMessage());
            }
        }while($data['count']>=$counter);
        return true;
    }




   public function setStatus( $framework_id, $status ) {
        try {
            $query = $this->db->prepare('
            UPDATE `frameworks` SET `framework_status`= ? WHERE framework_id = ?
            ');

            return $query->execute([ $status, $framework_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setCategoryStatus( $category_id, $status ) {
        try {
            $query = $this->db->prepare('
            UPDATE `framework_categories` SET `category_status`= ? WHERE category_id = ?
            ');

            return $query->execute([ $status, $category_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setGoalStatus( $goal_id, $status ) {
        try {
            $query = $this->db->prepare('
            UPDATE `framework_goals` SET `goal_status`= ? WHERE goal_id = ?
            ');

            return $query->execute([ $status, $goal_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setCategory($category_id, $data) {
        try {
            $query = $this->db->prepare('
            UPDATE `framework_categories` 
            SET `category_name`= ?,`category_description`= ?
            WHERE category_id = ?
            ');

            return $query->execute([ $data['category_name'],$data['category_description'], $category_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setGroup($data) {
        try {
            $query = $this->db->prepare('
            update framework_categories set category_group = ? 
            where category_group = ? and framework_id = ?
            ');

            return $query->execute([$data['group_newname'], $data['group_name'], $data["framework_id"] ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function deleteGroup($data) {
        try {
            $query = $this->db->prepare('
            delete from framework_categories 
            where category_group = ? and framework_id = ?
            ');

            return $query->execute([$data['group_name'], $data["framework_id"] ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function purge($story_id) {
        try {
            $query = $this->db->prepare('
                delete from stories
                where story_id = ?
            ');

            return $query->execute([ $story_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeGoals($category_id) {
        try {
            $query = $this->db->prepare('
                delete from framework_goals
                where category_id = ?
            ');

            return $query->execute([ $category_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeCategory($category_id) {
        try {
            $query = $this->db->prepare('
                delete from framework_categories
                where category_id = ?
            ');

            return $query->execute([$category_id]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeMediaStory($story_id) {
        try {
            $query = $this->db->prepare('
                delete from media_story
                where story_id = ?
            ');

            return $query->execute([ $story_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeMedia($media_id) {
        try {
            $query = $this->db->prepare('
                delete from medias
                where media_id = ?
            ');

            return $query->execute([ $media_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setFramework($framework_id, $data) {
        try {
            $query = $this->db->prepare('

                UPDATE `frameworks` SET 
                `framework_name`=?,
                `framework_month_min`=?,
                `framework_month_max`=? 
                WHERE framework_id = ?
            ');

            return $query->execute([ $data['name'], $data['month_min'], $data['month_max'],$framework_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
