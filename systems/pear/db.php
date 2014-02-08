<?php

define('DB_OK', 1);

define('DB_ERROR', -1);

define('DB_ERROR_SYNTAX', -2);

define('DB_ERROR_CONSTRAINT', -3);

define('DB_ERROR_NOT_FOUND', -4);

define('DB_ERROR_ALREADY_EXISTS', -5);

define('DB_ERROR_UNSUPPORTED', -6);

define('DB_ERROR_MISMATCH', -7);

define('DB_ERROR_INVALID', -8);

define('DB_ERROR_NOT_CAPABLE', -9);

define('DB_ERROR_TRUNCATED', -10);

define('DB_ERROR_INVALID_NUMBER', -11);

define('DB_ERROR_INVALID_DATE', -12);

define('DB_ERROR_DIVZERO', -13);

define('DB_ERROR_NODBSELECTED', -14);

define('DB_ERROR_CANNOT_CREATE', -15);

define('DB_ERROR_CANNOT_DROP', -17);

define('DB_ERROR_NOSUCHTABLE', -18);

define('DB_ERROR_NOSUCHFIELD', -19);

define('DB_ERROR_NEED_MORE_DATA', -20);

define('DB_ERROR_NOT_LOCKED', -21);

define('DB_ERROR_VALUE_COUNT_ON_ROW', -22);

define('DB_ERROR_INVALID_DSN', -23);

define('DB_ERROR_CONNECT_FAILED', -24);

define('DB_ERROR_EXTENSION_NOT_FOUND',-25);

define('DB_ERROR_ACCESS_VIOLATION', -26);

define('DB_ERROR_NOSUCHDB', -27);

define('DB_ERROR_CONSTRAINT_NOT_NULL',-29);

define('DB_PARAM_SCALAR', 1);

define('DB_PARAM_OPAQUE', 2);

define('DB_PARAM_MISC',   3);

define('DB_BINMODE_PASSTHRU', 1);

define('DB_BINMODE_RETURN', 2);

define('DB_BINMODE_CONVERT', 3);


define('DB_FETCHMODE_DEFAULT', 0);

define('DB_FETCHMODE_ORDERED', 1);

define('DB_FETCHMODE_ASSOC', 2);

define('DB_FETCHMODE_OBJECT', 3);

define('DB_FETCHMODE_FLIPPED', 4);

define('DB_GETMODE_ORDERED', DB_FETCHMODE_ORDERED);
define('DB_GETMODE_ASSOC',   DB_FETCHMODE_ASSOC);
define('DB_GETMODE_FLIPPED', DB_FETCHMODE_FLIPPED);

define('DB_TABLEINFO_ORDER', 1);
define('DB_TABLEINFO_ORDERTABLE', 2);
define('DB_TABLEINFO_FULL', 3);

define('DB_AUTOQUERY_INSERT', 1);
define('DB_AUTOQUERY_UPDATE', 2);

define('DB_PORTABILITY_NONE', 0);

define('DB_PORTABILITY_LOWERCASE', 1);

define('DB_PORTABILITY_RTRIM', 2);

define('DB_PORTABILITY_DELETE_COUNT', 4);

define('DB_PORTABILITY_NUMROWS', 8);

define('DB_PORTABILITY_ERRORS', 16);

define('DB_PORTABILITY_NULL_TO_EMPTY', 32);

define('DB_PORTABILITY_ALL', 63);

class DB
{
    function &factory($type, $options = false)
    {
        if (!is_array($options)) {
            $options = array('persistent' => $options);
        }

        if (isset($options['debug']) && $options['debug'] >= 2) {
            // expose php errors with sufficient debug level
            include_once "DB/{$type}.php";
        } else {
            @include_once "DB/{$type}.php";
        }

        $classname = "DB_${type}";

        if (!class_exists($classname)) {
            $tmp = PEAR::raiseError(null, DB_ERROR_NOT_FOUND, null, null,
                                    "Unable to include the DB/{$type}.php"
                                    . " file for '$dsn'",
                                    'DB_Error', true);
            return $tmp;
        }

        @$obj =& new $classname;

        foreach ($options as $option => $value) {
            $test = $obj->setOption($option, $value);
            if (DB::isError($test)) {
                return $test;
            }
        }

        return $obj;
    }

    function &connect($dsn, $options = array())
    {
        $dsninfo = DB::parseDSN($dsn);
        $type = $dsninfo['phptype'];

        if (!is_array($options)) {
            $options = array('persistent' => $options);
        }

        if (isset($options['debug']) && $options['debug'] >= 2) {
            include_once "db/${type}.php";
        } else {
            @include_once "db/${type}.php";
        }

        $classname = "DB_${type}";
        if (!class_exists($classname)) {
            $tmp = PEAR::raiseError(null, DB_ERROR_NOT_FOUND, null, null,
                                    "Unable to include the DB/{$type}.php"
                                    . " file for '$dsn'",
                                    'DB_Error', true);
            return $tmp;
        }

        @$obj =& new $classname;

        foreach ($options as $option => $value) {
            $test = $obj->setOption($option, $value);
            if (DB::isError($test)) {
                return $test;
            }
        }

        $err = $obj->connect($dsninfo, $obj->getOption('persistent'));
        if (DB::isError($err)) {
            $err->addUserInfo($dsn);
            return $err;
        }

        return $obj;
    }

    function apiVersion()
    {
        return '@package_version@';
    }

    function isError($value)
    {
        return is_a($value, 'DB_Error');
    }


    function isConnection($value)
    {
        return (is_object($value) &&
                is_subclass_of($value, 'db_common') &&
                method_exists($value, 'simpleQuery'));
    }

    function isManip($query)
    {
        $manips = 'INSERT|UPDATE|DELETE|REPLACE|'
                . 'CREATE|DROP|'
                . 'LOAD DATA|SELECT .* INTO|COPY|'
                . 'ALTER|GRANT|REVOKE|'
                . 'LOCK|UNLOCK';
        if (preg_match('/^\s*"?(' . $manips . ')\s+/i', $query)) {
            return true;
        }
        return false;
    }

 
    function errorMessage($value)
    {
        static $errorMessages;
        if (!isset($errorMessages)) {
            $errorMessages = array(
                DB_ERROR                    => 'unknown error',
                DB_ERROR_ACCESS_VIOLATION   => 'insufficient permissions',
                DB_ERROR_ALREADY_EXISTS     => 'already exists',
                DB_ERROR_CANNOT_CREATE      => 'can not create',
                DB_ERROR_CANNOT_DROP        => 'can not drop',
                DB_ERROR_CONNECT_FAILED     => 'connect failed',
                DB_ERROR_CONSTRAINT         => 'constraint violation',
                DB_ERROR_CONSTRAINT_NOT_NULL=> 'null value violates not-null constraint',
                DB_ERROR_DIVZERO            => 'division by zero',
                DB_ERROR_EXTENSION_NOT_FOUND=> 'extension not found',
                DB_ERROR_INVALID            => 'invalid',
                DB_ERROR_INVALID_DATE       => 'invalid date or time',
                DB_ERROR_INVALID_DSN        => 'invalid DSN',
                DB_ERROR_INVALID_NUMBER     => 'invalid number',
                DB_ERROR_MISMATCH           => 'mismatch',
                DB_ERROR_NEED_MORE_DATA     => 'insufficient data supplied',
                DB_ERROR_NODBSELECTED       => 'no database selected',
                DB_ERROR_NOSUCHDB           => 'no such database',
                DB_ERROR_NOSUCHFIELD        => 'no such field',
                DB_ERROR_NOSUCHTABLE        => 'no such table',
                DB_ERROR_NOT_CAPABLE        => 'DB backend not capable',
                DB_ERROR_NOT_FOUND          => 'not found',
                DB_ERROR_NOT_LOCKED         => 'not locked',
                DB_ERROR_SYNTAX             => 'syntax error',
                DB_ERROR_UNSUPPORTED        => 'not supported',
                DB_ERROR_TRUNCATED          => 'truncated',
                DB_ERROR_VALUE_COUNT_ON_ROW => 'value count on row',
                DB_OK                       => 'no error',
            );
        }

        if (DB::isError($value)) {
            $value = $value->getCode();
        }

        return isset($errorMessages[$value]) ? $errorMessages[$value]
                     : $errorMessages[DB_ERROR];
    }


    function parseDSN($dsn)
    {
        $parsed = array(
            'phptype'  => false,
            'dbsyntax' => false,
            'username' => false,
            'password' => false,
            'protocol' => false,
            'hostspec' => false,
            'port'     => false,
            'socket'   => false,
            'database' => false,
        );

        if (is_array($dsn)) {
            $dsn = array_merge($parsed, $dsn);
            if (!$dsn['dbsyntax']) {
                $dsn['dbsyntax'] = $dsn['phptype'];
            }
            return $dsn;
        }

        // Find phptype and dbsyntax
        if (($pos = strpos($dsn, '://')) !== false) {
            $str = substr($dsn, 0, $pos);
            $dsn = substr($dsn, $pos + 3);
        } else {
            $str = $dsn;
            $dsn = null;
        }

        if (preg_match('|^(.+?)\((.*?)\)$|', $str, $arr)) {
            $parsed['phptype']  = $arr[1];
            $parsed['dbsyntax'] = !$arr[2] ? $arr[1] : $arr[2];
        } else {
            $parsed['phptype']  = $str;
            $parsed['dbsyntax'] = $str;
        }

        if (!count($dsn)) {
            return $parsed;
        }

        if (($at = strrpos($dsn,'@')) !== false) {
            $str = substr($dsn, 0, $at);
            $dsn = substr($dsn, $at + 1);
            if (($pos = strpos($str, ':')) !== false) {
                $parsed['username'] = rawurldecode(substr($str, 0, $pos));
                $parsed['password'] = rawurldecode(substr($str, $pos + 1));
            } else {
                $parsed['username'] = rawurldecode($str);
            }
        }

        // Find protocol and hostspec

        if (preg_match('|^([^(]+)\((.*?)\)/?(.*?)$|', $dsn, $match)) {
            // $dsn => proto(proto_opts)/database
            $proto       = $match[1];
            $proto_opts  = $match[2] ? $match[2] : false;
            $dsn         = $match[3];

        } else {
            // $dsn => protocol+hostspec/database (old format)
            if (strpos($dsn, '+') !== false) {
                list($proto, $dsn) = explode('+', $dsn, 2);
            }
            if (strpos($dsn, '/') !== false) {
                list($proto_opts, $dsn) = explode('/', $dsn, 2);
            } else {
                $proto_opts = $dsn;
                $dsn = null;
            }
        }

        // process the different protocol options
        $parsed['protocol'] = (!empty($proto)) ? $proto : 'tcp';
        $proto_opts = rawurldecode($proto_opts);
        if ($parsed['protocol'] == 'tcp') {
            if (strpos($proto_opts, ':') !== false) {
                list($parsed['hostspec'],
                     $parsed['port']) = explode(':', $proto_opts);
            } else {
                $parsed['hostspec'] = $proto_opts;
            }
        } elseif ($parsed['protocol'] == 'unix') {
            $parsed['socket'] = $proto_opts;
        }

        if ($dsn) {
            if (($pos = strpos($dsn, '?')) === false) {
                // /database
                $parsed['database'] = rawurldecode($dsn);
            } else {
                // /database?param1=value1&param2=value2
                $parsed['database'] = rawurldecode(substr($dsn, 0, $pos));
                $dsn = substr($dsn, $pos + 1);
                if (strpos($dsn, '&') !== false) {
                    $opts = explode('&', $dsn);
                } else { // database?param1=value1
                    $opts = array($dsn);
                }
                foreach ($opts as $opt) {
                    list($key, $value) = explode('=', $opt);
                    if (!isset($parsed[$key])) {
                        // don't allow params overwrite
                        $parsed[$key] = rawurldecode($value);
                    }
                }
            }
        }

        return $parsed;
    }
}

class DB_Error extends PEAR_Error
{
    function DB_Error($code = DB_ERROR, $mode = PEAR_ERROR_RETURN,
                      $level = E_USER_NOTICE, $debuginfo = null)
    {
        if (is_int($code)) {
            $this->PEAR_Error('DB Error: ' . DB::errorMessage($code), $code,
                              $mode, $level, $debuginfo);
        } else {
            $this->PEAR_Error("DB Error: $code", DB_ERROR,
                              $mode, $level, $debuginfo);
        }
    }
}

class DB_result
{
    var $autofree;

    var $dbh;

    var $fetchmode;

    var $fetchmode_object_class;

    var $limit_count = null;

    var $limit_from = null;

    var $parameters;

    var $query;

    var $result;
	
	var $row_counter = null;

    var $statement;

    function DB_result(&$dbh, $result, $options = array())
    {
        $this->autofree    = $dbh->options['autofree'];
        $this->dbh         = &$dbh;
        $this->fetchmode   = $dbh->fetchmode;
        $this->fetchmode_object_class = $dbh->fetchmode_object_class;
        $this->parameters  = $dbh->last_parameters;
        $this->query       = $dbh->last_query;
        $this->result      = $result;
        $this->statement   = empty($dbh->last_stmt) ? null : $dbh->last_stmt;
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
    }

    function setOption($key, $value = null)
    {
        switch ($key) {
            case 'limit_from':
                $this->limit_from = $value;
                break;
            case 'limit_count':
                $this->limit_count = $value;
        }
    }

    function &fetchRow($fetchmode = DB_FETCHMODE_DEFAULT, $rownum = null)
    {
        if ($fetchmode === DB_FETCHMODE_DEFAULT) {
            $fetchmode = $this->fetchmode;
        }
        if ($fetchmode === DB_FETCHMODE_OBJECT) {
            $fetchmode = DB_FETCHMODE_ASSOC;
            $object_class = $this->fetchmode_object_class;
        }
        if ($this->limit_from !== null) {
            if ($this->row_counter === null) {
                $this->row_counter = $this->limit_from;
                // Skip rows
                if ($this->dbh->features['limit'] === false) {
                    $i = 0;
                    while ($i++ < $this->limit_from) {
                        $this->dbh->fetchInto($this->result, $arr, $fetchmode);
                    }
                }
            }
            if ($this->row_counter >= ($this->limit_from + $this->limit_count))
            {
                if ($this->autofree) {
                    $this->free();
                }
                $tmp = null;
                return $tmp;
            }
            if ($this->dbh->features['limit'] === 'emulate') {
                $rownum = $this->row_counter;
            }
            $this->row_counter++;
        }
        $res = $this->dbh->fetchInto($this->result, $arr, $fetchmode, $rownum);
        if ($res === DB_OK) {
            if (isset($object_class)) {
                // The default mode is specified in the
                // DB_common::fetchmode_object_class property
                if ($object_class == 'stdClass') {
                    $arr = (object) $arr;
                } else {
                    $arr = &new $object_class($arr);
                }
            }
            return $arr;
        }
        if ($res == null && $this->autofree) {
            $this->free();
        }
        return $res;
    }

    
    function fetchInto(&$arr, $fetchmode = DB_FETCHMODE_DEFAULT, $rownum = null)
    {
        if ($fetchmode === DB_FETCHMODE_DEFAULT) {
            $fetchmode = $this->fetchmode;
        }
        if ($fetchmode === DB_FETCHMODE_OBJECT) {
            $fetchmode = DB_FETCHMODE_ASSOC;
            $object_class = $this->fetchmode_object_class;
        }
        if ($this->limit_from !== null) {
            if ($this->row_counter === null) {
                $this->row_counter = $this->limit_from;
                // Skip rows
                if ($this->dbh->features['limit'] === false) {
                    $i = 0;
                    while ($i++ < $this->limit_from) {
                        $this->dbh->fetchInto($this->result, $arr, $fetchmode);
                    }
                }
            }
            if ($this->row_counter >= (
                    $this->limit_from + $this->limit_count))
            {
                if ($this->autofree) {
                    $this->free();
                }
                return null;
            }
            if ($this->dbh->features['limit'] === 'emulate') {
                $rownum = $this->row_counter;
            }

            $this->row_counter++;
        }
        $res = $this->dbh->fetchInto($this->result, $arr, $fetchmode, $rownum);
        if ($res === DB_OK) {
            if (isset($object_class)) {
                // default mode specified in the
                // DB_common::fetchmode_object_class property
                if ($object_class == 'stdClass') {
                    $arr = (object) $arr;
                } else {
                    $arr = new $object_class($arr);
                }
            }
            return DB_OK;
        }
        if ($res == null && $this->autofree) {
            $this->free();
        }
        return $res;
    }

    function numCols()
    {
        return $this->dbh->numCols($this->result);
    }

    function numRows()
    {
        if ($this->dbh->features['numrows'] === 'emulate'
            && $this->dbh->options['portability'] & DB_PORTABILITY_NUMROWS)
        {
            if ($this->dbh->features['prepare']) {
                $res = $this->dbh->query($this->query, $this->parameters);
            } else {
                $res = $this->dbh->query($this->query);
            }
            if (DB::isError($res)) {
                return $res;
            }
            $i = 0;
            while ($res->fetchInto($tmp, DB_FETCHMODE_ORDERED)) {
                $i++;
            }
            return $i;
        } else {
            return $this->dbh->numRows($this->result);
        }
    }

    function nextResult()
    {
        return $this->dbh->nextResult($this->result);
    }

    function free()
    {
        $err = $this->dbh->freeResult($this->result);
        if (DB::isError($err)) {
            return $err;
        }
        $this->result = false;
        $this->statement = false;
        return true;
    }

    function tableInfo($mode = null)
    {
        if (is_string($mode)) {
            return $this->dbh->raiseError(DB_ERROR_NEED_MORE_DATA);
        }
        return $this->dbh->tableInfo($this, $mode);
    }

   function getQuery()
    {
        return $this->query;
    }

    function getRowCounter()
    {
        return $this->row_counter;
    }
}

class DB_row
{
    function DB_row(&$arr)
    {
        foreach ($arr as $key => $value) {
            $this->$key = &$arr[$key];
        }
    }
}

?>
