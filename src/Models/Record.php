<?php

use \Carbon\Carbon;


class Record extends App\Models\Model {
	public function getAll($school_id) {

        try {
            $query = $this->db->prepare("
                select * from records
                where school_id = ?
            ");

            $query->execute([ $school_id]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}
	

    public function getAllByWeek($school_id, $week) {

		$year = date("Y");

        try {
            $query = $this->db->prepare("
                select * from records
                where school_id = ?
                and WEEKOFYEAR(record_date) = ?
                and YEAR(record_date) = $year
                order by record_date, record_time
            ");

            $query->execute([ $school_id, $week ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($record_id) {
        try {
            $query = $this->db->prepare('
                select * from records
                where record_id = ?
            ');

            $query->execute([ $record_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from records
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAllChild($child_id, $week) {
        try {

			$year = date("Y");

            $query = $this->db->prepare("
                select * from child_record
                join records
                    on records.record_id = child_record.record_id
                where child_record.child_id = ?
                and WEEKOFYEAR(records.record_date) = ?
                and YEAR(records.record_date) = $year
                and records.record_public = 1
                order by records.record_date, records.record_time
            ");

            $query->execute([ $child_id, $week ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getParams($record_id) {
        try {
            $query = $this->db->prepare('
                select * from record_params
                where record_id = ?
            ');

            $query->execute([ $record_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getChildren($record_id) {
        try {
            $query = $this->db->prepare('
                select * from child_record
                join children
                    on children.child_id = child_record.child_id
                where child_record.record_id = ?
                order by children.child_name
            ');

            $query->execute([ $record_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getMedias($record_id) {
        try {
            $query = $this->db->prepare('
                select * from media_record
                join medias
                    on medias.media_id = media_record.media_id
                where media_record.record_id = ?
            ');

            $query->execute([ $record_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($school_id, $user_id, $type, $time, $comment, $data) {
        try {
            $query = $this->db->prepare('
                insert into records (
                school_id,
                user_id,
                record_public,
                record_date,
                record_time,
                record_type,
                record_comment,
                created_at,
                updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([
            	$school_id,
	            $user_id,
	            $data['record_public'],
	            $data['record_date'],
	            $time,
	            $type,
	            $comment,
	            Carbon::now(),
	            Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createChild($child_id, $record_id) {
        try {
            $query = $this->db->prepare('
                insert into child_record (child_id, record_id)
                values (?, ?)
            ');

            return $query->execute([ $child_id, $record_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createMedia($media_id, $record_id) {
        try {
            $query = $this->db->prepare('
                insert into media_record (media_id, record_id)
                values (?, ?)
            ');

            return $query->execute([ $media_id, $record_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setDetails($record_id, $data, $comment, $time="00:00:00") {
        try {

            $query = $this->db->prepare('
                update records
                set record_date = ?,
					record_comment = ?,
					record_public = ?,
					record_time = ?,
                    updated_at = ?
                where record_id = ?

			');

            return $query->execute([ $data['record_date'], $comment, $data['record_public'], $time, Carbon::now(), $record_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setParam($record_id, $key, $value) {
        try {
            $query = $this->db->prepare('
                insert into record_params (record_id, param_id, param_value)
                values (?, ?, ?)
                on duplicate key update
                    param_value = values(param_value)
            ');

            $query->execute([ $record_id, $key, $value ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($record_id) {
        try {
            $query = $this->db->prepare('
                delete records, child_record, media_record, medias from records
                left join child_record
                    on child_record.record_id = records.record_id
                left join media_record
                    on media_record.record_id = records.record_id
                left join medias
                    on medias.media_id = media_record.media_id
                where records.record_id = ?
            ');

            return $query->execute([ $record_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeChildren($record_id) {
        try {
            $query = $this->db->prepare('
                delete from child_record
                where record_id = ?
            ');

            return $query->execute([ $record_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeMedias($record_id) {
        try {
            $query = $this->db->prepare('
                delete media_record, medias from media_record
                left join medias
                    on medias.media_id = media_record.media_id
                where media_record.record_id = ?
            ');

            return $query->execute([ $record_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}


/**
 * Class MultipleRecordSaver
 *
 * Saves multiple records for new(2.22.2018 by ilia@m52studios.com) Daily Records UX interface where
 * multiple records can be entered in one go, instead of saving only one.
 */
class MultipleRecordSaver {

	protected $_record_public;
	protected $_school_id;
	protected $_data;
	protected $_user_id;

	protected $_record_id;

	//protected $_app;

	public function setParams($school_id, $user_id, $data, $record_public = 0) {
		$this->_data = $data;
		$this->_school_id = $school_id;
		$this->_record_public = $record_public;
		$this->_user_id = $user_id;
	}

//	public function setApp($app) {
//		$this->_app = $app;
//	}

	public function createRecords($app) {
		/**
		 * TODO: Break this up into a strategy pattern
		 */

		$Timeline = new Timeline($app);
		$Media = new Media($app);
		$Record = new Record($app);

		/**
		 * General : if note_comment is not empty
		 * Disposition: if param.mood[0] is not empty
		 * Meal : if param.meal_time is not empty
		 * Nap : if param.start_nap is not empty
		 * Bathroom Break : if param.toilet_time is not empty
		 * Diaper : if param.diaper_time is not empty
		 * Medication : if param.start_medication is not empty
		 */
		if($this->_data['note_comment'] != '') {
			$this->_record_id  = $Record->create($this->_school_id,
				$this->_user_id,
				'note',
				'',
				$this->_data['note_comment'],
				$this->_data);


			// Add child to record
			// Timeline
			foreach($this->_data['children'] as $child_id) {
				$Record->createChild($child_id, $this->_record_id);

				$Timeline->create($this->_user_id, $child_id,
					'record',
					$this->_record_id,
					$this->_data['record_public'],
					$this->_data['note_comment']);
			}

			// Media
			if($this->_data['note_media']) {
				$app->logger->debug('Media files found.', [ 'group' => $this->_data['note_media'] ]);

				$group = $app->uploader->getGroup($this->_data['note_media']);
				$files = $group->getFiles();

				foreach ($files as $file) {
					$url_full = $file->resize(1600)->getUrl();
					$url_thumbnail = $file->resize(400)->getUrl();

					$resized_full = $app->uploader->createLocalCopy($url_full);
					$resized_full->store();

					$resized_thumbnail = $app->uploader->createLocalCopy($url_thumbnail);
					$resized_thumbnail->store();

					$file->delete();

					$app->logger->debug('Saved media.',
						[ 'record_id' => $this->_record_id,
						  'full_url' => $resized_full->getUrl(),
						  'thumbnail_url' => $resized_thumbnail->getUrl()
						]);

					$media_id = $Media->create(
						$resized_full->getUrl(),
						$resized_thumbnail->getUrl(),
						$resized_full->data['mime_type']
					);
					$Record->createMedia($media_id, $this->_record_id);
				}
			}

			$this->_record_id = -1;
		}

		if(!empty($this->_data['param']['mood'][0])) {
			$this->_record_id  = $Record->create($this->_school_id,
				$this->_user_id,
				'mood',
				'',
				$this->_data['mood_comment'],
				$this->_data);

			// Params
			$mood_arr = array_filter($this->_data['param']['mood']);
			$moods = implode(', ', $mood_arr);
			$Record->setParam($this->_record_id, 'mood', $moods);

			// Add child to record
			// Timeline
			foreach($this->_data['children'] as $child_id) {
				$Record->createChild($child_id, $this->_record_id);

				$Timeline->create($this->_user_id, $child_id,
					'record',
					$this->_record_id,
					$this->_data['record_public'],
					$this->_data['mood_comment']);
			}

			// Media
			if($this->_data['mood_media']) {
				$app->logger->debug('Media files found.', [ 'group' => $this->_data['mood_media'] ]);

				$group = $app->uploader->getGroup($this->_data['mood_media']);
				$files = $group->getFiles();

				foreach ($files as $file) {
					$url_full = $file->resize(1600)->getUrl();
					$url_thumbnail = $file->resize(400)->getUrl();

					$resized_full = $app->uploader->createLocalCopy($url_full);
					$resized_full->store();

					$resized_thumbnail = $app->uploader->createLocalCopy($url_thumbnail);
					$resized_thumbnail->store();

					$file->delete();

					$app->logger->debug('Saved media.',
						[ 'record_id' => $this->_record_id,
						  'full_url' => $resized_full->getUrl(),
						  'thumbnail_url' => $resized_thumbnail->getUrl()
						]);

					$media_id = $Media->create(
						$resized_full->getUrl(),
						$resized_thumbnail->getUrl(),
						$resized_full->data['mime_type']
					);
					$Record->createMedia($media_id, $this->_record_id);
				}
			}

			$this->_record_id = -1;
		}

		if(!empty($this->_data['param']['meal_time'])) {
			$this->_record_id  = $Record->create($this->_school_id,
				$this->_user_id,
				'meal',
				$this->_data['param']['meal_time'],
				$this->_data['meal_comment'],
				$this->_data);

			// Params
			$Record->setParam($this->_record_id, 'amount', $this->_data['param']['meal_amount']);
			$Record->setParam($this->_record_id, 'meal', $this->_data['param']['meal']);
			$app->logger->info(json_encode($this->_data['param']['food']));
			$Record->setParam($this->_record_id, 'food', $this->_data['param']['food']);

			// Add child to record
			// Timeline
			foreach($this->_data['children'] as $child_id) {
				$Record->createChild($child_id, $this->_record_id);

				$Timeline->create($this->_user_id, $child_id,
					'record',
					$this->_record_id,
					$this->_data['record_public'],
					$this->_data['meal_comment']);
			}

			// Media
			if($this->_data['meal_media']) {
				$app->logger->debug('Media files found.', [ 'group' => $this->_data['meal_media'] ]);

				$group = $app->uploader->getGroup($this->_data['meal_media']);
				$files = $group->getFiles();

				foreach ($files as $file) {
					$url_full = $file->resize(1600)->getUrl();
					$url_thumbnail = $file->resize(400)->getUrl();

					$resized_full = $app->uploader->createLocalCopy($url_full);
					$resized_full->store();

					$resized_thumbnail = $app->uploader->createLocalCopy($url_thumbnail);
					$resized_thumbnail->store();

					$file->delete();

					$app->logger->debug('Saved media.',
						[ 'record_id' => $this->_record_id,
						  'full_url' => $resized_full->getUrl(),
						  'thumbnail_url' => $resized_thumbnail->getUrl()
						]);

					$media_id = $Media->create(
						$resized_full->getUrl(),
						$resized_thumbnail->getUrl(),
						$resized_full->data['mime_type']
					);
					$Record->createMedia($media_id, $this->_record_id);
				}
			}

			$this->_record_id = -1;
		}

		if(!empty($this->_data['param']['start_nap'])) {
			$this->_record_id  = $Record->create($this->_school_id,
				$this->_user_id,
				'nap',
				$this->_data['param']['start_nap'],
				$this->_data['nap_comment'],
				$this->_data);

			// Params
			$Record->setParam($this->_record_id, 'end', $this->_data['param']['end_nap']);


			// Add child to record
			// Timeline
			foreach($this->_data['children'] as $child_id) {
				$Record->createChild($child_id, $this->_record_id);

				$Timeline->create($this->_user_id, $child_id,
					'record',
					$this->_record_id,
					$this->_data['record_public'],
					$this->_data['nap_comment']);
			}


			// Media
			if($this->_data['nap_media']) {
				$app->logger->debug('Media files found.', [ 'group' => $this->_data['nap_media'] ]);

				$group = $app->uploader->getGroup($this->_data['nap_media']);
				$files = $group->getFiles();

				foreach ($files as $file) {
					$url_full = $file->resize(1600)->getUrl();
					$url_thumbnail = $file->resize(400)->getUrl();

					$resized_full = $app->uploader->createLocalCopy($url_full);
					$resized_full->store();

					$resized_thumbnail = $app->uploader->createLocalCopy($url_thumbnail);
					$resized_thumbnail->store();

					$file->delete();

					$app->logger->debug('Saved media.',
						[ 'record_id' => $this->_record_id,
						  'full_url' => $resized_full->getUrl(),
						  'thumbnail_url' => $resized_thumbnail->getUrl()
						]);

					$media_id = $Media->create(
						$resized_full->getUrl(),
						$resized_thumbnail->getUrl(),
						$resized_full->data['mime_type']
					);
					$Record->createMedia($media_id, $this->_record_id);
				}
			}

			$this->_record_id = -1;
		}

		if(!empty($this->_data['param']['toilet_time'])) {
			$this->_record_id  = $Record->create($this->_school_id,
				$this->_user_id,
				'toilet',
				$this->_data['param']['toilet_time'],
				$this->_data['toilet_comment'],
				$this->_data);

			// Params

			// Add child to record
			// Timeline
			foreach($this->_data['children'] as $child_id) {
				$Record->createChild($child_id, $this->_record_id);

				$Timeline->create($this->_user_id, $child_id,
					'record',
					$this->_record_id,
					$this->_data['record_public'],
					$this->_data['toilet_comment']);
			}


			// Media
			if($this->_data['toilet_media']) {
				$app->logger->debug('Media files found.', [ 'group' => $this->_data['toilet_media'] ]);

				$group = $app->uploader->getGroup($this->_data['toilet_media']);
				$files = $group->getFiles();

				foreach ($files as $file) {
					$url_full = $file->resize(1600)->getUrl();
					$url_thumbnail = $file->resize(400)->getUrl();

					$resized_full = $app->uploader->createLocalCopy($url_full);
					$resized_full->store();

					$resized_thumbnail = $app->uploader->createLocalCopy($url_thumbnail);
					$resized_thumbnail->store();

					$file->delete();

					$app->logger->debug('Saved media.',
						[ 'record_id' => $this->_record_id,
						  'full_url' => $resized_full->getUrl(),
						  'thumbnail_url' => $resized_thumbnail->getUrl()
						]);

					$media_id = $Media->create(
						$resized_full->getUrl(),
						$resized_thumbnail->getUrl(),
						$resized_full->data['mime_type']
					);
					$Record->createMedia($media_id, $this->_record_id);
				}
			}

			$this->_record_id = -1;
		}

		if(!empty($this->_data['param']['diaper_time'])) {
			$this->_record_id  = $Record->create($this->_school_id,
				$this->_user_id,
				'diaper',
				$this->_data['param']['diaper_time'],
				$this->_data['diaper_comment'],
				$this->_data);

			// Params
			$Record->setParam($this->_record_id, 'condition', $this->_data['param']['condition']);

			// Add child to record
			// Timeline
			foreach($this->_data['children'] as $child_id) {
				$Record->createChild($child_id, $this->_record_id);

				$Timeline->create($this->_user_id, $child_id,
					'record',
					$this->_record_id,
					$this->_data['record_public'],
					$this->_data['diaper_comment']);
			}


			// Media
			if($this->_data['diaper_media']) {
				$app->logger->debug('Media files found.', [ 'group' => $this->_data['diaper_media'] ]);

				$group = $app->uploader->getGroup($this->_data['diaper_media']);
				$files = $group->getFiles();

				foreach ($files as $file) {
					$url_full = $file->resize(1600)->getUrl();
					$url_thumbnail = $file->resize(400)->getUrl();

					$resized_full = $app->uploader->createLocalCopy($url_full);
					$resized_full->store();

					$resized_thumbnail = $app->uploader->createLocalCopy($url_thumbnail);
					$resized_thumbnail->store();

					$file->delete();

					$app->logger->debug('Saved media.',
						[ 'record_id' => $this->_record_id,
						  'full_url' => $resized_full->getUrl(),
						  'thumbnail_url' => $resized_thumbnail->getUrl()
						]);

					$media_id = $Media->create(
						$resized_full->getUrl(),
						$resized_thumbnail->getUrl(),
						$resized_full->data['mime_type']
					);
					$Record->createMedia($media_id, $this->_record_id);
				}
			}

			$this->_record_id = -1;
		}

		if(!empty($this->_data['param']['start_medication'])) {
			$this->_record_id  = $Record->create($this->_school_id,
				$this->_user_id,
				'medication',
				$this->_data['param']['start_medication'],
				$this->_data['medication_comment'],
				$this->_data);

			// Params
			$Record->setParam($this->_record_id, 'name', $this->_data['param']['medication_name']);
			$Record->setParam($this->_record_id, 'amount', $this->_data['param']['medication_amount']);
			$Record->setParam($this->_record_id, 'unit', $this->_data['param']['medication_unit']);

			// Add child to record
			// Timeline
			foreach($this->_data['children'] as $child_id) {
				$Record->createChild($child_id, $this->_record_id);

				$Timeline->create($this->_user_id, $child_id,
					'record',
					$this->_record_id,
					$this->_data['record_public'],
					$this->_data['medication_comment']);
			}


			// Media
			if($this->_data['medication_media']) {
				$app->logger->debug('Media files found.', [ 'group' => $this->_data['medication_media'] ]);

				$group = $app->uploader->getGroup($this->_data['medication_media']);
				$files = $group->getFiles();

				foreach ($files as $file) {
					$url_full = $file->resize(1600)->getUrl();
					$url_thumbnail = $file->resize(400)->getUrl();

					$resized_full = $app->uploader->createLocalCopy($url_full);
					$resized_full->store();

					$resized_thumbnail = $app->uploader->createLocalCopy($url_thumbnail);
					$resized_thumbnail->store();

					$file->delete();

					$app->logger->debug('Saved media.',
						[ 'record_id' => $this->_record_id,
						  'full_url' => $resized_full->getUrl(),
						  'thumbnail_url' => $resized_thumbnail->getUrl()
						]);

					$media_id = $Media->create(
						$resized_full->getUrl(),
						$resized_thumbnail->getUrl(),
						$resized_full->data['mime_type']
					);
					$Record->createMedia($media_id, $this->_record_id);
				}
			}

			$this->_record_id = -1;
		}
	}

	protected function addChildToRecord($record_id, $child_id) {

	}

	protected function addMediaToRecord() {

	}

	protected function addToTimeLine() {

	}

	protected function sendEmailToParents() {

	}
}