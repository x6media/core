<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * The Arr class provides a few nice functions for making
 * dealing with arrays easier
 *
 * @package     Fuel
 * @subpackage  Core
 */
class Arr {

	/**
	 * Flattens a multi-dimensional associative array down into a 1 dimensional
	 * associative array.
	 *
	 * @param   array   the array to flatten
	 * @param   string  what to glue the keys together with
	 * @param   bool    whether to reset and start over on a new array
	 * @return  array
	 */
	public static function flatten_assoc($array, $glue = ':', $reset = true)
	{
		static $return = array();
		static $curr_key = array();

		if ($reset)
		{
			$return = array();
			$curr_key = array();
		}

		foreach ($array as $key => $val)
		{
			$curr_key[] = $key;
			if (is_array($val) and array_values($val) !== $val)
			{
				static::flatten_assoc($val, $glue, false);
			}
			else
			{
				$return[implode($glue, $curr_key)] = $val;
			}
			array_pop($curr_key);
		}
		return $return;
	}

	/**
	 * Filters an array on prefixed associative keys.
	 *
	 * @param   array   the array to filter.
	 * @param   string  prefix to filter on.
	 * @param   bool    whether to remove the prefix.
	 * @return  array
	 */
	public static function filter_prefixed($array, $prefix = 'prefix_', $remove_prefix = true)
	{
		$return = array();
		foreach ($array as $key => $val)
		{
			if(preg_match('/^'.$prefix.'/', $key))
			{
				if($remove_prefix === true)
				{
					$key = preg_replace('/^'.$prefix.'/','',$key);
				}
				$return[$key] = $val;
			}
		}
		return $return;
	}

	/**
	 * Filters an array by an array of keys
	 *
	 * @param   array   the array to filter.
	 * @param   array   the keys to filter
	 * @param   bool    if true, removes the matched elements.
	 * @return  array
	 */
	public static function filter_keys($array, $keys, $remove = false)
	{
		$return = array();
		foreach ($keys as $key)
		{
			if (isset($array[$key]) and  ! $remove)
			{
				$return[$key] = $array[$key];
			}
			elseif (isset($array[$key]) and $remove)
			{
				unset($array[$key]);
			}
		}
		return $remove ? $array : $return;
	}

	/**
	 * Returns the element of the given array or a default if it is not set.
	 *
	 * @param   array  the array to fetch from
	 * @param   mixed  the key to fetch from the array
	 * @param   mixed  the value returned when not an array or invalid key
	 * @return  mixed
	 */
	public static function element($array, $key, $default = false)
	{
		$key = explode('.', $key);
		if(count($key) > 1)
		{
			if ( ! is_array($array) or ! array_key_exists($key[0], $array))
			{
				return $default;
			}
			$array = $array[$key[0]];
			unset($key[0]);
			$key = implode('.', $key);
			$array = static::element($array, $key, $default);
			return $array;
		}
		else
		{
			$key = $key[0];
			if ( ! is_array($array) or ! array_key_exists($key, $array))
			{
				return $default;
			}
			return $array[$key];
		}
	}

	/**
	 * Returns the elements of the given array or a default if it is not set.
	 *
	 * @param   array  the array to fetch from
	 * @param   array  the keys to fetch from the array
	 * @param   mixed  the value returned when not an array or invalid key
	 * @return  mixed
	 */
	public static function elements($array, $keys, $default = false)
	{
		$return = array();

		if ( ! is_array($array) or ! is_array($keys))
		{
			throw new \InvalidArgumentException('Arr::elements() - $keys and $array must be arrays.');
		}

		foreach ($keys as $key)
		{
			if ( ! array_key_exists($key, $array))
			{
				$return[$key] = $default;
			}
			else
			{
				$return[$key] = $array[$key];
			}
		}

		return $return;
	}

	/**
	 * Insert value(s) into an array, mostly an array_splice alias
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   int          the numeric position at which to insert, negative to count from the end backwards
	 * @return  bool         false when array shorter then $pos, otherwise true
	 */
	public static function insert(Array &$original, $value, $pos)
	{
		if (count($original) < abs($pos))
		{
			\Error::notice('Position larger than number of elements in array in which to insert.');
			return false;
		}

		array_splice($original, $pos, 0, $value);
		return true;
	}

	/**
	 * Insert value(s) into an array after a specific key
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   string|int   the key after which to insert
	 * @return  bool         false when key isn't found in the array, otherwise true
	 */
	public static function insert_after_key(Array &$original, $value, $key)
	{
		$pos = array_search($key, array_keys($original));
		if ($pos === false)
		{
			\Error::notice('Unknown key after which to insert the new value into the array.');
			return false;
		}

		return static::insert($original, $value, $pos + 1);
	}

	/**
	 * Insert value(s) into an array after a specific value (first found in array)
	 *
	 * @param   array        the original array (by reference)
	 * @param   array|mixed  the value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param   string|int   the value after which to insert
	 * @return  bool         false when value isn't found in the array, otherwise true
	 */
	public static function insert_after_value(Array &$original, $value, $search)
	{
		$key = array_search($search, $original);
		if ($key === false)
		{
			\Error::notice('Unknown value after which to insert the new value into the array.');
			return false;
		}

		return static::insert_after_key($original, $value, $key);
	}

	/**
	 * Sorts a multi-dimensional array by it's values.
	 *
	 * @access	public
	 * @param	array	The array to fetch from
	 * @param	string	The key to sort by
	 * @param	string	The order (asc or desc)
	 * @param	int		The php sort type flag
	 * @return	array
	 */
	public static function sort($array, $key, $order = 'asc', $sort_flags = SORT_REGULAR)
	{
		if ( ! is_array($array))
		{
			throw new \InvalidArgumentException('Arr::sort() - $array must be an array.');
		}

		foreach ($array as $k=>$v)
		{
			$b[$k] = static::element($v, $key);
		}

		switch ($order)
		{
			case 'asc':
				asort($b, $sort_flags);
			break;

			case 'desc':
				arsort($b, $sort_flags);
			break;

			default:
				throw new \InvalidArgumentException('Arr::sort() - $order must be asc or desc.');
			break;
		}

		foreach ($b as $key=>$val)
		{
			$c[] = $array[$key];
		}

		return $c;
	}

	/**
	 * Find the average of an array
	 *
	 * @param   array    the array containing the values
	 * @return  numeric  the average value
	 */
	public static function average($array)
	{
		// No arguments passed, lets not divide by 0
		if ( ! ($count = count($array)) > 0)
		{
			return 0;
		}

		return (array_sum($array) / $count);
	}

	/**
	 * Replaces key names in an array by names in $replace
	 *
	 * @param   array    the array containing the key/value combinations
	 * @param   array    the array containing the replacement keys
	 * @return  array    the array with the new keys
	 */
	public static function replace_keys($source, $replace)
	{
		if ( ! is_array($source) or ! is_array($replace))
		{
			throw new \InvalidArgumentException('Arr::replace_keys() - $source and $replace must arrays.');
		}

		$result = array();

		foreach ($source as $key => $value)
		{
			if (array_key_exists($key, $replace))
			{
				$result[$replace[$key]] = $value;
			}
			else
			{
				$result[$key] = $value;
			}
		}

		return $result;
	}

}

/* End of file arr.php */
