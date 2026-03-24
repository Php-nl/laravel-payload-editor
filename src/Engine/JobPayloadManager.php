<?php

namespace PhpNl\LaravelPayloadEditor\Engine;

use Illuminate\Contracts\Database\ModelIdentifier;
use ReflectionClass;

class JobPayloadManager
{
    /**
     * Unserialize the job command from the JSON payload.
     */
    public function unserializeCommand(string $jsonPayload): object
    {
        $payload = json_decode($jsonPayload, true);

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
                $schema[$name] = [
                    'type' => 'ModelIdentifier',
                    'class' => $value->class,
                    'value' => $value->id,
                    'editable' => true,
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

            // Handle Eloquent ModelIdentifier
            if (is_object($currentValue) && $currentValue instanceof ModelIdentifier) {
                $currentValue->id = $this->castValue($newValue, gettype($currentValue->id));
                $property->setValue($command, $currentValue);

                continue;
            }

            // Skip modification if this property wasn't deemed editable
            if (! is_scalar($currentValue) && ! is_null($currentValue)) {
                continue;
            }

            $type = $property->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : 'string';

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
