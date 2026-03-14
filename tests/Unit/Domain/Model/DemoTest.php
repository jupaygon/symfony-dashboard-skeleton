<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model;

use App\Domain\Model\Demo;
use PHPUnit\Framework\TestCase;

class DemoTest extends TestCase
{
    public function testCreateDemoWithName(): void
    {
        $demo = new Demo('Test');

        $this->assertSame('Test', $demo->getName());
        $this->assertNull($demo->getId());
    }

    public function testSetName(): void
    {
        $demo = new Demo('Original');
        $demo->setName('Updated');

        $this->assertSame('Updated', $demo->getName());
    }
}
