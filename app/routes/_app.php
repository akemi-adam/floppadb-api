<?php

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
    //
});
