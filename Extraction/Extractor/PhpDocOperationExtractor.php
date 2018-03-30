<?php

namespace Draw\Swagger\Extraction\Extractor;

use Draw\Swagger\Extraction\ExtendedReflectionClass;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\MediaType;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\QueryParameter;
use Draw\Swagger\Schema\Reference;
use Draw\Swagger\Schema\RequestBody;
use Draw\Swagger\Schema\Response;
use Draw\Swagger\Schema\Schema;
use Draw\SwaggerBundle\Extractor\FOSRestViewOperationExtractor;
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
     * @var FOSRestViewOperationExtractor
     */
    private $viewExtractor;

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
     * PhpDocOperationExtractor constructor.
     * @param FOSRestViewOperationExtractor $viewExtractor
     */
    public function __construct(FOSRestViewOperationExtractor $viewExtractor)
    {
        $this->viewExtractor = $viewExtractor;
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
     * @return void
     * @throws \ReflectionException
     * @throws ExtractionImpossibleException
     */
    public function extract($method, &$operation, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($method, $operation, $extractionContext)) {
            return;
        }

        $factory = DocBlockFactory::createInstance();
        $docBlock = $factory->create($method->getDocComment());

        if (!$operation->summary) {
            $operation->summary = $docBlock->getSummary();
        }

        if ($operation->description) {
            $operation->description = $docBlock->getDescription();
        }

        $statusCode = $this->getStatusCode($method);
        $returnTags = $docBlock->getTagsByName('return');
        if (empty($returnTags)) {
            $this->generateResponse($operation, $extractionContext, $statusCode);
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

                $responseGenerated = false;
                foreach ($types as $actualType) {
                    $actualType = $this->convertType($method, $actualType);
                    // We want to exclude specific types, leave only one and generate response for it
                    if ($this->shouldSkipType($actualType) === false) {
                        $responseGenerated = true;
                        $this->generateResponse($operation, $extractionContext, $statusCode, $returnTag->getDescription(), $actualType);
                    }
                    // But if all response types were skipped excluded, we want to generate generic 200 response
                    if ($responseGenerated === false) {
                        $this->generateResponse($operation, $extractionContext, $statusCode, $returnTag->getDescription());
                    }
                }
            }
        }

        $deprecatedTags = $docBlock->getTagsByName('deprecated');
        if (empty($deprecatedTags) === false) {
            $operation->deprecated = true;
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Deprecated $deprecatedTag */
            foreach ($deprecatedTags as $deprecatedTag) {
                $operation->setCustomProperty(
                    'deprecationDescription',
                    $operation->getCustomProperty('deprecationDescription').$deprecatedTag->getDescription()
                );
            }
        }

        /** @var RequestBody $requestBody */
        $requestBody = $operation->requestBody;
        if ($requestBody instanceof Reference) {
            throw new ExtractionImpossibleException('References in request body aren\'t currently supported.');
        }

        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param $paramTag */
        foreach ($docBlock->getTagsByName('param') as $paramTag) {
            $parameterName = trim($paramTag->getVariableName(), '$');

            /** @var QueryParameter $parameter */
            $parameter = null;
            foreach ($operation->parameters as $existingParameter) {
                if ($existingParameter->name === $parameterName) {
                    $parameter = $existingParameter;
                    break;
                }
            }

            if (null !== $parameter) {
                if ($parameter->description === null) {
                    $parameter->description = $paramTag->getDescription() !== null ? $paramTag->getDescription()->__toString() : null;
                }
                if ($parameter->schema === null) {
                    $parameter->schema = new Schema();
                }

                if ($parameter->schema->type === null) {
                    $this->extractType(
                        $this->convertType($method, (string)$paramTag->getType()),
                        $parameter->schema,
                        $extractionContext
                    );
                }
                continue;
            }

            if (null !== $requestBody) {
                if (isset($requestBody->content['application/json']->schema->properties[$parameterName])) {
                    $parameter = $requestBody->content['application/json']->schema->properties[$parameterName];

                    if (!$parameter->description) {
                        $parameter->description = $paramTag->getDescription();
                    }

                    if (!$parameter->type) {
                        $this->extractType(
                            $this->convertType($method, (string)$paramTag->getType()),
                            $parameter,
                            $extractionContext
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
    public function canExtract($source, $type, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof ReflectionMethod) {
            return false;
        }

        if (!$type instanceof Operation) {
            return false;
        }

        return true;
    }

    /**
     * @param string $type
     * @param Schema $target
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws ExtractionImpossibleException
     */
    private function extractType($type, $target, $extractionContext)
    {
        $subContext = $extractionContext->createSubContext();
        $subContext->setParameter('direction', 'in');
        $extractionContext->getSwagger()->extract(
            $type,
            $target,
            $subContext
        );
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
     *
     * @return string
     * @throws \ReflectionException
     */
    private function convertType($method, $type)
    {
        $isArray = false;
        $type = trim($type);
        if (\substr($type, -2) === '[]') {
            $isArray = true;
            $type = \rtrim($type, '[]');
        }

        if (null !== $primitiveType = TypeSchemaExtractor::convertType($type)) {
            $type = $primitiveType['type'];
        } elseif (!class_exists($type)) {
            $reflectionClass = new ExtendedReflectionClass($method->getDeclaringClass()->getName());
            $type = $reflectionClass->getFQCN($type);
        }
        if ($isArray === true) {
            $type .= '[]';
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
        return \in_array($type, $this->excludedReturnTypes, true);
    }

    /**
     * Generates new response based on extracted data
     *
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     * @param int $statusCode
     * @param string $description
     * @param string $type
     *
     * @throws ExtractionImpossibleException
     */
    private function generateResponse($operation, $extractionContext, $statusCode = 200, $description = 'Generic 200 response', $type = 'test')
    {
        $responseType = $this->classExists($type) ? 'application/json' : 'text/html';
        $response = new Response();
        $response->content[$responseType] = $mediaType = new MediaType();
        $mediaType->schema = new Schema();
        $response->description = $description;
        $operation->responses[$statusCode] = $response;

        $subContext = $extractionContext->createSubContext();
        $subContext->setParameter('direction', 'out');

        $extractionContext->getSwagger()->extract($type, $mediaType->schema, $subContext);
    }

    private function classExists($type)
    {
        $type = trim($type);
        if (\substr($type, -2) === '[]') {
            $type = \rtrim($type, '[]');
        }

        return class_exists($type);

    }

    /**
     * @param ReflectionMethod $method
     * @return int
     */
    private function getStatusCode(ReflectionMethod $method)
    {
        $view = $this->viewExtractor->getView($method);
        if ($view !== null && $view->getStatusCode() !== null) {
            return $view->getStatusCode();
        } else {
            return 200;
        }
    }
}
