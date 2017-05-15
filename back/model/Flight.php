<?php

namespace Model;

class Flight
{
    public function CreateFlightTable()
    {
        $query = "SHOW TABLES LIKE 'flights';";
        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);
        if(!$result->fetch_array())
        {
            $query = "CREATE TABLE `flights` (`id` BIGINT NOT NULL AUTO_INCREMENT,
                `bort` VARCHAR(255),
                `voyage` VARCHAR(255),
                `startCopyTime` BIGINT(20),
                `uploadingCopyTime` BIGINT(20),
                `performer` VARCHAR(255),
                `bruType` VARCHAR(255),
                `departureAirport` VARCHAR(255),
                `arrivalAirport` VARCHAR(255),
                `flightAditionalInfo` TEXT,
                `fileName` VARCHAR(255),
                `apTableName` VARCHAR(20),
                `bpTableName` VARCHAR(20),
                `exTableName` VARCHAR(20),
                PRIMARY KEY (`id`)) " .
                "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $stmt = $link->prepare($query);
            if (!$stmt->execute()) {
                echo('Error during query execution ' . $query);
                error_log('Error during query execution ' . $query);
            }
        }
        $c->Disconnect();
        unset($c);
    }

    public function GetFlightInfo($extFlightId)
    {
        $c = new DataBaseConnector;
        $link = $c->Connect();

        $flightId = $extFlightId;

        $query = "SELECT * FROM `flights` WHERE id = '".$flightId."' LIMIT 1;";

        $result = $link->query($query);
        $flightInfo = array();

        if($row = $result->fetch_array()) {
            foreach ($row as $key => $value) {
                if(($key === 'flightAditionalInfo')
                    && ($value !== null)
                ) {
                    $flightInfo = array_merge($flightInfo,
                        json_decode($value, true)
                    );

                }
                $flightInfo[$key] = $value;
            }
        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $flightInfo;
    }

    public function GetFlights($extAvailableFlightIds, $extOrderName = 'id', $extOrderType = 'ASC')
    {
        $availableFlightIds = $extAvailableFlightIds;
        $orderName = $extOrderName;
        $orderType = $extOrderType;

        $listFlights = array();
        if(count($availableFlightIds) > 0)
        {
            $inString = "";
            foreach($availableFlightIds as $id)
            {
                $inString .= "'" . $id ."',";
            }

            $inString = substr($inString, 0, -1);

            $c = new DataBaseConnector;
            $link = $c->Connect();

            $query = "SELECT * FROM `flights` WHERE `id` IN (".$inString.") ORDER BY `".$orderName."` ".$orderType.";";
            $mySqliSelectFlightsResult = $link->query($query);//, MYSQLI_USE_RESULT);

            while($row = $mySqliSelectFlightsResult->fetch_array())
            {
                $flight = $this->GetFlightInfo($row['id']);
                array_push($listFlights, $flight);
            }
            $mySqliSelectFlightsResult->free();
            $c->Disconnect();

            unset($c);
        }

        return $listFlights;
    }

    public function GetAllFlightIds()
    {
        $listFlights = [];

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `id` FROM `flights` WHERE 1;";
        $mySqliSelectFlightsResult = $link->query($query);

        while($row = $mySqliSelectFlightsResult->fetch_array())
        {
            array_push($listFlights, $row['id']);
        }
        $mySqliSelectFlightsResult->free();
        $c->Disconnect();

        unset($c);

        return $listFlights;
    }

    public function GetFlightsByAuthor($extAuthor)
    {
        $author = $extAuthor;

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "SELECT `id` FROM `flights` WHERE `author` = '".$author."';";
        $mySqliResult = $link->query($query);//, MYSQLI_USE_RESULT);

        $list = array();
        while($row = $mySqliResult->fetch_array())
        {
            $item = $this->GetFlightInfo($row['id']);
            array_push($list, $item);
        }
        $mySqliResult->free();
        $c->Disconnect();

        unset($c);

        return $list;
    }

    public function GetFlightsByFolder($extFolder, $extAvailableFlightIds)
    {
        $folder = $extFolder;
        $availableFlightIds = $extAvailableFlightIds;

        $listFlights = array();
        if(count($availableFlightIds) > 0)
        {
            $inString = "";
            foreach($availableFlightIds as $id)
            {
                $inString .= "'" . $id ."',";
            }

            $inString = substr($inString, 0, -1);

            $query = "SELECT `id` FROM `flights` WHERE `id` IN (".$inString.") " .
                    "AND `folder` = " .$folder. " " .
                    "ORDER BY `id`;";

            $c = new DataBaseConnector;
            $link = $c->Connect();

            $mySqliSelectFlightsResult = $link->query($query);//, MYSQLI_USE_RESULT);

            while($row = $mySqliSelectFlightsResult->fetch_array())
            {
                $flight = $this->GetFlightInfo($row['id']);
                array_push($listFlights, $flight);
            }
            $mySqliSelectFlightsResult->free();
            $c->Disconnect();

            unset($c);
        }

        return $listFlights;
    }

    public function PrepareFlightsList($extAvailableFlightIds)
    {
        $availableFlightIds = $extAvailableFlightIds;

        $listFlights = (array)$this->GetFlights($availableFlightIds);
        $i = 0;
        $flightsListInfo = array();

        while($i < count($listFlights))
        {
            $flight = (array)$listFlights[$i];
            $flightInfo = $flight;

            $flightInfo['exceptionsSearchPerformed'] = false;
            if($flight['exTableName'] != "")
            {
                $flightInfo['exceptionsSearchPerformed'] = true;
            }

            $flightInfo['cellNum'] = $flight['id'];
            $flightInfo['uploadDate'] = date('H:i:s Y-m-d', $flight['uploadingCopyTime']);
            $flightInfo['flightDate'] = date('H:i:s Y-m-d', $flight['startCopyTime']);

            $i++;
            array_push($flightsListInfo, $flightInfo);
        }
        return $flightsListInfo;
    }

    public function InsertNewFlight($bort,
            $voyage,
            $startCopyTime,
            $fdrId,
            $bruType,
            $performer,
            $departureAirport,
            $arrivalAirport,
            $uploadedFile,
            $extAditionalInfo,
            $userId
    ) {

        $uploadingCopyTime = time();
        $aditionalInfo = "";
        if(($extAditionalInfo !== null) &&
            ($extAditionalInfo !== false))  {
            $aditionalInfo = strval($extAditionalInfo);
        }

        $tableName = "_".uniqid();
        $tableNameAp = $tableName."_ap";
        $tableNameBp = $tableName."_bp";
        $exTableName = '';
        $paramsTables = array("tableNameAp" => $tableNameAp, "tableNameBp" => $tableNameBp);

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "INSERT INTO `flights` (`bort`,
                `voyage`,
                `startCopyTime`,
                `uploadingCopyTime`,
                `performer`,
                `id_fdr`,
                `brutype`,
                `departureAirport`,
                `arrivalAirport`,
                `flightAditionalInfo`,
                `fileName`,
                `guid`,
                `apTableName`,
                `bpTableName`,
                `exTableName`,
                `id_user`)
                VALUES ('".$bort."',
                        '".$voyage."',
                        ".$startCopyTime.",
                        ".$uploadingCopyTime.",
                        '".$performer."',
                        '".$fdrId."',
                        '".$bruType."',
                        '".$departureAirport."',
                        '".$arrivalAirport."',
                        '".$aditionalInfo."',
                        '".$uploadedFile."',
                        '".$tableName."',
                        '".$tableNameAp."',
                        '".$tableNameBp."',
                        '".$exTableName."',
                        '".$userId."');";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $query = "SELECT LAST_INSERT_ID();";
        $result = $link->query($query);
        $row = $result->fetch_array();
        $flightId = $row["LAST_INSERT_ID()"];

        $c->Disconnect();
        unset($c);

        return $flightId;
    }

    public function CreateFlightParamTables($extFlightId, $extCycloAp, $extCycloBp)
    {
        $flightId = $extFlightId;
        $cycloAp = $extCycloAp;
        $cycloBp = $extCycloBp;

        $flightInfo = $this->GetFlightInfo($flightId);
        $tableNameAp = $flightInfo["apTableName"];
        $tableNameBp = $flightInfo["bpTableName"];
        $apTables = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();
        foreach($cycloAp as $prefix => $prefixCyclo)
        {
            array_push($apTables, $tableNameAp."_".$prefix);
            $query = "CREATE TABLE `".$tableNameAp."_".$prefix."` (`frameNum` MEDIUMINT, `time` BIGINT";

            for($i = 0; $i < count($prefixCyclo); $i++) {
                $query .= ", `".$prefixCyclo[$i]["code"]."` FLOAT(7,2)";
            }

            $query .= ", PRIMARY KEY (`frameNum`, `time`)) " .
                    "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $stmt = $link->prepare($query);
            $stmt->execute();
        }

        foreach($cycloBp as $prefix => $prefixCyclo)
        {
            $query = "CREATE TABLE `".$tableNameBp."_".$prefix."` (`frameNum` MEDIUMINT, `time` BIGINT, `code` varchar(255)) " .
                "DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $stmt = $link->prepare($query);
            $stmt->execute();
        }

        $stmt->close();

        $c->Disconnect();
        unset($c);

        return $apTables;
    }

    public function UpdateFlightInfo($extFlightId, $extFlightInfo)
    {
        $flightId = $extFlightId;
        $flightInfo = $extFlightInfo;
        foreach($flightInfo as $key => $value) {
            $c = new DataBaseConnector;
            $link = $c->Connect();

            $query = "UPDATE `flights` SET `".
                            $key."`='".$value."'
                            WHERE id='".$flightId."';";
            $stmt = $link->prepare($query);
            $stmt->execute();
            $stmt->close();

            $c->Disconnect();
            unset($c);
        }
    }

    public function GetFlightsByFilter($filter)
    {
        $query = "SELECT `id` FROM `flights` WHERE ";

        foreach ($filter as $key => $val) {
            if($key === 'from') {
                $query .= "(`startCopyTime` > ".$val.") AND ";
            } else if($key === 'to') {
                $query .= "(`startCopyTime` < ".$val.") AND ";
            } else {
                $query .= "(`".$key."` LIKE '%".$val."%') AND ";
            }
        }

        $query = substr($query, 0, -4);
        $query .= ";";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);

        $arr = [];
        while($row = $result->fetch_array()) {
            $arr[] = $row['id'];
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $arr;
    }

    public function DeleteFlight($flightId, $prefixes)
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Integer is required. Passed: "
                . json_encode($flightId), 1);
        }

        $flightInfo = $this->GetFlightInfo($flightId);
        $file = $flightInfo['fileName'];
        $guid = $flightInfo['guid'];

        $result = array();
        $result['status'] = array();
        $result['query'] = array();

        $c = new DataBaseConnector;
        $link = $c->Connect();

        $query = "DELETE FROM `flights` WHERE id=".$flightId.";";
        $result['query'][] = $query;
        $stmt = $link->prepare($query);
        $result['status'][] = $stmt->execute();
        $stmt->close();

        foreach($prefixes as $item => $prefix)
        {
            $tableName =  $guid . $prefix;
            $query = "SHOW TABLES LIKE '". $tableName ."';";
            $res = $link->query($query);
            if (count($res->fetch_array()))
            {
                $query = "DROP TABLE `". $tableName ."`;";
                $result['query'][] = $query;
                $stmt = $link->prepare($query);
                $result['status'][] = $stmt->execute();
                $stmt->close();
            }
        }

        $c->Disconnect();

        unset($c);

        if(file_exists($file))
        {
            unlink($file);
        }

        if(in_array(false, $result['status']))
        {
            $result['status'] = false;
        }
        else
        {
            $result['status'] = true;
        }

        return $result;
    }

    public function DropTable($extTableName)
    {
        $tableName = $extTableName;

        $c = new DataBaseConnector;

        $query = "DROP TABLE `". $tableName ."`;";

        $link = $c->Connect();
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);
    }

    public function GetMaxFlightId()
    {
        $query = "SELECT MAX(`id`) FROM `flights` WHERE 1;";

        $c = new DataBaseConnector;
        $link = $c->Connect();
        $result = $link->query($query);
        $maxId = 1;
        if($row = $result->fetch_array())
        {
            $maxId = $row['MAX(`id`)'];
        }

        $result->free();
        $c->Disconnect();
        unset($c);

        return $maxId;

    }
}

?>
