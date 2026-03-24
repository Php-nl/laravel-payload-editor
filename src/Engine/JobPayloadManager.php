<?php

namespace PhpNl\LaravelPayloadEditor\Engine;

use Illuminate\Contracts\Database\ModelIdentifier;
use ReflectionClass;

class JobPayloadManager
{
    /**
     * Unserialize the job command from the JSON payload.
     *
     * @throws \RuntimeException
     */
    public function unserializeCommand(string $jsonPayload): object
    {
        $payload = json_decode($jsonPayload, true);

        if (! isset($payload['data']['command'])) {
            throw new \RuntimeException('Job payload does not contain a serialized command.');
        }

        return unserialize($payload['data']['command']);
    }

    /**
     * Parse the job object into an editable schema for the UI.
     */
    public function getEditableSchema(object $command): array
    {
        $schema = [];
        $reflection = new ReflectionClass($command);

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            // Skip internal queue/framework properties
            if (in_array($name, ['job', 'connection', 'queue', 'chainConnection', 'chainQueue', 'chainCatchCallbacks', 'delay', 'middleware', 'chained', 'afterCommit'])) {
                continue;
            }

            $property->setAccessible(true);
            $value = $property->isInitialized($command) ? $property->getValue($command) : null;
            $type = $property->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';

            // Handle standard ModelIdentifier for Eloquent models
            // Allowing users to adjust the ID if the wrong record was passed
            if (is_object($value) && $value instanceof ModelIdentifier) {
                if (is_array($value->id)) {
                    $schema[$name] = [
                        'type' => 'ModelIdentifier (Array)',
                        'value' => '<Array of IDs>',
                        'editable' => false,
                    ];
                } else {
                    $schema[$name] = [
                        'type' => 'ModelIdentifier',
                        'class' => $value->class,
                        'value' => $value->id,
                        'editable' => true,
                    ];
                }

                continue;
            }

            // Handle native Enums
            if ($typeName !== 'mixed' && is_subclass_of($typeName, \UnitEnum::class)) {
                $schema[$name] = [
                    'type' => $typeName,
                    'value' => $value instanceof \BackedEnum ? $value->value : ($value instanceof \UnitEnum ? $value->name : null),
                    'editable' => true,
                ];

                continue;
            }

            // Uninitialized objects (other than enums/identifiers) cannot be safely populated via string input
            if (! $property->isInitialized($command) && $typeName !== 'mixed' && class_exists($typeName)) {
                $schema[$name] = [
                    'type' => $typeName,
                    'value' => '<Uninitialized Object>',
                    'editable' => false,
                ];

                continue;
            }

            // For now, only scalar types and nulls are trivially editable
            $isEditable = is_scalar($value) || is_null($value);

            $schema[$name] = [
                'type' => $typeName,
                'value' => $isEditable ? $value : '<'.gettype($value).'>',
                'editable' => $isEditable,
            ];
        }

        return $schema;
    }

    /**
     * Modify the unserialized command with new values from the UI,
     * maintaining strict types using reflection.
     *
     * @return string Serialized command
     */
    public function modifyAndSerialize(object $command, array $updates): string
    {
        $reflection = new ReflectionClass($command);

        foreach ($updates as $propertyName => $newValue) {
            if (! $reflection->hasProperty($propertyName)) {
                continue;
            }

            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);

            $currentValue = $property->isInitialized($command) ? $property->getValue($command) : null;
            $type = $property->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'string';

            // Handle Eloquent ModelIdentifier
            if (is_object($currentValue) && $currentValue instanceof ModelIdentifier) {
                if (! is_array($currentValue->id)) {
                    $currentValue->id = $this->castValue($newValue, gettype($currentValue->id));
                    $property->setValue($command, $currentValue);
                }

                continue;
            }

            // Handle native Enums
            if ($typeName !== 'string' && is_subclass_of($typeName, \UnitEnum::class)) {
                if (is_subclass_of($typeName, \BackedEnum::class)) {
                    // Backed enum casting
                    $castedValue = $this->castValue($newValue, (new ReflectionClass($typeName))->getBackingType()?->getName() ?? 'string');
                    $enumInstance = $typeName::tryFrom($castedValue);
                    if ($enumInstance) {
                        $property->setValue($command, $enumInstance);
                    }
                } else {
                    // Unit enum (pure name)
                    foreach ($typeName::cases() as $case) {
                        if ($case->name === $newValue) {
                            $property->setValue($command, $case);
                            break;
                        }
                    }
                }

                continue;
            }

            // Skip uninitialized complex objects
            if (! $property->isInitialized($command) && class_exists($typeName)) {
                continue;
            }

            // Skip modification if this property wasn't deemed editable
            if (! is_scalar($currentValue) && ! is_null($currentValue)) {
                continue;
            }

            $property->setValue($command, $this->castValue($newValue, $typeName));
        }

        return serialize($command);
    }

    /**
     * Rebuild the full framework JSON payload with the newly serialized command string.
     */
    public function rebuildPayload(string $jsonPayload, string $newCommandString): string
    {
        $payload = json_decode($jsonPayload, true);
        $payload['data']['command'] = $newCommandString;

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Cast string inputs from UI to proper PHP types to avoid strict type errors in jobs.
     */
    protected function castValue(mixed $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return null; // Assuming property is nullable if empty form submission
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => (string) $value,
        };
    }
}
