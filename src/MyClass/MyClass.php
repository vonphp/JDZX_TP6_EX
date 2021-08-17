<?php
namespace A\MyClass;

use think\Event;

class MyClass
{
    public static function postPackageInstall(Event $event)
    {
        var_dump($event);
        // do stuff
    }
}