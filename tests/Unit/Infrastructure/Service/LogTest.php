<?php

namespace App\Tests\Unit\Infrastructure\Service;

use App\Infrastructure\Service\Log;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset logger to null before each test
        Log::setLogger(null);
    }

    public function testNullLoggerDoesNotThrow(): void
    {
        // Should not throw any exception
        Log::error('test');
        Log::info('test');
        Log::debug('test');
        Log::warning('test');
        Log::critical('test');
        Log::alert('test');
        Log::emergency('test');
        Log::notice('test');
        Log::log('info', 'test');

        $this->assertTrue(true); // If we got here, no exception
    }

    public function testErrorDelegatesToLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('test message', ['key' => 'val']);

        Log::setLogger($logger);
        Log::error('test message', ['key' => 'val']);
    }

    public function testInfoDelegatesToLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with('hello', []);

        Log::setLogger($logger);
        Log::info('hello');
    }

    public function testDebugDelegatesToLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('debug');

        Log::setLogger($logger);
        Log::debug('msg');
    }

    public function testWarningDelegatesToLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        Log::setLogger($logger);
        Log::warning('msg');
    }

    public function testCriticalDelegatesToLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('critical');

        Log::setLogger($logger);
        Log::critical('msg');
    }

    public function testLogDelegatesToLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with('warning', 'msg', []);

        Log::setLogger($logger);
        Log::log('warning', 'msg');
    }

    public function testClassBaseNameExtractsFQCN(): void
    {
        $result = Log::classBaseName('App\\Infrastructure\\Service\\MyService::myMethod');
        $this->assertSame('MyService::myMethod', $result);
    }

    public function testClassBaseNameHandlesSimpleString(): void
    {
        $result = Log::classBaseName('myMethod');
        $this->assertSame('myMethod', $result);
    }

    public function testClassBaseNameHandlesSingleBackslash(): void
    {
        $result = Log::classBaseName('Namespace\\ClassName');
        $this->assertSame('ClassName', $result);
    }
}
