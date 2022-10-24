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

    $style = 'display: ' . $this->_config->get('display', 'inline-block') . ';';
    $style .= 'max-width: ' . $this->_config->get('max-width', '100%') . ';';

    $img->setAttribute('style', $style);

    $img->addClass(...$this->_config->classes());
    return $img;
  }
}
