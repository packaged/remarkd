<?php
namespace Packaged\Remarkd\Rules;

class Image implements RemarkdRule
{
  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    return preg_replace_callback('/!\[([^\]]*)\]\((.*?)\s*("(?:.*[^"])")?\s*\)/', [$this, '_createTag'], $text);
  }

  protected function _imageUrl($src)
  {
    return $src;
  }

  protected function _createTag($raw)
  {
    $attr = [];

    if(isset($raw[2]) && !empty($raw[2]))
    {
      $attr[] = 'src="' . $this->_imageUrl($raw[2]) . '"';
    }
    if(isset($raw[1]) && !empty($raw[1]))
    {
      $attr[] = 'alt="' . $raw[1] . '"';
    }
    if(isset($raw[3]) && !empty($raw[3]))
    {
      $attr[] = 'title="' . str_replace('"', '', $raw[3]) . '"';
    }

    return sprintf('<img %s/>', implode(' ', $attr));
  }
}
