<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Dispatch\ResourceManager;
use Packaged\Glimpse\Tags\Media\Image;

class ImageObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'img';
  }

  public function render(): string
  {
    $resMan = ResourceManager::resources();
    if($resMan->isExternalUrl($this->_config->get('src')))
    {
      $src = $this->_config->get('src');
    }
    else
    {
      $cwd = $this->_context->meta()->get('cwd');
      $src = $resMan->getResourceUri($cwd . '/' . ($this->_config->get('src') ?? ''));
    }

    $img = Image::create($src, $this->_config->get("alt"));

    $img->setAttribute('style', 'max-width: ' . $this->_config->get('max-width', '100%'));
    $img->addClass(...$this->_config->classes());
    return $img;
  }
}
