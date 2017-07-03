<?php

namespace Draw\Swagger\Extraction\Extractor;

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
            $response = new Response();
            $response->schema = $responseSchema = new Schema();
            $response->description = "Generic 200 response";
            $operation->responses[200] = $response;
            $subContext = $extractionContext->createSubContext();
            $subContext->setParameter('direction', 'out');
            $extractionContext->getSwagger()->extract("test", $responseSchema, $subContext);
        } else {
            /** @var DocBlock\Tags\Return_ $returnTag */
            foreach ($returnTags as $returnTag) {
                /** @var Object_ $type */
                $type = $returnTag->getType();

                // If multiple return types are specified in return tag
                if ($type instanceof Compound) {
                    $types = explode('|', $type->__toString());
                    foreach ($types as $actualType) {
                        $response = new Response();
                        $response->schema = $responseSchema = new Schema();
                        $response->description = $returnTag->getDescription();
                        $operation->responses[200] = $response;

                        $subContext = $extractionContext->createSubContext();
                        $subContext->setParameter('direction', 'out');

                        $extractionContext->getSwagger()->extract((string)$actualType, $responseSchema, $subContext);
                    }
                } else {
                    $response = new Response();
                    $response->schema = $responseSchema = new Schema();
                    $response->description = $returnTag->getDescription();
                    $operation->responses[200] = $response;

                    $subContext = $extractionContext->createSubContext();
                    $subContext->setParameter('direction', 'out');

                    $extractionContext->getSwagger()->extract((string)$type, $responseSchema, $subContext);
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
                    $parameter->type = (string)$paramTag->getType();
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
                            (string)$paramTag->getType(),
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
}