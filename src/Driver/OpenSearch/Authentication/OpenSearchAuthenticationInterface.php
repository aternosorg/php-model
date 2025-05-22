<?php

namespace Aternos\Model\Driver\OpenSearch\Authentication;

use Psr\Http\Message\RequestInterface;

interface OpenSearchAuthenticationInterface
{
    /**
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function applyTo(RequestInterface $request): RequestInterface;
}
