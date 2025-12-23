<?php

namespace App\Tests\Service;

use App\Service\CacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheServiceTest extends TestCase
{
    public function testRememberCacheMissSetsTtlAndExecutesCallback(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $item = $this->createMock(ItemInterface::class);

        $ttl = 123;
        $expected = ['fresh' => true];
        $called = false;

        $item->expects($this->once())
            ->method('expiresAfter')
            ->with($ttl);
        $cache->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('key'),
                $this->callback(function ($closure) {
                    $this->assertIsCallable($closure);
                    return true;
                })
            )
            ->willReturnCallback(function (string $key, callable $closure) use ($item, &$called) {
                $called = true;
                return $closure($item);
            });

        $service = new CacheService($cache);

        $result = $service->remember('key', fn() => $expected, $ttl);

        $this->assertTrue($called, 'Callback should be executed on cache miss');
        $this->assertSame($expected, $result);
    }

    public function testRememberCacheHitSkipsCallbackAndReturnsCachedValue(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cached = ['cached' => true];

        $callbackCalled = false;
        $callback = function () use (&$callbackCalled) {
            $callbackCalled = true;
            return ['should_not_be_returned'];
        };
        $cache->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('exists'),
                $this->callback('is_callable')
            )
            ->willReturn($cached);

        $service = new CacheService($cache);

        $result = $service->remember('exists', $callback, 60);

        $this->assertFalse($callbackCalled, 'Callback must not be executed on cache hit');
        $this->assertSame($cached, $result);
    }

    public function testForgetDelegatesToDelete(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('delete')
            ->with('to-delete')
            ->willReturn(true);

        $service = new CacheService($cache);

        $this->assertTrue($service->forget('to-delete'));
    }
}
