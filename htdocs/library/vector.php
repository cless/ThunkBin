<?php

/**
 * A vector is an interface to an array or hashtable. it provides functions
 * to verify if an item exists, to set an item or to retreive a value based
 * on a key. Retreiving values can also force a certain type (e.g. int)
 */
class Vector
{
    private $array;
    private $ReadOnly;
    /**
     * Creates the vector based on an input array
     *
     * \param array Reference to the array on which the vector instance should operate.
     * \param readonly If readonly is set to true, the Vector::Set function will be disabled. 
     */
    function __construct(&$array, $readonly = false)
    {
        $this->readonly = $readonly; 
        if(is_array($array))
            $this->array =& $array;
        else
            $this->array = array();
    }
    
    /**
     * Set a key->value pair in the vector. If the array is read only then this
     * function does nothing at all, it does not even throw an error.
     *
     * \param key The key for the value, this can be a simple integral index, a string,
     *            or anything that can be used as array index in php. If they key already
     *            exists it will be overwritten.
     * \param value the value that is saved for the given key.
     */
    function Set($key, $value)
    {
        if($this->ReadOnly == false)
            $this->array[$key] = $value;
    }

    /**
     * Verifies if a given key has a value.
     *
     * \param key The key that is to be verifed 
     * \return true when the key->value pair exists in the array, false otherwise
     */
    function Exists($key)
    {
        if(isset($this->array[$key]))
            return true;
        else
            return false;
    }

    /**
     * Get the raw value of key->value pair.
     *
     * \param key The key for which the value is to be retreived
     * \return The value for the key as the actual type it is stored as. (e.g. int, object, string).
     *         If the key does not exist the return value is false
     */
    function AsDefault($key)
    {
        if(!isset($this->array[$key]))
            return false;
        else 
            return $this->array[$key];
    }
    
    /**
     * Get the integer value of key->value pair.
     *
     * \param key The key for which the value is to be retreived
     * \return The integral representation of the key.
     *         If the key does not exist the return value is 0
     */
    function AsInt($key)
    {
        if(!isset($this->array[$key]))
            return 0;

        $value = $this->array[$key];
        if(is_numeric($value) && is_int($value + 0))
            return $value + 0;
        else
            return 0;
    }

    /**
     * Get the float value of key->value pair.
     *
     * \param key The key for which the value is to be retreived
     * \return The float representation of the key.
     *         If the key does not exist the return value is 0.0
     */
    function AsFloat($key)
    {
        if(!isset($this->array[$key]))
            return 0.0;

        $value = $this->array[$key];
        if(is_numeric($value) && is_float($value + 0))
            return $value + 0;
        if(is_numeric($value) && is_int($value + 0))
            return $value + 0.0;
        else
            return 0.0;
    }
    
    /**
     * Get the string value of key->value pair.
     *
     * \param key The key for which the value is to be retreived
     * \return The string representation of the key.
     * \bug If the value can not be represented as a string the behavior is undefined
     */
    function AsString($key)
    {
        if(!isset($this->array[$key]))
            return '';

        $value = $this->array[$key];
        if(is_numeric($value) && is_string($value))
            return $value;
        else
            return '' . $value;
    }
}

?>
