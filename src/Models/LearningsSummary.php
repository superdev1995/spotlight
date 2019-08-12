<?php

use \Carbon\Carbon;


class LearningsSummary extends App\Models\Model {

	public function create($school_id, $user_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into learning_summary (
                school_id,
                user_id,
                learning_summary_public,
                name_theme,
                picture_description,
                created_at,
				updated_at,
				week,
				year)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([
            	$school_id,
	            $user_id,
	            '1',
	            $data['theme'],
	            $data['picture'],
	            Carbon::now(),
				Carbon::now(),
				$data['week'],
				$data['year'] ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}
	
    public function createChild($child_id, $learningSummary_id) {
        try {
            $query = $this->db->prepare('
                insert into child_learning_summary (child_id, learning_summary_id)
                values (?, ?)
            ');

            return $query->execute([ $child_id, $learningSummary_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function createGoal($goal_id, $learningSummary_id) {
        try {
            $query = $this->db->prepare('
                insert ignore into goal_learning_summary (goal_id, learning_summary_id)
                values (?, ?)
            ');

            return $query->execute([ $goal_id, $learningSummary_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createText($text, $text_id, $learningSummary_id) {
        try {
            $query = $this->db->prepare('
                insert ignore into text_learning_summary (text_id, learning_summary_id, contents)
                values (?, ?, ?)
            ');

            return $query->execute([$text_id, $learningSummary_id, $text]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createMedia($media_id, $learningSummary_id) {
        try {
            $query = $this->db->prepare('
                insert ignore into media_learning_summary (media_id, learning_summary_id)
                values (?, ?)
            ');

            return $query->execute([ $media_id, $learningSummary_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function setDetails($learningSummary_id, $data) {
        try {
            $query = $this->db->prepare('
                update learning_summary
                    set name_theme = ?,
                    picture_description = ?,
                    updated_at = ?
                where learning_summary_id = ?
            ');

            return $query->execute([
                $data['theme'],
	            $data['picture'],
                Carbon::now(),
                $learningSummary_id
            ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

	public function getAllLearningSummaryForAYear($school_id, $year) {

		try{
			$query = $this->db->prepare('
				select * from learning_summary 
				where year = ? and
				school_id = ? and
                deleted is FALSE
			');

			$query->execute([ $year, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllLearningSummaryForAWeek($school_id, $year, $week) {

		try{
			$query = $this->db->prepare('
				select * from learning_summary 
				where year = ? and
				 week = ? and 
				 school_id = ? and
				 deleted is FALSE
			');

			$query->execute([ $year, $week, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
	
	public function getLearningSummary($learningSummary_id) {
		try {
			$query = $this->db->prepare( '
		              select * from learning_summary where learning_summary_id = ?
		              ' );

			$query->execute( [ $learningSummary_id ] );

			return $query->fetch( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
    }
    
    public function getChildren($learningSummary_id) {
        try {
            $query = $this->db->prepare('
                select * from child_learning_summary
                join children
                    on children.child_id = child_learning_summary.child_id
                where child_learning_summary.learning_summary_id = ?
                order by children.child_name
            ');

            $query->execute([ $learningSummary_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getLearningSummaryGoals($learningSummary_id) {
        try {
            $query = $this->db->prepare('
                select * from goal_learning_summary
                join framework_goals
                    on framework_goals.goal_id = goal_learning_summary.goal_id
                join framework_categories
                    on framework_categories.category_id = framework_goals.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                where goal_learning_summary.learning_summary_id = ?
                order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
            ');

            $query->execute([ $learningSummary_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getLearningSummaryTexts($learningSummary_id) {
        try {
            $query = $this->db->prepare('
                select * from text_learning_summary
                join framework_texts
                    on framework_texts.text_id = text_learning_summary.text_id
                join framework_categories
                    on framework_categories.category_id = framework_texts.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                where text_learning_summary.learning_summary_id = ?
                order by frameworks.framework_name, framework_categories.category_name, framework_texts.text_sort
            ');

            $query->execute([$learningSummary_id]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getMedias($learningSummary_id) {
        try {
            $query = $this->db->prepare('
                select * from media_learning_summary
                join medias
                    on medias.media_id = media_learning_summary.media_id
                where media_learning_summary.learning_summary_id = ?
            ');

            $query->execute([ $learningSummary_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


    public function purge($learningSummary_id) {
        try {
            $query = $this->db->prepare('
                delete learning_summary, child_learning_summary, media_learning_summary, medias, text_learning_summary,goal_learning_summary from learning_summary
                left join child_learning_summary
                    on child_learning_summary.learning_summary_id = learning_summary.learning_summary_id
                left join media_learning_summary
                    on media_learning_summary.learning_summary_id = learning_summary.learning_summary_id
                left join medias
                    on medias.media_id = media_learning_summary.media_id
                left join text_learning_summary
                    on text_learning_summary.text_id = learning_summary.learning_summary_id
                left join goal_learning_summary
                    on goal_learning_summary.goal_id = learning_summary.learning_summary_id
                where learning_summary.learning_summary_id = ?
            ');

            return $query->execute([ $learningSummary_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeGoals($learningSummary_id) {
        try {
            $query = $this->db->prepare('
                delete from goal_learning_summary
                where learning_summary_id = ?
            ');

            return $query->execute([ $learningSummary_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeTexts($learningSummary_id) {
        try {
            $query = $this->db->prepare('
                delete from text_learning_summary
                where learning_summary_id = ?
            ');

            return $query->execute([$learningSummary_id]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeMedias($story_id) {
        try {
            $query = $this->db->prepare('
                delete media_learning_summary, medias from media_learning_summary
                left join medias
                    on medias.media_id = media_learning_summary.media_id
                where media_learning_summary.learning_summary_id = ?
            ');

            return $query->execute([ $story_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
