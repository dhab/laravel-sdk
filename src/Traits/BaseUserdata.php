<?php

namespace DreamHack\SDK\Traits;

use App\Models\User;
use DB;
use Log;

trait BaseUserdata
{
    /**
     * Replace some placeholders with user data
     *
     * @param string Template string
     * @param User User object
     * @return string
     */
    protected function template($str, $user)
    {
        $str = str_replace('%DHID%', $user->id, $str);
        $str = str_replace('%EMAIL%', $user->email, $str);
        return $str;
    }

    /**
     * Turn an array of WHERE-cases into a single where joined with OR
     *
     * @param string Template string
     * @param User User object
     * @return string
     */
    private function transformWhere($where, $user)
    {
        $vm = $this;

        return join(" OR ", array_map(function ($value) use ($vm, $user) {
              return $vm->template($value, $user);
        }, $where));
    }

    /**
     * Generate a bunch of select queries
     *
     * @param User User object
     * @return array
     */
    protected function getSelectQueries($user)
    {
        $queries = [];

        foreach (config('userdata.tables') as $table => $data) {
            $queries[$table] = sprintf(
                "SELECT %s FROM %s WHERE %s",
                $data['fields'],
                $table,
                $this->transformWhere($data['where'], $user)
            );
        }

        return $queries;
    }

    /**
     * Generate a bunch of delete queries
     *
     * @param User User object
     * @return array
     */
    protected function getDeleteQueries($user)
    {
        $queries = [];

        foreach (config('userdata.tables') as $table => $data) {
            $queries[$table] = sprintf(
                "DELETE FROM %s WHERE %s",
                $table,
                $this->transformWhere($data['where'], $user)
            );
        }

        return $queries;
    }

    /**
     * The delete queries needs to be in a certain order, order them
     *
     * @param array Queries
     * @return array
     */
    protected function orderDeleteQueries($queries)
    {
        $ordered = [];

        foreach ($queries as $table => $data) {
            if ($table !== "users") {
                $ordered[] = [$table => $data];
            }
        }

        if (isset($queries["users"])) {
            $ordered[] = ["users" => $queries["users"]];
        }

        return $ordered;
    }

    /**
     * Run a bunch of select queries
     *
     * @param array Queries
     * @return array
     */
    protected function selectMany($queries)
    {
        $results = [];
        foreach ($queries as $table => $query) {
            $results[$table] = DB::select($query);
        }
        return $results;
    }

    /**
     * Turn objects into arrays, might do other stuff in the future
     *
     * @param array Results
     * @return array
     */
    protected function cleanResults($results)
    {
        $cleaned = [];
        foreach ($results as $table => $rows) {
            foreach ($rows as $foo) {
                $cleaned[$table][] = ((array)$foo);
            }
        }
        return $cleaned;
    }

    /**
     * Load some data from database for caching
     *
     * @param string Column (not used i think)
     * @param array Config
     * @return array
     */
    protected function fillCache($column, $config)
    {
        $ret = [];

        $query = sprintf(
            "SELECT %s, %s FROM %s",
            $config['field'],
            $config['key'],
            $config['table']
        );

        $rows = DB::select($query);

        foreach ($rows as $row) {
            $row = ((array)$row);
            $ret[$row[$config['key']]] = $row[$config['field']];
        }

        return $ret;
    }

    /**
     * Expand IDs to more human readable data.
     * E.g. turn client_id = x into something like:
     * client_id = [ x => actual name of client ]
     *
     * @param array Config
     * @param string Column
     * @param string Value
     * @return array
     */
    protected function explainValue($config, $column, $value)
    {
        static $cache = [];
        if (!isset($cache[$column])) {
            $cache[$column] = $this->fillCache($column, $config);
        }
        return $cache[$column][$value] ? [$value => $cache[$column][$value]] : $value;
    }

    /**
     * Add explanations, see explainValue()
     *
     * @param array Results
     * @return array
     */
    protected function addExplanations($results)
    {
        $return = [];
        $explanations = config('userdata.explanations');
        foreach ($results as $table => $rows) {
            foreach ($rows as $i => $row) {
                foreach ($row as $column => $value) {
                    if (isset($explanations[$column])) {
                        $return[$table][$i][$column] = $this->explainValue($explanations[$column], $column, $value);
                    } else {
                        $return[$table][$i][$column] = $value;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Turn a DELETE query into a SELECT to find number of rows matching
     *
     * @param string Query
     * @return string
     */
    private function deleteToSelectCount($query)
    {
        return str_replace('DELETE ', 'SELECT COUNT(*) AS count ', $query);
    }

    /**
     * Run or simulate deleting a user
     *
     * @param array Queries
     */
    protected function runDeletes($queries, $dryRun)
    {
        $ret = [];

        foreach ($queries as $data) {
            $table = key($data);
            $query = $data[$table];
            if ($dryRun) {
                $res = DB::select($this->deleteToSelectCount($query));
                $rows = $res[0]->count;
                if ($rows) {
                    $ret[$table] = $rows;
                }
            } else {
                DB::delete($query);
            }
        }

        return $ret;
    }

    /**
     * Main export function
     *
     * @param object User with keys (id, email)
     */
    protected function getData($user)
    {
        $selects = $this->getSelectQueries($user);
        $results = $this->selectMany($selects);
        $cleaned = $this->cleanResults($results);
        return $this->addExplanations($cleaned);
    }

    /**
     * Main delete function
     *
     * @param object User with keys (id, email)
     * @param bool Dry run
     */
    protected function deleteData($user, $dryRun)
    {
          $queries = $this->getDeleteQueries($user);
          $ordered = $this->orderDeleteQueries($queries);
          return $this->runDeletes($ordered, $dryRun);
    }
}
