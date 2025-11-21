<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'cors'     => \App\Filters\CorsFilter::class,
    ];

    /**
     * List of special required filters.
     * HAPUS forcehttps dan pagecache dari sini
     */
    public array $required = [
        'before' => [
            // KOSONG - no required before filters
        ],
        'after' => [
            'toolbar', // Keep only toolbar
        ],
    ];

    public array $globals = [
        'before' => [
            'cors',
        ],
        'after' => [
            'toolbar',
            'cors',
        ],
    ];

    public array $methods = [];
    public array $filters = [];
}