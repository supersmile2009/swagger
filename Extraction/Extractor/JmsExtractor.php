<?php

namespace Draw\Swagger\Extraction\Extractor;

use Draw\Swagger\Extraction\ExtractionContext;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Schema;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Metadata\PropertyMetadata;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

class JmsExtractor implements ExtractorInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $factory;

    /**
     * @var PropertyNamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var TypeSchemaExtractor
     */
    private $typeSchemaExtractor;

    /**
     * Constructor, requires JMS Metadata factory
     */
    public function __construct(
        MetadataFactoryInterface $factory,
        PropertyNamingStrategyInterface $namingStrategy,
        TypeSchemaExtractor $typeSchemaExtractor
    ) {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->typeSchemaExtractor = $typeSchemaExtractor;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionClass) {
            return false;
        }

        if (!$type instanceof Schema) {
            return false;
        }

        return !is_null($this->factory->getMetadataForClass($source->getName()));
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionClass $reflectionClass
     * @param Schema $schema
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($reflectionClass, $schema, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($reflectionClass, $schema, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $meta = $this->factory->getMetadataForClass($reflectionClass->getName());

        $exclusionStrategies = array();


        $subContext = $extractionContext->createSubContext();

        if ($schema->serializerGroups !== null) {
            $exclusionStrategies[] = new GroupsExclusionStrategy($schema->serializerGroups);
        }
        // If this is child class with discriminator map, store information about parent class alias
        // and extract parent class
        if (isset($meta->discriminatorBaseClass)
            && $meta->discriminatorBaseClass !== $reflectionClass->getName()
        ) {
            $schema->parentAlias = $this->typeSchemaExtractor->getAliasFor($reflectionClass->getParentClass()->getName());
            $this->extractTypeSchema($reflectionClass->getParentClass()->getName(), $subContext);
        }

        foreach ($meta->propertyMetadata as $property => $item) {
            if ($this->shouldSkipProperty($exclusionStrategies, $item)) {
                continue;
            }
            // If there is a base class and current property belongs to it (is inherited), don't extract this properties
            if (isset($meta->discriminatorBaseClass)
                && $meta->discriminatorBaseClass !== $reflectionClass->getName()
                && $meta->discriminatorBaseClass === $item->class
            ) {
                continue;
            }

            if ($type = $this->getNestedTypeInArray($item) === 'stdClass') {
                $propertySchema = new Schema();
                $propertySchema->type = 'stdClass';
            } elseif ($type = $this->getNestedTypeInArray($item)) {
                $propertySchema = new Schema();
                $propertySchema->type = 'array';
                $propertySchema->items = $this->extractTypeSchema($type, $subContext);
            } else {
                $propertySchema = $this->extractTypeSchema($item->type['name'], $subContext);
            }

            if ($item->readOnly) {
                $propertySchema->readOnly = true;
            }

            $propertySchema->serializerGroups = $item->groups;

            /** @var \ReflectionProperty $reflectionProperty */
            $reflectionProperty = $item->reflection;
            // Reflecion can be null when extracting VirtualProperty
            if ($reflectionProperty !== null) {
                $docComment = $reflectionProperty->getDocComment();
                // Can be false if there is no doc block
                if ($docComment !== false) {
                    $factory = DocBlockFactory::createInstance();
                    $docBlock = $factory->create($reflectionProperty->getDocComment());
                    $deprecatedTags = $docBlock->getTagsByName('deprecated');
                    if (empty($deprecatedTags) === false) {
                        $propertySchema->deprecated = true;
                        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Deprecated $deprecatedTag */
                        foreach ($deprecatedTags as $deprecatedTag) {
                            $propertySchema->deprecationDescription .= $deprecatedTag->getDescription();
                        }
                    }
                }
            }


            $name = $this->namingStrategy->translateName($item);
            $schema->properties[$name] = $propertySchema;

            // We can't get description for disctriminator field, it doesn't exist as normal property
            if ($property !== $meta->discriminatorFieldName) {
                try {
                    $propertySchema->description = $this->getDescription($item);
                } catch (\InvalidArgumentException $e) {
                    // Property has no description. Nothing critical.
                }
            }

        }
    }

    private function extractTypeSchema($type, ExtractionContext $extractionContext)
    {
        $extractionContext->getSwagger()->extract($type, $schema = new Schema(), $extractionContext);

        return $schema;
    }

    /**
     * Check the various ways JMS describes values in arrays, and
     * get the value type in the array
     *
     * @param  PropertyMetadata $item
     * @return string|null
     */
    private function getNestedTypeInArray(PropertyMetadata $item)
    {
        if (isset($item->type['name']) && in_array($item->type['name'], array('array', 'ArrayCollection'))) {
            if (isset($item->type['params'][1]['name'])) {
                // E.g. array<string, MyNamespaceMyObject>
                // All assoc arrays in JS are JSONs, not arrays. stdClass corresponds to JSON.
                return 'stdClass';
            }
            if (isset($item->type['params'][0]['name'])) {
                // E.g. array<MyNamespaceMyObject>
                return $item->type['params'][0]['name'];
            }
        }

        return null;
    }

    /**
     * @param PropertyMetadata $item
     * @return string
     */
    private function getDescription(PropertyMetadata $item)
    {
        $ref = new \ReflectionClass($item->class);
        $factory = DocBlockFactory::createInstance();
        if ($item instanceof VirtualPropertyMetadata) {
            try {
                $docBlock = $factory->create($ref->getMethod($item->getter)->getDocComment());
            } catch (\ReflectionException $e) {
                return '';
            }
        } else {
            $docBlock = $factory->create($ref->getProperty($item->name)->getDocComment());
        }

        return $docBlock->getSummary();
    }

    /**
     * @param \JMS\Serializer\Exclusion\ExclusionStrategyInterface[] $exclusionStrategies
     * @param $item
     * @return bool
     */
    private function shouldSkipProperty($exclusionStrategies, $item)
    {
        foreach ($exclusionStrategies as $strategy) {
            if (true === $strategy->shouldSkipProperty($item, SerializationContext::create())) {
                return true;
            }
        }

        return false;
    }
}
