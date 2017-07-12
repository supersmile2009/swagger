<?php

namespace Draw\Swagger\Extraction\Extractor;

use Draw\Swagger\Extraction\ExtendedReflectionClass;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\BodyParameter;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\QueryParameter;
use Draw\Swagger\Schema\Response;
use Draw\Swagger\Schema\Schema;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;

class PhpDocOperationExtractor implements ExtractorInterface
{
    private $exceptionResponseCodes = [];

    /**
     * Stores an array of return types (strings containing FQCNs) which should be skipped when generating responses
     *
     * @var array
     */
    private $excludedReturnTypes = [];

    /**
     * Set excluded types
     *
     * @param array $types
     */
    public function setExcludedTypes($types)
    {
        $this->excludedReturnTypes = $types;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param ReflectionMethod $method
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws ExtractionImpossibleException
     * @return void
     */
    public function extract($method, $operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($method, $operation, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $factory = DocBlockFactory::createInstance();
        $docBlock = $factory->create($method->getDocComment());

        if (!$operation->summary) {
            $operation->summary = $docBlock->getSummary();
        }

        if ($operation->description) {
            $operation->description = $docBlock->getDescription();
        }

        $returnTags = $docBlock->getTagsByName('return');
        if (empty($returnTags)) {
            $this->generate200Response($operation, $extractionContext);
        } else {
            /** @var DocBlock\Tags\Return_ $returnTag */
            foreach ($returnTags as $returnTag) {
                /** @var Object_ $type */
                $type = $returnTag->getType();

                // If multiple return types are specified in return tag, separate them
                if ($type instanceof Compound) {
                    $types = explode('|', (string)$type);
                } else {
                    $types = [(string)$type];
                }

                $i = 1;
                $count = count($types);
                foreach ($types as $actualType) {
                    $actualType = $this->convertTypeToFQCN($method, $actualType);
                    // We want to exclude specific types, leave only one and generate response for it
                    // But if all response types are excluded, we want to generate generic 200 response
                    if ($this->shouldSkipType($actualType)) {
                        if ($i < $count) {
                            $i++;
                            continue;
                        } else {
                            $this->generate200Response($operation, $extractionContext);
                            continue;
                        }
                    }
                    $this->generate200Response($operation, $extractionContext, $returnTag->getDescription(), $actualType);
                }
            }
        }

        if ($docBlock->getTagsByName('deprecated')) {
            $operation->deprecated = true;
        }

        /* @var $throwTag \phpDocumentor\Reflection\DocBlock\Tags\Throws */
        foreach ($docBlock->getTagsByName('throws') as $throwTag) {
            /** @var Object_ $type */
            $type = $throwTag->getType();
            $type = $this->convertTypeToFQCN($method, (string)$type);
            $exceptionClass = new \ReflectionClass((string)$type);
            $exception = $exceptionClass->newInstanceWithoutConstructor();
            list($code, $message) = $this->getExceptionInformation($exception);
            $operation->responses[$code] = $exceptionResponse = new Response();

            if ($throwTag->getDescription()) {
                $message = $throwTag->getDescription();
            } else {
                if (!$message) {
                    $exceptionClassDocBlock = new DocBlock($exceptionClass->getDocComment());
                    $message = $exceptionClassDocBlock->getShortDescription();
                }
            }

            $exceptionResponse->description = $message;
        }

        $bodyParameter = null;

        foreach ($operation->parameters as $parameter) {
            if ($parameter instanceof BodyParameter) {
                $bodyParameter = $parameter;
                break;
            }
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param $paramTag */
        foreach ($docBlock->getTagsByName('param') as $paramTag) {
            $parameterName = trim($paramTag->getVariableName(), '$');

            /** @var QueryParameter $parameter */
            $parameter = null;
            foreach ($operation->parameters as $existingParameter) {
                if ($existingParameter->name == $parameterName) {
                    $parameter = $existingParameter;
                    break;
                }
            }

            if (!is_null($parameter)) {
                if (!$parameter->description) {
                    $parameter->description = $paramTag->getDescription();
                }

                if (!$parameter->type) {
                    $parameter->type = $this->convertTypeToFQCN($method, (string)$paramTag->getType());
                }
                continue;
            }

            if (!is_null($bodyParameter)) {
                /* @var BodyParameter $bodyParameter */
                if (isset($bodyParameter->schema->properties[$parameterName])) {
                    $parameter = $bodyParameter->schema->properties[$parameterName];

                    if (!$parameter->description) {
                        $parameter->description = $paramTag->getDescription();
                    }

                    if (!$parameter->type) {
                        $subContext = $extractionContext->createSubContext();
                        $subContext->setParameter('direction', 'in');
                        $extractionContext->getSwagger()->extract(
                            $this->convertTypeToFQCN($method, (string)$paramTag->getType()),
                            $parameter, $subContext
                        );
                    }

                    continue;
                }
            }
        }
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param $source
     * @param $type
     * @param ExtractionContextInterface $extractionContext
     *
     * @return boolean
     */
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof ReflectionMethod) {
            return false;
        }

        if (!$type instanceof Operation) {
            return false;
        }

        return true;
    }

    private function getExceptionInformation(\Exception $exception)
    {
        foreach ($this->exceptionResponseCodes as $class => $information) {
            if ($exception instanceof $class) {
                return $information;
            }
        }

        return [500, null];
    }

    public function registerExceptionResponseCodes($exceptionClass, $code = 500, $message = null)
    {
        $this->exceptionResponseCodes[$exceptionClass] = [$code, $message];
    }

    /**
     * @param ReflectionMethod $method
     * @param string $type
     * @return string
     */
    private function convertTypeToFQCN($method, $type)
    {
        $isArray = false;
        $type = trim($type);
        if (strpos($type, '[]') !== false) {
            $isArray = true;
            $type = trim($type, '[]');
        }
        if (!class_exists($type)) {
            $reflectionClass = new ExtendedReflectionClass($method->getDeclaringClass()->getName());
            $type = $reflectionClass->getFQCN($type);
        }
        if ($isArray === true) {
            $type = $type . '[]';
        }
        return $type;
    }

    /**
     * Checks if provided return type should be skipped
     *
     * @param string $type
     * @return boolean
     */
    private function shouldSkipType($type)
    {
        return in_array($type, $this->excludedReturnTypes);
    }

    /**
     * Generates new response based on extracted data
     *
     * @param string $description
     * @param string $type
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     */
    private function generate200Response($operation, $extractionContext, $description = 'Generic 200 response', $type = 'test')
    {
        $response = new Response();
        $response->schema = $responseSchema = new Schema();
        $response->description = $description;
        $operation->responses[200] = $response;

        $subContext = $extractionContext->createSubContext();
        $subContext->setParameter('direction', 'out');

        $extractionContext->getSwagger()->extract($type, $responseSchema, $subContext);
    }
}