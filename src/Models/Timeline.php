<?php

use \Carbon\Carbon;


class Timeline extends App\Models\Model {
    public function getAll($child_id, $all = 0) {
        try {
            $query = $this->db->prepare('
                select *, timelines.created_at AS posted_at from timelines
                left join users
                    on users.user_id = timelines.user_id
                where timelines.child_id = ?
                and (
                    timelines.timeline_public = 1
                    '.(($all == 1) ? 'or timelines.timeline_public = 0' : '').'
                )
                order by timelines.created_at desc

            ');

            $query->execute([ $child_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOne($timeline_id) {
        try {
            $query = $this->db->prepare('
                select * from timelines
                where timeline_id = ?
            ');

            $query->execute([ $timeline_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($user_id, $child_id, $type, $linked_id = 0, $public = 0, $comment = null) {
        try {
            $query = $this->db->prepare('
                insert into timelines (user_id, child_id, timeline_type, timeline_linked_id, timeline_public, timeline_comment, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $user_id, $child_id, $type, $linked_id, $public, $comment, Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getComments($timeline_id) {
        try {
            $query = $this->db->prepare('
                select *, timeline_comments.created_at as comment_created_at from timeline_comments
                join users
                    on users.user_id = timeline_comments.user_id
                where timeline_comments.timeline_id = ?
                order by timeline_comments.created_at asc
            ');

            $query->execute([ $timeline_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function createComment($timeline_id, $user_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into timeline_comments (timeline_id, user_id, body, created_at, updated_at)
                values (?, ?, ?, ?, ?)
            ');

            $query->execute([ $timeline_id, $user_id, $data['comment'], Carbon::now(), Carbon::now() ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($type, $linked_id) {
        try {
            $query = $this->db->prepare('
                delete timelines, timeline_comments from timelines
                left join timeline_comments
                    on timeline_comments.timeline_id = timelines.timeline_id
                where timelines.timeline_type = ?
                and timelines.timeline_linked_id = ?
            ');

            return $query->execute([ $type, $linked_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function updateVisibility($type, $linked_id,$public) {
        try {
            $query = $this->db->prepare('
                UPDATE timelines SET timeline_public = ?
                where timelines.timeline_type = ?
                and timelines.timeline_linked_id = ?
            ');

            return $query->execute([$public, $type, $linked_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getOnewithLinkedID($type, $linked_id) {
        try {
            $query = $this->db->prepare('
                select * from timelines 
                where timelines.timeline_type = ?
                and timelines.timeline_linked_id = ?
            ');

            return $query->execute([ $type, $linked_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
