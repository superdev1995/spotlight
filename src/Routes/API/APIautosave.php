<?php

  $app->group('/API', function() use($app) {

    /***************************************************************************
     * Post 'autosave'
     *
     * Write to file textfield from learning story
     *
     * param: cookie, iname, input
     **************************************************************************/
     $this->post('/autosave', function($req, $res, $args) use($app) {
       $s = new Autosave();

       $data = $req->getParsedBody();

       if (!$data['cookie'] || !$data['iname'] || !$data['input']) {
           $this->logger->info('Auto Save sent incomplete request.');

           return $res->withJson(['error'=>'Can not save such input with Auto Save.']);
       }

       $s.save($data['cookie'], $data['iname'], $data['input']);

       return $res->withJson(['success'=>'Input saved by Auto Save.']);
     })->setName('autosaveSave');

     /***************************************************************************
      * Post 'autosave/:cookie/:fieldname'
      *
      * Fetch textfield data
      **************************************************************************/
      $this->post('/autosave/{cookie}/{iname}', function($req, $res, $args) use($app) {
        $s = new Autosave();

        $cookie = $args['cookie'];
        $iname = $args['iname'];

        $input = $s.fetch($cookie, $iname);

        return $res->withJson(['success']->$input);
      })->setName('autosaveFetch');

  });

?>
