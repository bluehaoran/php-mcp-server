<?php

declare(strict_types=1);

namespace PhpMcp\Server\Support;

use InvalidArgumentException;
use ReflectionMethod;
use ReflectionException;

/**
 * Utility class to validate and resolve MCP element handlers.
 */
class HandlerResolver
{
    /**
     * Validates and resolves a handler to its class name, method name, and ReflectionMethod instance.
     *
     * A handler can be:
     * - An array: [ClassName::class, 'methodName']
     * - A string: InvokableClassName::class (which will resolve to its '__invoke' method)
     *
     * @param array|string $handler The handler to resolve.
     * @return array{className: class-string, methodName: string, reflectionMethod: ReflectionMethod}
     *               An associative array containing 'className', 'methodName', and 'reflectionMethod'.
     *
     * @throws InvalidArgumentException If the handler format is invalid, the class/method doesn't exist,
     *                                  or the method is unsuitable (e.g., static, private, abstract).
     */
    public static function resolve(array|string $handler): array
    {
        $className = null;
        $methodName = null;

        if (is_array($handler)) {
            if (count($handler) !== 2 || !isset($handler[0]) || !isset($handler[1]) || !is_string($handler[0]) || !is_string($handler[1])) {
                throw new InvalidArgumentException('Invalid array handler format. Expected [ClassName::class, \'methodName\'].');
            }
            [$className, $methodName] = $handler;
            if (!class_exists($className)) {
                throw new InvalidArgumentException("Handler class '{$className}' not found for array handler.");
            }
            if (!method_exists($className, $methodName)) {
                throw new InvalidArgumentException("Handler method '{$methodName}' not found in class '{$className}' for array handler.");
            }
        } elseif (is_string($handler) && class_exists($handler)) {
            $className = $handler;
            $methodName = '__invoke';
            if (!method_exists($className, $methodName)) {
                throw new InvalidArgumentException("Invokable handler class '{$className}' must have a public '__invoke' method.");
            }
        } else {
            throw new InvalidArgumentException('Invalid handler format. Expected [ClassName::class, \'methodName\'] or InvokableClassName::class string.');
        }

        try {
            $reflectionMethod = new ReflectionMethod($className, $methodName);

            if ($reflectionMethod->isStatic()) {
                throw new InvalidArgumentException("Handler method '{$className}::{$methodName}' cannot be static.");
            }
            if (!$reflectionMethod->isPublic()) {
                throw new InvalidArgumentException("Handler method '{$className}::{$methodName}' must be public.");
            }
            if ($reflectionMethod->isAbstract()) {
                throw new InvalidArgumentException("Handler method '{$className}::{$methodName}' cannot be abstract.");
            }
            if ($reflectionMethod->isConstructor() || $reflectionMethod->isDestructor()) {
                throw new InvalidArgumentException("Handler method '{$className}::{$methodName}' cannot be a constructor or destructor.");
            }

            return [
                'className' => $className,
                'methodName' => $methodName,
                'reflectionMethod' => $reflectionMethod,
            ];
        } catch (ReflectionException $e) {
            // This typically occurs if class_exists passed but ReflectionMethod still fails (rare)
            throw new InvalidArgumentException("Reflection error for handler '{$className}::{$methodName}': {$e->getMessage()}", 0, $e);
        }
    }
}
