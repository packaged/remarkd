<?php
namespace Packaged\Remarkd\Rules;

class Image implements RemarkdRule, ProtectorAware
{
  protected $_protect;

  public function setProtector(?callable $protect)
  {
    $this->_protect = $protect;
  }

  protected function _shield($value)
  {
    return $this->_protect ? ($this->_protect)($value) : $value;
  }

  public function apply(string $text): string
  {
    /** @noinspection HtmlUnknownTarget */
    $text = preg_replace_callback('/image::([^\[]+)\[([^\]]*)]/', [$this, '_createAsciiDocTag'], $text);
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
      $attr[] = 'src="' . $this->_shield($this->_imageUrl($raw[2])) . '"';
    }
    if(isset($raw[1]) && !empty($raw[1]))
    {
      $attr[] = 'alt="' . $this->_shield($raw[1]) . '"';
    }
    if(isset($raw[3]) && !empty($raw[3]))
    {
      $attr[] = 'title="' . $this->_shield(str_replace('"', '', $raw[3])) . '"';
    }

    return sprintf('<img %s/>', implode(' ', $attr));
  }

  protected function _createAsciiDocTag($raw)
  {
    $parts = array_map('trim', explode(',', $raw[2]));
    $attr = ['src="' . $this->_shield($this->_imageUrl($raw[1])) . '"'];

    if(!empty($parts[0]))
    {
      $attr[] = 'alt="' . $this->_shield($parts[0]) . '"';
    }
    if(!empty($parts[1]))
    {
      $attr[] = 'width="' . $parts[1] . '"';
    }
    if(!empty($parts[2]))
    {
      $attr[] = 'height="' . $parts[2] . '"';
    }

    return sprintf('<img class="block" %s/>', implode(' ', $attr));
  }
}
