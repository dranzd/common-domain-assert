<?php

namespace Dranzd\Common\EventSourcing\Util;

final class ObjectUtil
{
    private static $instance;

    private function __construct()
    {
        // nothing here
    }

    /**
     * @static
     * @return self
     */
    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @see self::valueOfProperty
     */
    public function getPropertyValue()
    {
        return \call_user_func_array([$this, 'valueOfProperty'], \func_get_args());
    }

    /**
     * @param  object               $object
     * @param  string               $property
     * @param  ReflectionClass|null $relfection
     * @return mixed
     */
    public function valueOfProperty($object, $property, $reflection = null)
    {
        if (null === $reflection) {
            $reflection = new \ReflectionClass(\get_class($object));
        }

        $property = $reflection->getProperty($property);

        $value = null;

        if ($property->isPrivate() || $property->isProtected()) {
            $property->setAccessible(true);
            $value = $property->getValue($object);
            $property->setAccessible(false);
        } else {
            $value = $property->getValue($object);
        }

        return $value;
    }

    /**
     * @param  object               $object
     * @param  string               $property
     * @param  mixed                $value
     * @param  ReflectionClass|null $relfection
     * @return null
     */
    public function setPropertyValue($object, $property, $value, $reflection = null)
    {
        if (null === $reflection) {
            $reflection = new \ReflectionClass(\get_class($object));
        }

        /**
 * @var \ReflectionClass $reflection
*/

        if ($reflection->hasProperty($property)) {
            $property = $reflection->getProperty($property);
        } else {
            $parentReflection = new \ReflectionClass(\get_parent_class($object));
            if ($parentReflection->hasProperty($property)) {
                $property = $parentReflection->getProperty($property);
            }
        }

        $isAccessible = !$property->isPrivate() && !$property->isProtected();

        if (!$isAccessible) {
            $property->setAccessible(true);
        }

        $property->setValue($object, $value);

        if (!$isAccessible) {
            $property->setAccessible(false);
        }
    }

    /**
     * @param  string               $className
     * @param  ReflectionClass|null $reflection
     * @return Object|null
     */
    public function newInstanceWithoutConstructor($className, $reflection = null)
    {
        if (!class_exists($className)) {
            return null;
        }

        if (null === $reflection) {
            $reflection = new \ReflectionClass($className);
        }

        return $reflection->newInstanceWithoutConstructor();
    }

    // public function extractByClassMethods($object, $recursive = false)
    // {
    //     $extractor = new \Laminas\Hydrator\ClassMethods();
    //     $array     = $extractor->extract($object);

    //     if (!$recursive) {
    //         return $array;
    //     }

    //     foreach ($array as $k => $v) {
    //         if (!is_object($v)) {
    //             continue;
    //         }

    //         $array[$k] = $this->extractByClassMethods($v, $recursive);
    //     }

    //     return $array;
    // }
}
