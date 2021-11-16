<?php
namespace Packaged\Remarkd\Rules;

class LinkText implements RemarkdownRule
{
  public function apply(string $text): string
  {
    return preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function ($matches) {
      return '<a href="' . $matches[2] . '">' . $matches[1] . '</a>';
    }, $text);
  }
}
