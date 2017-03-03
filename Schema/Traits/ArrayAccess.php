<?php
namespace  Draw\Swagger\Schema\Traits;

/**
 * Class ArrayAccess
 * @package Draw\Swagger\Schema\Traits\ArrayAccess
 */
trait ArrayAccess
{
    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset  An offset to check for.
     * @return bool          Returns TRUE on success or FALSE on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }
    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset  The offset to retrieve.
     * @return mixed         Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->$offset) ? $this->$offset : null;
    }
    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset  The offset to assign the value to.
     * @param mixed $value   The value to set.
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception("Method not implemented.");
    }
    /**
     * Unsets an offset.
     *
     * @param mixed $offset  The offset to unset.
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        throw new \Exception("Method not implemented.");

    }
}