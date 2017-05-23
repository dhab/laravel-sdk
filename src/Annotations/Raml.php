<?php

namespace DreamHack\SDK\Annotations;

use DB;
use DreamHack\SDK\Facades\Fake;

/**
 * 
 **/
class Raml {
    private $errors = [];
    private $params = [
        'title' => '',
        'version' => '',
        'description' => '',
        'protocols' => [],
        'baseUri' => '',
        'mediaType' => [],
        'documentation' => [],
        'types' => [
            'Error' => [
                'name' => 'Error',
                'type' => 'object',
                'description' => 'This general error structure is used throughout this API.',
                'properties' => [
                    'status' => [
                        'type' => 'integer',
                        'minimum' => 400,
                        'maximum' => 599,
                    ],
                    'error' => [
                        'type' => 'string',
                        'description' => 'Message describing the problem in relation to the error type in human-readable form'
                    ]
                ]
            ]
        ]
    ];


    function __construct(array $params = []) {
        $this->params = array_merge($this->params, $params);
    }

    public function addEndpoints($endpoints) {
        $result = [];
        foreach( $endpoints as $key => $endpoint ) {
            $doc = $endpoint->reflection->getMethod($endpoint->method)->getDocComment();
            $doc = preg_replace("/^([^\*\/]*)(\/\*\*| \*|\*\/)/m", "", $doc, -1); // Remove the comment chars
            $doc = explode('@', $doc);

            $responses = [];
            $permissions = [];
            foreach($doc as $line) {
                $line = preg_split('/\s+/', trim($line), 3);

                switch(array_shift($line)) {
                case 'return':
                    $code = 200;
                    if ( is_numeric($line[0]) ) {
                        $code = array_shift($line);
                    }
                    if ( !$type = $this->ramlType($line[0]) )
                        continue 2;

                    if ( isset($raml['types'][$type['name']]['schema']) ) {
                        $body = [
                            $raml['types'][$type['name']]['schema'] => $type['definition']
                        ];
                    }

                    $responses[$code] = [
                        'description' => $type['description'],
                        'body' => $type['definition']
                    ];

                    break;
                }
            }
            if ( !$responses ) { // Enpoint has no documented returns, just skip it
                foreach($endpoint->paths as $path) {
                    $this->errors[$path->verb . ' ' . $path->path] = 'Documentation is missing';
                }
                continue;
            }

            if ( !isset($endpoint->skipAuth) || !$endpoint->skipAuth ) {
                $responses[401] = [
                    'description' => 'No credentials are provided, or the provided credentials are refused.',
                    'body' => [
                        'type' => 'Error',
                    ]
                ];
                $permissions[] = " * Requires authenticated user";
            }

            if ( !$permissions ) 
                $permissions[] = '_Anonymous access is allowed_';

            foreach($endpoint->paths as $path) {
                $subTree = [
                    $path->verb => [
                        'description' => 
                            trim($doc[0] ?? 'Unknown')."\n\n".
                            "__Permissions__\n\n".
                            implode("\n",$permissions),
                        //'queryParameters' => [
                            //'$size' => [
                                //'type' => 'integer',
                                //'description' => 'Size of the page to retrieve',
                                //'required' => false,
                                //'example' => 55,
                            //]
                        //],
                    ]
                ];
                if ( $responses ) {
                    $subTree[$path->verb]['responses'] = $responses;
                }

                // Add it to the endpoint tree
                $parts = explode('/', $path->path);
                foreach (array_reverse($parts) as $dir) {
                    $subTree = array('/'.$dir => $subTree);
                }
                $result = array_merge_recursive($result, $subTree);
            }
        }
    
        // Fix the dataset that gets borked by array_merge_recursive
        $clean = function ($row, $allow_combine = false) use (&$clean) {
            if ( is_array($row) ) {
                foreach($row as $key => $line) {
                    switch($key) {
                    case 'type':
                        // Remove all duplicates that is crated by array_merge_recursive
                        if ( is_array($line) )
                            $row[$key] = reset($line);
                        break;
                    default:
                        $combine = $allow_combine;
                        // Stop combining if we reached a http verb
                        if ( !is_array($line) || array_intersect(['get','post','put','delete'],array_keys($line)) ){
                            $combine = false;
                        }

                        // Clean/combine all childs
                        $row[$key] = $lines = $clean($line, $combine );

                        if ( $combine ) {
                            // Do the combine of paths
                            if ( is_numeric(trim($key, '/')) || (is_array($lines)) ) {
                                unset($row[$key]);
                                foreach( $lines as $key2 => $lines2 )
                                    $row[$key.'/'.trim($key2,'/')] = $lines2;
                            }
                        }
                        break;
                    }
                }
                ksort($row);
            }

            return $row;
        };
        $result = $clean($result, true);

        $this->endpoints = $result;
    }


    public function toArray() {
        $this->params['types'] = array_map(
            function($data) {unset($data['name']);return $data;}, 
            $this->params['types']
        );
        return $this->params + $this->endpoints;
    }

    public function errors() {
        return $this->errors;
    }

    private function ramlType($line, $namespace = '') {
        $line = preg_split('/\s+/', trim($line), 2);
        $is_array = strstr($line[0], '[]') !== false;
        $t = trim($line[0], '[]\\');

        switch($t) {
        case 'string':
        case 'boolean':
        case 'integer':
        case 'float':
        case 'null':
        case 'object':
        case 'array':
            $d = 'Basic php type: '.$t;
            break;
        default:
            $typedef = $this->ramlDefine($t, $namespace);
            if (!$typedef) {
                return false;
            }
            $line[0] = $typedef['name'];
            $d = $typedef['description'];
            $t = $typedef['name'];

            break;
        }
        
        if ( $is_array ) {
            $body = [
                'type' => 'array',
                'items' => $t
            ];
        } else {
            $body = [
                'type' => $line[0],
            ];
        }

        return [
            'name' => $t,
            'definition' => $body,
            'description' => $d,
        ];
    }

    private function ramlDefine($class, $namespace = '') {
        if ( $class == "Illuminate\Http\Response" ) {
            return false;
        }
        if ( isset($this->params['types'][$class]) ) {
            return $this->params['types'][$class];
        }
        if ( isset($this->params['types'][$namespace.'\\'.$class]) ) {
            return $this->params['types'][$namespace.'\\'.$class];
        }
        $class = trim($class,'\\');
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\Exception $ex) {
            // Try again but add current namespace
            try {
                $class = $namespace.'\\'.$class;
                $reflection = new \ReflectionClass($class);
            } catch (\Exception $ex) {
                $this->errors['reflect '.$class] = 'Failed to reflect type: '.$ex->getMessage();
                return false;
            }
        }
        $doc = $reflection->getDocComment();
        $doc = preg_replace("/^([^\*\/]*)(\/\*\*| \*|(\**)\*\/)/m", "", $doc, -1); // Remove the comment chars
        $doc = explode('@', $doc);

        $type = [
            'name' => $class,
            'type' => 'object',
            'description' => trim($doc[0]),
        ];

        if ( $reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class) ) {
            $instance = $reflection->newInstance();

            $attributes = self::getAllColumnsNamesFromTable($instance->getTable());
            foreach($attributes as $name => $t) {
                $type['properties'] = $type['properties'] ?? [];
                $type['properties'][$name] = $this->dbTypeToType($t['type']) + [
                    'description' => 'Database type is '.DB::connection()->getConfig('driver').'/'.$t['type'],
                    'required' => !$t['required'],
                ];
            }

            try {
                $type['example'] = $instance->fake();

                if ( isset($type['properties']['created_at']) ) {
                    $dates = [Fake::dateTime(), Fake::dateTime(), Fake::dateTime()];
                    sort($dates);

                    $type['example']['created_at'] = $dates[0];
                    $type['example']['updated_at'] = $dates[1];
                    if ( isset($type['properties']['deleted_at']) ) 
                        $type['example']['deleted_at'] = Fake::randomElement([$dates[2], null]);
                }
            } catch (\Exception $ex) {
            
            }

            $this->params['types'][$class] = $type;
            return $type;
        }
        if ( $reflection->isSubclassOf(\DreamHack\SDK\Http\Responses\Response::class) ) {
            $instance = $reflection->newInstance();
            if ( isset($instance->mime) )
                $type['schema'] = $instance->mime;
    
            foreach($doc as $line) {
                $line = preg_split('/\s/', trim($line), 2);
                switch($line[0]) {
                case 'example':
                    $example = $example ?? [];
                    $example[] = $line[1];
                    break;
                case 'property':
                    $line = preg_split('/\s+/', trim($line[1]), 3);
                    $is_array = strstr($line[0], '[]') !== false;
                    $is_optional = strstr($line[1], '?') !== false;
                    $t = trim($line[0], '[]');


                    $name = trim($line[1], '$?');
                    $type['properties'] = $type['properties'] ?? [];
                    $typedef = $this->ramlType($t, $reflection->getNamespaceName());

                    if ( $is_array ) {
                        $type['properties'][$name] = [
                            'type' => 'array',
                            'items' => $typedef['name'],
                            'required' => !$is_optional,
                        ];
                    } else {
                        $type['properties'][$name] = [
                            'type' => $typedef['name'],
                            'required' => !$is_optional,
                        ];
                    }
                    //$type['properties'][$name] = [
                        //'type' => $typedef['name'],
                        //'required' => !$is_optional,
                    //];
                    if ( isset($line[2]) ) 
                        $type['properties'][$name]['description'] = $line[2];
                    break;
                }
            }

            if ( isset($example) ) {
                $type['example'] = implode("\n",$example);
            }

            if ( is_callable([$instance, 'fake']) ) {
                $type['example'] = $instance->fake();
            }

            $this->params['types'][$class] = $type;
            return $type;
        }

        //$props = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        //foreach($props as $prop) {
            //if (preg_match('/@var\s+([^\s]+)/', $prop->getDocComment(), $matches)) {
                //$type['properties'][$prop->getName()] = [
                    //'type' => $matches[1],
                    //'description' => $prop->getName(),
                //];
            //} else {
                //$type['properties'][$prop->getName()] = [
                    //'type' => 'unknown',
                    //'description' => $prop->getName(),
                //];    
            //}
        //}
        $this->errors['no match '.$class] = 'Type does not match any enabled type';
        return false;
    }

    private function dbTypeToType( $type ) {
        $dbtype = explode('(', $type, 2);

        /**
         * Available RAML types:
         * time-only
         * datetime
         * datetime-only
         * date-only
         * number/integer
         * boolean
         * string
         * null
         * file
         * array
         * object
         * any
         */

        switch($dbtype[0]) {
        case 'char':
        case 'varchar':
        case 'tinytext':
        case 'mediumtext':
        case 'longtext':
        case 'text':
        case 'enum':
            if ( isset($dbtype[1]) )
                return [
                    'type' => 'string',
                    'maxLength' => intval($dbtype[1]),
                ];

            return ['type' => 'string'];
        case 'tinyint':
        case 'smallint':
        case 'mediumint':
        case 'int':
        case 'integer':
        case 'bigint':
        case 'decimal':
        case 'dec':
        case 'float':
        case 'double':
        case 'year':
            if ( isset($dbtype[1]) )
                return [
                    'type' => 'numeric',
                    'maximum' => intval($dbtype[1]),
                ];

            return ['type' => 'numeric'];
        case 'date':
            return ['type' => 'date-only'];
        case 'datetime':
        case 'timestamp':
            return ['type' => 'datetime'];
        case 'time':
            return ['type' => 'time-only'];
        case 'binary':
        case 'varbinary':
        case 'blob':
        case 'tinyblob':
        case 'mediumblob':
        case 'longblob':
            return ['type' => 'any'];
        }

        $this->errors['type '.$type] = 'Unknown database type';
        return ['type' => 'any'];
    }

    public static function getAllColumnsNamesFromTable( $table ) {
        switch (DB::connection()->getConfig('driver')) {
            //case 'pgsql':
                //$query = "SELECT column_name FROM information_schema.columns WHERE table_name = '".$this->getTable()."'";
                //$column_name = 'column_name';
                //$reverse = true;
                //break;

            case 'mysql':
                $query = 'SHOW COLUMNS FROM '.$table;
                $column_name = 'Field';
                $column_type = 'Type';
                $column_nullable = 'Null';
                $column_default = 'Default';
                $reverse = false;
                break;

            //case 'sqlsrv':
                //$parts = explode('.', $this->getTable());
                //$num = (count($parts) - 1);
                //$table = $parts[$num];
                //$query = "SELECT column_name FROM ".DB::connection()->getConfig('database').".INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'".$table."'";
                //$column_name = 'column_name';
                //$reverse = false;
                //break;

            default: 
                $error = 'Database driver not supported: '.DB::connection()->getConfig('driver');
                throw new Exception($error);
                break;
        }

        $columns = array();

        foreach(DB::select($query) as $column)
        {
            $columns[$column->$column_name] = [
                'type' => $column->$column_type,
                'required' => $column->$column_nullable == 'YES' || $column->$column_default !== null,
            ];
        }

        if($reverse)
        {
            $columns = array_reverse($columns);
        }

        return $columns;
    }
    
}
