<?php
namespace Packaged\Remarkd;

use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;

class Document implements ISafeHtmlProducer
{
  public $title;
  public $authors = [];

  public $revisionNumber;
  public $revisionDate;
  public $revisionRemark;

  /** @var \Packaged\Remarkd\DocumentData */
  public $data;

  /**
   * @var Section[]
   */
  public $sections = [];

  public function produceSafeHTML(): SafeHtml
  {
    $response = new SafeHtml('');
    $response->append(...$this->sections);
    return $response;
  }

}
