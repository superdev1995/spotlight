<?php

use \Carbon\Carbon;


class Video extends App\Models\Model {
    public function create($url, $mime_type, $school_id) {
        try {
            $query = $this->db->prepare('
                insert into videos (video_url, video_mime_type, school_id, uploaded_at)
                values (?, ?, ?, ?)
            ');

            $query->execute([ $url, $mime_type, $school_id, Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

	public function getAllAssociatedVideos($type, $id) {

		try{
			$query = $this->db->prepare('
				select * from video_' . $type . ' vt
				inner join videos m on vt.video_id = m.video_id
				where vt.'. $type .'_id = ?
			');

			$query->execute([ $id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function deleteAllAssociatedVideos($type, $id) {

		try{
			$query = $this->db->prepare('
                delete m, vt 
                from video_' . $type . ' vt
                inner join videos m on vt.video_id = m.video_id
                where vt.'. $type .'_id = ?
			');

			return $query->execute([ $id ]);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getVideosInADay($date, $school_id){
		try{
			$query = $this->db->prepare('
				SELECT * FROM videos
				WHERE DATE(uploaded_at) = ?
				AND school_id = ?
			');

			$query->execute([ $date, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
}
