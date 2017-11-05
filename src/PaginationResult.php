<?php

namespace Lampager\Idiorm;

use IdiormMethodMissingException;
use IdiormResultSet;
use Lampager\Idiorm\Concerns\HasSnakeAliases;
use Lampager\PaginationResult as BasePaginationResult;
use Model;
use ORM;

/**
 * PaginationResult
 *
 * @method array to_array() Get the instance as an array.
 * @method mixed json_serialize() Convert the object into something JSON serializable.
 * @method string to_json(int $options = 0) Convert the object to its JSON representation.
 * @method \ArrayIterator get_iterator() Get iterator of records.
 *
 * @mixin IdiormResultSet
 *
 * @see BasePaginationResult
 */
class PaginationResult extends BasePaginationResult implements \JsonSerializable
{
    use HasSnakeAliases;

    /**
     * Make dynamic calls into the collection.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        if ($this->hasSnakeMethod($method)) {
            return $this->callSnakeMethod($method, $parameters);
        }
        try {
            $r = $this->records instanceof IdiormResultSet ? $this->records : new IdiormResultSet($this->records);
            return $r->{$method}(...$parameters);
        } catch (IdiormMethodMissingException $e) {
            return $this->callSnakeMethod($method, $parameters);
        }
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach (get_object_vars($this) as $key => $value) {
            $array[strtolower(preg_replace('/[a-z]+(?=[A-Z])/', '\0_', $key))] = $value;
        }
        return $array;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return mixed
     */
    public function jsonSerialize()
    {
        $array = $this->toArray();
        if (isset($array['records']) && is_array($array['records'])) {
            $array['records'] = array_map(function ($record) {
                /* @var $record ORM|Model */
                return $record->asArray();
            }, $array['records']);
        }
        return $array;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int    $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
