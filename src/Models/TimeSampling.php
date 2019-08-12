<?php

use \Carbon\Carbon;

class TimeSampling extends App\Models\Model {
    public function getAll($school_id) {
        try {
            $query = $this->db->prepare('
                select * from timesampling
                where school_id = ?
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function getAllChild($room_id) {
        try {
            $query = $this->db->prepare('
                select * from children
                where room_id = ?
            ');

            $query->execute([ $room_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function getChildren($abc_id) {
        try {
            $query = $this->db->prepare('
                select * from timesampling_assoc
                join children
                    on children.child_id = timesampling_assoc.assoc_fk
                where timesampling_assoc.timesampling_fk = ?
                order by children.child_name
            ');

            $query->execute([ $abc_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function getRoom($abc_id) {
        try {
            $query = $this->db->prepare('
                select * from timesampling_assoc
                join rooms
                    on rooms.room_id = timesampling_assoc.assoc_fk
                where timesampling_assoc.timesampling_fk = ?
                order by rooms.room_id
            ');

            $query->execute([ $abc_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function getAllDate($school_id, $week) {
        try {
            $query = $this->db->prepare('
                select * from timesampling
                where school_id = ?
                and WEEKOFYEAR(created_at) = ?
            ');

            $query->execute([ $school_id, $week ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function getAllDateChild($school_id, $week, $child_id) {
        try {
            $query = $this->db->prepare('
                select * from timesampling
                join timesampling_assoc
                    on timesampling_assoc.timesampling_fk = timesampling.timesampling_id
                where timesampling.school_id = ?
				and timesampling_assoc.assoc_fk = ?
                and WEEKOFYEAR(timesampling.created_at) = ?
				and timesampling_assoc.assoc_type = "child"
				and timesampling.timesampling_public = 1
            ');

            $query->execute([ $school_id, $child_id, $week ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function getAllDatex($school_id, $week) {
        try {
            $query = $this->db->prepare('
                select * from timesampling
                join timesampling_assoc
                    on timesampling_assoc.timesampling_fk = timesampling.timesampling_id
                where timesampling.school_id = ?
                and WEEKOFYEAR(timesampling.created_at) = ?
				and timesampling.timesampling_public = 1
            ');

            $query->execute([ $school_id, $week ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from timesampling
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($timesampling_id) {
        try {
            $query = $this->db->prepare('
                select * from timesampling
                where timesampling_id = ?
                limit 1
            ');

            $query->execute([ $timesampling_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function Create($ts1, $ts2, $ts3, $ts4, $ts5, $ts6, $ts7, $ts8, $ts9, $ts10, $ts11, $ts12, $ts13, $ts14, $ts15, $ts16) {
        try {
			
            $query = $this->db->prepare('
				INSERT INTO timesampling (school_id, 
				user_id, 
				timesampling_assoc, 
				overview_number, 
				overview_date, 
				overview_time_started, 
				overview_time_finished, 
				overview_children_count, 
				overview_adult_count, 
				overview_setting, 
				overview_context, 
				overview_child_observe_desc, 
				overview_child_aim, 
				overview_evaluation, 
				overview_recommendations, 
				timesampling_public, 
				created_at, 
				updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ');

            $query->execute([ $ts1, $ts2, $ts3, $ts4, $ts5, $ts6, $ts7, $ts8, $ts9, $ts10, $ts11, $ts12, $ts13, $ts14, $ts15, $ts16, Carbon::now(), Carbon::now() ]);

			return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function Update($ts1, $ts2, $ts3, $ts4, $ts5, $ts6, $ts7, $ts8, $ts9, $ts10, $ts11, $ts12, $ts13, $ts14) {
        try {
            $query = $this->db->prepare('
				update timesampling set 
					timesampling_assoc = ?,
					overview_number = ?,
					overview_date = ?,
					overview_time_started = ?,
					overview_time_finished = ?,
					overview_children_count = ?,
					overview_adult_count = ?,
					overview_setting = ?,
					overview_context = ?,
					overview_child_observe_desc = ?,
					overview_child_aim = ?,
					overview_evaluation = ?,
					overview_recommendations = ?,
					updated_at = ?
				where timesampling_id = ?
            ');

            $query->execute([ $ts2, $ts3, $ts4, $ts5, $ts6, $ts7, $ts8, $ts9, $ts10, $ts11, $ts12, $ts13, $ts14, Carbon::now(), $ts1,]);

            return $abc_id;
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function purge($abc_id) {
        try {
            $query = $this->db->prepare('
                delete from timesampling
                where timesampling_id = ?
            ');

            return $query->execute([ $abc_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
    public function purgeASS($timesampling_id) {
        try {
            $query = $this->db->prepare('
                delete from timesampling_assoc
                where timesampling_fk = ?
            ');

            return $query->execute([ $timesampling_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function assTimeSampling($timesampling_id, $type, $associations) {
		try {
			$values = array();

			$query_string = 'INSERT INTO timesampling_assoc (timesampling_fk, assoc_type, assoc_fk) VALUES ';

			foreach($associations as $assoc) {
				$query_string .= '(?,?,?),';

				array_push($values, $timesampling_id, $type, $assoc);
			}

			$query_string = rtrim($query_string, ",");

			$query = $this->db->prepare($query_string);

			$query->execute($values);
		} catch (PDOException $e) {
			$this->logger->error("Associate TimeSampling: " . $e->getMessage());
		}
	}
	
	public function addTSBlocks($timesampling_id, $blocks) {
		// https://stackoverflow.com/questions/1176352/pdo-prepared-inserts-multiple-rows-in-single-query

		try {
			$values = array();

			$query_string = '
				INSERT INTO timesampling_block 
				(timesampling_id, 
				time, 
				actions,
				social_group,
				observation,
				created_at) VALUES ';

			/**
			 * Generate a single Insert statement instead of individual execution for each daily plan block
			 * Add values to parameters accordingly
			 */
			foreach($blocks as $block) {
				$query_string .= '(?,?,?,?,?,?),';

				array_push($values,
					$timesampling_id,
					$block['time'],
					$block['actions'],
					$block['social_group'],
					$block['observation'],
					Carbon::now()
				);
			}

			// Trim the last ',' from the query string
			$query_string = rtrim($query_string, ",");

			$query = $this->db->prepare($query_string);

			$query->execute($values);
		} catch (PDOException $e) {
			$this->logger->error("Time sampling Blocks " . $e->getMessage());
		}
	}
	public function getBlocks($timesampling_id) {
		try {
			$query = $this->db->prepare('
                select * from timesampling_block
                where timesampling_id = ?
            ');

			$query->execute([ $timesampling_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

    public function purgeBlocks($timesampling_id) {
        try {
            $query = $this->db->prepare('
                delete from timesampling_block
                where timesampling_id = ?
            ');

            return $query->execute([ $timesampling_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	public function connectMedia( $timesampling_id, $block_number, $media_id ) {
		try {
			$query = $this->db->prepare( '
                INSERT INTO media_timesampling (media_id, block_number, timesampling_id)
                VALUES (?,?,?)
            ' );

			return $query->execute( [ $media_id, $block_number, $timesampling_id ] );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}
	
    public function getMedias($timesampling_id) {
        try {
            $query = $this->db->prepare('
                select * from media_timesampling
                join medias
                    on medias.media_id = media_timesampling.media_id
                where media_timesampling.timesampling_id = ?
            ');

            $query->execute([ $timesampling_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}