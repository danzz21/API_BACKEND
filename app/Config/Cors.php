<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cors extends BaseConfig
{
    // File kosong - hanya untuk menghindari error "file not found"
    public $allowedOrigins = ['*'];
    public $allowedHeaders = ['*'];
    public $allowedMethods = ['*'];
    public $maxAge = 7200;
    public $supportsCredentials = false;
}