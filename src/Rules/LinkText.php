<?php
namespace Packaged\Remarkd\Rules;

class LinkText implements RemarkdRule
{
  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace('/\[(.*?)]\((.*?)\)/', '<a href="\2">\1</a>', $text);
  }
}
