<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class Assert
{
    public static $isEnabled = true;

    public static $exceptions = [
        'default' => \InvalidArgumentException::class,
        'domain' => \DomainException::class,
    ];

    public static function isEnabled(?bool $flag = null): bool
    {
        if ($flag === null) {
            return static::$isEnabled;
        }

        static::$isEnabled = $flag;

        return $flag;
    }

    public static function setException(string $type, string $fqcn): void
    {
        static::$exceptions[$type] = $fqcn;
    }

    public static function setExceptions(array $exceptions): void
    {
        static::$exceptions = array_merge(
            static::$exceptions,
            $exceptions
        );
    }

    public static function that($value)
    {
        return new class ($value, static::class) {
            public $value;
            public $class;
            public $assert = true;
            public $all = false;

            public function __construct($value, $class)
            {
                $this->value = $value;
                $this->class = $class;
            }

            public function nullOr()
            {
                $this->assert = $this->value !== null;
                return $this;
            }

            public function all()
            {
                $this->all = true;
                return $this;
            }

            public function __call($method, $args)
            {
                if (strpos(strtolower($method), 'nullor') === 0) {
                    $this->nullOr();
                    $method = substr($method, 6);
                }

                if (strpos(strtolower($method), 'all') === 0) {
                    $this->all();
                    $method = substr($method, 3);
                }

                if ($this->all) {
                    $method = "all$method";
                }

                $this->assert && [$this->class, $method]($this->value, ...$args);
                return $this;
            }
        };
    }

    public static function thatAll(array $values)
    {
        return static::that($values)->all();
    }

    public static function thatNullOr($value)
    {
        return static::that($value)->nullOr();
    }

    public static function thatAllNullOr(array $values)
    {
        return static::that($values)->all()->nullOr();
    }

    public static function __callStatic($method, $args)
    {
        if (strpos(strtolower($method), 'nullor') === 0) {
            return static::nullOr(substr($method, 6), $args);
        }

        if (strpos(strtolower($method), 'all') === 0) {
            return static::all(substr($method, 3), array_shift($args), $args);
        }

        throw new \BadMethodCallException('Unknown assertion "' . $method . '" called.');
    }

    protected static function nullOr($method, $args)
    {
        if ($args[0] === null) {
            return true;
        }

        return static::$method(...$args);
    }

    protected static function all(string $method, array $set, array $args)
    {
        foreach ($set as $value) {
            static::$method($value, ...$args);
        }

        return true;
    }

    public static function string($value, $exceptionType = 'default'): bool
    {
        if (!static::$isEnabled || is_string($value)) {
            return true;
        }

        static::throwException(
            'Value "%s" is not a string.',
            [static::valueToString($value)],
            102,
            $exceptionType
        );
    }

    public static function oneOf($value, array $choices, $exceptionType = 'domain'): bool
    {
        if (!self::$isEnabled || in_array($value, $choices)) {
            return true;
        }

        static::throwException(
            'The value "%s" is not one of %s.',
            [static::valueToString($value), join(', ', $choices)],
            100,
            $exceptionType
        );
    }

    protected static function throwException($message, array $replacements, $code, $type = "default")
    {
        $message = vsprintf($message, $replacements);
        $exception = self::$exceptions[$type] ?? \InvalidArgumentException::class;

        throw new $exception($message, $code);
    }

    protected static function valueToString($value)
    {
        switch ($type = gettype($value)) {
            case 'NULL':
            case 'unknown type':
                $value = null;
                break;

            case 'array':
                $value = '[' . count($value) . ']';
                break;

            case 'object':
                $value = get_class($value);
                break;

            case 'resource':
                $value = get_resource_type($value);
                break;

            case 'boolean':
                $value = $value ? 'true' : 'false';
                break;

            case 'double':
                $type = 'float';
                break;

            default:
                break;
        }

        return sprintf("<%s>%s", $type, $value ? " $value" : "");
    }
}
