<?php

namespace TT\Installer;

class Installer
{
    public static function postInstall(\Composer\Script\Event $event): void
    {
        print_r(get_class_methods($event->getComposer()->getPackage()));
    }
}
