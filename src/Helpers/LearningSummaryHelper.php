<?php

class LearningSummaryHelper {

	public static function assembleWeeklySummaryStats($yearly_weekly_learning_summary) {

		$assembledArray = array();

		foreach($yearly_weekly_learning_summary as $key => $val) {
			$assembledArray[$val->week]['learning_summary_count']++;
		}

		return $assembledArray;
	}
}