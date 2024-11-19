<?php
namespace Packaged\Remarkd\Objects;

use Packaged\Glimpse\Tags\Media\Image;

class ImageObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'img';
  }

  public function render(): string
  {
    if(class_exists('\Packaged\Dispatch\ResourceManager'))
    {
      $resMan = \Packaged\Dispatch\ResourceManager::resources();
    }
    else
    {
      $resMan = $this->_context->getResourceRoot();
    }

    if($resMan->isExternalUrl($this->_config->get('src')))
    {
      $src = $this->_config->get('src');
    }
    else
    {
      $cwd = $this->_context->meta()->get('cwd');

      if(class_exists('\Packaged\Dispatch\ResourceManager'))
      {
        $src = $resMan->getResourceUri($cwd . DIRECTORY_SEPARATOR . ($this->_config->get('src') ?? ''));
      }
      else
      {
        $src = $this->_context->getResourceRoot() . DIRECTORY_SEPARATOR . $cwd . DIRECTORY_SEPARATOR . ($this->_config->get('src') ?? '');
      }
    }

    $img = Image::create($src, $this->_config->get("alt"));

    $style = 'display: ' . $this->_config->get('display', 'inline-block') . ';';
    $style .= 'max-width: ' . $this->_config->get('max-width', '100%') . ';';

    if($this->_config->has('float'))
    {
      $style .= 'float: ' . $this->_config->get('float') . ';';
    }

    $img->setAttribute('style', $style);

    $img->addClass(...$this->_config->classes());
    return $img;
  }
}
