<?php

use \Carbon\Carbon;


class ParentLibrary extends App\Models\Model{
    public function getAll($school_id)
    {
        try {
            $query = $this->db->prepare('
                select * from libraries
				where school_id = ?
                order by created_at desc
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
                select count(*) from libraries
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getDownloadCount() {
        try {
            $query = $this->db->prepare('
                select sum(library_downloads) from libraries
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($library_id) {
        try {
            $query = $this->db->prepare('
                select *, libraries.created_at as library_created_at from libraries
                join users
                    on users.user_id = libraries.user_id
                where libraries.library_id = ?
                limit 1
            ');

            $query->execute([ $library_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($user_id, $data, $school_id) {
        try {
            $query = $this->db->prepare('
                insert into libraries (user_id, library_name, library_description, created_at, updated_at, school_id)
                values (?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $user_id, $data['libName'], $data['libDesc'], Carbon::now(), Carbon::now(), $school_id]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setDownloads($library_id) {
        try {
            $query = $this->db->prepare('
                update libraries
                set library_downloads = library_downloads + 1,
                    updated_at = ?
                where library_id = ?
            ');

            return $query->execute([ Carbon::now(), $library_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function shareCheck($school_id) {
        try {
            $query = $this->db->prepare('
                select room_id from rooms
				where school_id = ?
                order by room_id
            ');

            $query->execute([ $school_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function shareInRoom($library_id, $room_id) {
        try {
            $query = $this->db->prepare('
                insert into library_share (library_id, room_id)
                values (?, ?)
            ');

            $query->execute([ $library_id, $room_id ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function unshareInRoom($library_id, $room_id) {
        try {
            $query = $this->db->prepare('
                delete from library_share
                where library_id = ?
				and room_id = ?
            ');

            $query->execute([ $library_id, $room_id ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
	
	public function isSharedInRoom($library_id) {
        try {
            $query = $this->db->prepare('
                select room_id from library_share
				where library_id = ?
				order by room_id
            ');

            $query->execute([ $library_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function librariesInRoom($room_id) {
        try {
            $query = $this->db->prepare('
                select * from libraries 
                inner join library_share 
                on libraries.library_id = library_share.library_id 
                where library_share.room_id = ? 
                order by library_share.library_id

            ');

            $query->execute([ $room_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getRoom($child_id) {
        try {
            $query = $this->db->prepare('
                select room_id from children
                where child_id = ?
            ');

            $query->execute([ $child_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}