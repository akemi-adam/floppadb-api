<?php

use Leaf\FS;

// db()->autoConnect();

app()->use(new \App\Middleware\ConfigDatabaseMiddleware);

app()->get('/', function () {
    response()->json(['message' => 'Congrats!! You\'re on Leaf API']);
});

app()->get('/database', function () {
    $data = array_map(function ($database) {
        $tables = array_map(
            fn ($table) => $table['Tables_in_' . $database['Database']],
            db()->query('SHOW TABLES FROM ' . $database['Database'])->get()
        );
        return [$database['Database'] => $tables];
    }, db()->query('SHOW DATABASES')->get());

    response()->json($data);
});

app()->post('/statements', function () {
    $statements = array_map(fn ($statement) => str_replace("&#039;", "'", $statement), request()->get('statements'));

    $data = array_map(function ($statement) {
        try {
            $result = db()->query($statement)->get() ?: "The statement: $statement; was executed with successfuly!";

            return $result;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }, $statements);

    response()->json($data);
});

app()->get('/export', function () {
    $path = StoragePath('backups/' . request()->get('database') . '.sql');

    $createFile = function (string $path, int $version = 0) use (&$createFile) {
        if (!file_exists($path)) {
            return FS::createFile($path);
        }

        $version += 1;

        $newPath = StoragePath(sprintf("backups/%s_%s.sql", request()->get('database'), $version));

        return $createFile($newPath, $version);
    };

    $createFile($path);

    try {
        $dump = new \Ifsnop\Mysqldump\Mysqldump(
            sprintf("mysql:host=%s;dbname=%s", _env('DB_HOST', 'localhost'), request()->get('database')),
            _env('DB_USERNAME', 'root'),
            _env('DB_PASSWORD')
        );
        $dump->start($path);
    } catch (\Exception $e) {
        return response()->json([ 'error' => 'Failed to generate backup', 'message' => $e->getMessage() ], 500);
    }

    response()->download($path, request()->get('database'), 200);
});
