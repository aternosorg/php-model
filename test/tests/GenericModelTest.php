<?php

namespace Aternos\Model\Test\Tests;

use Aternos\Model\GenericModel;
use PHPUnit\Framework\TestCase;

class GenericModelTest extends TestCase
{
    public function testRegistry(): void
    {
        $this->assertTrue(GenericModel::isRegistryEnabled());
        GenericModel::disableRegistry();
        $this->assertFalse(GenericModel::isRegistryEnabled());
        GenericModel::enableRegistry();
        $this->assertTrue(GenericModel::isRegistryEnabled());
    }
}
