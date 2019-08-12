<?php

$app->group( '/running-records', function () use ( $app ) {
	/***************************************************************************
	 * GET '/running-records'
	 *
	 * View summary page where user can select date to display specific weekly
	 *  Plan
	 **************************************************************************/
	$this->get( '', function ( $req, $res, $args ) use ( $app ) {
		$School = new School( $this );

		if ( ! $School->getUser( $_SESSION['school_id'], $req->getAttribute( 'user_id' ) ) ) {
			$this->logger->info( 'School::getUser failed.', [
				'school_id' => $_SESSION['school_id'],
				'user_id'   => $req->getAttribute( 'user_id' )
			] );
			$this->flash->addMessage( 'danger', 'You don’t have sufficient rights.' );

			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'home' ) );
		}

		$RunningRecord = new RunningRecord( $this );
		$view          = [
			'records' => $RunningRecord->getAll( $_SESSION['school_id'] ),
			'title'   => 'Running Records'
		];

		return $this->view->render( $res, 'runningRecords.html', $view );
	} )->setName( 'runningRecords' );

	/***************************************************************************
	 * GET '/running-records/list'
	 *
	 * Child selection display page
	 *
	 **************************************************************************/
	$this->get( '/list', function ( $req, $res, $args ) use ( $app ) {
		$Child = new Child( $this );

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Children Overview';

		$children = $Child->getAssociatedChildren( $req->getAttribute( 'user_id' ) );

		//if only one child assigned - redirect to his/her running records
		if ( count( $children ) == 1 ) {
			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'runningRecordForChild', [ 'child_id' => $children[0]->child_id ] ) );
		}

		$view['children'] = $children;

		return $this->view->render( $res, 'runningRecordsSelectChild.html', $view );
	} )->setName( 'runningRecordsParent' );


	/***************************************************************************
	 * GET '/running-records/list/{child_id}'
	 *
	 * List of Running Records for a given child
	 **************************************************************************/
	$this->get( '/list/{child_id}', function ( $req, $res, $args ) use ( $app ) {

		$RunningRecord = new RunningRecord( $this );
		$Child = new Child($this);

		$records = $RunningRecord->getAllForParent( $req->getAttribute( 'user_id' ), $args['child_id'] );
		$child_details = $Child->getOne($args['child_id']);

		$view          = [
			'records' => $records,
			'title'   => 'Running Records for '.$child_details->child_name
		];

		return $this->view->render( $res, 'runningRecords.html', $view );
	} )->setName( 'runningRecordForChild' );


	/***************************************************************************
	 * GET '/running-records/{record_id}/edit'
	 *
	 * show form for editing running record
	 **************************************************************************/
	$this->get( '/{record_id}/edit', function ( $req, $res, $args ) use ( $app ) {

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Running Record';

		$view['group_items'] = [
			'basic'          => 'Basic',
			'purpose'        => 'Purpose',
			'observation'    => 'Observation',
			'interpretation' => 'Interpretation',
			'extension'      => 'Extension',
			'teacher'        => 'Teacher or Student Comments',
			'manager'        => 'Manager or Tutor Comments'
		];

		$view['types'] = [
			'school' => 'School',
			'room'   => 'Room(s)',
			'child'  => 'Child(ren)',
		];

		$view['emerging_interests'] = [
			'school'   => 'School',
			'children' => 'Children'
		];

		$Child         = new Child( $this );
		$Room          = new Room( $this );
		$RunningRecord = new RunningRecord( $this );

		$view['formdata'] = $RunningRecord->getOne( $args['record_id'] );
		if ( ! $view['formdata'] ) {
			$this->flash->addMessage( 'danger', 'Running Record not found' );

			return $res->withStatus( 302 )->withHeader( 'Location', $this->router->pathFor( 'runningRecords' ) );
		}

		$view['assoc_id_arr'] = $RunningRecord->getChildrenIds( $args['record_id'] );

		$view['children'] = $Child->getAll( $_SESSION['school_id'] );
		$view['rooms']    = $Room->getAll( $_SESSION['school_id'] );

		return $this->view->render( $res, 'runningRecordCreate.html', $view );
	} )->setName( 'runningRecordEdit' );

	/***************************************************************************
	 * GET '/running-records/add'
	 *
	 * show form for adding running record
	 **************************************************************************/
	$this->get( '/add', function ( $req, $res, $args ) use ( $app ) {

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Running Record';

		$view['group_items'] = [
			'basic'          => 'Basic',
			'purpose'        => 'Purpose',
			'observation'    => 'Observation',
			'interpretation' => 'Interpretation',
			'extension'      => 'Extension',
			'teacher'        => 'Teacher or Student Comments',
			'manager'        => 'Manager or Tutor Comments'
		];

		$view['types'] = [
			'school' => 'School',
			'room'   => 'Room(s)',
			'child'  => 'Child(ren)',
		];

		$view['emerging_interests'] = [
			'school'   => 'School',
			'children' => 'Children'
		];

		$Child = new Child( $this );
		$Room  = new Room( $this );

		$view['children'] = $Child->getAll( $_SESSION['school_id'] );
		$view['rooms']    = $Room->getAll( $_SESSION['school_id'] );

		return $this->view->render( $res, 'runningRecordCreate.html', $view );
	} )->setName( 'runningRecordAdd' );


	/***************************************************************************
	 * POST 'running-records/create'
	 *
	 * Save data for a new Running Record
	 **************************************************************************/
	$this->post( '/create', function ( $req, $res, $args ) use ( $app ) {

		$School        = new School( $this );
		$RunningRecord = new RunningRecord( $this );
		$Child         = new Child( $this );
		$Media         = new Media( $this );

		$data = $req->getParsedBody();

		if ( $req->getAttribute( 'csrf_status' ) === false ) {
			$this->logger->error( 'CSRF failure.' );
			$this->flash->addMessage( 'danger', 'Internal error.' );

			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'home' ) );
		}

		if ( ! $School->getUser( $_SESSION['school_id'], $req->getAttribute( 'user_id' ) ) ) {
			$this->logger->info( 'School::getUser failed.', [
				'school_id' => $_SESSION['school_id'],
				'user_id'   => $req->getAttribute( 'user_id' )
			] );
			$this->flash->addMessage( 'danger', 'You don’t have sufficient rights.' );

			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'home' ) );
		}

		if ( ! $data['children'] ) {
			$this->logger->info( 'No child selected.', [ 'user_id' => $req->getAttribute( 'user_id' ) ] );
			$this->flash->addMessage( 'danger', 'You forgot to select the children involved.' );

			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'runningRecordsAdd' ) );
		}

		$data['record_public'] = isset($data['record_public']) ? 1 : 0;
		$data['user_id']       = $req->getAttribute( 'user_id' );

		$running_record_id = $RunningRecord->addRunningRecord( $_SESSION['school_id'], $data );


		foreach ( $data['children'] as $child_id ) {
			$RunningRecord->connectChild( $running_record_id, $child_id );
			//check records.php to example of e-mail notification
		}


		// Media
		if ( $data['running_record_media'] ) {
			$this->logger->debug( 'Media files found.', [ 'group' => $data['running_record_media'] ] );

			$group = $this->uploader->getGroup( $data['running_record_media'] );
			$files = $group->getFiles();

			foreach ( $files as $file ) {
				$url_full      = $file->resize( 1600 )->getUrl();
				$url_thumbnail = $file->resize( 400 )->getUrl();

				$resized_full = $this->uploader->createLocalCopy( $url_full );
				$resized_full->store();

				$resized_thumbnail = $this->uploader->createLocalCopy( $url_thumbnail );
				$resized_thumbnail->store();

				$file->delete();

				$this->logger->debug( 'Saved media.',
					[
						'record_id'     => $running_record_id,
						'full_url'      => $resized_full->getUrl(),
						'thumbnail_url' => $resized_thumbnail->getUrl()
					] );

				$media_id = $Media->create(
					$resized_full->getUrl(),
					$resized_thumbnail->getUrl(),
					$resized_full->data['mime_type']
				);
				$RunningRecord->connectMedia( $running_record_id, $media_id );
			}
		}


		return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'runningRecordShow',
			[ 'record_id' => $running_record_id ] ) );
	} )->setName( 'runningRecordCreate' );


	/***************************************************************************
	 * POST 'running-records/update'
	 *
	 * Update Running Record
	 **************************************************************************/
	$this->post( '/update', function ( $req, $res, $args ) use ( $app ) {

		$School        = new School( $this );
		$RunningRecord = new RunningRecord( $this );
		$Media         = new Media( $this );

		$data = $req->getParsedBody();

		if ( $req->getAttribute( 'csrf_status' ) === false ) {
			$this->logger->error( 'CSRF failure.' );
			$this->flash->addMessage( 'danger', 'Internal error.' );

			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'home' ) );
		}

		if ( ! $School->getUser( $_SESSION['school_id'], $req->getAttribute( 'user_id' ) ) ) {
			$this->logger->info( 'School::getUser failed.', [
				'school_id' => $_SESSION['school_id'],
				'user_id'   => $req->getAttribute( 'user_id' )
			] );
			$this->flash->addMessage( 'danger', 'You don’t have sufficient rights.' );

			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'home' ) );
		}


		$running_record_id = $data['running_record_id'];
		if ( ! $data['children'] ) {
			$this->logger->info( 'No child selected.', [ 'user_id' => $req->getAttribute( 'user_id' ) ] );
			$this->flash->addMessage( 'danger', 'You forgot to select the children involved.' );

			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'runningRecordEdit',
				[ 'record_id' => $running_record_id ] ) );
		}


		$data['record_public'] = isset($data['record_public']) ? 1 : 0;

		$RunningRecord->editRunningRecord( $running_record_id, $data );
		$RunningRecord->disconnectAllChildren( $running_record_id );

		foreach ( $data['children'] as $child_id ) {
			$RunningRecord->connectChild( $running_record_id, $child_id );
		}

		// Media
		if ( $data['running_record_media'] ) {
			$this->logger->debug( 'Media files found.', [ 'group' => $data['running_record_media'] ] );

			$group = $this->uploader->getGroup( $data['running_record_media'] );
			$files = $group->getFiles();

			foreach ( $files as $file ) {
				$url_full      = $file->resize( 1600 )->getUrl();
				$url_thumbnail = $file->resize( 400 )->getUrl();

				$resized_full = $this->uploader->createLocalCopy( $url_full );
				$resized_full->store();

				$resized_thumbnail = $this->uploader->createLocalCopy( $url_thumbnail );
				$resized_thumbnail->store();

				$file->delete();

				$this->logger->debug( 'Saved media.',
					[
						'record_id'     => $running_record_id,
						'full_url'      => $resized_full->getUrl(),
						'thumbnail_url' => $resized_thumbnail->getUrl()
					] );

				$media_id = $Media->create(
					$resized_full->getUrl(),
					$resized_thumbnail->getUrl(),
					$resized_full->data['mime_type']
				);
				$RunningRecord->connectMedia( $running_record_id, $media_id );
			}
		}


		return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'runningRecordShow',
			[ 'record_id' => $running_record_id ] ) );
	} )->setName( 'runningRecordUpdate' );


	/***************************************************************************
	 * GET '/running-records/{record_id}'
	 *
	 * show  running record
	 **************************************************************************/
	$this->get( '/{record_id}', function ( $req, $res, $args ) use ( $app ) {

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Running Record';

		$RunningRecord = new RunningRecord( $this );

		$view['medias']        = $RunningRecord->getMedias( $args['record_id'] );
		$view['runningRecord'] = $RunningRecord->getOne( $args['record_id'] );

		if ( ! $view['runningRecord'] ) {
			$this->flash->addMessage( 'danger', 'Running Record not found' );

			return $res->withStatus( 302 )->withHeader( 'Location', $this->router->pathFor( 'runningRecords' ) );
		}


		return $this->view->render( $res, 'runningRecordDetails.html', $view );
	} )->setName( 'runningRecordShow' );

	/***************************************************************************
	 * GET '/running-records/{record_id}'
	 *
	 * show  running record
	 **************************************************************************/
	$this->post( '/{record_id}/delete', function ( $req, $res, $args ) use ( $app ) {

		$RunningRecord     = new RunningRecord( $this );
		$running_record_id = $args['record_id'];
		$RunningRecord->disconnectAllChildren( $running_record_id );

		$medias = $RunningRecord->getMedias( $args['record_id'] );
		foreach ( $medias as $media ) {
			$file = $this->uploader->getFile( $media->media_full_url );
			$file->delete();
		}

		$RunningRecord->disconnectAllMedia( $running_record_id );
		$RunningRecord->delete( $running_record_id );


		$this->logger->info( 'Running Record deleted.', [
			'running_record_id' => $running_record_id,
			'user_id'           => $req->getAttribute( 'user_id' )
		] );
		$this->flash->addMessage( 'success', 'The running story has been deleted.' );


		return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'runningRecords' ) );
	} )->setName( 'runningRecordDelete' );
} );