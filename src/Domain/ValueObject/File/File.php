<?php

namespace Dranzd\Common\EventSourcing\Domain\ValueObject\File;

use Dranzd\Common\EventSourcing\Domain\ValueObject\ArrayValueObjectTranslatorCapable;
use Dranzd\Common\EventSourcing\Domain\ValueObject\CompareSameValueTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Label;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Name;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Path;
use Dranzd\Common\EventSourcing\Domain\ValueObject\Native\String\Literal as Type;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ToStringUsingObjectHashTrait;
use Dranzd\Common\EventSourcing\Domain\ValueObject\ValueObject;
use Dranzd\Common\EventSourcing\Util\ArrayUtil;

class File implements ValueObject, ArrayValueObjectTranslatorCapable
{
    use CompareSameValueTrait, ToStringUsingObjectHashTrait;

    /**
     * @var Label
     */
    private $label;

    /**
     * @var Name
     */
    private $name;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var Path
     */
    private $path;

    private function __construct(
        Label $label,
        Name $name,
        Type $type,
        Path $path
    ) {
        $this->label = $label;
        $this->name = $name;
        $this->type = $type;
        $this->path = $path;
    }

    final public static function notProvided(): File
    {
        return self::fromArray(['', '', '', '']);
    }

    final public static function fromArray(array $array): File
    {
        if (isset($array[0])) {
            return \call_user_func_array(['static', 'fromNative'], $array);
        }

        \extract(ArrayUtil::keyToCamelCase($array));

        return static::fromNative(
            $label,
            $name,
            $type,
            $path
        );
    }

    final public function sameValueAs(ValueObject $object): bool
    {
        return $this->checkSameValueAs($object);
    }

    final public function toArray(): array
    {
        return [
            'label' => $this->label->toNative(),
            'name' => $this->name->toNative(),
            'type' => $this->type->toNative(),
            'path' => $this->path->toNative(),
        ];
    }

    private static function fromNative(): File
    {
        $label = (string) \func_get_arg(0);
        $name = (string) \func_get_arg(1);
        $type = (string) \func_get_arg(2);
        $path = (string) \func_get_arg(3);

        $label = Label::fromNative($label);
        $name = Name::fromNative($name);
        $type = Type::fromNative($type);
        $path = Label::fromNative($path);

        return new self(
            $label,
            $name,
            $type,
            $path
        );
    }
}
