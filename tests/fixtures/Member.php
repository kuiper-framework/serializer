<?php

declare(strict_types=1);

namespace kuiper\serializer\fixtures;

class Member
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
