<?php

namespace BS\Tests\Traits;

use BS\Tests\AbstractTestCase;
use BS\Tests\MockedAuthService;

trait AuthenticationTrait
{
    /**
     * @param bool $forceAuthenticate
     *
     * @return $this
     */
    protected function authenticate($forceAuthenticate = false)
    {
        /**
         * @var AbstractTestCase $this
         */
        if (!$this->getApplicationServiceLocator()->has('AuthService') || $forceAuthenticate) {
            $this->getApplicationServiceLocator()->setAllowOverride(true);
            $this->getApplicationServiceLocator()->setService('AuthService', new MockedAuthService());
        }

        return $this;
    }
}
