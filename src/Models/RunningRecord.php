<?php

use \Carbon\Carbon;

class RunningRecord extends App\Models\Model {
	/**
	 * inserts new running record
	 *
	 * @param $school_id
	 * @param $data
	 *
	 * @return mixed
	 */
	public function addRunningRecord( $school_id, $data ) {

		try {
			$query = $this->db->prepare( '
                INSERT INTO `running_records` (
                school_id,
                user_id,
                observer,	 
				course,	 
				record_date, 
				start_time, 
				end_time, 
				age,	 
				context,	 
				purpose,	 
				observation,	 
				interpretation,	 
				extension,	 
				teacher_comments,	 
				manager_comments,	 
				public,
				created_at,	 
				updated_at
                )
                VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
                )
            ' );

			$query->execute( [
				$school_id,
				$data['user_id'],
				$data['observer'],
				$data['course'],
				$data['date'] ? $data['date'] : null,
				$data['start'],
				$data['end'],
				$data['age'],
				$data['context'],
				$data['purpose'],
				$data['observation'],
				$data['interpretation'],
				$data['extension'],
				$data['teacher'],
				$data['manager'],
				$data['record_public'],
				Carbon::now(),
				Carbon::now()
			] );

			return $this->db->lastInsertId();
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}

	}

	/**
	 * updates running record
	 *
	 * @param $running_record_id
	 * @param $data
	 *
	 * @return mixed
	 */
	public function editRunningRecord( $running_record_id, $data ) {

		try {
			$query = $this->db->prepare( '
                UPDATE `running_records` SET 
                observer = ?,	 
				course = ?,	 
				record_date = ?, 
				start_time = ?, 
				end_time = ?, 
				age = ?,	 
				context = ?,	 
				purpose = ?,	 
				observation = ?,	 
				interpretation = ?,	 
				extension = ?,	 
				teacher_comments = ?,	 
				manager_comments = ?,	 
				public = ?,
				updated_at = ?	 
				WHERE running_record_id =? 
            ' );

			$query->execute( [
				$data['observer'],
				$data['course'],
				$data['date'] ? $data['date'] : null,
				$data['start'],
				$data['end'],
				$data['age'],
				$data['context'],
				$data['purpose'],
				$data['observation'],
				$data['interpretation'],
				$data['extension'],
				$data['teacher'],
				$data['manager'],
				$data['record_public'],
				Carbon::now(),
				$running_record_id
			] );

			return $this->db->lastInsertId();
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}

	}

	/**
	 * assigns child to running record
	 *
	 * @param $record_id
	 * @param $child_id
	 *
	 * @return mixed
	 */
	public function connectChild( $record_id, $child_id ) {
		try {
			$query = $this->db->prepare( '
                INSERT INTO child_running_record (child_id, running_record_id)
                VALUES (?, ?)
            ' );

			return $query->execute( [ $child_id, $record_id ] );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * removes all children from running record
	 *
	 * @param $record_id
	 *
	 * @return mixed
	 */
	public function disconnectAllChildren( $record_id ) {
		try {
			$query = $this->db->prepare( '
                DELETE FROM child_running_record WHERE running_record_id =?
            ' );

			return $query->execute( [ $record_id ] );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * assigns uploaded file to running record
	 *
	 * @param $record_id
	 * @param $media_id
	 *
	 * @return mixed
	 */
	public function connectMedia( $record_id, $media_id ) {
		try {
			$query = $this->db->prepare( '
                INSERT INTO media_running_record (media_id, running_record_id)
                VALUES (?, ?)
            ' );

			return $query->execute( [ $media_id, $record_id ] );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * removes all uploaded files connection from running record
	 *
	 * @param $record_id
	 *
	 * @return mixed
	 */
	public function disconnectAllMedia( $record_id ) {
		try {
			$query = $this->db->prepare( '
                DELETE media_running_record.*, medias.* FROM  media_running_record
                JOIN medias
                    ON medias.media_id = media_running_record.media_id
                    WHERE running_record_id =?
            ' );

			return $query->execute( [ $record_id ] );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * Fetch all running records for give school
	 *
	 * @param $school_id
	 *
	 * @return mixed
	 */
	public function getAll( $school_id ) {
		try {
			$query = $this->db->prepare( '
                SELECT *, running_records.created_at AS running_record_created_at 
                FROM running_records
                JOIN child_running_record 
                	ON child_running_record.running_record_id = running_records.running_record_id
                JOIN children
                    ON children.child_id = child_running_record.child_id
                JOIN users
                    ON users.user_id = running_records.user_id
                WHERE children.school_id = ?
                ORDER BY running_records.created_at DESC
            ' );

			$query->execute( [ $school_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * Fetches all running records of a children that are visible for parent
	 *
	 * @param $parent_user_id
	 * @param $child_id
	 *
	 * @return mixed
	 */
	public function getAllForParent( $parent_user_id, $child_id ) {
		try {
			$query = $this->db->prepare( '
                SELECT *, running_records.created_at AS running_record_created_at 
                FROM running_records
                JOIN child_running_record 
                	ON child_running_record.running_record_id = running_records.running_record_id
                JOIN children
                    ON children.child_id = child_running_record.child_id
                JOIN users
                    ON users.user_id = running_records.user_id
                JOIN child_user
                    ON child_user.child_id = child_running_record.child_id
                WHERE 
                child_user.status = "A" 
                AND child_user.user_id = ?
                AND child_user.child_id = ?
                AND running_records.public = 1 
                ORDER BY running_records.created_at DESC
            ' );

			$query->execute( [ $parent_user_id, $child_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * Fetches running record and connected information by id
	 *
	 * @param $running_record_id
	 *
	 * @return mixed
	 */
	public function getOne( $running_record_id ) {
		try {
			$query = $this->db->prepare( '
                SELECT running_records.*,users.*, running_records.created_at AS running_record_created_at, GROUP_CONCAT(children.child_name SEPARATOR ", ") AS `child_name` 
                FROM running_records
                JOIN child_running_record 
                	ON child_running_record.running_record_id = running_records.running_record_id
                JOIN children
                    ON children.child_id = child_running_record.child_id
                JOIN users
                    ON users.user_id = running_records.user_id
                WHERE running_records.running_record_id = ?
                GROUP BY running_records.running_record_id
            ' );

			$query->execute( [ $running_record_id ] );

			return $query->fetchObject();
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * returns ids of children connected to running record
	 *
	 * @param $running_record_id
	 *
	 * @return array
	 */
	public function getChildrenIds( $running_record_id ) {
		try {
			$query = $this->db->prepare( '
                SELECT child_id FROM child_running_record
                WHERE running_record_id =? 
            ' );

			$query->execute( [ $running_record_id ] );

			return array_map( function ( $row ) {
				return $row['child_id'];
			}, $query->fetchAll( PDO::FETCH_ASSOC ) );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * returns files uploaded to media record
	 *
	 * @param $running_record_id
	 *
	 * @return mixed
	 */
	public function getMedias( $running_record_id ) {
		try {
			$query = $this->db->prepare( '
                SELECT * FROM media_running_record
                JOIN medias
                    ON medias.media_id = media_running_record.media_id
                WHERE media_running_record.running_record_id = ?
            ' );

			$query->execute( [ $running_record_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * Deletes running record
	 *
	 * @param $running_record_id
	 *
	 * @return mixed
	 */
	public function delete( $running_record_id ) {
		try {
			$query = $this->db->prepare( '
                DELETE FROM running_records WHERE running_record_id = ?
            ' );

			return $query->execute( [ $running_record_id ] );

		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}
}