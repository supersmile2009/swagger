<?php

namespace Draw\Swagger\Extraction;

interface ExtractionContextInterface
{
    /**
     * @return \Draw\Swagger\OpenApiGenerator
     */
    public function getSwagger();

    /**
     * @return \Draw\Swagger\Schema\OpenApi
     */
    public function getRootSchema();

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasParameter($name);

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getParameter($name, $default = null);

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param $name
     * @param $value
     */
    public function setParameter($name, $value);

    /**
     * @param $name
     */
    public function removeParameter($name);

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters);

    /**
     * @return ExtractionContextInterface
     */
    public function createSubContext();
}
