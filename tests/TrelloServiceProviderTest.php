<?php

use LaravelTrello\Wrapper;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\ServiceProvider;
use LaravelTrello\TrelloServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return '/tmp/test-config/' . ltrim($path, '/');
    }
}

/**
 * Test double that records calls to mergeConfigFrom() and publishes()
 * so we don't have to load the real Laravel runtime (env(), etc.).
 */
class TestableTrelloServiceProvider extends TrelloServiceProvider
{
    /** @var array<string, string> */
    public array $merged = [];

    /** @var array<int, array{0: array<string, string>, 1: mixed}> */
    public array $publishCalls = [];

    protected function mergeConfigFrom($path, $key)
    {
        $this->merged[$key] = $path;
    }

    protected function publishes(array $paths, $groups = null)
    {
        $this->publishCalls[] = [$paths, $groups];
    }
}

class TrelloServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendLaravelServiceProvider(): void
    {
        $this->assertTrue(is_subclass_of(TrelloServiceProvider::class, ServiceProvider::class));
    }

    /**
     * @test
     */
    public function shouldRegisterTrelloSingletonAndMergeConfig(): void
    {
        $captured = null;

        $app = $this->createMock(Application::class);
        $app->expects($this->once())
            ->method('singleton')
            ->with('trello', $this->callback(function ($factory) use (&$captured) {
                $captured = $factory;

                return is_callable($factory);
            }));

        $provider = new TestableTrelloServiceProvider($app);
        $provider->register();

        $expectedConfigPath = realpath(__DIR__ . '/../config/trello.php');

        $this->assertArrayHasKey('trello', $provider->merged);
        $this->assertSame($expectedConfigPath, realpath($provider->merged['trello']));
        $this->assertNotNull($captured, 'singleton factory was not captured');

        $config = $this->createMock(ConfigRepository::class);
        $config->method('get')->willReturn('');

        $this->assertInstanceOf(Wrapper::class, $captured(['config' => $config]));
    }

    /**
     * @test
     */
    public function shouldRegisterPublishablesOnBoot(): void
    {
        $app = $this->createMock(Application::class);
        $provider = new TestableTrelloServiceProvider($app);

        $provider->boot();

        $this->assertCount(1, $provider->publishCalls);

        [$paths, $groups] = $provider->publishCalls[0];

        $this->assertNull($groups);

        $expectedSource = realpath(__DIR__ . '/../config/trello.php');
        $expectedTarget = config_path('trello.php');

        $this->assertCount(1, $paths);
        $this->assertSame($expectedSource, realpath(array_key_first($paths)));
        $this->assertSame($expectedTarget, reset($paths));
    }
}
