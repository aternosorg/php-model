<?php

namespace Aternos\Model\Driver\OpenSearch\Authentication;

use Psr\Http\Message\RequestInterface;

class BearerAuthentication implements OpenSearchAuthenticationInterface
{
    /**
     * @param string $token
     */
    public function __construct(
        protected string $token
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function applyTo(RequestInterface $request): RequestInterface
    {
        return $request->withHeader("Authorization", "Bearer " . $this->token);
    }
}
