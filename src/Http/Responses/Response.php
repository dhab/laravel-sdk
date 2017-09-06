<?php

namespace DreamHack\SDK\Http\Responses;
use Carbon\Carbon;
use DreamHack\SDK\Contracts\Requestable;
use DreamHack\SDK\Eloquent\Model;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Collection;

class Response extends IlluminateResponse {

    public static function getDefaultStatusCode() {
        return 200;
    }

    public static function getBaseHeaders() {
        return [
            "Content-Type" => "application/json",
        ];
    }

    public function __construct($content, $status = false, $headers = false) {
        parent::__construct($content, $status?:self::getDefaultStatusCode(), $headers?array_merge(self::getBaseHeaders(), $headers):self::getBaseHeaders());
    }


    protected function collectionSubset($collection, $fields) {
        return $collection->map(function($row) use ($fields) {
            $ret = [];
            foreach($fields as $field) {
                $ret[$field] = $row->$field;
            }
            return $ret;
        });
    }

    protected static function castCollectionSubsetIterator($fields) {
        return function($row) use ($fields) {
            $ret = [];
            foreach($fields as $field => $castType) {
                $value = $row->$field;
                if(class_exists($castType) && (is_subclass_of($castType, Model::class) || in_array(Requestable::class, class_implements($castType)))) {
                    if($value === null ){
                        continue;
                    }
                    if($value instanceof Collection) {
                        $value = self::castCollectionSubset($value, $castType::getFields(), $castType::getKeyByField(), $castType::getGroupByField());
                    } else {
                        $value = self::castCollectionSubsetIterator($castType::getFields())($value);
                    }
                } else if(is_string($castType)) {
                    if($value === null) {
                        $ret[$field] = $value;   
                        continue;
                    }
                    switch ($castType) {
                        case 'int':
                        case 'integer':
                            $value = (int) $value;
                            break;
                        case 'real':
                        case 'float':
                        case 'double':
                            $value = (float) $value;
                            break;
                        case 'uuid':
                        case 'string':
                            $value = (string) $value;
                            break;
                        case 'bool':
                        case 'boolean':
                            $value = (bool) $value;
                            break;
                        case 'collection':
                            $value = new BaseCollection($value);
                            break;
                        case 'date':
                            $value = static::asDate($value)->toW3cString();
                            break;
                        case 'datetime':
                            $value = static::asDateTime($value)->toW3cString();
                            break;
                        case 'timestamp':
                            $value = static::asTimestamp($value);
                            break;
                        case 'self':
                            if($value) {
                                $value = self::castCollectionSubsetIterator($fields)($value);
                            }
                            break;
                    }
                } else {
                    continue;
                }
                $ret[$field] = $value;
            }
            return $ret;
        };
    }

    protected static function castCollectionSubset($collection, $fields, $idKey = false, $groupBy = false) {
        if($groupBy) {
            $ret = collect([]);
            $collection = $collection->groupBy($groupBy)->all();
            foreach($collection as $key => $group) {
                if($idKey) {
                    $group = $group->keyBy($idKey);
                }
                $ret[$key] = $group->map(self::castCollectionSubsetIterator($fields));
            }
            return $ret;
        }
        if($idKey) {
            $collection = $collection->keyBy($idKey);
        }
        return $collection->map(static::castCollectionSubsetIterator($fields));
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string  $value
     * @param  bool  $asObject
     * @return mixed
     */
    public static function fromJson($value, $asObject = false)
    {
        return json_decode($value, ! $asObject);
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon
     */
    protected static function asDate($value)
    {
        return static::asDateTime($value)->startOfDay();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon
     */
    protected static function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

         // If the value is already a DateTime instance, we will just skip the rest of
         // these checks since they will be a waste of time, and hinder performance
         // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (static::isStandardDateFormat($value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Carbon::createFromFormat(
            static::getDateTimeFormat(), $value
        );
    }

    public static function getDateFormat() {
        return 'Y-m-d';
    }

    public static function getDateTimeFormat() {
        return 'Y-m-d H:i:s';
    }

    /**
     * Determine if the given value is a standard date format.
     *
     * @param  string  $value
     * @return bool
     */
    protected static function isStandardDateFormat($value)
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }

    /**
     * Convert a DateTime to a storable string.
     *
     * @param  \DateTime|int  $value
     * @return string
     */
    public static function fromDateTime($value)
    {
        return static::asDateTime($value)->format(
            static::getDateTimeFormat()
        );
    }

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed  $value
     * @return int
     */
    protected static function asTimestamp($value)
    {
        return static::asDateTime($value)->getTimestamp();
    }
}
