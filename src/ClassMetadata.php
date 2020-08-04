<?php

declare(strict_types=1);

namespace kuiper\serializer;

class ClassMetadata
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var array
     */
    private $getters = [];

    /**
     * @var array
     */
    private $setters = [];

    /**
     * ClassMetadata constructor.
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function addSetter(Field $field)
    {
        $this->setters[$field->getName()] = $field;

        return $this;
    }

    public function addGetter(Field $field)
    {
        $this->getters[$field->getName()] = $field;

        return $this;
    }

    /**
     * @return Field|null
     */
    public function getSetter(string $name)
    {
        return isset($this->setters[$name]) ? $this->setters[$name] : null;
    }

    /**
     * @return Field|null
     */
    public function getGetter(string $name)
    {
        return isset($this->getters[$name]) ? $this->getters[$name] : null;
    }

    /**
     * @return Field[]
     */
    public function getGetters()
    {
        return array_values($this->getters);
    }

    /**
     * @return Field[]
     */
    public function getSetters()
    {
        return array_values($this->setters);
    }
}
