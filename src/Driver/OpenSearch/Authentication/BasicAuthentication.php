<?php

namespace Aternos\Model\Driver\OpenSearch\Authentication;

use Psr\Http\Message\RequestInterface;

class BasicAuthentication implements OpenSearchAuthenticationInterface
{
    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(
        protected string $username,
        protected string $password
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function applyTo(RequestInterface $request): RequestInterface
    {
        return $request->withHeader("Authorization", "Basic " . base64_encode($this->username . ":" . $this->password));
    }
}
