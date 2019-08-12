<?php

use Carbon\Services;

$app->group( '/family-and-child', function () use ( $app ) {

	/***************************************************************************
	 * GET 'family-and-child'
	 *
	 * View all family-and-child of a school
	 **************************************************************************/

	$this->get( '', function ( $req, $res, $args ) use ( $app ) {
		$Child      = new Child( $this );
		$Room       = new Room( $this );
		$School     = new School( $this );
		$enrollment = new Enrollment( $this );

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Child Profiles';

		$view['school_user'] = $School->getUser( $_SESSION['school_id'], $req->getAttribute( 'user_id' ) );

		if ( $req->getAttribute( 'user' )->user_type == 'P' ) {

			$view['children'] = $enrollment->getChildrenForParent( $req->getAttribute( 'user' )->user_id );


			for ( $i = 0; $i < count( $view['children'] ); $i ++ ) {

				foreach ( $view['children'][ $i ] as $page_arr => $value ) {

					if ( ! is_array( $value ) ) {

						if ( json_decode( $value ) === null ) {

							$sel_cat_arr[ $i ][ $page_arr ] = $value;

						} else {

							$sel_cat_arr[ $i ][ $page_arr ] = json_decode( $value, true );

						}

					} else {

						$sel_cat_arr[ $i ][ $page_arr ] = $value;

					}
				}

			}

			$view['children'] = $sel_cat_arr;


		} else {
			if ( isset($_GET['search']) ) {

				//var_dump($enrollment->getEnrollments());
				$view['children'] = $enrollment->getEnrollments();

				$view['search']   = $_GET['search'];

			} else {
				$view['children'] = $enrollment->getEnrollments();
			}
		}


		$view['archived_children'] = $Child->getAll( $_SESSION['school_id'], 'D' );
		$view['rooms']             = $Room->getAll( $_SESSION['school_id'] );
		$view['enrollmentCount'] = $enrollment->getEnrollmentsCount($_SESSION['school_id']);

		return $this->view->render( $res, 'parents-and-child.html', $view );

	} )->setName( 'familyAndChild' );

	/***************************************************************************
	 * POST 'family-and-child/create'
	 *
	 * Create a new enrollment
	 **************************************************************************/
	$this->post( '/create', function ( $req, $res, $args ) use ( $app ) {

		$services = new Enrollment( $this );
		$Child = new Child($this);
		$School = new School($this);

		$user = $req->getAttribute('user');
		$data = $req->getParsedBody();
		$child = $Child->getOneMore($data['child_id']);


		/**
		 * Create a new child et set a enrollement
		 */
		// if ( isset( $_POST['new_env_create'] ) ) {
		// 	if ( !empty($data['required_files']['file-directory'][0] && $data['required_files']['file-directory'][1]) ) {
		// 		foreach ( $data['required_files']['file-directory'] as $required_file ) {
		// 			$required_file1 = $this->uploader->getFile( $required_file );
		// 			$required_file1->store();
		// 		}
		// 	}
		// 	if ( !empty($data['custom_fields']['file-directory'][0] && $data['custom_fields']['file-directory'][1]) ) {
		// 		foreach ( $data['custom_fields']['file-directory]'] as $custom_file ) {
		// 			$custom_file1 = $this->uploader->getFile( $custom_file );
		// 			$custom_file1->store();
		// 		}
		// 	}

		// 	foreach ( $data as $field => $value ) {
		// 		if ( ! is_array( $value ) ) {
		// 			if ( json_decode( $value ) === null ) {
		// 				$data[ $field ] = json_encode( $value );
		// 			} else {
		// 				$data[ $field ] = $value;
		// 			}
		// 		} else {
		// 			$data[ $field ] = json_encode( $value );
		// 		}
		// 	}
		// 	unset( $data['new_env_create'] );
		// 	unset( $_POST['new_env_create'] );
		// 	$services->setEnrollmentAndChild( 'enrollments', $data, $_POST['room_id'] );
		// }

		if ( isset( $_POST['edit_children_env'] ) ) {

			if ( !empty($data['required_files']['file-directory'][0] && $data['required_files']['file-directory'][1]) ) {
				foreach ( $data['required_files']['file-directory'] as $required_file ) {
					$required_file1 = $this->uploader->getFile( $required_file );
					$required_file1->store();
				}
			}
			if ( !empty($data['custom_fields']['file-directory'][0] && $data['custom_fields']['file-directory'][1]) ) {
				foreach ( $data['custom_fields']['file-directory]'] as $custom_file ) {
					$custom_file1 = $this->uploader->getFile( $custom_file );
					$custom_file1->store();
				}
			}

			foreach ( $data as $field => $value ) {

				if ( ! is_array( $value ) ) {
					if ( json_decode( $value ) === null ) {
						$data[ $field ] = json_encode( $value );
					} else {
						$data[ $field ] = $value;
					}
				} else {
					$data[ $field ] = json_encode( $value );
				}
			}
		
			unset( $data['edit_children_env'] );
			unset( $_POST['edit_children_env'] );
			$id = [ "id_field" => "child_id", "id_value" => $data['edit_child_id'] ];
			
			$children = json_decode( $data['children'], true );


			unset( $data['children'] );
			$test= $services->setEnrollmentOnlyForParent( $data['edit_child_id'], $data['sent-info'] );
return $res->withJson($test);
			unset( $data['sent-info'] );
			unset( $data['edit_child_id'] );
			$services->setChildIfExist( 'children', $children, $id );
			$data['child_id'] = $id['id_value'];

			unset( $data['room_id'] );
			$services->setNewEnrollment( 'enrollments', $data );


		}


		if($user->user_type == 'T') {

			foreach ($Child->getParents($data['child_id']) as $parent) {
				if (!$parent->user_notify_record) {
					continue;
				}

				$this->mailer->send('enrolmentParents.html', [
						'to' => $parent->user_email,
						'subject' => 'A new enrolment form has been filled for your child.',
						'first_name' => $parent->user_first_name,
						'user' => $req->getAttribute('user'),
						'child' => $child,
				]);

				$this->logger->info('Notification sent.', ['email' => $parent->user_email]);
			}
		}else{

			foreach ($School->getAdministrators($_SESSION['school_id']) as $administrator) {

				if (!$administrator->user_notify_record) {
                	continue;
            	}

				$this->mailer->send('enrolmentAdmin.html', [
					'to' => $administrator->user_email,
					'subject' => 'A new enrolment form has been filled, a validation is required.',
					'first_name' => $administrator->user_first_name,
					'user' => $req->getAttribute('user'),
					'child' => $child,
					'usercreate' => $user,
				]);
            	$this->logger->info('Notification sent.', [ 'email' => $administrator->user_email ]);
        	}
		}


		return $res->withRedirect( '/family-and-child' );
	} )->setName( 'familyAndChildCreate' );


	/***************************************************************************
	 * POST 'family-and-child/get-enrollment-count'
	 *
	 * See number of enrollment
	 **************************************************************************/
	$this->post( '/get-enrollment-count', function ( $req, $res, $args ) use ( $app ) {

		$enrollment      = new Enrollment( $this );
		$enrollmentCount = $enrollment->getEnrollmentsCount($_SESSION['school_id']);

		return $this->view->render( $res, 'childEnrollmentCount.html', [ 'enrollmentCount' => $enrollmentCount ] );

	} )->setName( 'familyAndChildGetCount' );

} );