<?php

declare(strict_types=1);

namespace PHPMud\World\Domain\Character;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';
}
