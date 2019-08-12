<?php

use \Carbon\Carbon;


class RiskAssessment extends App\Models\Model {
    
    public function getAllAssessments($school_id, $user_id, $date, $user_type) {
    	try {
    		$query_string = '
    			SELECT * FROM risk_assessments
    			WHERE school_fk = ?
    			AND date = ?
    			AND deleted = 0
    			AND (user_fk = ?';
    		
    		// retrieve data that are shared if authorized
    		if( $user_type==="T" )
    			$query_string .= '
    			OR shareteachers = 1';
    		else
    			$query_string .= '
    			OR shareparents = 1';
    		
    		$query_string .= ')';
    		
    		$query = $this->db->prepare($query_string);
    		$query->execute([ $school_id, $date, $user_id ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function getAllRisks($assessment_id) {
    	try {
    		$query = $this->db->prepare('
    			SELECT * FROM risks
    			WHERE risk_assessment_fk = ?
    			AND deleted = 0
			');
    		$query->execute([ $assessment_id ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function getAllRisksCount($assessment_id) {
    	try {
    		$query = $this->db->prepare('
    			SELECT 1 FROM risks
    			WHERE risk_assessment_fk = ?
    			AND deleted = 0
			');
    		$query->execute([ $assessment_id ]);
    		
		    return $query->rowCount();
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }

    public function getAssessment($assessment_id) {
    	try {
    		$query = $this->db->prepare('
    			SELECT * FROM risk_assessments
	    			WHERE risk_assessment_id = ?');
    		$query->execute([ $assessment_id ]);
    		
    		return $query->fetch( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function getRisk($assessment_id, $risk_id) {
    	try {
    		$query = $this->db->prepare('
    			SELECT * FROM risks
    			WHERE risk_assessment_fk = ?
    			AND risk_id = ?
    			AND deleted = 0
			');
    		$query->execute([ $assessment_id, $risk_id ]);
    		
    		return $query->fetch( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function getRisksToRemind() {
    	try {
    		$query = $this->db->prepare('
    			SELECT risk_assessments.school_fk,
					risk_assessments.user_fk,
					risk_assessments.risk_assessment_id,
					risk_assessments.date as assessment_date,
					risks.date as risk_date
				FROM risks
    				INNER JOIN risk_assessments ON risk_assessments.risk_assessment_id = risks.risk_assessment_fk
    			WHERE risk_assessments.deleted = 0
    			AND risks.deleted = 0
    			AND risks.date = ?
			');
    		    		
    		$query->execute([ date("Y-m-d", strtotime('+1 week')) ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function addAssessment($school_id, $user_id, $name, $date, $minimise, $review, $shareTeachers, $shareParents) {
    
    	try {
			$query = $this->db->prepare('
                insert into risk_assessments (school_fk, user_fk, name, date, minimise, review, shareteachers, shareparents)
                values (?, ?, ?, ?, ?, ?, ?, ?)
            ');

			$query->execute([ $school_id, $user_id, $name, $date, $minimise, $review, $shareTeachers, $shareParents ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }
    
    public function updateAssessment($assessment_id, $name, $date, $minimise, $review, $shareTeachers, $shareParents) {
    
    	$name = ($name == "" ? null : $name);
    	
    	try {
    		$query = $this->db->prepare('
    			UPDATE risk_assessments
    				SET name = ?,
    				date = ?,
    				minimise = ?, 
    				review = ?, 
    				shareteachers = ?, 
    				shareparents = ?
	    			WHERE risk_assessment_id = ?');
    		$query->execute([ $name, $date, $minimise, $review, $shareTeachers, $shareParents, $assessment_id ]);
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function addRisk($risk_assessment_fk, $description, $people, $rating, $actions, $further_actions, $date, $rating_after) {

    	$date = ($date == "" ? null : $date);

    	try {
    		$query = $this->db->prepare('
    			INSERT INTO risks (risk_assessment_fk, description, people, rating, actions, further_actions, date, rating_after)
	    			VALUES ( ?,?,?,?,?,?,?,? )
	    	');
    		
    		$query->execute([ $risk_assessment_fk, $description, $people, $rating, $actions, $further_actions, $date, $rating_after ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function updateRisk($risk_id, $description, $people, $rating, $actions, $further_actions, $date, $rating_after) {

    	$date = ($date == "" ? null : $date);

    	try {
    		$query = $this->db->prepare('
    			UPDATE risks
	    			SET description = ?,
	    			people = ?,
	    			rating = ?,
	    			actions = ?,
	    			further_actions = ?,
	    			date = ?,
	    			rating_after = ?
	    			WHERE risk_id = ?');
    		$query->execute([ $description, $people, $rating, $actions, $further_actions, $date, $rating_after, $risk_id ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function deleteAssessment($assessment_id) {
    	try {
    		$query = $this->db->prepare('
    			UPDATE risk_assessments
	    		SET deleted = 1
    			WHERE risk_assessment_id = ?');
    		$query->execute([ $assessment_id ]);	
    		
    		// delete risks
    		$risks = $this->getAllRisks($assessment_id);
    		foreach($risks as $risk){
    			$this->deleteRisk($risk->risk_id);
    		}
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function deleteRisk($risk_id) {
    	try {
    		$query = $this->db->prepare('
    			UPDATE risks
	    		SET deleted = 1
    			WHERE risk_id = ?');
    		return $query->execute([ $risk_id ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }

}  
