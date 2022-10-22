<?php
namespace Packaged\Remarkd\Objects;

class VideoObject extends AbstractRemarkdObject
{
  public function getIdentifier(): string
  {
    return 'video';
  }

  public function render(): string
  {
    switch($this->_config->get('aspect'))
    {
      case '1:1';
        $padding = '100';
        break;
      case '4:3';
        $padding = '75';
        break;
      case '3:2';
        $padding = '66.66';
        break;
      case '8:5';
        $padding = '62.5';
        break;
      case '16:9':
      default:
        $padding = '56.25';
        break;
    }
    $containerAppend = ' style="padding-top: ' . $padding . '%"';

    switch($this->_config->get('source', 'youtube'))
    {
      case 'youtube':
        $content = $this->_completeYoutube($this->_key);
        break;
    }

    return '<div class="video-container"' . $containerAppend . '>' . $content . '</div>';
  }

  protected function _completeYoutube($id)
  {
    return '<iframe '
      . 'src="https://www.youtube.com/embed/' . $id . '" '
      . 'title="YouTube video player" frameborder="0" '
      . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" '
      . 'allowfullscreen></iframe>';
  }

}
