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

    $content = "";
    switch($this->_config->get('source', 'youtube'))
    {
      case 'youtube':
        $content = $this->_completeYoutube($this->_key, $this->_config->get('start', 0), $this->_config->get('end', 0));
        break;
      case 'self':
        $type = $this->_config->get('type', 'video/mp4');
        $content = '<video controls><source src="' . $this->_key . '" type="' . $type . '"></video>';
        break;
    }

    return '<div class="video-container"' . $containerAppend . '>' . $content . '</div>';
  }

  protected function _completeYoutube($id, $start = 0, $end = 0)
  {
    $opts = [];
    if($start > 0)
    {
      $opts[] = 'start=' . $start;
    }
    if($end > 0)
    {
      $opts[] = 'end=' . $end;
    }

    $opts[] = 'rel=0';
    return '<iframe '
      . 'src="https://www.youtube.com/embed/' . $id . (empty($opts) ? '' : '?' . implode(';', $opts)) . '" '
      . 'title="YouTube video player" frameborder="0" '
      . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" '
      . 'allowfullscreen></iframe>';
  }

}
