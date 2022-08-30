<?php
namespace Packaged\Remarkd\Rules;

class HighlightText implements RemarkdRule
{
  public function apply(string $text): string
  {
    return preg_replace('/(\#)(.+?)\1/', '<mark class="highlight">\2</mark>', $text);
  }

}
