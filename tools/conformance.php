<?php
// Reports, as JSON, whether the PHP parser reproduces each fixture's
// expected.html. Used by the docs build step (build:conformance) to bake PHP
// results into docs/assets/conformance-status.json.
//
//   php tools/conformance.php        # prints {"accordion":true,...}

require __DIR__ . '/../vendor/autoload.php';

use Packaged\Remarkd\Remarkd;

$root = __DIR__ . '/../requirements/features';
$result = [];

foreach (scandir($root) as $name) {
    if ($name === '.' || $name === '..') {
        continue;
    }
    $dir = "$root/$name";
    if (!is_dir($dir)) {
        continue;
    }
    $input = @file_get_contents("$dir/input.remarkd");
    $expected = @file_get_contents("$dir/expected.html");
    if ($input === false || $expected === false) {
        continue;
    }
    $got = (new Remarkd())->parse(rtrim($input, "\r\n"));
    $result[$name] = trim($got) === trim($expected);
}

echo json_encode($result);
