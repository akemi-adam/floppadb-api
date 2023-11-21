<?php

db()->autoConnect();

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
