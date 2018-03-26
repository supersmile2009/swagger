<?php

namespace Draw\Swagger\Extraction\Extractor;

use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Reference;
use Draw\Swagger\Schema\Schema;

class TypeSchemaExtractor implements ExtractorInterface
{
    /**
     * @var string[]
     */
    private $definitionAliases = [];

    private $definitionHashes = [];

    public function registerDefinitionAlias($definition, $alias)
    {
        $this->definitionAliases[$definition] = $alias;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param Schema $target
     * @param ExtractionContextInterface $extractionContext
     *
     * @return boolean
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$target instanceof Schema) {
            return false;
        }

        if (null === static::getPrimitiveType($source)) {
            return false;
        }

        return true;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param string $source
     * @param Schema|Reference $target
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws ExtractionImpossibleException
     * @throws \ReflectionException
     */
    public function extract($source, &$target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $primitiveType = static::getPrimitiveType($source);

        $target->type = $primitiveType['type'];

        if ($target->type === 'array') {
            $target->items = new Schema();
            if (isset($primitiveType['subType'])) {
                $extractionContext->getSwagger()->extract(
                    $primitiveType['subType'],
                    $target->items,
                    $extractionContext
                );
            }

            return;
        }

        if ($target->type === 'object') {
            $reflectionClass = new \ReflectionClass($primitiveType['class']);
            $name = $reflectionClass->getName();
            $rootSchema = $extractionContext->getRootSchema();

            if ($direction = $extractionContext->getParameter('direction')) {
                $context = $extractionContext->getParameter($direction.'-model-context');
            } else {
                $context = $extractionContext->getParameter('model-context');
            }

            if (array_key_exists($name, $this->definitionAliases)) {
                $name = $this->definitionAliases[$name];
            }

            $definitionName = str_replace('\\', '.', $name);

            if ($rootSchema->components->hasSchema($definitionName) === false) {
                $rootSchema->components->addSchema($definitionName, $modelSchema = new Schema());
                $modelSchema->type = 'object';
            }
            $modelSchema = $rootSchema->components->schemas[$definitionName];

            // Merge serializer groups
            $lastContext = $modelSchema->getCustomProperty('serializerGroups') ?: [];
            if (isset($context['serializer-groups'])) {
                $context['serializer-groups'] = array_unique(array_merge($lastContext, $context['serializer-groups']));
            } else {
                $context['serializer-groups'] = $lastContext;
            }
            $modelSchema->setCustomProperty('serializerGroups', $context['serializer-groups']);

            $hash = $this->getHash($context['serializer-groups']);

            // If serializer groups hash changed (new groups added)
            if ($this->hashExists($name, $hash) === false) {
                $extractionContext->getSwagger()->extract(
                    $reflectionClass,
                    $modelSchema,
                    $extractionContext
                );
            }

            $target = new Reference();

            $target->ref = $rootSchema->components->getSchemaReference($definitionName);
            return;
        }

        if (isset($primitiveType['format'])) {
            $target->format = $primitiveType['format'];
        }
    }

    private function getHash(array $context)
    {
        return md5(http_build_query($context));
    }

    private function hashExists($modelName, $hash)
    {
        if (!array_key_exists($modelName, $this->definitionHashes)) {
            $this->definitionHashes[$modelName] = [];
        }

        // If hash not found - register it and return false (meaning it wasn't registered)
        if (!\in_array($hash, $this->definitionHashes[$modelName], true)) {
            $this->definitionHashes[$modelName][] = $hash;
            return false;
        }

        return true;
    }

    public static function getPrimitiveType($type)
    {
        if (!\is_string($type)) {
            return null;
        }

        $primitiveType = [];

        $typeOfArray = str_replace('[]', '', $type);
        if ($typeOfArray != $type) {
            if ($typeOfArray !== substr($type, 0, -2)) {
                return null;
            }

            $primitiveType['type'] = 'array';
            $primitiveType['subType'] = $typeOfArray;
            return $primitiveType;
        }

        if (null !== $primitiveType = static::convertType($type)) {
            return $primitiveType;
        }

        if (class_exists($type)) {
            return [
                'type' => 'object',
                'class' => $type,
            ];
        };

        return null;
    }

    public static function convertType($type)
    {
        static $types = [
            'int' => ['type' => 'integer', 'format' => 'int32'],
            'integer' => ['type' => 'integer', 'format' => 'int32'],
            'long' => ['type' => 'integer', 'format' => 'int64'],
            'float' => ['type' => 'number', 'format' => 'float'],
            'double' => ['type' => 'number', 'format' => 'double'],
            'string' => ['type' => 'string'],
            'byte' => ['type' => 'string', 'format' => 'byte'],
            'bool' => ['type' => 'boolean'],
            'boolean' => ['type' => 'boolean'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'DateTime' => ['type' => 'string', 'format' => 'date-time'],
            'dateTime' => ['type' => 'string', 'format' => 'date-time'],
            'password' => ['type' => 'string', 'format' => 'password'],
            'array' => ['type' => 'array'],
            'stdClass' => ['type' => 'object'],
        ];

        if (array_key_exists($type, $types)) {
            return $types[$type];
        }

        return null;

    }

    public function getAliasFor($className)
    {
        if (array_key_exists($className, $this->definitionAliases)) {
            return $this->definitionAliases[$className];
        }
        return $className;
    }
}
