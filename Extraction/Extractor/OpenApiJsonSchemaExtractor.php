<?php

namespace Draw\Swagger\Extraction\Extractor;

use Draw\Swagger\Extraction\ExtractionContextInterface;
use Draw\Swagger\Extraction\ExtractionImpossibleException;
use Draw\Swagger\Extraction\ExtractorInterface;
use Draw\Swagger\Schema\OpenApi;
use JMS\Serializer\Serializer;

/**
 * Class OpenApiJsonSchemaExtractor
 *
 * Deserializes a JSON OpenAPI spec to instance of OpenApi class.
 * Is used to setup an initial default fields from predefined JSON.
 */
class OpenApiJsonSchemaExtractor implements ExtractorInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Extract the requested data.
     *
     * The system is a incrementing extraction system. A extractor can be call before you and you must complete the
     * extraction.
     *
     * @param string $source
     * @param OpenApi $openApi
     * @param ExtractionContextInterface $extractionContext
     *
     * @throws ExtractionImpossibleException
     * @return void
     */
    public function extract($source, &$openApi, ExtractionContextInterface $extractionContext)
    {
        if (!$this->canExtract($source, $openApi, $extractionContext)) {
            throw new ExtractionImpossibleException();
        }

        $result = $this->serializer->deserialize($source, \get_class($openApi), 'json');

        foreach ($result as $key => $value) {
            $openApi->{$key} = $value;
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
        if (!is_string($source)) {
            return false;
        }

        if (!is_object($type)) {
            return false;
        }

        if (!$type instanceof OpenApi) {
            return false;
        }

        $schema = json_decode($source, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            return false;
        }

        if (!array_key_exists('openapi', $schema)) {
            return false;
        }

        if (strpos($schema['openapi'], '3.') !== 0) {
            return false;
        }

        return true;
    }
}
