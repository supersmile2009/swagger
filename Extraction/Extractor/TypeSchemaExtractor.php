<?php

namespace Draw\Swagger\Extraction\Extractor;

use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Schema\Schema as SupportedTarget;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Schema;

class TypeSchemaExtractor implements ExtractorInterface
{
    /**
     * @var string[]
     */
    private $definitionAliases = array();

    private $definitionHashes = array();

    public function registerDefinitionAlias($definition, $alias)
    {
        $this->definitionAliases[$definition] = $alias;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param SupportedTarget $target
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$target instanceof SupportedTarget) {
            return false;
        }

        if(is_null($this->getPrimitiveType($source))) {
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
     * @param SupportedTarget $target
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws ExtractionImpossibleException
     */
    public function extract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $target, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $primitiveType = $this->getPrimitiveType($source);

        $target->type = $primitiveType['type'];

        if($target->type == 'array') {
            $target->items = $itemsSchema = new Schema();
            if(isset($primitiveType['subType'])) {
                $extractionContext->getSwagger()->extract(
                    $primitiveType['subType'],
                    $itemsSchema,
                    $extractionContext
                );
            }

            return;
        }

        if($target->type == "object") {
            $reflectionClass = new \ReflectionClass($primitiveType['class']);
            $name = $reflectionClass->getName();
            $rootSchema = $extractionContext->getRootSchema();

            if($direction = $extractionContext->getParameter('direction')) {
                $context = $extractionContext->getParameter($direction . '-model-context');
            } else {
                $context = $extractionContext->getParameter('model-context');
            }

            if(array_key_exists($name, $this->definitionAliases)) {
                $name = $this->definitionAliases[$name];
            }

            $definitionName = str_replace('\\','.', $name);

            // If definition for Entity is already registered, merge serializer groups
            if ($rootSchema->hasDefinition($definitionName)) {
                $lastContext = $rootSchema->definitions[$definitionName]->serializerGroups;
                if (isset($context['serializer-groups'])) {
                    $context['serializer-groups'] = array_unique(array_merge($lastContext, $context['serializer-groups']));
                } else {
                    $context['serializer-groups'] = $lastContext;
                }

            }

            $hash = $this->getHash($context['serializer-groups']);

            // If definition has not been added and processed yet OR if serializer groups hash changed (new groups added)
            if(!$rootSchema->hasDefinition($definitionName) || $this->hashExists($name, $hash) === false) {
                $rootSchema->addDefinition($definitionName, $refSchema = new Schema());
                $refSchema->type = "object";
                if (isset($context['serializer-groups'])) {
                    $refSchema->serializerGroups = $context['serializer-groups'];
                }
                $extractionContext->getSwagger()->extract(
                    $reflectionClass,
                    $refSchema,
                    $extractionContext
                );
            }

            $target->ref = $rootSchema->getDefinitionReference($definitionName);
            return;
        }

        if(isset($primitiveType['format'])) {
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
        if (false === array_search($hash, $this->definitionHashes[$modelName])) {
            $this->definitionHashes[$modelName][] = $hash;
            return false;
        } else {
            return true;
        }
    }

    private function getPrimitiveType($type)
    {
        if(!is_string($type)) {
            return null;
        }

        $primitiveType = array();

        $typeOfArray = str_replace('[]','', $type);
        if($typeOfArray != $type) {
            if($typeOfArray !== substr($type,0,-2)) {
                return null;
            }

            $primitiveType['type'] = 'array';
            $primitiveType['subType'] = $typeOfArray;
            return $primitiveType;
        }

        $types = array(
            'int' => array('type' => 'integer', 'format' => 'int32'),
            'integer' => array('type' => 'integer', 'format' => 'int32'),
            'long' => array('type' => 'integer', 'format' => 'int64'),
            'float' => array('type' => 'number', 'format' => 'float'),
            'double' => array('type' => 'number', 'format' => 'double'),
            'string' => array('type' => 'string'),
            'byte' => array('type' => 'string', 'format' => 'byte'),
            'boolean' => array('type' => 'boolean'),
            'date' => array('type' => 'string', 'format' => 'date'),
            'DateTime' => array('type' => 'string', 'format' => 'date-time'),
            'dateTime' => array('type' => 'string', 'format' => 'date-time'),
            'password' => array('type' => 'string', 'format' => 'password'),
            'array' => array('type' => 'array')
        );

        if(array_key_exists($type, $types)) {
            return $types[$type];
        }

        if(class_exists($type)) {
            return array(
                'type' => 'object',
                'class' => $type
            );
        };

        return null;
    }

    public function getAliasFor($className) {
        if(array_key_exists($className, $this->definitionAliases)) {
            return $this->definitionAliases[$className];
        }
        return $className;
    }
}