<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class Channel
{
    public static $compressionTypes = [
        'none' => 'none',
        'aroundRange' => 'aroundRange',
        'general' => 'general'
    ];

    public function GetChannel(
        $tableName,
        $code,
        $prefix,
        $startFrame,
        $endFrame,
        $seriesCount,
        $totalFramesCount,
        $compression
    ) {
        $pointPairList = [];

        $divider = ceil($totalFramesCount * $seriesCount / POINT_MAX_COUNT);

        if(($compression === $this::$compressionTypes['none'])
            || !in_array($compression, $this::$compressionTypes)
        ) {
            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE 1 "
                . "ORDER BY `time` ASC";

            $c = new DataBaseConnector();
            $link = $c->Connect();
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }

            $result->free();
            $c->Disconnect();

            unset($c);
        } else if ($compression === $this::$compressionTypes['aroundRange']) {
            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
                ((`frameNum` < ".$startFrame.") AND
                ((`frameNum` % ".$divider.") = 0))
                ORDER BY `time` ASC";

            $c = new DataBaseConnector();
            $link = $c->Connect();
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }
            $result->free();

            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
                ((`frameNum` >= ".$startFrame.") AND
                (`frameNum` <= ".$endFrame."))
                ORDER BY `time` ASC";

            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }
            $result->free();

            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE
                ((`frameNum` > ".$endFrame.") AND
                ((`frameNum` % ".$divider.") = 0))
                ORDER BY `time` ASC";
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }
            $result->free();
            $c->Disconnect();
            unset($c);
        } if(($compression === $this::$compressionTypes['general'])
            || !in_array($compression, $this::$compressionTypes)
        ) {
            $query = "SELECT `time`, `".$code."` FROM `".$tableName."_".$prefix."` WHERE"
                . " (`frameNum` % ".$divider." = 0)"
                . " ORDER BY `time` ASC";

            $c = new DataBaseConnector();
            $link = $c->Connect();
            $result = $link->query($query);

            while($row = $result->fetch_array())
            {
                $point = array($row['time'], $row[$code]);
                $pointPairList[] = $point;
            }

            $result->free();
            $c->Disconnect();

            unset($c);
        }

        return $pointPairList;
    }

    private function GetBinaryChannel($extTableName, $extCode, $extStepLength, $extFreq)
    {
        $tableName = $extTableName;
        $code = $extCode;
        $stepLength = $extStepLength;
        $freq = $extFreq;
        $stepMicroTime = $stepLength / $freq * 1000;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE " .
            "`code` = '".$code."' " .
            "ORDER BY `time` ASC;";

        $result = $link->query($query);

        $pointPairList = array();
        $pointPairList2 = array();

        //if exists though one row in table
        if($row = $result->fetch_array())
        {
            $point = array('null','null');
            $pointPairList[] = $point;
            $currTime = $row['time'];

            $point = array($currTime, 1);
            $pointPairList[] = $point;
            $previousTime = $currTime;

            $pointPairList2[] = $point;

            //our task is to find first appearence of bp, write it, path to the last
            //appearence, also write it, put null and than search next appearance
            while($row = $result->fetch_array())
            {
                $currTime = $row['time'];

                $pointPairList2[] = array($currTime, 1);
                if($previousTime == $currTime - $stepMicroTime)
                {
                    if(count($pointPairList) > 2)
                    {
                        if($pointPairList[count($pointPairList) - 3][1] == 'null')
                        {
                            $point = array($currTime, 1);
                            $pointPairList[count($pointPairList) - 1] = $point;
                            $previousTime = $currTime;
                        }
                        else
                        {
                            $point = array($currTime, 1);
                            $pointPairList[] = $point;
                            $previousTime = $currTime;
                        }
                    }
                    else
                    {
                        $point = array($currTime, 1);
                        $pointPairList[] = $point;
                        $previousTime = $currTime;
                    }
                }
                else
                {
                    $point = array('null','null');
                    $pointPairList[] = $point;
                    $point = array($currTime, 1);
                    $pointPairList[] = $point;
                    $previousTime = $currTime;
                }
            }

            $result->free();
        }
        else
        {
            $point = array('null','null');
            $pointPairList[] = $point;
        }
        $c->Disconnect();

        unset($c);


        return $pointPairList;
    }

    public function GetBinaryParam($extTableName, $extCode, $extStepLength, $extFreq)
    {
        $bpTableName = $extTableName;
        $stepLength = $extStepLength;
        $code = $extCode;
        $freq = $extFreq;

        $pointPairList = array();

        $pointPairList = $this->GetBinaryChannel($bpTableName, $code, $stepLength, $freq);

        $tempString = json_encode($pointPairList);
        //in bin params point equal to null we had put ["null","null"]
        $searchSubstr = '["null","null"]';
        //$searchSubstr = 'null';
        $transmitStr = str_replace($searchSubstr, 'null', $tempString);
        //var_dump($transmitStr);


        return json_decode($transmitStr);
        //return $pointPairList;
    }

    public function GetNormalizedApParam($extApTableName,
            $extStepDivider, $extCode, $extFreq, $extPefix,
            $extStartFrame, $extEndFrame)
    {
        $tableName = $extApTableName . "_" . $extPefix;
        $stepDivider = $extStepDivider;
        $code = $extCode;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $steps = $extFreq;
        $duplication = $stepDivider / $steps;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `".$code."` FROM `".$tableName."` WHERE
            `frameNum` >= ".$startFrame." AND `frameNum` < ".$endFrame."
            ORDER BY `frameNum`ASC;";

        $result = $link->query($query);

        $normArr = array();
        while($row = $result->fetch_array())
        {
            array_push($normArr, $row[$code]);
            for($i = 1; $i < $duplication; $i++)
            {
                array_push($normArr, $row[$code]);
            }
        }

        $result->free();
        $c->Disconnect();

        return $normArr;
    }

    public function GetNormalizedBpParam($extBpTableName,
            $extStepDivider, $extCode, $extFreq, $extPefix,
            $extStartFrame, $extEndFrame)
    {
        $tableName = $extBpTableName . "_".$extPefix;
        $stepDivider = $extStepDivider;
        $code = $extCode;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        $steps = $extFreq;
        $duplication = $stepDivider / $steps;
        $totalRows = ($endFrame - $startFrame) * $stepDivider;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `frameNum`, `time` FROM `".$tableName."` WHERE `code` = '" . $code . "' ".
            "AND `frameNum` >= ".$startFrame." AND `frameNum` < ".$endFrame. " ".
            "ORDER BY `time` ASC;";

        $result = $link->query($query);

        $normArr = array();
        for($i = 0; $i < $totalRows; $i++)
        {
            $normArr[$i] = 0;
        }

        while($row = $result->fetch_array())
        {
            $position = ($row['frameNum'] - $startFrame) * $stepDivider;
            $normArr[$position] = 1;
            for($i = 1; $i < $stepDivider; $i++)
            {
                $position = ($row['frameNum'] - $startFrame) * $stepDivider + $i;
                $normArr[$position] = 1;
            }
        }

        $result->free();
        $c->Disconnect();

        return $normArr;
    }

    public function NormalizeTime($extStepDivider, $extStepLength,
            $extTotalFrameNum, $extStartCopyTime, $extStartFrame, $extEndFrame)
    {
        $stepLength = $extStepLength;
        $stepDivider = $extStepDivider;
        $totalFrameNum = $extTotalFrameNum;
        $startCopyTime = $extStartCopyTime;
        $startFrame = $extStartFrame;
        $endFrame = $extEndFrame;
        //date_default_timezone_set('Europe/Kiev');
        $stepMicroTime = round($stepLength * 1000 / $stepDivider, 0);

        $normTime = array();
        for($i = $startFrame; $i < $endFrame; $i++)
        {
            $microTime = 0;
            $dateInterval = $i * $stepLength;
            $currTime = $startCopyTime + $dateInterval;
            array_push($normTime, date("H:i:s", $currTime). "." . $microTime);
            for($j = 1; $j < $stepDivider; $j++)
            {
                $microTime = $j * $stepMicroTime;
                $dateInterval = $i * $stepLength;
                $currTime = $startCopyTime + $dateInterval;
                array_push($normTime, date("H:i:s", $currTime) . "." . $microTime);
            }
        }
        return $normTime;
    }

    public function GetParamMinMax($extApTableName, $extParamCode)
    {
        $apTableName = $extApTableName;
        $paramCode = $extParamCode;

        $minMax = array();

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT MIN(`".$paramCode."`), MAX(`".$paramCode."`) FROM `".$apTableName."` WHERE 1;";
        $result = $link->query($query);

        $row = $result->fetch_array();
        $minMax['min'] = $row["MIN(`".$paramCode."`)"];
        $minMax['max'] = $row["MAX(`".$paramCode."`)"];

        $result->free();
        $c->Disconnect();

        return $minMax;

    }
}
