<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

class PlanHelper {

	/**
	 * @param $yearly_weekly_plans
	 *
	 * @return array
	 */
	public static function assembleWeeklyPlanStats($yearly_weekly_plans) {

		$assembledArray = array();

		foreach($yearly_weekly_plans as $key => $val) {
			$assembledArray[$val->week]['plan_count']++;
			switch($val->assoc) {
				case 'room':
					$assembledArray[$val->week]['room_count']++;
					break;
				case 'school':
					$assembledArray[$val->week]['school_count']++;
					break;
				case 'child':
					$assembledArray[$val->week]['child_count']++;
					break;
			}
		}

		return $assembledArray;
	}

	/**
	 * @param $yearly_monthly_plans
	 *
	 * @return array
	 */
	public static function assembleMonthlyPlanStats($yearly_monthly_plans) {

		$assembledArray = array();

		foreach($yearly_monthly_plans as $val) {
			$assembledArray[$val->month]['plan_count']++;
			switch($val->assoc) {
				case 'room':
					$assembledArray[$val->month]['room_count']++;
					break;
				case 'school':
					$assembledArray[$val->month]['school_count']++;
					break;
				case 'child':
					$assembledArray[$val->month]['child_count']++;
					break;
			}
		}

		return $assembledArray;
	}

	/**
	 * @param $yearly_weekly_invoices
	 *
	 * @return array
	 */
	public static function assembleWeeklyInvoiceStats($yearly_weekly_invoices) {

		$assembledArray = array();

		//print_r($yearly_weekly_invoices);

		foreach($yearly_weekly_invoices as $key => $val) {

			$assembledArray[$val->week]['plan_id'] = $val->id;
			$assembledArray[$val->week]['in_session'] = $val->user_id;

			switch($val->user_id) {
				case $val->user_id:
					$assembledArray[$val->week]['school_count']++;
					break;
			}
		}

		return $assembledArray;
	}


	/**
	 * @param $assoc_entity
	 *
	 * @return string
	 */
	public static function assocEntityToDbTable($assoc_entity) {
		switch($assoc_entity) {
			case 'child':
				return 'children';
			case 'room':
				return 'rooms';
			case 'school':
				return 'schools';
		}
	}
}