<?php

/*
 * OK cross-version.
 */
trait FooTrait
{
    abstract public function bah($a);
    abstract protected function bar($a);
    private function baz($a) {}
}

/*
 * Outside scope.
 */
abstract class Foo
{
    abstract private function bar($a); // Forbidden, but not our concern.
}

interface FooInterface
{
    abstract private function bar($a); // Forbidden, but not our concern.
}

/*
 * PHP 8.0: abstract private methods in traits.
 */
trait FooTrait
{
    abstract private function bar($a);
}
