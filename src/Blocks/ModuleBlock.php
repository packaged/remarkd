<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\Modules\RemarkdModule;

class ModuleBlock implements BlockMatcher
{
  /** @var RemarkdModule[] */
  protected $_register = [];

  public function registerModule(RemarkdModule $module)
  {
    $this->_register[$module->identifier()] = $module;
    return $this;
  }

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/^([\w-]+)::(.*)(\[(.*)?\])\s*$/mi', $line, $matches))
    {
      if(isset($this->_register[$matches[1]]))
      {
        $module = clone $this->_register[$matches[1]];
        $module->setKey($matches[2]);
        $module->setAttributes(new Attributes($matches[4]));
        return $module;
      }
    }
    return null;
  }

}
