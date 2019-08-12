<?php
/**
 * User: dadirlam@hotmail.com
 */

use \Carbon\Carbon;

$app->group('/API/attendanceInsights', function() use($app) {
	/***************************************************************************
	 * GET 'children'
	 *
	 * View all children of a school and provide attendance insights buttons
	 **************************************************************************/
	$this->get('', function($req, $res, $args) use($app) {
		$Child = new Child($this);
		$Room = new Room($this);
		$School = new School($this);

		$view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

		if (!$view['school_user']) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		if (isset($_GET['search'])) {
			$view['children'] = $Child->getSearch($_SESSION['school_id'], $_GET['search']);
			$view['search'] = $_GET['search'];
		} else {
			$view['children'] = $Child->getAll($_SESSION['school_id']);
		}

		$view['archived_children'] = $Child->getAll($_SESSION['school_id'], 'D');
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

		$data=array('childs'=>$view['children'],'rooms'=>$view['rooms'],'archived_child'=>$view['archived_children']);

        return $res->withJson($data);
	})->setName('attendanceInsightsChildren');


	/***************************************************************************
	 * GET '/attendanceInsights/:child_id/:year/:month/'
	 *
	 * View attendance insights for a child with a given month/year
	 **************************************************************************/
	$this->get( '/{child_id}/{year}/{month}', function ( $req, $res, $args ) use ( $app ) {
		$Insights = new AttendanceInsights($this);
		$School = new School($this);

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$view['months'] = array(
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December'
		);

		/**
		*Calendar section
		*/
		$view['year'] = $args['year'];
		$view['month'] = $args['month'];
		$view['next_month'] = Carbon::create($args['year'], $args['month'],"15")->addMonth()->format('m');
		$view['next_year'] = Carbon::create($args['year'], $args['month'],"15")->addMonth()->format('Y');
		$view['prev_month'] = Carbon::create($args['year'], $args['month'],"15")->subMonth()->format('m');
		$view['prev_year'] = Carbon::create($args['year'], $args['month'],"15")->subMonth()->format('Y');

		$view['child_id'] = $args['child_id'];

		$month_timesheets_obj =	$Insights->getAttendanceInsightsMonthly($args['child_id'], $args['year'], $args['month']);
		$calendar = array();
		foreach ($month_timesheets_obj as $timesheet) {

			if ($timesheet->timesheet_in || $timesheet->timesheet_out) {

				$time_in = Carbon::parse($timesheet->timesheet_in);
				$time_out = Carbon::parse($timesheet->timesheet_out);

				$day_duration = $time_out->diffInSeconds($time_in);
				$formatted_duration = $day_duration > 0 ? gmdate('H:i', $day_duration) : '';

				$calendar[$timesheet->timesheet_date]['week_number'] = Carbon::parse($timesheet->timesheet_date)->format('W');
				$calendar[$timesheet->timesheet_date]['time_in'] = Carbon::parse($timesheet->timesheet_in)->format('H:i');
				$calendar[$timesheet->timesheet_date]['time_total'] = $formatted_duration;
				$calendar[$timesheet->timesheet_date]['time_out'] = Carbon::parse($timesheet->timesheet_out)->format('H:i');

			}

			switch($timesheet->missing) {
				case 'Absent':
					$calendar[$timesheet->timesheet_date]['type'] = 'Absent';
					$calendar[$timesheet->timesheet_date]['cell_class'] = 'table-danger';
					break;
				case 'Sick':
					$calendar[$timesheet->timesheet_date]['type'] = 'Sick';
					$calendar[$timesheet->timesheet_date]['cell_class'] = 'table-warning';
					break;
				case 'Holiday':
					$calendar[$timesheet->timesheet_date]['type'] = 'Holiday';
					$calendar[$timesheet->timesheet_date]['cell_class'] = 'table-info';
					break;
				default:
					if ($timesheet->timesheet_in || $timesheet->timesheet_out) {
						$calendar[$timesheet->timesheet_date]['cell_class'] = 'table-default';
					} else {
						$calendar[$timesheet->timesheet_date]['cell_class'] = 'table-default';
					}
					break;
			}

		}

		$view['calendar_layout'] = Calendar::MakeCalendar($args['month'], $args['year']);
		$view['calendar_data'] = $calendar;

		/**
		*Graphs Section
		*/
		// $view['date_start'] = $_GET['date_start'] ? $_GET['date_start'] : date('Y-m-d', strtotime($args['year'].'-'.$args['month'].'-01'));
		// $view['date_end'] = $_GET['date_end'] ? $_GET['date_end'] : date('Y-m-d', strtotime('last day of '.$view['months'][$args['month']].' '.$args['year']));

		// $carbonStart = Carbon::parse($view['date_start']);
		// $carbonEnd = Carbon::parse($view['date_end']);
		// $view['maxDays'] = $carbonStart->diffInDays($carbonEnd);

		// $view['graph_days'] = array_fill_keys([
		// 	'Sunday',
		// 	'Monday',
		// 	'Tuesday',
		// 	'Wednesday',
		// 	'Thursday',
		// 	'Friday',
		// 	'Saturday'
		// ], array());
		
		// $attendanceHistory = $Insights->getAttendanceInsights($args['child_id'], $view['date_start'], $view['date_end']);
		// foreach ($attendanceHistory as $record) {
		// 	$day = Carbon::parse($record->timesheet_date)->format('l');
		// 	switch ($record->missing) {
		// 		case 'Sick':
		// 			$view['graph_days'][$day]['Sick']++;
		// 			$view['graph_horizontal']['Sick']++;
		// 			break;
		// 		case 'Absent':
		// 			$view['graph_days'][$day]['Absent']++;
		// 			$view['graph_horizontal']['Absent']++;
		// 			break;
		// 		case 'Holiday':
		// 			$view['graph_days'][$day]['Holiday']++;
		// 			$view['graph_horizontal']['Holiday']++;
		// 			break;
		// 		default:
		// 			if ($record->timesheet_in) {
		// 				// Uncomment to enable present days showing in day graph
		// 				$view['graph_days'][$day]['Present']++;
		// 				$view['graph_horizontal']['Present']++;
		// 			}
		// 			break;
		// 	}
		// 	$view['graph_days'][$day]['Total']++;
		// }
		// $view['maxDays'] = array_sum($view['graph_horizontal']);
	
		$data=array('calendar_layout'=>$view['calendar_layout'],'calendar_data'=>$view['calendar_data'],
		'prev_month'=>$view['prev_month'],'prev_year'=>$view['prev_year'],'next_month'=>$view['next_month'],'next_year'=>$view['next_year'],
		'year'=>$view['year'],'month'=>$view['month'],'months'=>$view['months']);

		return $res->withJson($data);
		//return $this->view->render( $res, 'attendanceInsightsReport.html', $view );
	})->setName( 'attendanceInsightsReport' );
});
