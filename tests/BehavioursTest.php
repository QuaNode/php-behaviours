<?php

/**
 * Created by PhpStorm.
 * User: Fareez
 * Date: 2/10/17
 * Time: 5:03 PM
 */

use PHPUnit\Framework\TestCase;

class BehavioursTest extends TestCase
{
    public function testBehaviours(){
        $stub = $this->createMock(Behaviours::class);
        $stub->method('instance')
            ->will($this->returnSelf());
        $this->assertSame($stub,$stub->instance());
    }
}
