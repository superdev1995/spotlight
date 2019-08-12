<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

use \Carbon\Carbon;

/**
 * Class Plan
 *
 * This class maintains functionality that is common to DailyPlan, WeeklyPlan, and MonthlyPlan classes
 */
class Plan extends App\Models\Model {

	/**
	 * @param $table
	 * @param $assoc_type
	 * @param $assoc_id
	 *
	 * @return mixed
	 */
	public function getPlanAssociation($table, $assoc_type, $assoc_id) {
		try {
			$query = $this->db->prepare( '
		              select * from ?
		              where ' . $assoc_type . '_id' . ' = ?
		              ' );

			$query->execute( [ $table, $assoc_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**************************************************************************
	 *************************** DAILY PLAN RELATED ***************************
	 **************************************************************************/

	/**
	 * @param $school_id
	 * @param $date
	 *
	 * @return
	 */
	public function getAllDailyPlans($school_id, $date) {
		try {
			$query = $this->db->prepare('
                select * from daily_plans
                where school_fk = ? AND date = ? AND deleted is FALSE
                
            ');

			$query->execute([ $school_id, $date ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function getDailyPlan($plan_id) {
		try {
			$query = $this->db->prepare('
                select * from daily_plans
                where daily_plan_id = ?
            ');

			$query->execute([ $plan_id ]);

			return $query->fetch(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function getDailyPlanBlocks($plan_id) {
		try {
			$query = $this->db->prepare('
                select * from daily_plan_blocks
                where daily_plan_fk = ? and deleted is false
            ');

			$query->execute([ $plan_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function deleteDailyPlan($plan_id) {
		try {
			$query = $this->db->prepare('
                update daily_plans
                set deleted = true
                where daily_plan_id = ?
            ');

			return $query->execute([ $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function deleteDailyPlanBlocks($plan_id) {
		try {
			$query = $this->db->prepare('
                update daily_plan_blocks
                set deleted = true
                where daily_plan_fk = ?
            ');

			return $query->execute([ $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}



	/**
	 * @param $school_id
	 * @param $date
	 * @param $name
	 * @param $assoc
	 * @param $created_by
	 *
	 * @return mixed
	 */
	public function addDailyPlan($school_id, $date, $name, $assoc, $created_by, $plan_img_url) {
        
        try {
			$query = $this->db->prepare('
                insert into daily_plans (school_fk, date, name, assoc, created_by, plan_img_url)
                values (?, ?, ?, ?, ?, ?)
            ');

			$query->execute([ $school_id, $date, $name, $assoc, $created_by, $plan_img_url ]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 * @param $type
	 * @param $associations
	 *
	 * TODO: Move to PlanAssociations class
	 */
	public function associateDailyPlan($plan_id, $type, $associations) {
		// For logic explanation, reference function addDailyPlanBlocks
		try {
			$values = array();

			$query_string = 'INSERT INTO daily_plan_assoc (daily_plan_fk, assoc_type, assoc_fk) VALUES ';

			foreach($associations as $assoc) {
				$query_string .= '(?,?,?),';

				array_push($values, $plan_id, $type, $assoc);
			}

			$query_string = rtrim($query_string, ",");

			$query = $this->db->prepare($query_string);

			$query->execute($values);
		} catch (PDOException $e) {
			$this->logger->error("Associate Daily Plan: " . $e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 * @param $blocks
	 */
	public function addDailyPlanBlocks($plan_id, $blocks) {
		// https://stackoverflow.com/questions/1176352/pdo-prepared-inserts-multiple-rows-in-single-query

		try {
			$values = array();

			$query_string = 'INSERT INTO daily_plan_blocks (daily_plan_fk, time_block, description) VALUES ';

			/**
			 * Generate a single Insert statement instead of individual execution for each daily plan block
			 * Add values to parameters accordingly
			 */
			foreach($blocks as $block) {
				$query_string .= '(?,?,?),';

				//$block_values = array($plan_id, $block['time_block'], $block['description']);
				//$values = array_merge($values, $block_values);

				/**
				 * below is faster than above
				 */

				array_push($values, $plan_id, $block['time_block'], $block['description']);

			}

			// Trim the last ',' from the query string
			$query_string = rtrim($query_string, ",");

			$query = $this->db->prepare($query_string);

			$query->execute($values);
		} catch (PDOException $e) {
			$this->logger->error("daily Plan Blocks " . $e->getMessage());
		}
	}

	public function createDailyPlanVideo($video_id, $daily_plan_id) {
		try {
			$query = $this->db->prepare('
                insert into video_daily_plan (video_id, daily_plan_id)
                values (?, ?)
            ');

			return $query->execute([ $video_id, $daily_plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 * @param $school_id
	 * @param $date
	 * @param $name
	 * @param $type
	 */
	public function editDailyPlan( $plan_id, $school_id, $date, $name, $type, $plan_img_url ) {
		try {
			$query = $this->db->prepare('
                update daily_plans set school_fk = ?, date = ?, name = ?, assoc = ?, plan_img_url = ?
                where daily_plan_id = ?
            ');

			$query->execute([ $school_id, $date, $name, $type, $plan_img_url, $plan_id]);

			//return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $goal_id
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function createGoalDailyPlan($goal_id, $plan_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_daily_plan (goal_fk, daily_plan_fk)
                values (?, ?)
            ');

			return $query->execute([ $goal_id, $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function getDailyPlanGoals($plan_id) {
		try {
			$query = $this->db->prepare('
            select * from goal_daily_plan
            join framework_goals
                on framework_goals.goal_id = goal_daily_plan.goal_fk
            join framework_categories
                on framework_categories.category_id = framework_goals.category_id
            join frameworks
                on frameworks.framework_id = framework_categories.framework_id
            where goal_daily_plan.daily_plan_fk = ?
            order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $plan_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 */
	public function purgeDailyPlanGoals( $plan_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_daily_plan
				where daily_plan_fk = ?
			');

			$query->execute([ $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**************************************************************************
	 *************************** WEEKLY PLAN RELATED **************************
	 **************************************************************************/

	/**
	 * @param $school_id
	 * @param $year
	 *
	 * @return mixed
	 */
	public function getAllWeeklyPlansForAYear($school_id, $year) {

		try{
			$query = $this->db->prepare('
				select * from weekly_plans 
				/*left join weekly_plan_assoc on weekly_plan_assoc.weekly_plan_fk = weekly_plans.weekly_plan_id*/
				where year = ? and
				 school_fk = ? and
				 deleted is FALSE
			');

			$query->execute([ $year, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $date1
	 * @param $date2
	 * @param $school_id
	 *
	 * @return mixed
	 */
	public function getAllWeeklyPlansbyDate($date1, $date2, $school_id) {
        try {
            $query = $this->db->prepare('
            select * from weekly_plans 
			where weekly_plans.updated_at between ? and ?
			and weekly_plans.school_fk= ?
            ');
      
            $query->execute([$date1, $date2, $school_id]);
      
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->logger->error($e->getMessage());
        }
    }


	/**
	 * @param $school_id
	 * @param $year
	 * @param $week
	 *
	 * @return mixed
	 */
	public function getAllWeeklyPlansForAWeek($school_id, $year, $week) {

		try{
			$query = $this->db->prepare('
				select * from weekly_plans 
				where year = ? and
				 week = ? and 
				 school_fk = ? and
				 deleted is FALSE
			');

			$query->execute([ $year, $week, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $table
	 * @param $plan_id
	 * @param $assoc_type
	 *
	 * @return mixed
	 */
	public function getAllWeeklyPlanAssociations($table, $plan_id, $assoc_type) {
		try {
			$query = $this->db->prepare( '
		              select *, ' . $table .'. ' . $assoc_type . '_name as name
		              from weekly_plan_assoc
		              left join ' . $table . ' on '. $table . '.' . $assoc_type. '_id' . ' = weekly_plan_assoc.assoc_fk
		              where weekly_plan_fk = ?
		              ' );

			$query->execute( [ $plan_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}


	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function getWeeklyPlan($plan_id) {
		try {
			$query = $this->db->prepare( '
		              select * from weekly_plans where weekly_plan_id = ?
		              ' );

			$query->execute( [ $plan_id ] );

			return $query->fetch( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}


	/**
	 * @param $school_id
	 * @param $year
	 * @param $week
	 * @param $name
	 * @param $assoc
	 * @param $created_by
	 *
	 * @return mixed
	 */
	public function addWeeklyPlan($school_id, $year, $week,  $name, $assoc, $created_by,$data ) {
		try {
			$query = $this->db->prepare('
                insert into weekly_plans (school_fk, year, week, assoc, name, created_by, created_at, updated_at, keyword_1, keyword_2, keyword_3, keyword_4, keyword_5)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

			$query->execute([ $school_id, $year, $week, $assoc, $name, $created_by, Carbon::now(), Carbon::now(), 
			$data['keyword_1'], $data['keyword_2'], $data['keyword_3'], $data['keyword_4'], $data['keyword_5']]);

			return $this->db->lastInsertId();
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 * @param $type
	 * @param $associations
	 */
	public function associateWeeklyPlan($plan_id, $type, $associations) {
		// For logic explanation, reference function addDailyPlanBlocks
		try {
			$values = array();

			$query_string = 'INSERT INTO weekly_plan_assoc (weekly_plan_fk, assoc_type, assoc_fk) VALUES ';

			foreach($associations as $assoc) {
				$query_string .= '(?,?,?),';

				array_push($values, $plan_id, $type, $assoc);
			}

			$query_string = rtrim($query_string, ",");

			$query = $this->db->prepare($query_string);

			$query->execute($values);
		} catch (PDOException $e) {
			$this->logger->error("Associate Weekly Plan: " . $e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 * @param $blocks
	 */
	public function addWeeklyPlanBlocks($plan_id, $blocks) {
		// https://stackoverflow.com/questions/1176352/pdo-prepared-inserts-multiple-rows-in-single-query

		try {
			$values = array();

			$query_string = '
				INSERT INTO weekly_daily_blocks 
				(weekly_plan_fk, 
				day, 
				learning_opportunity,
				time_when,
				rationale_interests,
				family_involvement,
				materials) VALUES ';

			/**
			 * Generate a single Insert statement instead of individual execution for each daily plan block
			 * Add values to parameters accordingly
			 */
			foreach($blocks as $block) {
				$query_string .= '(?,?,?,?,?,?,?),';

				array_push($values,
					$plan_id,
					$block['day'],
					$block['learning_opportunity'],
					$block['time_when'],
					$block['rationale_interests'],
					$block['family_involvement'],
					$block['materials']
				);
			}

			// Trim the last ',' from the query string
			$query_string = rtrim($query_string, ",");

			$query = $this->db->prepare($query_string);

			$query->execute($values);
		} catch (PDOException $e) {
			$this->logger->error("weekly Plan Blocks " . $e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 * @param $name
	 * @param $assoc
	 */
	public function editWeeklyPlan($plan_id, $name, $assoc, $data ) {
		$name = ($name == "" ? null : $name);

		try {
			$query = $this->db->prepare('
                update weekly_plans
                set name = ?,
                	assoc = ?,
					updated_at = ?,
					keyword_1 = ?,
					keyword_2 = ?,
					keyword_3 = ?,
					keyword_4 = ?,
					keyword_5 = ?
                where weekly_plan_id = ?
            ');

			$query->execute([ $name, $assoc, Carbon::now(), $data['keyword_1'], $data['keyword_2'], 
			$data['keyword_3'], $data['keyword_4'], $data['keyword_5'], $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	public function getWeeklyPlanBlocks($plan_id) {
		try {
			$query = $this->db->prepare( '
	              select * from weekly_daily_blocks
	              where weekly_plan_fk = ?
	              ' );

			$query->execute( [ $plan_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}


	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function purgeWeeklyPlanBlocks($plan_id) {
		try {
			$query = $this->db->prepare( '
		              delete from weekly_daily_blocks 
		              where weekly_plan_fk = ?
		              ' );

			$query->execute( [ $plan_id ] );

			return $query->fetch( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * @param $plan_id
	 */
	public function deleteWeeklyPlan($plan_id) {

		try{
			$query = $this->db->prepare('
				update weekly_plans
				set deleted = TRUE
				where weekly_plan_id = ?
			');

			$query->execute([ $plan_id ]);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function createWeeklyPlanVideo($video_id, $weekly_plan_id) {
		try {
			$query = $this->db->prepare('
                insert into video_weekly_plan (video_id, weekly_plan_id)
                values (?, ?)
            ');

			return $query->execute([ $video_id, $weekly_plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $goal_id
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function createGoalWeeklyPlan($goal_id, $plan_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_weekly_plan (goal_fk, weekly_plan_fk)
                values (?, ?)
            ');

			return $query->execute([ $goal_id, $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function getWeeklyPlanGoals($plan_id) {
		try {
			$query = $this->db->prepare('
            select * from goal_weekly_plan
            join framework_goals
                on framework_goals.goal_id = goal_weekly_plan.goal_fk
            join framework_categories
                on framework_categories.category_id = framework_goals.category_id
            join frameworks
                on frameworks.framework_id = framework_categories.framework_id
            where goal_weekly_plan.weekly_plan_fk = ?
            order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $plan_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 */
	public function purgeWeeklyPlanGoals( $plan_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_weekly_plan
				where weekly_plan_fk = ?
			');

			$query->execute([ $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**************************************************************************
	 ************************** MONTHLY PLAN RELATED **************************
	 *************************************************************************
	 *
	 * @param $data
	 *
	 * @return
	 */
	public function createMonthlyPlan($data) {

		try {
			$query = $this->db->prepare('
                insert into monthly_plans (
				school_fk,
				plan_public,
                month,
				year,
				assoc,
                theme,
                well_being,
                identity_belonging,
                communication,
                exploring_thinking,
                description,
                expressive_arts_design,
                literacy,
                mathematics,
                personal_social_emotional_development,
                physical_development,
                understanding_the_world,
                connected_contribute,
                confident_learners,
                comment,
                created_at,
                updated_at)
                value (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ');

			$query->execute([
				$data['school_id'],
				$data['public'],
				$data['month'],
				$data['year'],
				$data['monthlyPlan_type'],
				$data['theme'],
				$data['well_being'],
				$data['identity_belonging'],
				$data['communication'],
				$data['exploring_thinking'],
				$data['description'],
				$data['expressive_arts_design'],
				$data['literacy'],
				$data['mathematics'],
				$data['personal_social_emotional_development'],
				$data['physical_development'],
				$data['understanding_the_world'],
				$data['connected_contribute'],
				$data['confident_learners'],
				$data['comment'],
				Carbon::now(),
				Carbon::now()
			]);

			
			return $this->db->lastInsertId();
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 * @param $type
	 * @param $associations
	 */
	public function associateMonthlyPlan($plan_id, $type, $associations) {
		// For logic explanation, reference function addDailyPlanBlocks
		try {
			$values = array();

			$query_string = 'INSERT INTO monthly_plan_assoc (monthly_plan_fk, assoc_type, assoc_fk) VALUES ';

			foreach($associations as $assoc) {
				$query_string .= '(?,?,?),';

				array_push($values, $plan_id, $type, $assoc);
			}

			$query_string = rtrim($query_string, ",");

			$query = $this->db->prepare($query_string);

			$query->execute($values);
		} catch (PDOException $e) {
			$this->logger->error("Associate Monthly Plan: " . $e->getMessage());
		}
	}

	/**
	 * @param $school_id
	 * @param $year
	 *
	 * @return mixed
	 */
	public function getAllMonthlyPlansForAYear($school_id, $year) {

		try{
			$query = $this->db->prepare('
				select monthly_plan_id as id, month, assoc from 
				monthly_plans 
				where year = ? and
				 school_fk = ? and
				 deleted is FALSE
			');

			$query->execute([ $year, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $school_id
	 * @param $year
	 *
	 * @return mixed
	 */
	public function getPublicMonthlyPlansForAYear($school_id, $year) {

		try{
			$query = $this->db->prepare('
				select monthly_plan_id as id, month from 
				monthly_plans 
				where year = ? and
				 school_fk = ? and
				 plan_public = 1 and
				 deleted is FALSE
			');

			$query->execute([ $year, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $table
	 * @param $plan_id
	 * @param $assoc_type
	 *
	 * @return mixed
	 */
	public function getAllMonthlyPlanAssociations($table, $plan_id, $assoc_type) {
		try {
			$query = $this->db->prepare( '
		              select *, ' . $table .'. ' . $assoc_type . '_name as name
		              from monthly_plan_assoc
		              left join ' . $table . ' on '. $table . '.' . $assoc_type. '_id' . ' = monthly_plan_assoc.assoc_fk
		              where monthly_plan_fk = ?
		              ' );

			$query->execute( [ $plan_id ] );

			return $query->fetchAll( PDO::FETCH_OBJ );
		} catch ( PDOException $e ) {
			$this->logger->error( $e->getMessage() );
		}
	}

	/**
	 * @param $school_id
	 * @param $year
	 * @param $month
	 *
	 * @return mixed
	 */
	public function getAllMonthlyPlansForAMonth($school_id, $year, $month) {

		try{
			$query = $this->db->prepare('
				select * from monthly_plans 
				where year = ? and
				 month = ? and 
				 school_fk = ? and
				 deleted is FALSE
			');

			$query->execute([ $year, $month, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function getMonthlyPlan($plan_id) {

		try {
			$query = $this->db->prepare('
				select * from monthly_plans where 
					monthly_plan_id = ? and
					deleted is FALSE
			');

			$query->execute([ $plan_id ]);

			return $query->fetch(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 * @param $data
	 *
	 * TODO: Refactor for different countries
	 */
	public function updateMonthlyPlan($plan_id, $data) {

		try {
			$query = $this->db->prepare('
                update monthly_plans

				set
					plan_public = ?,
					theme = ?,
	                well_being = ?,
	                identity_belonging = ?,
	                communication = ?,
	                exploring_thinking = ?,
	                description = ?,
	                expressive_arts_design = ?,
	                literacy = ?,
	                mathematics = ?,
	                personal_social_emotional_development = ?,
	                physical_development = ?,
	                understanding_the_world = ?,
	                connected_contribute = ?,
	                confident_learners = ?,
	                comment = ?
                where monthly_plan_id = ?
            ');

			$query->execute([

				$data['public'],
				$data['theme'],
				$data['well_being'],
				$data['identity_belonging'],
				$data['communication'],
				$data['exploring_thinking'],
				$data['description'],
				$data['expressive_arts_design'],
				$data['literacy'],
				$data['mathematics'],
				$data['personal_social_emotional_development'],
				$data['physical_development'],
				$data['understanding_the_world'],
				$data['connected_contribute'],
				$data['confident_learners'],
				$data['comment'],
				$plan_id ]);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 */
	public function deleteMonthlyPlan($plan_id) {

		try{
			$query = $this->db->prepare('
				update monthly_plans
				set deleted = TRUE
				where monthly_plan_id = ?
			');

			$query->execute([ $plan_id ]);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $media_id
	 * @param $monthly_plan_id
	 *
	 * @return mixed
	 */
	public function createMonthlyMedia($media_id, $monthly_plan_id) {
		try {
			$query = $this->db->prepare('
                insert into media_monthly_plan (media_id, monthly_plan_id)
                values (?, ?)
            ');

			return $query->execute([ $media_id, $monthly_plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function createMonthlyPlanVideo($video_id, $monthly_plan_id) {
		try {
			$query = $this->db->prepare('
                insert into video_monthly_plan (video_id, monthly_plan_id)
                values (?, ?)
            ');

			return $query->execute([ $video_id, $monthly_plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $goal_id
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function createGoal($goal_id, $plan_id) {
		try {
			$query = $this->db->prepare('
                insert ignore into goal_monthly_plan (goal_fk, monthly_plan_fk)
                values (?, ?)
            ');

			return $query->execute([ $goal_id, $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**
	 * @param $plan_id
	 *
	 * @return mixed
	 */
	public function getMonthlyPlanGoals($plan_id) {
		try {
			$query = $this->db->prepare('
            select * from goal_monthly_plan
            join framework_goals
                on framework_goals.goal_id = goal_monthly_plan.goal_fk
            join framework_categories
                on framework_categories.category_id = framework_goals.category_id
            join frameworks
                on frameworks.framework_id = framework_categories.framework_id
            where goal_monthly_plan.monthly_plan_fk = ?
            order by frameworks.framework_name, framework_categories.category_name, framework_goals.goal_sort
        ');

			$query->execute([ $plan_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $plan_id
	 */
	public function purgeMonthlyPlanGoals( $plan_id ) {
		try{
			$query = $this->db->prepare('
				delete from goal_monthly_plan
				where monthly_plan_fk = ?
			');

			$query->execute([ $plan_id ]);
		} catch (PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}


	/**************************************************************************
	 *************************** WEEKLY INVOICES RELATED **************************
	 **************************************************************************/

	/**
	 * @param $school_id
	 * @param $year
	 *
	 * @return mixed
	 */

	public function getAllWeeklyInvoicesAllForAYear($school_id, $year) {

		try{
			$query = $this->db->prepare('
				select *, school_user.school_id as role_school_id, invoice.school_id as inv_school_id from invoice
				inner join school_user ON invoice.school_id = school_user.school_id
				/*left join weekly_plan_assoc on weekly_plan_assoc.weekly_plan_fk = weekly_plans.weekly_plan_id*/
				where year = ?
				and invoice.school_id = ?
				and role = 1

			');

			$query->execute([ $year, $school_id ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllWeeklyInvoicesTeacherForAYear($school_id, $year, $idT) {

		try{
			$query = $this->db->prepare('
				select * from invoice
				/*left join weekly_plan_assoc on weekly_plan_assoc.weekly_plan_fk = weekly_plans.weekly_plan_id*/
				where year = ?
				and school_id = ?
				and idT = ?
			');

			$query->execute([ $year, $school_id, $idT ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllWeeklyInvoicesParentForAYear($school_id, $year, $idP, $approved) {

		try{
			$query = $this->db->prepare('
				select *, child_user.status as status_user, invoice.status as status from invoice
	  			inner join child_user ON child_user.child_id = invoice.idC
				where year = ?
				and school_id = ?
				and child_user.user_id = ?
				and approved = ?
			');

			$query->execute([ $year, $school_id, $idP, $approved ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllWeeklyInvoicesParentForAWeek($school_id, $year, $week, $idP, $approved) {

		try{
			$query = $this->db->prepare('
				select *, child_user.status as status_user, invoice.status as status from invoice
	  			inner join child_user ON child_user.child_id = invoice.idC
				where year = ?
				and week = ?
				and school_id = ?
				and child_user.user_id = ?
				and approved = ?
			');

			$query->execute([ $year, $week ,$school_id, $idP, $approved ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	/**
	 * @param $school_id
	 * @param $year
	 * @param $week
	 * @param $idT
	 *
	 * @return mixed
	 */
	public function getAllWeeklyInvoicesTeacherForAWeek($school_id, $year, $week, $idT) {

		try{
			$query = $this->db->prepare('
				select * from invoice
				where year = ?
			    and week = ?
			    and school_id = ?
			    and idT = ?
			');

			$query->execute([ $year, $week, $school_id, $idT ]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}

	public function getAllWeeklyInvoicesAllForAWeek($school_id, $year, $week, $child_id, $status, $approved, $validate ) {

		$querySubString = "";

		if (isset($child_id) && $child_id != ""){
			$querySubString .= " and idC = '$child_id' ";
		}else{
			$querySubString .= "";
		}

		if (isset($status) && $status != ""){
			$querySubString .= " and status = '$status' ";
		}else{
			$querySubString .= "";
		}

		if (isset($approved) && $approved != ""){
			$querySubString .= " and approved = '$approved' ";
		}else{
			$querySubString .= "";
		}

		if (isset($validate) && $validate != ""){
			$querySubString .= " and validate = '$validate' ";
		}else{
			$querySubString .= "";
		}

		try{
			$query = $this->db->prepare("
				select * from invoice
				inner join users on invoice.user_id = users.user_id
				where year = ?
			    and week = ?
			    and school_id = ?
				$querySubString
			");

			$query->execute([ $year, $week, $school_id]);

			return $query->fetchAll(PDO::FETCH_OBJ);
		} catch(PDOException $e) {
			$this->logger->error($e->getMessage());
		}
	}
    
}