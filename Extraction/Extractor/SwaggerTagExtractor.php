<?php

namespace Draw\Swagger\Extraction\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\Operation;
use Draw\Swagger\Schema\Tag;

class SwaggerTagExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param \ReflectionMethod $source
     * @param Operation $target
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext)
    {
        if (!$source instanceof \ReflectionMethod) {
            return false;
        }

        if (!$target instanceof Operation) {
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
     * @param \ReflectionMethod $method
     * @param Operation $operation
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($method, &$operation, ExtractionContextInterface $extractionContext)
    {
        foreach($this->annotationReader->getMethodAnnotations($method) as $annotation) {
            if($annotation instanceof Tag) {
                $operation->tags[] = $annotation->name;
            }
        }
    }
}
