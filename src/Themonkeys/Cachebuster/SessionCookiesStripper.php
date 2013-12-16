<?php namespace Themonkeys\Cachebuster;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class NoCookiesResponseHeaderBag extends ResponseHeaderBag {

    public function __construct(ResponseHeaderBag $copy)
    {
        $this->cacheControl = $copy->cacheControl;
        $this->headers = $copy->headers;
        $this->computedCacheControl = $copy->computedCacheControl;
        $this->headerNames = $copy->headerNames;
        // don't copy cookies

    }

    public function setCookie(Cookie $cookie)
    {
        // do nothing
    }

}

class SessionCookiesStripper implements HttpKernelInterface {

    /**
     * The wrapped kernel implementation.
     *
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $app;

    /**
     * The wrapped filter
     *
     * @var StripSessionCookiesFilter
     */
    private $filter;

    /**
     * Create a new SessionCookiesStripper instance.
     *
     * @param  \Symfony\Component\HttpKernel\HttpKernelInterface  $app
     * @param  StripSessionCookiesFilter $filter
     * @return void
     */
    public function __construct(HttpKernelInterface $app, StripSessionCookiesFilter $filter)
    {
        $this->app = $app;
        $this->filter = $filter;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request A Request instance
     * @param integer $type The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $response = $this->app->handle($request, $type, $catch);
        if ($this->filter->matches($request)) {
            $this->filter->filter($request, null);
            // wrap the response so it refuses any extra cookies
            $response->headers = new NoCookiesResponseHeaderBag($response->headers);
        }
        return $response;
    }
}