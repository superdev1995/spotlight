<?php

use \Carbon\Carbon;


class Gdpr extends App\Models\Model {

    public function getCategories() {
        try {
            $query = $this->db->prepare('
                select * from gdpr_categories
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
                select * from gdpr_questions
                join gdpr_categories
                    on gdpr_categories.category_id = gdpr_questions.category_id
                where gdpr_questions.question_id = ?
                limit 1
            ');

            $query->execute([ $question_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getFirstQuestion() {
        try {
            $query = $this->db->prepare('
                select * from gdpr_questions
                join gdpr_categories
                    on gdpr_categories.category_id = gdpr_questions.category_id
                order by gdpr_categories.category_sort, gdpr_questions.question_sort
                limit 1
            ');

            $query->execute();

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getQuestions() {
        try {
            $query = $this->db->prepare('
                select * from gdpr_questions
                join gdpr_categories
                    on gdpr_categories.category_id = gdpr_questions.category_id
                order by gdpr_categories.category_sort, gdpr_questions.question_sort
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
                select * from gdpr_school
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
                select *, gdpr_answers.answer_id as question_answer_id, gdpr_answers.answer_body as body from gdpr_answers
                left join gdpr_school
                    on gdpr_school.answer_id = gdpr_answers.answer_id
                    and gdpr_school.school_id = ?
                where gdpr_answers.question_id = ?
                order by gdpr_answers.answer_sort
            ');

            $query->execute([ $school_id, $question_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getFirstConducted($school_id){
        try {
            $query = $this->db->prepare('
                select created_at from gdpr_school
                where school_id = ?
                order by created_at
                limit 1
            ');

            $query->execute([ $school_id ]);

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getLastConducted($school_id){
        try {
            $query = $this->db->prepare('
                select updated_at from gdpr_school
                where school_id = ?
                order by updated_at desc
                limit 1
            ');

            $query->execute([ $school_id ]);

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getAnswer($answer_id){
        try {
            $query = $this->db->prepare('
                select * from gdpr_answers
                where answer_id = ?
                limit 1
            ');

            $query->execute([ $answer_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getSchoolAnswerCount($school_id, $question_id) {
        try {
            $query = $this->db->prepare('
                select * from gdpr_school
                join gdpr_answers
                    on gdpr_answers.answer_id = gdpr_school.answer_id
                where gdpr_school.school_id = ?
                and gdpr_school.question_id = ?
            ');

            $query->execute([ $school_id, $question_id ]);

            return $query->rowCount();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getQuestionAnswer($school_id, $question_id){
        try {
            $query = $this->db->prepare('
                select * from gdpr_school
                where school_id = ?
                and question_id = ?
                limit 1
            ');

            $query->execute([ $school_id, $question_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function getQuestionByCategoryId($category_id, $sort) {
        try {
            $query = $this->db->prepare('
                select * from gdpr_questions
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
                select * from gdpr_questions
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
                select * from gdpr_questions
                where category_id = ?
            ');

            $query->execute([ $category_id ]);

            return $query->rowCount();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function create($school_id, $user_id, $question_id, $answer_id, $file_url = null, $answer_body, $additional_information) {
        try {
            $query = $this->db->prepare('
                insert into gdpr_school (question_id, answer_id, school_id, user_id, answer_body, additional_information, file_url, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            return $query->execute([ $question_id, $answer_id, $school_id, $user_id, $answer_body, $additional_information, $file_url, Carbon::now(), Carbon::now() ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function purge($school_id, $question_id) {
        try {
            $query = $this->db->prepare('
                delete from gdpr_school
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
                delete from gdpr_school
                where school_id = ?
            ');

            return $query->execute([ $school_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
