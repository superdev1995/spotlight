<?php

use \Carbon\Carbon;


class Media extends App\Models\Model {
    public function create($url_full, $url_thumbnail, $mime_type) {
        try {
            $query = $this->db->prepare('
                insert into medias (media_full_url, media_thumbnail_url, media_mime_type)
                values (?, ?, ?)
            ');

            $query->execute([ $url_full, $url_thumbnail, $mime_type ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    

	/**
     * @param $type
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getAllAssociatedMedia($type, $id) {

		try{
			$query = $this->db->prepare('
				select * from media_' . $type . ' mt
				inner join medias m on mt.media_id = m.media_id
				where mt.'. $type .'_id = ?
			');

			$query->execute([ $id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }
    
    /**
     * @param $type
	 * @param $id
	 *
	 * @return mixed
	 */
	public function deleteAllAssociatedMedia($type, $id) {

		try{
			$query = $this->db->prepare('
                delete m, mt 
                from media_' . $type . ' mt
                inner join medias m on mt.media_id = m.media_id
                where mt.'. $type .'_id = ?
			');

			return $query->execute([ $id ]);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
}
