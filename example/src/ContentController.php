<?php
namespace Packaged\RemarkdExample;

use Cubex\Controller\Controller;
use Packaged\Context\Context;
use Packaged\Dispatch\ResourceManager;
use Packaged\Dispatch\Resources\ResourceFactory;
use Packaged\Glimpse\Tags\Link;
use Packaged\Glimpse\Tags\Lists\ListItem;
use Packaged\Glimpse\Tags\Lists\UnorderedList;
use Packaged\Glimpse\Tags\Span;
use Packaged\Helpers\Strings;
use Packaged\Remarkd\Document;
use Packaged\Remarkd\Parser;
use Packaged\Remarkd\Remarkd;
use Packaged\Remarkd\Section;
use Packaged\RemarkdExample\Layout\DocPage;
use Packaged\RemarkdExample\Layout\Wrap;
use Packaged\Ui\Renderable;

class ContentController extends Controller
{
  protected $_replacementData = [];

  protected function _replacements($content)
  {
    return str_replace(array_keys($this->_replacementData), array_values($this->_replacementData), $content);
  }

  protected function _generateRoutes()
  {
    return 'docs';
  }

  protected function _prepareResponse(Context $c, $result, $buffer = null)
  {
    $pagelets = $c->request()->headers->has('x-pagelet-request');

    if($pagelets && $result instanceof DocPage)
    {
      $result->isFullPage = false;
    }

    if(($result instanceof Renderable || is_scalar($result)) && !$pagelets)
    {
      return parent::_prepareResponse($c, (new Wrap())->setContent($result), $buffer);
    }

    return parent::_prepareResponse($c, $result, $buffer);
  }

  protected function _docs($project)
  {
    $rmpm = ResourceManager::alias('remarkd');
    $rmpm->requireCss('resources/css/remarkd.css');
    $rmpm->requireCss('resources/css/remarkd-theme.css');
    $rmpm->requireJs('resources/js/tabs.js');

    $resDir = $this->getContext()->getProjectRoot() . '/resources/';
    $dir = $resDir . $project;

    if(empty($this->_replacementData))
    {
      $this->_replacementData = json_decode(file_get_contents($resDir . '/replacements.json'), true);
    }

    $page = new DocPage();
    $page->setContent(Span::create('Page not found')->addClass('not-found'));

    if(!file_exists($dir))
    {
      //If the DIR doesnt exist may as well bail out
      return $page;
    }

    $file = ltrim($this->request()->offsetPath(empty($project) ? 0 : 1) ?: 'index', '/');
    $base = rtrim($dir . $file, '/');

    if(file_exists($base . '.html'))
    {
      $page->setContent(file_get_contents($base . '.html'));
      return $page;
    }
    else if(file_exists($base . '/index.html'))
    {
      $page->setContent(file_get_contents($base . '/index.html'));
      return $page;
    }

    $loc = $base . '.md';
    if(!file_exists($loc))
    {
      $loc = $base . '.remarkd';
    }
    if(!file_exists($loc))
    {
      $loc = $base . '/index.md';
    }
    if(!file_exists($loc))
    {
      $loc = $base . '/index.remarkd';
    }
    if(file_exists($loc))
    {
      $remarkd = new Remarkd();
      $remarkd->setIncludePath($dir);
      $cwd = substr(dirname($loc), strlen($resDir));
      $remarkd->ctx()->meta()->set('cwd', $cwd);

      $d = new Parser(file($loc), $remarkd);
      $doc = $d->parse();
      //echo '<pre>'; var_dump($doc);die;

      $this->getContext()->meta()->set('title', $doc->title);

      $page->setToc($this->_toc($doc));
      $page->setContent($doc->produceSafeHTML());
      return $page;

      $path = substr($loc, 0, -3);
      $bname = basename($path);
      $vars = substr($path, 0, strlen($bname) * -1) . '.' . $bname . '/';

      $availableData = glob($vars . '*.json', GLOB_NOSORT);
      $replacementDatas = [];
      foreach($availableData as $availableFile)
      {
        $info = pathinfo($availableFile);
        $replacementDatas[$info['filename']] = Strings::titleize($info['filename']);
      }

      $this->getContext()->meta()->set('replacement-datas', $replacementDatas);

      if($this->request()->query->has('data'))
      {
        $dataFile = $vars . str_replace('/', '-', $this->request()->query->get('data')) . '.json';
        if(file_exists($dataFile))
        {
          $this->getContext()->meta()->set('current-data', $this->request()->query->get('data'));
          $meta = $remarkd->ctx()->meta();
          $data = json_decode(file_get_contents($dataFile), true);
          if($data)
          {
            foreach($data as $key => $value)
            {
              $value = $this->_replacements($value);
              $this->getContext()->meta()->set('md-' . $key, $value);
              $meta->set($key, $value);
            }
          }
          else
          {
            echo json_last_error_msg();
            die;
          }
        }
      }

      $content = $remarkd->render($md);
      $page->setContent($content);

      $matches = null;
      preg_match('/<h1([^>]+)>([^<]*)<\/h1>/', $content, $matches);
      if(isset($matches[2]))
      {
        $this->getContext()->meta()->set('page.title', $matches[2]);
      }
    }
    else
    {
      $loc = $dir . $file;
      if(!is_dir($loc) && file_exists($loc))
      {
        return ResourceFactory::fromFile($loc);
      }
    }
    return $page;
  }

  protected function _toc(Document $doc)
  {
    return $this->_addTocChildren(new UnorderedList(), $doc->sections);
  }

  protected function _addTocChildren(UnorderedList $list, array $sections)
  {
    foreach($sections as $section)
    {
      if($section instanceof Section && $section->level > 0)
      {
        $list->addItem(
          ListItem::create(Link::create('#' . $section->id, $section->title))->addClass('toc--' . $section->level)
        );
        if($section->hasChildren())
        {
          $list = $this->_addTocChildren($list, $section->children);
        }
      }
      else if(is_array($section))
      {
        $list = $this->_addTocChildren($list, $section);
      }
    }
    return $list;
  }

  public function getDocs()
  {
    return $this->_docs('');
  }
}
