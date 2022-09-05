<?php
namespace Packaged\Remarkd\Objects;

class AnchorObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'anchor';
  }

  public function render(): string
  {
    if(!isset($this->_config['name']) || empty($this->_config['name']))
    {
      return '[ANCHOR MISSING NAME]';
    }
    return '<a name="' . $this->_config['name'] . '"></a>';
  }

}
