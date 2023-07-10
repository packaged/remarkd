<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Glimpse\Tags\Link;
use Packaged\Helpers\Strings;

class LinkObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'link';
  }

  public function render(): string
  {
    $text = $this->_config->get('text', Strings::titleize($this->_key));
    $link = Link::create($this->_config->get('href', $this->_key), $text);

    $target = $this->_config->get('target');
    if($target !== null)
    {
      $link->setAttribute('target', $target);
    }

    $hrefLang = $this->_config->get('hreflang');
    if($hrefLang !== null)
    {
      $link->setAttribute('hreflang', $hrefLang);
    }

    return $link;
  }
}
