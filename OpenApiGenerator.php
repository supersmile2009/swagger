<?php

namespace Draw\Swagger;

use Doctrine\Common\Annotations\AnnotationReader;
use Draw\Swagger\Extraction\ExtractionContext;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\Extractor\OpenApiJsonSchemaExtractor;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\OpenApi;
use Draw\Swagger\Schema\Reference;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;

/**
 * Class Generator
 *
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class OpenApiGenerator
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $extractors = [];

    /**
     * @var ExtractorInterface[]
     */
    private $sortedExtractors;

    /**
     * OpenApiGenerator constructor.
     *
     * @param SerializerInterface|null $serializer
     *
     * @throws \JMS\Serializer\Exception\InvalidArgumentException
     */
    public function __construct(SerializerInterface $serializer = null)
    {
        if (null === $serializer) {
            $serializer = SerializerBuilder::create()->configureListeners(
                function (EventDispatcher $dispatcher) {
                    $dispatcher->addSubscriber(new JMSSerializerListener());
                }
            )->build();

        }
        $this->serializer = $serializer;

        $this->registerExtractor(new OpenApiJsonSchemaExtractor($this->serializer), -1, 'swagger');
    }

    public function registerExtractor(ExtractorInterface $extractorInterface, $position = 0, $section = 'default')
    {
        $this->extractors[$section][$position][] = $extractorInterface;
    }

    /**
     * @param OpenApi $schema
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function validate(OpenApi $schema)
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping(new AnnotationReader())
            ->getValidator();
        /** @var ConstraintViolationList $errorList */
        $errorList = $validator->validate($schema, null, true);

        if ($errorList->count() > 0) {
            throw new \InvalidArgumentException(''.$errorList->__toString());
        }
    }

    /**
     * @param OpenApi $schema
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function dump(OpenApi $schema)
    {
        $this->validate($schema);
        return $this->serializer->serialize($schema, 'json');
    }

    /**
     * @api
     *
     * @param mixed $source
     * @param mixed $target
     * @param ExtractionContextInterface|null $extractionContext
     *
     * @return mixed
     * @throws ExtractionImpossibleException
     */
    public function extract($source, &$target = null, ExtractionContextInterface $extractionContext = null)
    {
        if (null === $target) {
            $target = new OpenApi();
        }

        if (null === $extractionContext) {
            $extractionContext = new ExtractionContext($this, $target);
        }

        $realTarget = $target;
        $sortedExtractors = $this->getSortedExtractors();
        foreach ($sortedExtractors as $extractor) {
            $realTarget = static::resolveReference($realTarget, $extractionContext->getRootSchema());
            $beforeExtraction = $realTarget;
            if ($extractor->canExtract($source, $realTarget, $extractionContext)) {
                $extractor->extract($source, $realTarget, $extractionContext);
            }
            if (\get_class($beforeExtraction) !== \get_class($realTarget)) {
                $target = $realTarget;
            }
        }

        return $target;
    }

    /**
     * @return ExtractorInterface[]
     */
    private function getSortedExtractors()
    {
        if (null === $this->sortedExtractors) {
            $this->sortedExtractors = [];
            foreach ($this->extractors as $section => $extractors) {
                ksort($extractors);
                $this->sortedExtractors = call_user_func_array('array_merge', $extractors);
            }
        }

        return $this->sortedExtractors;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param $target
     * @param OpenApi $rootSchema
     *
     * @return mixed
     *
     * @throws ExtractionImpossibleException
     */
    public static function resolveReference($target, OpenApi $rootSchema)
    {
        if ($target instanceof Reference) {
            $parts = explode('/', ltrim($target->ref, '#/'));

            $currentItem = $rootSchema;
            foreach ($parts as $part) {
                if (is_array($currentItem)) {
                    $currentItem = $currentItem[$part];
                } elseif (property_exists(get_class($currentItem), $part)) {
                    $currentItem = $currentItem->{$part};
                } else {
                    throw new ExtractionImpossibleException("Reference {$target->ref} not found.");
                }
            }
            $target = $currentItem;
        }

        return $target;
    }
} 
