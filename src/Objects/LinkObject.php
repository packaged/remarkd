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
    $target = $this->_config->has('target') ? $this->_config->get('target') : '';

    $link = Link::create($this->_config->get('href', $this->_key), $text);

    if($target)
    {
      $link->setAttribute('target', $target);
    }

    return $link;
  }
}
