<?php

use \Carbon\Carbon;


class Infection extends App\Models\Model {
    
    //
    public function getAllSample() {
        try {
            $query = $this->db->prepare('
                select * from infection_letter_sample
                order by letter_name
            ');

            $query->execute();

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getEditLater($user_id){
        
        try {
            $query = $this->db->prepare('
                select * from infection_letter
                where user_id = ? 
                and send_to = \'SE\'
                order by created_at desc
            ');
            
            $query->execute([ $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getOne($letter_id){
        try {
            $query = $this->db->prepare('
                select * from infection_letter
                where letter_id = ?
            ');

            $query->execute([ $letter_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getOneSample($letter_id){
        try {
            $query = $this->db->prepare('
                select * from infection_letter_sample
                where letter_sample_id = ?
            ');

            $query->execute([ $letter_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    
    public function create($user_id, $school_id, $data) {
        try {
            $query = $this->db->prepare('
                insert into infection_letter (school_id, user_id, send_to,letter_name, letter_body, created_at)
                values ( ?, ?, ?, ?, ?, ?)
            ');

            $query->execute([ $school_id, $user_id, $data['sendto'], $data['letter_n'], $data['description'], Carbon::now() ]);
            

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function edit($data) {
        try {
            $query = $this->db->prepare('
                update infection_letter 
                set send_to = ?,
                    letter_name = ?,
                    letter_body = ?,
                    created_at = ?
                where letter_id = ?
            ');
            
            return $query->execute([ $data['sendto'], $data['letter_n'], $data['description'], Carbon::now(), $data['edit_s'] ]);
            
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function auditCount() {
        try {
            $query = $this->db->prepare('
                select count(question_sort) from infection_question
                where category_name = "Audit Infectious Disease"
            ');

            $query->execute();

            return $query->fetchColumn(0);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
        
    public function getQuestion($question_id) {
        try {
            $query = $this->db->prepare('
                select * from infection_question
                where category_name = "Audit Infectious Disease" and question_sort = ?
                limit 1
            ');

            $query->execute([ $question_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function getQuestionId($question_id) {
        try {
            $query = $this->db->prepare('
                select infection_question_id from infection_question
                where category_name = "Audit Infectious Disease" and question_sort = ?
                limit 1
            ');

            $query->execute([ $question_id ]);

            return $query->fetchObject();
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function createAnswer($school_id, $user_id, $question_id, $answer) {
        try {
            $query = $this->db->prepare('
                insert into infection_question_answered (infection_question_id, school_id, user_id, category_name, answer, answerer_at)
                values (?, ?, ?, ?, ?, ?, ?)
            ');

            return $query->execute([ $question_id, $school_id, $user_id, $answer_id, $file_url, Carbon::now(), Carbon::now() ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    public function createQuestion($school_id, $user_id, $question_id, $answer_id, $file_url = null) {
        try {
            $query = $this->db->prepare('
                insert into infection_question_answered (infection_question_id, school_id, user_id, category_name,multiple_answer, answer, answerer_at)
                values (?, ?, ?, ?, ?, ?, ?)
            ');

            return $query->execute([ $question_id, $answer_id, $school_id, $user_id, $file_url, Carbon::now(), Carbon::now() ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
    
    public function purge($school_id, $user_id, $question_id) {
        try {
            $query = $this->db->prepare('
                delete from infection_question_answered
                where school_id = ?
                and user_id_?
                and infection_question_id = ?
            ');

            return $query->execute([ $school_id, $user_id, $question_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    
    
    //--------------------------------------------------------------------------------------------------------
    
    
    
    public function getCount() {
        try {
            $query = $this->db->prepare('
                select count(distinct(school_id)) from infection_question
            ');

            $query->execute();

            return $query->fetchColumn(0);
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
    //--------------------------------------------------------------------------------------------------------
    
}


