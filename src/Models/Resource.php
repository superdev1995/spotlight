<?php

use \Carbon\Carbon;


class Resource extends App\Models\Model {
    public function getAll() {
        try {
            $query = $this->db->prepare('
                select * from resources
                where resource_status = "A"
                order by created_at desc
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(*) from resources
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
                select sum(resource_downloads) from resources
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($resource_id) {
        try {
            $query = $this->db->prepare('
                select *, resources.created_at as resource_created_at from resources
                join users
                    on users.user_id = resources.user_id
                where resources.resource_id = ?
                and resources.resource_status = "A"
                limit 1
            ');

            $query->execute([ $resource_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCategories() {
        try {
            $query = $this->db->prepare('
                select * from resource_categories
                order by category_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($user_id, $categories, $url, $data) {
        try {
            $query = $this->db->prepare('
                insert into resources (user_id, resource_name, resource_description, resource_min_age, resource_max_age, categories, resource_url, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $user_id, $data['name'], $data['description'], $data['min_age'], $data['max_age'], $categories, $url, Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function setDownloads($resource_id) {
        try {
            $query = $this->db->prepare('
                update resources
                set resource_downloads = resource_downloads + 1,
                    updated_at = ?
                where resource_id = ?
            ');

            return $query->execute([ Carbon::now(), $resource_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
