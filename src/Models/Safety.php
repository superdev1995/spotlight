<?php

use \Carbon\Carbon;


class Safety extends App\Models\Model {
    
    public function getAllDuties($school_id) {
    	try {
    		$query_string = '
    			SELECT * FROM safety_fire_duties
    			WHERE school_fk = ?';
    		
    		// retrieve data that are shared if authorized
    		
    		$query = $this->db->prepare($query_string);
    		$query->execute([ $school_id ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function getAllRegister($school_id) {
    	try {
    		$query_string = '
    			SELECT * FROM safety_general_register
    			WHERE school_fk = ?
    			AND  deleted = 0;';
    		
    		// retrieve data that are shared if authorized
    		$query = $this->db->prepare($query_string);
    		$query->execute([ $school_id ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function getAllAttachments($school_id) {
    	try {
    		$query_string = '
    			SELECT * FROM safety_attachments
    			WHERE school_fk = ?;';
    		
    		$query = $this->db->prepare($query_string);
    		$query->execute([ $school_id ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function getAttachment($attachment_id, $school_id){
    	try {
    		$query_string = '
    			SELECT * FROM safety_attachments
    			WHERE attachment_id = ?
    			AND school_fk = ?;';
    		
    		// retrieve data that are shared if authorized
    		
    		$query = $this->db->prepare($query_string);
    		$query->execute([ $attachment_id, $school_id ]);
    		
    		return $query->fetch( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function getAllInventory($school_id) {
    	try {
    		$query_string = '
    			SELECT * FROM safety_inventory
    			WHERE school_fk = ?
    			AND deleted = 0;';
    		
    		// retrieve data that are shared if authorized
    		
    		$query = $this->db->prepare($query_string);
    		$query->execute([ $school_id ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function getAllLogs($school_id) {
    	try {
    		$query_string = '
    			SELECT * FROM safety_log_book
    			WHERE school_fk = ?
    			AND  deleted = 0;';
    		
    		// retrieve data that are shared if authorized
    		
    		$query = $this->db->prepare($query_string);
    		$query->execute([ $school_id ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    
    public function getEntriesToRemind() {
    	try {
    		$query = $this->db->prepare('
    			SELECT school_fk,
					date,
					date_to_be_completed
				FROM safety_general_register
    			WHERE deleted = 0
    			AND completed = 0
    			AND date_to_be_completed = ?
			');
    		
    		$query->execute([ date("Y-m-d", strtotime('+1 week')) ]);
    		
    		return $query->fetchAll( PDO::FETCH_OBJ );
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function addDuty($type, $school_id, $name, $phone, $duties, $comments) {
    
    	try {
			$query = $this->db->prepare('
                insert into safety_fire_duties (type, school_fk, name, phone, duties, comments)
                values (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = ?, phone = ?, duties = ?, comments = ?
    
            ');
			$query->execute([ $type, $school_id, $name, $phone, $duties, $comments, $name, $phone, $duties, $comments ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }
    
    public function addItem($school_id, $location_of_equipment, $number, $type, $location) {
    
    	try {
			$query = $this->db->prepare('
                insert into safety_inventory (school_fk, location_of_equipment, number, type, location)
                values (?, ?, ?, ?, ?);
    
            ');
			$query->execute([ $school_id, $location_of_equipment, $number, $type, $location ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }
    
    public function updateItem($id, $location_of_equipment, $number, $type, $location) {

    	try {
    		$query = $this->db->prepare('
    			UPDATE safety_inventory
	    			SET location_of_equipment = ?,
	    			number = ?,
	    			type = ?,
	    			location = ?
	    			WHERE id = ?');
	    	$query->execute([ $location_of_equipment, $number, $type, $location, $id ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function deleteItem($id) {
    	try {
    		$query = $this->db->prepare('
    			UPDATE safety_inventory
	    		SET deleted = 1
    			WHERE id = ?');
    		return $query->execute([ $id ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }
    
    public function addRegisterEntry($school_id, $date, $time, $log_number, $documented_by, $drill, $inspection_of, $fire, $fault, $other, $action, $date_to_be_completed, $completed, $file_url) {
    
    	try {
			$query = $this->db->prepare('
                insert into safety_general_register (school_fk, date, time, log_number, documented_by, drill, inspection_of, fire, fault, other, action, 
                	date_to_be_completed, completed, file_url)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
            ');
			$query->execute([ $school_id, $date, $time, $log_number, $documented_by, $drill, $inspection_of, $fire, $fault, $other, $action, $date_to_be_completed, $completed, $file_url ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }
    
    public function updateRegisterEntry($id, $date, $time, $log_number, $documented_by, $drill, $inspection_of, $fire, $fault, $other, $action, $date_to_be_completed, $completed, $file_url) {

    	try {
    		$query = $this->db->prepare('
    			UPDATE safety_general_register
	    			SET date = ?, 
	    				time = ?, 
	    				log_number = ?, 
	    				documented_by = ?, 
	    				drill = ?, 
	    				inspection_of = ?, 
	    				fire = ?, 
	    				fault = ?, 
	    				other = ?, 
	    				action = ?, 
	    				date_to_be_completed = ?, 
	    				completed = ?,
	    				file_url = ?
	    			WHERE id = ?');
	    	$query->execute([ $date, $time, $log_number, $documented_by, $drill, $inspection_of, $fire, $fault, $other, $action, $date_to_be_completed, $completed, $file_url, $id ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function deleteRegisterEntry($id) {
    	try {
    		$query = $this->db->prepare('
    			UPDATE safety_general_register
	    		SET deleted = 1
    			WHERE id = ?');
    		return $query->execute([ $id ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }

	public function createAttachment($school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into safety_attachments (school_fk, name, file_url)
                values (?, ?, ?)
            ');

            return $query->execute([ $school_id, $data['name'], $data['file_url'] ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

	public function addLogEntry($school_id, $date, $nature_of_drill, $persons, $evacuation_time, $person_in_charge, $comments) {
    
    	try {
			$query = $this->db->prepare('
                insert into safety_log_book (school_fk, date, nature_of_drill, persons, evacuation_time, person_in_charge, comments)
                values (?, ?, ?, ?, ?, ?, ?);
            ');
			$query->execute([ $school_id, $date, $nature_of_drill, $persons, $evacuation_time, $person_in_charge, $comments ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }
    
    public function updateLogEntry($id, $date, $nature_of_drill, $persons, $evacuation_time, $person_in_charge, $comments) {

    	try {
    		$query = $this->db->prepare('
    			UPDATE safety_log_book
	    			SET date = ?, 
	    				nature_of_drill = ?, 
	    				persons = ?, 
	    				evacuation_time = ?, 
	    				person_in_charge = ?, 
	    				comments = ?
	    			WHERE id = ?');
	    	$query->execute([ $date, $nature_of_drill, $persons, $evacuation_time, $person_in_charge, $comments, $id ]);
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    
    }
    
    public function deleteLogEntry($id) {
    	try {
    		$query = $this->db->prepare('
    			UPDATE safety_log_book
	    		SET deleted = 1
    			WHERE id = ?');
    		return $query->execute([ $id ]);	
    		
    	} catch (PDOException $e) {
    		$this->logger->error($e->getMessage());
    	}
    }

	public function purgeAttachment($attachment_id, $school_id) {
        try {
            $query = $this->db->prepare('
                delete from safety_attachments
                where attachment_id = ?
                and school_fk = ?
            ');

            return $query->execute([ $attachment_id, $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

}  
