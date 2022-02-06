<?php

use Semaio\TrelloApi\Manager;
use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Config\Repository;

class WrapperTest extends TestCase
{
    /**
     * @test
     */
    public function shouldHaveToPassRepoToWrapperConstructor(): void
    {
        $repository = $this->getRepositoryMock();
        $wrapper = new \LaravelTrello\Wrapper($repository);
        $this->assertInstanceOf(Repository::class, $wrapper->config);
    }

    /**
     * @test
     */
    public function shouldGetManagerFromWrapper(): void
    {
        $repository = $this->getRepositoryMock();
        $wrapper = new \LaravelTrello\Wrapper($repository);
        $this->assertInstanceOf(Manager::class, $wrapper->manager());
    }

    /**
     * @test
     *
     */
    public function shouldNotGetMagicApiInstance(): void
    {
        $this->expectException(TypeError::class);
        $repository = $this->getRepositoryMock();
        $wrapper = new \LaravelTrello\Wrapper($repository);
        $wrapper->doNotExist();
    }

    private function getRepositoryMock()
    {
        $mock = $this->getMockBuilder(Repository::class)->getMock();

        $mock->method('get')->willReturn('');

        return $mock;
    }
}
