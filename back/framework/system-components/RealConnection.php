<?php

namespace Component;

use Exception;

class RealConnection
{
    private $_dbConfig;

    public function init($dbConfig)
    {
        $this->_dbConfig = $dbConfig;
    }

    public function create($db = "default")
    {
        if ($this->_dbConfig === null) {
            throw new Exception("RealConnectionFactory did not configure", 1);
        }

        $dbConfig = $this->_dbConfig["default"];

        if (isset($this->_dbConfig[$db])) {
            $dbConfig = $this->_dbConfig[$db];
        }

        $link = mysqli_init();
        mysqli_options($link, MYSQLI_OPT_LOCAL_INFILE, true);
        mysqli_real_connect($link,
            $dbConfig["host"],
            $dbConfig["user"],
            $dbConfig["password"],
            $dbConfig["dbname"]
        );

        $link->select_db($dbConfig["dbname"]);
        $link->set_charset($dbConfig["charset"]);

        if (mysqli_connect_errno()) {
            throw new Exception("Mysqli connection error " . mysqli_connect_error(), 1);
        }

        return $link;
    }

    public function destroy($link)
    {
        if (method_exists ($link, "close")) {
            $link->close();
        }
    }

    public function drop($link, $table)
    {
        $table = mysqli_real_escape_string ($link , $table);
        $query = "DROP TABLE `".$table."`;";
        $result = $link->query($query);

        if (!$result->fetch_array()) {
            return null;
        }

        return $table;
    }

    public function loadFile($tableName, $file, $db = 'flights')
    {
        $link = $this->create($db);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $file = str_replace('\\\\', '/', $file);
            $file = str_replace('\\', '/', $file);
        }

        $query = "LOAD DATA LOCAL INFILE '".$file."' INTO TABLE `".$tableName."` FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n';";
        $link->query($query);

        $this->destroy($link);

        unset($c);
    }
}