<?php

use LaravelTrello\Facade;
use PHPUnit\Framework\TestCase;

class FacadeTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendLaravelFacade(): void
    {
        $this->assertTrue(is_subclass_of(Facade::class, \Illuminate\Support\Facades\Facade::class));
    }

    /**
     * @test
     */
    public function shouldExposeTrelloAccessor(): void
    {
        $reflection = new ReflectionMethod(Facade::class, 'getFacadeAccessor');
        $reflection->setAccessible(true);

        $this->assertSame('trello', $reflection->invoke(null));
    }
}
