<?php
namespace Packaged\Remarkd\Rules;

class MonospacedText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/\B`(.+?)`/', '<span class="monospace">\1</span>', $text);
  }
}
