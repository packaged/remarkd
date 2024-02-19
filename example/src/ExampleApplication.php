<?php
namespace Packaged\RemarkdExample;

use Cubex\Application\Application;
use Cubex\Cubex;
use Cubex\Events\Handle\ResponsePreSendHeadersEvent;
use Packaged\Context\Context;
use Packaged\Context\WithContext;
use Packaged\Context\WithContextTrait;
use Packaged\Dispatch\Dispatch;
use Packaged\Dispatch\Resources\ResourceFactory;
use Packaged\Helpers\ValueAs;
use Packaged\Http\Request;
use Packaged\Http\Response;
use Packaged\Routing\Handler\FuncHandler;
use Packaged\Routing\HealthCheckCondition;
use Packaged\Routing\Route;
use Packaged\Routing\Routes\InsecureRequestUpgradeRoute;

class ExampleApplication extends Application implements WithContext
{
  const DISPATCH_PATH = '/r';
  use WithContextTrait;

  protected function _generateRoutes()
  {
    //Handle common health check calls.
    yield Route::with(new HealthCheckCondition())->setHandler(
      function () {
        return Response::create('OK');
      }
    );

    //Handle approved static resources from the public folder
    foreach(['favicon.ico', 'robots.txt'] as $publicFile)
    {
      yield self::_route(
        "/" . $publicFile,
        function (Context $c) use ($publicFile) {
          return ResourceFactory::fromFile($c->getProjectRoot() . '/public/' . $publicFile);
        }
      );
    }

    if(ValueAs::bool($this->getContext()->config()->getItem('serve', 'redirect_https')))
    {
      yield InsecureRequestUpgradeRoute::i();
    }

    $proxies = $this->getContext()->config()->getItem('serve', 'trusted_proxies');
    if($proxies !== null)
    {
      Request::setTrustedProxies(ValueAs::arr($proxies), Request::HEADER_X_FORWARDED_ALL);
    }

    yield self::_route(
      self::DISPATCH_PATH,
      new FuncHandler(
        function (Context $c): \Symfony\Component\HttpFoundation\Response {
          return Dispatch::instance()->handleRequest($c->request());
        }
      )
    );

    //Run any generic setup here
    $this->_setupApplication();

    yield self::_route('/', ContentController::class);

    //Let the parent application handle routes from here
    return parent::_generateRoutes();
  }

  public function __construct(Cubex $cubex)
  {
    parent::__construct($cubex);

    // Convert errors into exceptions
    set_error_handler(
      function ($errno, $errstr, $errfile, $errline) {
        if((error_reporting() & $errno) && !($errno & E_NOTICE))
        {
          throw new \ErrorException($errstr, 0, $errno, str_replace(dirname(__DIR__), '', $errfile), $errline);
        }
      }
    );
  }

  protected function _initialize()
  {
    parent::_initialize();

    //Setup our asset/resource handler
    $dispatch = new Dispatch($this->getContext()->getProjectRoot(), self::DISPATCH_PATH);
    //Add any aliases for namespaces we wish to reduce
    $dispatch->addAlias("remarkd", '../');
    //Make this instance of dispatch, globally available
    Dispatch::bind($dispatch);
  }

  protected function _setupApplication()
  {
    //Send debug headers locally
    $this->getCubex()->listen(
      ResponsePreSendHeadersEvent::class,
      function (ResponsePreSendHeadersEvent $e) {
        $r = $e->getResponse();
        if($r instanceof Response && $e->getContext()->isEnv(Context::ENV_LOCAL))
        {
          $r->enableDebugHeaders();
        }
      }
    );
  }
}
