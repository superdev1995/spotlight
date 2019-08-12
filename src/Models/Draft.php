<?php

use \Carbon\Carbon;

/**
 * Class Draft
 *
 * This class maintains functionality that is common to DraftDailyPlan, DraftWeeklyPlan, DraftMonthlyPlan, 
 * DraftLearningStory, DraftLearningSummary, DraftDailyRecords classes
 */
class Draft extends App\Models\Model {

	/**************************************************************************
	 ************************ DRAFT OF DAILY PLAN RELATED **********************
	 **************************************************************************/

	public function getAllDailyPlans($school_id, $user_id) {
		try {
			$query = $this->db->prepare('
                select * from draft_daily_plans
				where school_fk = ?
				and created_by = ?
				order by updated_at desc
            ');

			$query->execute([ $school_id, $user_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllDailyPlanAssociations($table, $draft_id, $assoc_type) {
		try {
			$query = $this->db->prepare( '
		              select *, ' . $table .'. ' . $assoc_type . '_name as name
		              from draft_daily_plan_assoc
		              left join ' . $table . ' on '. $table . '.' . $assoc_type. '_id' . ' = draft_daily_plan_assoc.assoc_fk
		              where draft_daily_plan_fk = ?
		              ' );

			$query->execute( [ $draft_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function getDailyPlan($draft_id) {
		try {
			$query = $this->db->prepare('
                select * from draft_daily_plans
				where draft_daily_plan_id = ?
            ');

			$query->execute([ $draft_id ]);

			return $query->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getDailyPlanBlocks($draft_id) {
		try {
			$query = $this->db->prepare('
                select * from draft_daily_plan_blocks
                where draft_daily_plan_fk = ?
            ');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function deleteDailyPlan($draft_id) {
		try {
			$query = $this->db->prepare('
				delete from draft_daily_plans
                where draft_daily_plan_id = ?
            ');

			return $query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function deleteDailyPlanBlocks($draft_id) {
		try {
			$query = $this->db->prepare('
				delete from draft_daily_plan_blocks
				where draft_daily_plan_fk = ?;
            ');

			return $query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function deleteDailyPlanBlock($block_id) {
		try {
			$query = $this->db->prepare('
                delete from draft_daily_plan_blocks
                where daily_plan_block_id = ?;
            ');

			return $query->execute([ $block_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addDailyPlan($school_id, $assoc, $created_by) {
        
        try {
			$query = $this->db->prepare('
                insert into draft_daily_plans (school_fk, assoc, created_on, updated_at, created_by)
                values (?, ?, ?, ?, ?);
            ');

			$query->execute([ $school_id, $assoc, Carbon::now(), Carbon::now(), $created_by ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function associateDailyPlan($draft_id, $type, $assoc_id) {
		try {
			$query = $this->db->prepare(
                'INSERT INTO draft_daily_plan_assoc 
				(draft_daily_plan_fk, assoc_type, assoc_fk) VALUES (?, ?, ?);
			');

			$query->execute([ $draft_id, $type, $assoc_id]);
		} catch (PDOException $e) {
			$this->logger->error("Associate Daily Plan: " . $e->getMessage());
		}
    }

	public function deassociateDailyPlan($draft_id, $type, $assoc_id) {
		try {
			$query = $this->db->prepare(
                'DELETE FROM draft_daily_plan_assoc 
                WHERE draft_daily_plan_fk = ? 
                AND assoc_type = ?
				AND assoc_fk = ?;');

			return $query->execute([ $draft_id, $type, $assoc_id ]);
		} catch (PDOException $e) {
			$this->logger->error("Associate Daily Plan: " . $e->getMessage());
		}
	}

	public function addDailyPlanBlock($draft_id) {
		try {
            $values = array();
            
			$query = $this->db->prepare(
				'INSERT INTO draft_daily_plan_blocks (draft_daily_plan_fk) VALUES (?);');
            
            $query->execute([ $draft_id ]);

            return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error("daily Plan Block " . $e->getMessage());
		}
	}

	public function editDailyPlanType( $draft_id, $type ) {
		try {
			$query = $this->db->prepare('
                update draft_daily_plans set assoc = ?
                where draft_daily_plan_id = ?
            ');

			$query->execute([ $type, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function editDailyPlanName( $draft_id, $name ) {
		try {
			$query = $this->db->prepare('
                update draft_daily_plans set name = ?
                where draft_daily_plan_id = ?
            ');

			$query->execute([ $name, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function editDailyPlanDate( $draft_id, $date ) {
		try {
			$query = $this->db->prepare('
                update draft_daily_plans set date = ?
                where draft_daily_plan_id = ?
            ');

			$query->execute([ $date, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function editDailyPlanImgUrl( $draft_id, $img_url ) {
		try {
			$query = $this->db->prepare('
                update draft_daily_plans set plan_img_url = ?
                where draft_daily_plan_id = ?
            ');

			$query->execute([ $img_url, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
	
	public function editDailyPlanVideoGroupUrl( $draft_id, $group_url ) {
		try {
			$query = $this->db->prepare('
                update draft_daily_plans set video_group_url = ?
                where draft_daily_plan_id = ?
            ');

			$query->execute([ $group_url, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function editDailyPlanTimeBlock($block_id, $time_block ) {
		try {
			$query = $this->db->prepare('
                update draft_daily_plan_blocks set time_block = ?
				where daily_plan_block_id = ?;
            ');

			$query->execute([ $time_block, $block_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function editDailyPlanBlockDescription( $block_id, $description ) {
		try {
			$query = $this->db->prepare('
                update draft_daily_plan_blocks set description = ?
				where daily_plan_block_id = ?;
            ');

			$query->execute([ $description, $block_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addGoalDailyPlan($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_draft_daily_plan (goal_fk, draft_daily_plan_fk)
				values (?, ?);
            ');

			return $query->execute([ $goal_id, $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function removeGoalDailyPlan($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                delete from goal_draft_daily_plan 
				where goal_fk = ? and draft_daily_plan_fk = ?;
            ');

			return $query->execute([ $goal_id, $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getDailyPlanGoals($draft_id) {
		try {
			$query = $this->db->prepare('
            select * from goal_draft_daily_plan
            join framework_goals
                on framework_goals.goal_id = goal_draft_daily_plan.goal_fk
            join framework_categories
                on framework_categories.category_id = framework_goals.category_id
            join frameworks
                on frameworks.framework_id = framework_categories.framework_id
            where goal_draft_daily_plan.draft_daily_plan_fk = ?
            order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeDailyPlanGoals( $draft_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_draft_daily_plan
				where draft_daily_plan_fk = ?
			');

			$query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function updateDailyPlan($draft_id){
		try {
			$query = $this->db->prepare('
                update draft_daily_plans set updated_at = ?
                where draft_daily_plan_id = ?
            ');

			$query->execute([ Carbon::now(), $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**************************************************************************
	 ************************ DRAFT OF WEEKLY PLAN RELATED **********************
	 **************************************************************************/
	
	public function getAllWeeklyPlansForAYear($school_id, $user_id, $year) {

		try{
			$query = $this->db->prepare('
				select * from draft_weekly_plans 
				where year = ? and
				school_fk = ?
				and created_by = ?
			');

			$query->execute([ $year, $school_id, $user_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllWeeklyPlansForAWeek($school_id, $user_id, $year, $week) {

		try{
			$query = $this->db->prepare('
				select * from draft_weekly_plans 
				where year = ? and
				 week = ? and 
				 school_fk = ?
				 and created_by = ?
				 order by updated_at desc
			');

			$query->execute([ $year, $week, $school_id, $user_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllWeeklyPlanAssociations($table, $draft_id, $assoc_type) {
		try {
			$query = $this->db->prepare( '
		              select *, ' . $table .'. ' . $assoc_type . '_name as name
		              from draft_weekly_plan_assoc
		              left join ' . $table . ' on '. $table . '.' . $assoc_type. '_id' . ' = draft_weekly_plan_assoc.assoc_fk
		              where draft_weekly_plan_fk = ?
		              ' );

			$query->execute( [ $draft_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function addWeeklyPlan($school_id, $year, $week, $assoc, $created_by ) {
		try {
			$query = $this->db->prepare('
                insert into draft_weekly_plans (school_fk, year, week, assoc, created_by, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?, ?)
            ');

			$query->execute([ $school_id, $year, $week, $assoc, $created_by, Carbon::now(), Carbon::now() ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addWeeklyPlanBlock($draft_id, $day) {
		try {
            $values = array();
            
            $query = $this->db->prepare(
				'INSERT INTO draft_weekly_daily_blocks (draft_weekly_plan_fk, day) 
				VALUES (?, ?);
				');
            
            $query->execute([ $draft_id, $day ]);

            return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error("daily Plan Block " . $e->getMessage());
		}
	}

	public function getWeeklyPlanBlocks($draft_id) {
		try {
			$query = $this->db->prepare( '
	              select * from draft_weekly_daily_blocks
	              where draft_weekly_plan_fk = ?
	              ' );

			$query->execute( [ $draft_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function getWeeklyPlanGoals($draft_id) {
		try {
			$query = $this->db->prepare('
            select * from goal_draft_weekly_plan
            join framework_goals
                on framework_goals.goal_id = goal_draft_weekly_plan.goal_fk
            join framework_categories
                on framework_categories.category_id = framework_goals.category_id
            join frameworks
                on frameworks.framework_id = framework_categories.framework_id
            where goal_draft_weekly_plan.draft_weekly_plan_fk = ?
            order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getWeeklyPlan($draft_id) {
		try {
			$query = $this->db->prepare( '
		              select * from draft_weekly_plans where draft_weekly_plan_id = ?
		              ' );

			$query->execute( [ $draft_id ] );

			return $query->fetch( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function deleteWeeklyPlan($draft_id) {
		try {
			$query = $this->db->prepare('
				delete from draft_weekly_plans
                where draft_weekly_plan_id = ?
            ');

			return $query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	public function deleteWeeklyPlanBlocks($draft_id) {
		try {
			$query = $this->db->prepare('
				delete from draft_weekly_daily_blocks
                where draft_weekly_plan_fk = ?
            ');

			return $query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function deleteWeeklyPlanBlock($block_id) {
		try {
			$query = $this->db->prepare(
                'delete from draft_weekly_daily_blocks
				where weekly_daily_block_id = ?;
            ');

			return $query->execute([ $block_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeWeeklyPlanGoals( $draft_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_draft_weekly_plan
				where draft_weekly_plan_fk = ?
			');

			$query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editWeeklyPlanType( $draft_id, $type ) {
		try {
			$query = $this->db->prepare('
                update draft_weekly_plans set assoc = ?
                where draft_weekly_plan_id = ?
            ');

			$query->execute([ $type, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function editWeeklyPlanName( $draft_id, $name ) {
		try {
			$query = $this->db->prepare('
                update draft_weekly_plans set name = ?
                where draft_weekly_plan_id = ?
            ');

			$query->execute([ $name, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editWeeklyPlanBlock($block_id, $section, $value ) {
		try {
			$query = $this->db->prepare('
                update draft_weekly_daily_blocks set '. $section . ' = ?
				where weekly_daily_block_id = ?;
            ');

			$query->execute([ $value, $block_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editWeeklyPlanKeyword($draft_id, $keyword_id, $value) {
		try {
			$query = $this->db->prepare('
                update draft_weekly_plans set '. $keyword_id . ' = ?
                where draft_weekly_plan_id = ?
            ');

			$query->execute([ $value, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
	
	public function editWeeklyPlanVideoGroupUrl( $draft_id, $group_url ) {
		try {
			$query = $this->db->prepare('
                update draft_weekly_plans set video_group_url = ?
                where draft_weekly_plan_id = ?
            ');

			$query->execute([ $group_url, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function associateWeeklyPlan($draft_id, $type, $assoc_id) {
		try {
			$query = $this->db->prepare(
                'INSERT INTO draft_weekly_plan_assoc 
				(draft_weekly_plan_fk, assoc_type, assoc_fk) VALUES (?, ?, ?);');

			$query->execute([ $draft_id, $type, $assoc_id]);
		} catch (PDOException $e) {
			$this->logger->error("Associate Daily Plan: " . $e->getMessage());
		}
    }

	public function deassociateWeeklyPlan($draft_id, $type, $assoc_id) {
		try {
			$query = $this->db->prepare(
                'DELETE FROM draft_weekly_plan_assoc 
                WHERE draft_weekly_plan_fk = ? 
                AND assoc_type = ?
				AND assoc_fk = ?;');

			return $query->execute([ $draft_id, $type, $assoc_id]);
		} catch (PDOException $e) {
			$this->logger->error("Associate Daily Plan: " . $e->getMessage());
		}
	}

	public function addGoalWeeklyPlan($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_draft_weekly_plan (goal_fk, draft_weekly_plan_fk)
				values (?, ?);
            ');

			return $query->execute([ $goal_id, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function removeGoalWeeklyPlan($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                delete from goal_draft_weekly_plan 
				where goal_fk = ? and draft_weekly_plan_fk = ?;
            ');

			return $query->execute([ $goal_id, $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function updateWeeklyPlan($draft_id){
		try {
			$query = $this->db->prepare('
                update draft_weekly_plans set updated_at = ?
                where draft_weekly_plan_id = ?
            ');

			$query->execute([ Carbon::now(), $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**************************************************************************
	 ************************ DRAFT OF MONTHLY PLAN RELATED **********************
	 **************************************************************************/

	public function getAllMonthlyPlansForAYear($school_id, $user_id, $year) {

		try{
			$query = $this->db->prepare('
				select * from draft_monthly_plans 
				where year = ? and
				 school_fk = ?
				 and created_by = ?
			');

			$query->execute([ $year, $school_id, $user_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addMonthlyPlan($school_id, $user_id, $year, $month ) {
		try {
			$query = $this->db->prepare('
                insert into draft_monthly_plans (school_fk, created_by, year, month, created_at, updated_at)
                values (?, ?, ?, ?, ?, ?)
            ');

			$query->execute([ $school_id, $user_id, $year, $month, Carbon::now(), Carbon::now() ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllMonthlyPlanAssociations($table, $draft_id, $assoc_type) {
		try {
			$query = $this->db->prepare( '
		              select *, ' . $table .'. ' . $assoc_type . '_name as name
		              from draft_monthly_plan_assoc
		              left join ' . $table . ' on '. $table . '.' . $assoc_type. '_id' . ' = draft_monthly_plan_assoc.assoc_fk
		              where draft_monthly_plan_fk = ?
		              ' );

			$query->execute( [ $draft_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function getAllMonthlyPlansForAMonth($school_id, $user_id, $year, $month) {

		try{
			$query = $this->db->prepare('
				select * from draft_monthly_plans 
				where year = ? and
				month = ? and 
				school_fk = ?
				and created_by = ?
				order by updated_at desc
			');

			$query->execute([ $year, $month, $school_id, $user_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getMonthlyPlanGoals($draft_id) {
		try {
			$query = $this->db->prepare('
            select * from goal_draft_monthly_plan
            join framework_goals
                on framework_goals.goal_id = goal_draft_monthly_plan.goal_fk
            join framework_categories
                on framework_categories.category_id = framework_goals.category_id
            join frameworks
                on frameworks.framework_id = framework_categories.framework_id
            where goal_draft_monthly_plan.draft_monthly_plan_fk = ?
            order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getMonthlyPlan($draft_id) {
		try {
			$query = $this->db->prepare( '
		              select * from draft_monthly_plans where draft_monthly_plan_id = ?
		              ' );

			$query->execute( [ $draft_id ] );

			return $query->fetch( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function deleteMonthlyPlan($draft_id) {
		try {
			$query = $this->db->prepare('
				delete from draft_monthly_plans
                where draft_monthly_plan_id = ?
            ');

			return $query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeMonthlyPlanGoals( $draft_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_draft_monthly_plan
				where draft_monthly_plan_fk = ?
			');

			$query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editMonthlyPlanType( $draft_id, $type ) {
		try {
			$query = $this->db->prepare('
                update draft_monthly_plans set assoc = ?
                where draft_monthly_plan_id = ?
            ');

			$query->execute([ $type, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function editMonthlyPlanPublic( $draft_id, $plan_public ) {
		try {
			$query = $this->db->prepare('
                update draft_monthly_plans set plan_public = ?
                where draft_monthly_plan_id = ?
            ');

			$query->execute([ $plan_public, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editMonthlyPlanGroupUrl( $draft_id, $group_url ) {
		try {
			$query = $this->db->prepare('
                update draft_monthly_plans set group_url = ?
                where draft_monthly_plan_id = ?
            ');

			$query->execute([ $group_url, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editMonthlyPlanVideoGroupUrl( $draft_id, $group_url ) {
		try {
			$query = $this->db->prepare('
                update draft_monthly_plans set video_group_url = ?
                where draft_monthly_plan_id = ?
            ');

			$query->execute([ $group_url, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editMonthlyPlanFormBlock( $draft_id, $column, $value ) {
		try {
			$query = $this->db->prepare('
                update draft_monthly_plans set '. $column .' = ?
                where draft_monthly_plan_id = ?
            ');

			$query->execute([ $value, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function associateMonthlyPlan($draft_id, $type, $assoc_id) {
		try {
			$query = $this->db->prepare(
                'INSERT INTO draft_monthly_plan_assoc 
				(draft_monthly_plan_fk, assoc_type, assoc_fk) VALUES (?, ?, ?);');

			$query->execute([ $draft_id, $type, $assoc_id]);
		} catch (PDOException $e) {
			$this->logger->error("Associate Monthly Plan: " . $e->getMessage());
		}
    }

	public function deassociateMonthlyPlan($draft_id, $type, $assoc_id) {
		try {
			$query = $this->db->prepare(
                'DELETE FROM draft_monthly_plan_assoc 
                WHERE draft_monthly_plan_fk = ? 
                AND assoc_type = ?
				AND assoc_fk = ?;');

			return $query->execute([ $draft_id, $type, $assoc_id]);
		} catch (PDOException $e) {
			$this->logger->error("Associate Monthly Plan: " . $e->getMessage());
		}
	}

	public function addGoalMonthlyPlan($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_draft_monthly_plan (goal_fk, draft_monthly_plan_fk)
				values (?, ?);
            ');

			return $query->execute([ $goal_id, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function removeGoalMonthlyPlan($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                delete from goal_draft_monthly_plan 
				where goal_fk = ? and draft_monthly_plan_fk = ?;
            ');

			return $query->execute([ $goal_id, $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function updateMonthlyPlan($draft_id){
		try {
			$query = $this->db->prepare('
                update draft_monthly_plans set updated_at = ?
                where draft_monthly_plan_id = ?
            ');

			$query->execute([ Carbon::now(), $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**************************************************************************
	 ************************ DRAFT OF STORY RELATED *************************
	 **************************************************************************/
	
	public function getAllStories($child_id, $user_id) {
        try {
            $query = $this->db->prepare('
                select *, draft_stories.updated_at as updated_at from draft_stories
                join children
                    on children.child_id = draft_stories.child_id
                join users
                    on users.user_id = draft_stories.user_id
                where draft_stories.child_id = ? and users.user_id = ?
                order by draft_stories.updated_at DESC
            ');

            $query->execute([ $child_id, $user_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }

	public function addStory($user_id, $child_id, $story_public ) {
		try {
			$query = $this->db->prepare('
                insert into draft_stories (user_id, child_id, story_public, created_at, updated_at)
                values (?, ?, ?, ?, ?)
            ');

			$query->execute([ $user_id, $child_id, $story_public, Carbon::now(), Carbon::now() ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getStoryGoals($draft_id) {
		try {
			$query = $this->db->prepare('
            select * from goal_draft_story
            join framework_goals
                on framework_goals.goal_id = goal_draft_story.goal_id
            join framework_categories
                on framework_categories.category_id = framework_goals.category_id
            join frameworks
                on frameworks.framework_id = framework_categories.framework_id
            where goal_draft_story.draft_story_id = ?
            order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getStory($draft_id) {
		try {
			$query = $this->db->prepare( '
		              select * from draft_stories where draft_story_id = ?
		              ' );

			$query->execute( [ $draft_id ] );

			return $query->fetch( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function deleteStory($draft_id) {
		try {
			$query = $this->db->prepare('
				delete from draft_stories
                where draft_story_id = ?
            ');

			return $query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeStoryGoals( $draft_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_draft_story
				where draft_story_id = ?
			');

			$query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editStoryName( $draft_id, $name ) {
		try {
			$query = $this->db->prepare('
                update draft_stories set story_name = ?
                where draft_story_id = ?
            ');

			$query->execute([ $name, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editStoryComment( $draft_id, $comment ) {
		try {
			$query = $this->db->prepare('
                update draft_stories set comment = ?
                where draft_story_id = ?
            ');

			$query->execute([ $comment, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editStoryPublic( $draft_id, $plan_public ) {
		try {
			$query = $this->db->prepare('
                update draft_stories set story_public = ?
                where draft_story_id = ?
            ');

			$query->execute([ $plan_public, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editStoryGroupUrl( $draft_id, $group_url ) {
		try {
			$query = $this->db->prepare('
                update draft_stories set group_url = ?
                where draft_story_id = ?
            ');

			$query->execute([ $group_url, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editStoryColumn( $draft_id, $column, $value ) {
		try {
			$query = $this->db->prepare('
                update draft_stories set '. $column .' = ?
                where draft_story_id = ?
            ');

			$query->execute([ $value, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editStoryKeyword( $draft_id, $keyword_id, $value ) {
		try {
			$query = $this->db->prepare('
                update draft_stories set '. $keyword_id .' = ?
                where draft_story_id = ?
            ');

			$query->execute([ $value, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addGoalStory($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_draft_story (goal_id, draft_story_id)
				values (?, ?);
            ');

			return $query->execute([ $goal_id, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
    }

	public function removeGoalStory($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                delete from goal_draft_story 
				where goal_id = ? and draft_story_id = ? 
            ');

			return $query->execute([ $goal_id, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function updateStory($draft_id){
		try {
			$query = $this->db->prepare('
                update draft_stories set updated_at = ?
                where draft_story_id = ?
            ');

			$query->execute([ Carbon::now(), $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**************************************************************************
	 ************************ DRAFT OF LEARNING SUMMARY RELATED **********************
	 **************************************************************************/

	public function getAllLearningSummaryForAYear($school_id, $user_id, $year) {

		try{
			$query = $this->db->prepare('
				select * from draft_learning_summary
				where year = ? and
				school_id = ? and
				user_id = ?
				order by updated_at desc
			');

			$query->execute([ $year, $school_id, $user_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllLearningSummaryForAWeek($school_id, $user_id, $year, $week) {

		try{
			$query = $this->db->prepare('
				select * from draft_learning_summary
				where year = ? and
				week = ? and 
				school_id = ? and
				user_id = ?
				order by updated_at desc
			');

			$query->execute([ $year, $week, $school_id, $user_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addLearningSummary($school_id, $year, $week, $created_by ) {
		try {
			$query = $this->db->prepare('
                insert into draft_learning_summary (
					school_id, 
					year, 
					week, 
					learning_summary_public,
					user_id, 
					created_at, 
					updated_at)
                values (?, ?, ?, ?, ?, ?, ?)
            ');

			$query->execute([ $school_id, $year, $week, '1', $created_by, Carbon::now(), Carbon::now() ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getLearningSummaryGoals($draft_id) {
		try {
			$query = $this->db->prepare('
            	select * from goal_draft_learning_summary
                join framework_goals
                    on framework_goals.goal_id = goal_draft_learning_summary.goal_id
                join framework_categories
                    on framework_categories.category_id = framework_goals.category_id
                join frameworks
                    on frameworks.framework_id = framework_categories.framework_id
                where goal_draft_learning_summary.draft_learning_summary_id = ?
                order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getLearningSummary($draft_id) {
		try {
			$query = $this->db->prepare( '
				select * from draft_learning_summary 
				where draft_learning_summary_id = ?
			' );

			$query->execute( [ $draft_id ] );

			return $query->fetch( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	public function getLearningSummaryChildren($draft_id){
		try {
            $query = $this->db->prepare('
                select * from child_draft_learning_summary
                join children
                    on children.child_id = child_draft_learning_summary.child_id
                where child_draft_learning_summary.draft_learning_summary_id = ?
                order by children.child_name
            ');

            $query->execute([ $draft_id ]);

            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}
	
	public function deleteLearningSummary($draft_id) {
		try {
			$query = $this->db->prepare('
				delete from draft_learning_summary
                where draft_learning_summary_id = ?
            ');

			return $query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeLearningSummaryGoals( $draft_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_draft_learning_summary
				where draft_learning_summary_id = ?
			');

			$query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function purgeLearningSummaryChildren($draft_id){
		try{
			$query = $this->db->prepare('
				delete from child_draft_learning_summary
				where draft_learning_summary_id = ?
			');

			$query->execute([ $draft_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
    
	public function editLearningSummaryNameTheme( $draft_id, $name_theme ) {
		try {
			$query = $this->db->prepare('
                update draft_learning_summary set name_theme = ?
                where draft_learning_summary_id = ?
            ');

			$query->execute([ $name_theme, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editLearningSummaryGroupUrl($draft_id, $group_url){
		try {
			$query = $this->db->prepare('
                update draft_learning_summary set group_url = ?
                where draft_learning_summary_id = ?
            ');

			$query->execute([ $group_url, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editLearningSummaryPictureDescription($draft_id, $picture_description){
		try {
			$query = $this->db->prepare('
                update draft_learning_summary set picture_description = ?
                where draft_learning_summary_id = ?
            ');

			$query->execute([ $picture_description, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
	
	public function associateLearningSummary($draft_id, $child_id) {
		try {
            $query = $this->db->prepare('
                insert into child_draft_learning_summary (child_id, draft_learning_summary_id)
				values (?, ?);
            ');

            return $query->execute([ $child_id, $draft_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }
    
	public function deassociateLearningSummary($draft_id, $child_id) {
		try {
            $query = $this->db->prepare('
				delete from child_draft_learning_summary 
				where child_id = ? and draft_learning_summary_id = ?;
            ');

            return $query->execute([ $child_id, $draft_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}

	public function addGoalLearningSummary($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_draft_learning_summary (goal_id, draft_learning_summary_id)
				values (?, ?);
            ');

			return $query->execute([ $goal_id, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
	
	public function removeGoalLearningSummary($draft_id, $goal_id) {
		try {
			$query = $this->db->prepare('
				delete from goal_draft_learning_summary 
				where goal_id = ? and draft_learning_summary_id = ?;
            ');

			return $query->execute([ $goal_id, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function updateLearningSummary($draft_id){
		try {
			$query = $this->db->prepare('
                update draft_learning_summary set updated_at = ?
                where draft_learning_summary_id = ?
            ');

			$query->execute([ Carbon::now(), $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**************************************************************************
	 ************************ DRAFT OF DAILY RECORD RELATED **********************
	 **************************************************************************/

	public function getRecordBatches($school_id, $user_id){
		try{
			$query = $this->db->prepare('
				select * from draft_record_batch 
				where school_id = ? and
				user_id = ?
				order by updated_at desc
			');

			$query->execute([ $school_id, $user_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	public function getRecordBatch($draft_id){
		try{
			$query = $this->db->prepare('
				select * from draft_record_batch
				where draft_record_batch_id = ?
			');

			$query->execute([ $draft_id ]);

			return $query->fetch( PDO::FETCH_OBJ );
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getRecordInBatch($draft_id, $type){
		try{
			$query = $this->db->prepare('
				select * from draft_records
				where draft_record_batch_id = ?
				and record_type = ?
			');

			$query->execute([ $draft_id, $type ]);

			return $query->fetch( PDO::FETCH_OBJ );
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getRecordsInBatch($draft_id){
		try{
			$query = $this->db->prepare('
				select * from draft_records 
				where draft_record_batch_id = ?
			');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getRecordParams($draft_record_id){
		try{
			$query = $this->db->prepare('
				select * from draft_record_params
				where draft_record_id = ?
			');

			$query->execute([ $draft_record_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getRecordChildren($draft_id){
		try{
			$query = $this->db->prepare('
				select * from child_draft_record_batch
				join children
					on children.child_id = child_draft_record_batch.child_id
				where child_draft_record_batch.draft_record_batch_id = ?
				order by children.child_name
			');

			$query->execute([ $draft_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addRecordBatch($school_id, $user_id, $record_public){
		try {
			$query = $this->db->prepare('
                insert into draft_record_batch (
					school_id, 
					user_id, 
					record_public,
					created_at, 
					updated_at)
                values (?, ?, ?, ?, ?)
            ');

			$query->execute([ $school_id, $user_id, $record_public, Carbon::now(), Carbon::now() ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addRecord($draft_id, $type){
		try {
			$query = $this->db->prepare('
                insert into draft_records (
					draft_record_batch_id, 
					record_type)
                values (?, ?)
            ');

			$query->execute([ $draft_id, $type ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function addRecordParam($draft_record_id, $param_id){
		try {
			$query = $this->db->prepare('
                insert into draft_record_params (
					draft_record_id, 
					param_id)
                values (?, ?)
            ');

			$query->execute([ $draft_record_id, $param_id ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	public function associateRecordBatch($draft_id, $child_id){
		try {
            $query = $this->db->prepare('
                insert into child_draft_record_batch (child_id, draft_record_batch_id)
				values (?, ?);
            ');

            return $query->execute([ $child_id, $draft_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}

	public function deassociateRecordBatch($draft_id, $child_id){
		try {
            $query = $this->db->prepare('
				delete from child_draft_record_batch 
				where child_id = ? and draft_record_batch_id = ?;
            ');

            return $query->execute([ $child_id, $draft_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}

	public function deleteRecordBatch($draft_id){
		try {
            $query = $this->db->prepare('
				delete from draft_record_batch 
				where draft_record_batch_id = ?;
            ');

            return $query->execute([ $draft_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}

	public function purgeRecordsInBatch($draft_id){
		try {
            $query = $this->db->prepare('
				delete from draft_records 
				where draft_record_batch_id = ?;
            ');

            return $query->execute([ $draft_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}

	public function purgeRecordChildren($draft_id){
		try {
            $query = $this->db->prepare('
				delete from child_draft_record_batch
				where draft_record_batch_id = ?;
            ');

            return $query->execute([ $draft_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}

	public function purgeRecordParams($draft_record_id){
		try {
            $query = $this->db->prepare('
				delete from draft_record_params
				where draft_record_id = ?;
            ');

            return $query->execute([ $draft_record_id ]);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
	}

	public function editRecordBatchPublic($draft_id, $record_public){
		try {
			$query = $this->db->prepare('
                update draft_record_batch set record_public = ?
                where draft_record_batch_id = ?
            ');

			$query->execute([ $record_public, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editRecordBatchDate($draft_id, $record_date){
		try {
			$query = $this->db->prepare('
                update draft_record_batch set record_date = ?
                where draft_record_batch_id = ?
            ');

			$query->execute([ $record_date, $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editRecordGroupUrl($draft_id, $type, $group_url){
		try {
			$query = $this->db->prepare('
                update draft_records set group_url = ?
                where draft_record_batch_id = ? and record_type = ?
            ');

			$query->execute([ $group_url, $draft_id, $type]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editRecordComment($draft_id, $type, $comment){
		try {
			$query = $this->db->prepare('
                update draft_records set record_comment = ?
                where draft_record_batch_id = ? and record_type = ?
            ');

			$query->execute([ $comment, $draft_id, $type]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editRecordTime($draft_id, $type, $time){
		try {
			$query = $this->db->prepare('
                update draft_records set record_time = ?
                where draft_record_batch_id = ? and record_type = ?
            ');

			$query->execute([ $time, $draft_id, $type]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function editRecordParam($draft_record_id, $param_id, $param_value){
		try {
			$query = $this->db->prepare('
                update draft_record_params set param_value = ?
                where draft_record_id = ? and param_id = ?
            ');

			$query->execute([ $param_value, $draft_record_id, $param_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function updateRecordBatch($draft_id){
		try {
			$query = $this->db->prepare('
                update draft_record_batch set updated_at = ?
                where draft_record_batch_id = ?
            ');

			$query->execute([ Carbon::now(), $draft_id]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

}