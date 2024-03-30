<?php
namespace Zf\Ext\Act;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareAct implements RequestHandlerInterface
{
    /**
     * Current request info
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $_request = null;

    /**
     * Get current request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * set current request
     */
    public function setRequest(ServerRequestInterface $request)
    {
        return $this->_request = $request;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->_request = $request;
        // Get action from uri
        $action = 'getAction';
        /**
         * @var \Mezzio\Router\RouteResult
         */
        $routeResult = $request->getAttribute('Mezzio\Router\RouteResult');
        
        $uri = '';
        if ($routeResult) {
            $matchedRoute = $routeResult->getMatchedRoute();
            $uri = $matchedRoute->getPath();

            if ($matchedRoute) {
                $routeName = $routeResult->getMatchedRouteName();
                $opts = explode('.', $routeName);
                if (count($opts) > 1) {
                    $action = array_pop($opts). 'Action';
                }

                unset($matchedRoute);
            }
        }
        
        if (!method_exists($this, $action)) {
            return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        // if ($uri) $this->updateAppVersion($uri);
        $response = $this->{$action}($request);
        if (method_exists($response, 'getPayLoad')) {
            $payLoad = $response->getPayLoad();
            return $response->withPayLoad($payLoad);
        }

        return $response;
    }
}
?>