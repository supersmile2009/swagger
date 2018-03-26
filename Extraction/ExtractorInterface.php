<?php

namespace Draw\Swagger\Extraction;

interface ExtractorInterface
{
    /**
     * Return if the extractor can extract the requested data or not.
     * This method should be called first before calling ExtractorInterface::extract()
     *
     * @param mixed $source
     * @param mixed $target
     * @param ExtractionContextInterface $extractionContext
     * @return boolean
     */
    public function canExtract($source, $target, ExtractionContextInterface $extractionContext): bool;

    /**
     * Extract the requested data.
     * This method shouldn't be called without calling ExtractorInterface::canExtract() first.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param mixed $source
     * @param mixed &$target Passed as reference to allow replace original target at runtime (e. g. replace Schema with Reference).
     * @param ExtractionContextInterface $extractionContext
     */
    public function extract($source, &$target, ExtractionContextInterface $extractionContext);
}
