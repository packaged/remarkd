<?php
namespace Packaged\Remarkd\Modules;

use Packaged\Helpers\Path;
use Packaged\Remarkd\Parser;
use Packaged\Remarkd\Remarkd;
use Packaged\SafeHtml\SafeHtml;

class IncludeModule extends AbstractRemarkdModule
{
  protected $_root;
  /** @var Remarkd */
  protected $_remarkd;

  public static function create(Remarkd $remarkd, $root = '')
  {
    $module = new static();
    $module->_remarkd = $remarkd;
    $module->_root = $root;
    return $module;
  }

  public function identifier(): string
  {
    return 'include';
  }

  public function produceSafeHTML(): SafeHtml
  {
    $path = Path::system($this->_root, $this->_key);
    if(file_exists($path))
    {
      $d = new Parser(file($path), $this->_remarkd);
      return $d->parse()->produceSafeHTML();
    }
    return new SafeHtml('<!-- unable to include ' . $this->_key . '-->');
  }
}
