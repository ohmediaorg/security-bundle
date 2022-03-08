<?php

namespace App\Provider;

use App\Entity\__PASCALCASE__;
use OHMedia\SecurityBundle\Provider\AbstractEntityProvider;

class __PASCALCASE__Provider extends AbstractEntityProvider
{
    public static function getHumanReadable(): string
    {
        // a word/phrase to describe your entity
        // in various flash messages
        return '__READABLE__';
    }

    public function getEntityClass(): string
    {
        return __PASCALCASE__::class;
    }

    public function getCustomActions(): array
    {
        $actions = [];

        return $actions;
    }
}
