<?php

use Carbon\Services;
use Dompdf\Dompdf as Dompdf;

$app->group('/enrollment-page', function() use($app) {
    /***************************************************************************
     * GET 'enrollment-page'
     *
     * View enrollment requests
     **************************************************************************/
    $this->get('', function ($req, $res, $args) use ($app) {

        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        $Services = new Enrollment($this);



        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Requests';

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (isset($_GET['search'])) {
            $view['children'] = $Child->getSearch($_SESSION['school_id'], $_GET['search']);
            $view['search'] = $_GET['search'];
        } else {
            $view['children'] = $Child->getAll($_SESSION['school_id']);
        }

        $view['archived_children'] = $Child->getAll($_SESSION['school_id'], 'D');
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children']=$Services->joinChildEnrollment();

        for ($i=0;$i<count($view['children']);$i++)
        {

            foreach ($view['children'][$i] as $page_arr => $value)
            {

                if(!is_array($value)){

                    if (json_decode($value) === null) {

                        $sel_cat_arr[$i][$page_arr] = $value;

                    } else {

                        $sel_cat_arr[$i][$page_arr] = json_decode($value, true);

                    }

                } else {

                    $sel_cat_arr[$i][$page_arr] = $value;

                }
            }

        }

        $view['children']=$sel_cat_arr;

        return $this->view->render($res, 'enrollment-page.html', $view);


    })->setName('enrollmentPage');

    /***************************************************************************
     * GET 'enrollment-page/approuved'
     *
     * View enrollment approuved
     **************************************************************************/
    $this->get('/approved', function ($req, $res, $args) use ($app) {

        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        $Services = new Enrollment($this);



        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Approved Forms';

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (isset($_GET['search'])) {
            $view['children'] = $Child->getSearch($_SESSION['school_id'], $_GET['search']);
            $view['search'] = $_GET['search'];
        } else {
            $view['children'] = $Child->getAll($_SESSION['school_id']);
        }

        $view['archived_children'] = $Child->getAll($_SESSION['school_id'], 'D');
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children']=$Services->joinChildEnrollmentApproved();

        for ($i=0;$i<count($view['children']);$i++)
        {

            foreach ($view['children'][$i] as $page_arr => $value)
            {

                if(!is_array($value)){

                    if (json_decode($value) === null) {

                        $sel_cat_arr[$i][$page_arr] = $value;

                    } else {

                        $sel_cat_arr[$i][$page_arr] = json_decode($value, true);

                    }

                } else {

                    $sel_cat_arr[$i][$page_arr] = $value;

                }
            }

        }

        $view['children']=$sel_cat_arr;
        //return $res->withJSON($view['school_user']);
        return $this->view->render($res, 'enrollment-approved-page.html', $view);
    })->setName('enrollmentApprovedPage');



    /***************************************************************************
     * POST 'enrollment-page/edit'
     *
     * View enrollment requests
     **************************************************************************/
    $this->post('/edit', function ($req, $res, $args) use ($app) {
        $services = new Enrollment($this);

        $user = $req->getAttribute('user');
        $data = $req->getParsedBody();

        if(isset($_POST['edit_children_env'])){

            if (isset($data['required_files']['file-directory'])) {
                foreach ($data['required_files']['file-directory'] as $required_file) {
                    if($required_file != ''){
                        $required_file1 = $this->uploader->getFile($required_file);
                        $required_file1->store();
                    };
                }
            }
            if (isset($data['custom_fields']['file-directory]'])) {
                foreach ($data['custom_fields']['file-directory]'] as $custom_file) {
                     if($custom_file != '') {
                         $custom_file1 = $this->uploader->getFile($custom_file);
                         $custom_file1->store();
                     }
                }
            }

            foreach ($data as $field => $value) {
                if(!is_array($value)){
                    if (json_decode($value) === null) {
                        $data[$field] = json_encode($value);
                    } else {
                        $data[$field] = $value;
                    }
                } else {
                    $data[$field] = json_encode($value);
                }
            }
            unset($data['edit_children_env']);
            unset($_POST['edit_children_env']);
            $id=["id_field"=>"child_id" , "id_value"=>$data['edit_child_id']  ];

            $services->setEnrollmentOnlyForParent($data['edit_child_id'],$data['sent-info']);
            unset($data['sent-info']);

            unset($data['edit_child_id']);
            $children=json_decode($data['children'],true);



            $services->setChildIfExist('children',$children,$id);
            unset($data['children']);
            $data['child_id']=$id['id_value'];
            $child_id = $_POST['edit_child_id'];
            $services->editEnrollment('enrollments',$data,$child_id);
        }


        try {
            $this->logger->error('Success, save information.');
            $this->flash->addMessage('success', 'The information has been saved.');
            return $res->withRedirect('/enrollment-page');
        } catch (Exception $e) {
            $this->logger->error('Could not save.');
            $this->flash->addMessage('danger', 'Could not update your details.');
            return $res->withRedirect('/enrollment-page');
        }

    })->setName('enrollmentPageEdit');


    /***************************************************************************
     * POST 'enrollment-approved-page/edit'
     *
     * View enrollment requests
     **************************************************************************/
    $this->post('/editApproved', function ($req, $res, $args) use ($app) {
        $services = new Enrollment($this);

        $user = $req->getAttribute('user');
        $data = $req->getParsedBody();

        if(isset($_POST['edit_children_env'])){

            if (isset($data['required_files']['file-directory'])) {
                foreach ($data['required_files']['file-directory'] as $required_file) {
                    if($required_file != ''){
                        $required_file1 = $this->uploader->getFile($required_file);
                        $required_file1->store();
                    };
                }
            }
            if (isset($data['custom_fields']['file-directory]'])) {
                foreach ($data['custom_fields']['file-directory]'] as $custom_file) {
                     if($custom_file != '') {
                         $custom_file1 = $this->uploader->getFile($custom_file);
                         $custom_file1->store();
                     }
                }
            }

            foreach ($data as $field => $value) {
                if(!is_array($value)){
                    if (json_decode($value) === null) {
                        $data[$field] = json_encode($value);
                    } else {
                        $data[$field] = $value;
                    }
                } else {
                    $data[$field] = json_encode($value);
                }
            }
            unset($data['edit_children_env']);
            unset($_POST['edit_children_env']);
            $id=["id_field"=>"child_id" , "id_value"=>$data['edit_child_id']  ];

            $services->setEnrollmentOnlyForParent($data['edit_child_id'],$data['sent-info']);
            unset($data['sent-info']);

            unset($data['edit_child_id']);
            $children=json_decode($data['children'],true);



            $services->setChildIfExist('children',$children,$id);
            unset($data['children']);
            $data['child_id']=$id['id_value'];
            $child_id = $_POST['edit_child_id'];
            $services->editEnrollment('enrollments',$data,$child_id);
        }


        try {
            $this->logger->error('Success, save information.');
            $this->flash->addMessage('success', 'The information has been saved.');
            return $res->withRedirect('/enrollment-page/approved');
        } catch (Exception $e) {
            $this->logger->error('Could not save.');
            $this->flash->addMessage('danger', 'Could not update your details.');
            return $res->withRedirect('/enrollment-page/approved');
        }

    })->setName('enrollmentApprovedPageEdit');



    /***************************************************************************
     * POST 'enrollment-page/confirmed'
     *
     * View enrollment requests
     **************************************************************************/
    $this->post('/confirmed', function ($req, $res, $args) use ($app) {

        $enrollment = new Enrollment($this);
        $enrollment->confirmEnrollment($_POST['confirm']);

        return $res->withRedirect('/enrollment-page');

    })->setName('enrollmentPageConfirmed');



    /***************************************************************************
     * POST 'enrollment-page/deny'
     *
     * View enrollment requests
     **************************************************************************/
    $this->post('/deny', function ($req, $res, $args) use ($app) {
        $enrollment = new Enrollment($this);
        $enrollment->denyEnrollment($_POST['deny']);

        return $res->withRedirect('/enrollment-page');

    })->setName('enrollmentPageDeny');


    /***************************************************************************
     * GET 'enrollment-page/print/:child_id'
     *
     * View enrollment print
     **************************************************************************/
    $this->get('/print/{child_id}', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        $Services = new Enrollment($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Child Profiles';

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (isset($_GET['search'])) {
            $view['children'] = $Child->getSearch($_SESSION['school_id'], $_GET['search']);
            $view['search'] = $_GET['search'];
        } else {
            $view['children'] = $Child->getAll($_SESSION['school_id']);
        }

        $view['archived_children'] = $Child->getAll($_SESSION['school_id'], 'D');
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children']=$Services->getChildEnrollment($args['child_id']);


        for ($i=0;$i<count($view['children']);$i++)
        {

            foreach ($view['children'][$i] as $page_arr => $value)
            {

                if(!is_array($value)){

                    if (json_decode($value) === null) {

                        $sel_cat_arr[$i][$page_arr] = $value;

                    } else {

                        $sel_cat_arr[$i][$page_arr] = json_decode($value, true);

                    }

                } else {

                    $sel_cat_arr[$i][$page_arr] = $value;

                }
            }

        }

        $view['children']=$sel_cat_arr;


        $dompdf = new Dompdf();
        //$dompdf->set_option('defaultFont', 'Courier');
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled',true);

        $content = $this->view->fetch('enrollmentPrint.html', $view);

        $dompdf->loadHtml($content);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $data = $req->getParsedBody();

        $file_name = 'enrollment_'.$args['child_id'].'.pdf';
        $out = $dompdf->output($file_name);
        file_put_contents('enrollments/'.$file_name, $out);

        return $res->withStatus(200)
        ->withHeader('Content-Type', 'application/pdf')
        ->write($out);

    })->setName('enrollment_print');


});


