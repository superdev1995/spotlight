<?php

use \Carbon\Carbon;


class Compliance extends App\Models\Model {
    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(distinct(school_id)) from question_school
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCategories($country_id) {
        try {
            $query = $this->db->prepare('
                select * from question_categories
                where country_id = ?
                order by category_sort
            ');

            $query->execute([ $country_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getQuestion($question_id) {
        try {
            $query = $this->db->prepare('
                select * from questions
                join question_categories
                    on question_categories.category_id = questions.category_id
                where questions.question_id = ?
                limit 1
            ');

            $query->execute([ $question_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getQuestionCode($question_id) {
        try {
            $query = $this->db->prepare('
                select question_code from questions
                join question_categories
                    on question_categories.category_id = questions.category_id
                where questions.question_id = ?
                limit 1
            ');

            $query->execute([ $question_id ]);

            return $query->fetchAll();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getFirstQuestionByCountry($country_id) {
        try {
            $query = $this->db->prepare('
                select * from questions
                join question_categories
                    on question_categories.category_id = questions.category_id
                where question_categories.country_id = ?
                order by question_categories.category_sort, questions.question_sort
                limit 1
            ');

            $query->execute([ $country_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getQuestions() {
        try {
            $query = $this->db->prepare('
                select * from questions
                join question_categories
                    on question_categories.category_id = questions.category_id
                order by question_categories.category_sort, questions.question_sort
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSchoolAnswer($school_id, $answer_id) {
        try {
            $query = $this->db->prepare('
                select * from question_school
                where school_id = ?
                and answer_id = ?
                limit 1
            ');

            $query->execute([ $school_id, $answer_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getAnswers($question_id, $school_id) {
        try {
            $query = $this->db->prepare('
                select *, question_answers.answer_id as question_answer_id from question_answers
                left join question_school
                    on question_school.answer_id = question_answers.answer_id
                    and question_school.school_id = ?
                where question_answers.question_id = ?
                order by question_answers.answer_sort
            ');

            $query->execute([ $school_id, $question_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSchoolAnswerCount($school_id, $question_id) {
        try {
            $query = $this->db->prepare('
                select * from question_school
                join question_answers
                    on question_answers.answer_id = question_school.answer_id
                where question_school.school_id = ?
                and question_school.question_id = ?
            ');

            $query->execute([ $school_id, $question_id ]);

            return $query->rowCount();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getQuestionByCategoryId($category_id, $sort) {
        try {
            $query = $this->db->prepare('
                select * from questions
                where category_id = ?
                and question_sort = ?
                order by category_id, question_sort
                limit 1
            ');

            $query->execute([ $category_id, $sort ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getLastQuestion() {
        try {
            $query = $this->db->prepare('
                select * from questions
                order by category_id desc, question_sort desc
                limit 1
            ');

            $query->execute();

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getCategoryCount($category_id) {
        try {
            $query = $this->db->prepare('
                select * from questions
                where category_id = ?
            ');

            $query->execute([ $category_id ]);

            return $query->rowCount();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($school_id, $user_id, $question_id, $answer_id, $file_url = null) {
        try {
            $query = $this->db->prepare('
                insert into question_school (question_id, answer_id, school_id, user_id, file_url, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?)
            ');

            return $query->execute([ $question_id, $answer_id, $school_id, $user_id, $file_url, Carbon::now(), Carbon::now() ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($school_id, $question_id) {
        try {
            $query = $this->db->prepare('
                delete from question_school
                where school_id = ?
                and question_id = ?
            ');

            return $query->execute([ $school_id, $question_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purgeAll($school_id) {
        try {
            $query = $this->db->prepare('
                delete from question_school
                where school_id = ?
            ');

            return $query->execute([ $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
