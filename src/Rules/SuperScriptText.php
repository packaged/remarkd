<?php
namespace Packaged\Remarkd\Rules;

class SuperScriptText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/\^([^\^]+?)\^/', '<sup>\1</sup>', $text);
  }
}
