<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Health extends ResourceController
{
    protected $format = 'json';

    /**
     * Connector Internal Healthcheck (§17.5).
     */
    public function index()
    {
        return $this->respond([
            'status' => 'ok',
            'service' => 'ekaa-connector',
            'timestamp' => date('c'),
        ]);
    }
}
