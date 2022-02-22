<?php
namespace Packaged\Remarkd\Rules;

class TipText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/{(.*?)}\((.*?)\)/', '<span class="tooltip" title="\2">\1</span>', $text);
  }
}
