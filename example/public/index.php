<?php
define('PHP_START', microtime(true));

use Packaged\RemarkdExample\ExampleApplication;
use Cubex\Cubex;

$loader = require_once(dirname(__DIR__, 2) . '/vendor/autoload.php');

try
{
  $cubex = new Cubex(dirname(__DIR__), $loader);
  $cubex->handle(new ExampleApplication($cubex));
}
catch(Throwable $e)
{
  print_r($e);
}
finally
{
  if($cubex instanceof Cubex)
  {
    //Call the shutdown command
    $cubex->shutdown();
  }
}
