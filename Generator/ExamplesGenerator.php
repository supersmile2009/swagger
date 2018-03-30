<?php

namespace Draw\Swagger\Generator;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Draw\Swagger\Extraction\Extractor\TypeSchemaExtractor;
use Draw\Swagger\OpenApiGenerator;
use Draw\Swagger\Schema\OpenApi;
use Draw\Swagger\Schema\Reference;
use Draw\Swagger\Schema\Schema;

class ExamplesGenerator
{
    /**
     * @var TypeSchemaExtractor
     */
    private $typeSchemaExtractor;
    /**
     * @var ClassMetadataFactory
     */
    private $metadataFactory;

    public function __construct(TypeSchemaExtractor $typeSchemaExtractor, ClassMetadataFactory $metadataFactory)
    {
        $this->typeSchemaExtractor = $typeSchemaExtractor;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param OpenApi $openApi
     *
     * @throws \Draw\Swagger\Extraction\ExtractionImpossibleException
     */
    public function generateExamples(OpenApi $openApi)
    {
        foreach ($openApi->paths as $path) {
            foreach ($path->getOperations() as $operation) {
                $deserializationGroups = $operation->getCustomProperty('deserializationGroups');
                if (null !== $requestBody = $operation->requestBody) {

                    foreach ($requestBody->content as $mediaType) {
                        $schema = $mediaType->schema;
                        $example = $this->getExampleOfType($schema, $deserializationGroups, $openApi);

                        $mediaType->example = $example;
                    }
                }
                $serializationGroups = $operation->getCustomProperty('serializationGroups');

                foreach ($operation->responses as $response) {
                    foreach ($response->content as $mediaType) {
                        $schema = $mediaType->schema;
                        $example = $this->getExampleOfType($schema, $serializationGroups, $openApi);
                        $mediaType->example = $example;
                    }
                }

            }
        }
    }

    /**
     * @param Schema|Reference $property
     * @param string[] $serializerGroups
     * @param OpenApi $openApi
     *
     * @param bool $recursive
     *
     * @return mixed
     *
     * @throws \Draw\Swagger\Extraction\ExtractionImpossibleException
     */
    public function getExampleOfType($property, $serializerGroups = null, $openApi, $recursive = true)
    {
        $originalProperty = $property;
        /** @var Schema $property */
        $property = OpenApiGenerator::resolveReference($property, $openApi);

        if (
            $serializerGroups === null
            || !$this->shouldSkipProperty($property->getCustomProperty('serializerGroups'), $serializerGroups)
        ) {
            switch ($property->type) {
                case 'integer':
                    return 1;
                case 'number':
                    return 1.2;
                case 'boolean':
                    return true;
                case 'string':
                    return 'string';
                case 'object':
                    $result = [];
                    foreach ($property->properties as $name => $subProperty) {
                        $subPropertyRecursion = true;
                        $subSerializerGroups = $subProperty->getCustomProperty('serializerGroups') !== null && $serializerGroups !== null
                            ? \array_intersect($serializerGroups, $subProperty->getCustomProperty('serializerGroups'))
                            : null;
                        if ($subProperty->allOf !== null) {
                            $subProperty = $subProperty->allOf[0];
                        }

                        // Prevent endless recursion on self-referencing entities.
                        if (
                            $originalProperty instanceof Reference
                            && $subProperty instanceof Reference
                            && $originalProperty->ref === $subProperty->ref
                        ) {
                            // If we're dealing with property, which references same entity
                            // and this is already a recursion call (further recursion prohibited)
                            // skip this property.
                            if ($recursive === false) {
                                continue;
                            }

                            // Don't recurse on sub-property type detection.
                            $subPropertyRecursion = false;
                        }
                        if (null !== $value = $this->getExampleOfType($subProperty, $subSerializerGroups, $openApi, $subPropertyRecursion)) {
                            $result[$name] = $value;
                        }
                    }
                    return empty($result) ? null : $result;
            }
        }

        return null;
    }



    /**
     * {@inheritDoc}
     */
    public function shouldSkipProperty($propertyGroups, $serializerGroups): bool
    {
        if (!$propertyGroups) {
            return !\in_array('Default', $serializerGroups, true);
        }

        return $this->shouldSkipUsingGroups($propertyGroups, $serializerGroups);
    }

    private function shouldSkipUsingGroups($propertyGroups, $serializerGroups): bool
    {
        foreach ($propertyGroups as $group) {
            if (\in_array($group, $serializerGroups, true)) {
                return false;
            }
        }

        return true;
    }

}
