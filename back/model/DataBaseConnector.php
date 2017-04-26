<?php
//UPDATE `bur4-1-07-01_bp` SET `color` = LEFT(MD5(UUID()), 6)

namespace Model;

use Exception;

class DataBaseConnector
{
    private $host = 'localhost';
    private $type = 'mysqli';
    private $user = '';
    private $pass = '';
    private $dbName = '';

    private $link;

    protected $glob;

    public function __construct() {
        global $CONFIG;

        if (!isset($CONFIG)) {
            throw new Exception("Configurations is not set", 1);
        }

        $this->host = $CONFIG['db']['host'];
        $this->type = $CONFIG['db']['type'];
        $this->user = $CONFIG['db']['user'];
        $this->pass = $CONFIG['db']['pass'];
        $this->dbName = $CONFIG['db']['dbName'];
    }

    // Connection function
    public function Connect()
    {
        $this->link = mysqli_init();
        mysqli_options($this->link, MYSQLI_OPT_LOCAL_INFILE, true);
        mysqli_real_connect($this->link, $this->host, $this->user, $this->pass, $this->dbName);
        $this->link->select_db($this->dbName);
        $this->link->set_charset("utf8");

        if (mysqli_connect_errno()) {
            return mysqli_connect_error();
        } else {
            return $this->link;
        }
    }

    public function TurnOffAutoCommit()
    {
        $this->link->autocommit(FALSE);
    }

    public function TurnOnAutoCommit()
    {
        $this->link->autocommit(TRUE);
    }

    public function Disconnect()
    {
        $this->link->close();
    }

    public function ExportTable($tableName, $fileName, $root)
    {
        $link = $this->Connect();

        $exportedFileName['dir'] = $root;
        $exportedFileName['tmp'] = sys_get_temp_dir().DIRECTORY_SEPARATOR.$fileName.".csv";
        $exportedFileName['root'] = $root.DIRECTORY_SEPARATOR.$fileName.".csv";
        $exportedFileName['filename'] = $fileName.".csv";

        /*GRANT FILE ON *.* TO 'dbUser'@'localhost'*/

        $query = "SELECT * FROM `".$tableName."`"
            ." INTO OUTFILE '".$exportedFileName['tmp']."'"
            ." FIELDS TERMINATED BY ','"
            ." LINES TERMINATED BY ';';";

        $result = $link->query($query);
        $this->Disconnect();

        if (file_exists($exportedFileName['tmp'])) {
            try {
                $status = copy($exportedFileName['tmp'], $exportedFileName['root']);
                //unlink($exportedFileName['tmp']);
            } catch(Exception $e) { }
        }

        return $exportedFileName;
    }

    public function checkTableExist($tableName)
    {
        $link = $this->Connect();

        $query = "SHOW TABLES LIKE '".$tableName."';";

        $result = $link->query($query);
        $this->Disconnect();

        if (!$result->fetch_array()) {
            return false;
        }

        return true;
    }
}
