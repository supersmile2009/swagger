<?php

namespace Draw\Swagger\Extraction\Extractor;

use Doctrine\Common\Annotations\Reader;
use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\ExternalDocumentation;
use Draw\Swagger\Schema\OpenApi;
use Draw\Swagger\Schema\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\DocBlockFactory;

class SwaggerTagExtractor implements ExtractorInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    public function __construct(Reader $reader)
    {
        $this->annotationReader = $reader;
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * Return if the extractor can extract the requested data or not.
     *
     * @param \ReflectionClass $source
     * @param OpenApi $target
     * @param ExtractionContextInterface $extractionContext
     *
     * @return boolean
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool
    {
        if (!$source instanceof \ReflectionClass) {
            return false;
        }

        if (!$target instanceof OpenApi) {
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
     * @param \ReflectionClass $class
     * @param OpenApi $openApi
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($class, &$openApi, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($class, $openApi, $extractionContext)) {
            return;
        }

        foreach ($this->annotationReader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Tag) {
                $docBlock = $this->docBlockFactory->create($class->getDocComment());

                $tag = new Tag();
                $tag->name = $annotation->name;
                $tag->description = $docBlock->getDescription();
                $openApi->tags[] = $tag;

                if ($docBlock->hasTag('see')) {
                    /** @var See[] $seeTags */
                    $seeTags = $docBlock->getTagsByName('see');
                    foreach ($seeTags as $seeTag) {
                        if ($seeTag->getReference() instanceof Url) {
                            $tag->externalDocs = $externalDoc = new ExternalDocumentation();
                            $externalDoc->url = $seeTag->getReference();
                            $externalDoc->description = $seeTag->getDescription();
                            break;
                        }
                    }

                }
            }
        }
    }
}
