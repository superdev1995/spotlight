<?php

namespace App\Models;

use Psr\Log\LoggerInterface;

class Model {
    /** @var \PDO */
    protected $db;
    
    /** @var LoggerInterface */
    protected $logger;

    public function __construct($app) {
        $this->db = $app->db;
        $this->logger = $app->logger;
    }
}
