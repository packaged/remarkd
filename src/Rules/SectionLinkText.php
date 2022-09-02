<?php
namespace Packaged\Remarkd\Rules;

use Packaged\Helpers\Strings;

class SectionLinkText implements RemarkdRule
{
  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace_callback('/(\<\<([\w\-]+)\>\>)/', function ($match) {
      return '<a href="#' . $match[2] . '">' . Strings::titleize($match[2]) . '</a>';
    }, $text);
  }
}
