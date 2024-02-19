<?php
namespace Packaged\Remarkd\Rules;

class HighlightText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/[^\#\&]\#([^\#]+)\#/', '<mark class="highlight">\1</mark>', $text);
  }
}
