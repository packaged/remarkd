<?php
namespace Packaged\Remarkd\Objects;

class LineBreakObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'br';
  }

  public function render(): string
  {
    return '<br>';
  }

}
