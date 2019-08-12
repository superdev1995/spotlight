<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

class Goals {

	//private $_all_goals = array();
	//private $_checked_goal_ids = array();

	/**
	 * Filter array only to include goals and respective names and headings that have been selected by user
	 *
	 * @param $all_goals
	 * @param $checked_goal_ids
	 *
	 * @return array
	 */
	public static function extractCheckedGoals($all_goals, $checked_goal_ids) {

		foreach($all_goals as $category_key => $goals) {

			$no_goals_checked_within_category = true;

			foreach($goals['goals'] as $goal_key => $val) {

				if(!in_array($val->goal_id, $checked_goal_ids)) {
					unset($all_goals[$category_key]['goals'][$goal_key]);
				} else {
					$no_goals_checked_within_category = false;
				}
			}

			if($no_goals_checked_within_category) { unset($all_goals[$category_key]); }
		}

		return $all_goals;
	}


}