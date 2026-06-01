<?php
namespace Packaged\Remarkd\Rules;

use Packaged\Helpers\Strings;

class SectionLinkText implements RemarkdRule
{
  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace_callback('/(\<\<([\w\-_]+)(,([^>]+))?\>\>)/', function ($match) {
      $id = str_replace('_', '-', ltrim($match[2], '_'));
      $label = $match[4] ?? Strings::titleize($id);
      return '<a href="#' . $id . '">' . $label . '</a>';
    }, $text);
  }
}
