<?php

namespace App\Middleware;

class ConfigDatabaseMiddleware extends \Leaf\Middleware
{
    public function call()
    {
        if (request()->get('database')) {
            db()->connect([
                'dbname' => request()->get('database'),
                'host' => _env('DB_HOST', '127.0.0.1'),
                'username' => _env('DB_USERNAME', 'root'),
                'password' => _env('DB_PASSWORD'),
            ]);
        } else {
            db()->autoConnect();
        }

        return $this->next();
    }
}
