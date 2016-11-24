<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class ChartController extends CController
{
    public $curPage = 'chartPage';
    public $chartActions;
    private $title = 'Title';

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $L = new Language();
        $this->chartActions = (array)$L->GetServiceStrs($this->curPage);
        unset($L);
    }

    public function PutCharset()
    {
        printf("<!DOCTYPE html>
            <html lang='%s'>
            <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>",
                $this->userLang);
    }

    public function PutTitle()
    {
        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($this->data['flightId']);
        unset($Fl);

        $bort = $flightInfo['bort'];
        $voyage = $flightInfo['voyage'];
        $copyDate = date('H:i:s d-m-Y', $flightInfo['startCopyTime']);
        $departureAirport = $flightInfo['departureAirport'];
        $arrivalAirport = $flightInfo['arrivalAirport'];

        printf("<title>%s: %s. %s: %s. %s: %s. %s - %s</title>",
        $this->lang->bort, $bort,
        $this->lang->flightDate, $copyDate,
        $this->lang->voyage, $voyage,
        $departureAirport, $arrivalAirport);
    }

    public function PutStyleSheets()
    {
        printf("<link href='stylesheets/basicImg/favicone.ico' rel='shortcut icon' type='image/x-icon' />
                <link rel='stylesheet' type='text/css' href='stylesheets/jquery-ui-1.10.3.custom.min.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/jsTreeThemes/default/style.min.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/pages/chart.css' />
                <link rel='stylesheet' type='text/css' href='stylesheets/style.css' />");
    }

    public function PutHeader()
    {
        printf("</head><body data-isprintpage='true'>");
    }

    public function EventHandler()
    {
        printf("<div id='eventHandler'></div>");
    }

    public function PutScripts()
    {
        printf("<script type='text/javascript' src='scripts/include/jquery/jquery-2.1.1.min.js'></script>");
        printf("<script type='text/javascript' src='scripts/include/jquery/jquery-ui-1.10.3.custom.min.js'></script>");

        //flot
        printf("<script type='text/javascript' src='scripts/include/flot/jquery.flot.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.time.min.js'></script>
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.colorhelpers.min.js'></script>-->
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.canvas.min.js'></script>
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.categories.min.js'></script>-->
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.crosshair.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.errorbars.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.navigate.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.resize.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.selection.min.js'></script>
            <script type='text/javascript' src='scripts/include/flot/jquery.flot.symbol.min.js'></script>
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.threshold.min.js'></script>-->

            <!--[if lte IE 8]><script type='text/javascript' src='scripts/include/flot/excanvas.min.js'></script><![endif]-->
            <!--<script type='text/javascript' src='scripts/include/flot/jquery.flot.axislabels.js'></script>-->");

        //manual scripts
        printf("<script type='text/javascript' src='scripts/chart.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/Lang.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/WindowFactory.proto.js'></script>");

        //chart scripts
        printf("<script type='text/javascript' src='scripts/proto/chart/Chart.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/AxesWorker.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/Exception.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/Legend.proto.js'></script>");
        printf("<script type='text/javascript' src='scripts/proto/chart/Param.proto.js'></script>");
    }

    public function PutFooter()
    {
        printf("</body></html>");
    }

    public function PrintInfoFromRequest()
    {

        foreach ($this->data as $key => $val)
        {
            if(($key == 'tplName') && isset($this->data['flightId']))
            {
                $tplName = $val;
                $flightId = $this->data['flightId'];

                $Fl = new Flight();
                $flightInfo = $Fl->GetFlightInfo($flightId);
                unset($Fl);
                $bruType = $flightInfo['bruType'];
                $Bru = new Bru();
                $bruInfo = $Bru->GetBruInfo($bruType);
                $PSTListTableName = $bruInfo['paramSetTemplateListTableName'];
                $apCycloTable = $bruInfo['gradiApTableName'];
                $bpCycloTable = $bruInfo['gradiBpTableName'];
                $Tpl = new PSTempl();
                $params = $Tpl->GetPSTParams($PSTListTableName, $tplName, $this->_user->username);
                unset($Tpl);

                $apParams = array();
                $bpParams = array();
                foreach ($params as $item)
                {
                    $type = $Bru->GetParamType($item, $apCycloTable, $bpCycloTable);
                    if($type == PARAM_TYPE_AP)
                    {
                        $apParams[] = $item;
                    }
                    else if($type == PARAM_TYPE_BP)
                    {
                        $bpParams[] = $item;
                    }
                }

                unset($Bru);
                printf("<div id='%s' class='InfoFromRequest'>%s</div>", 'apParams', implode(",", $apParams));
                printf("<div id='%s' class='InfoFromRequest'>%s</div>", 'bpParams', implode(",", $bpParams));
            }

            printf("<div id='%s' class='InfoFromRequest'>%s</div>", $key, $val);
        }
    }

    public function PrintWorkspace()
    {
        $userId = $this->_user->GetUserIdByName($this->_user->username);

        $O = new UserOptions();
        $mainChartColor = $O->GetOptionValue($userId, 'mainChartColor');
        $lineWidth = $O->GetOptionValue($userId, 'lineWidth');
        unset($O);

        printf("<div id='chartWorkspace' class='WorkSpace'>".
                "<div id='graphContainer' class='GraphContainer'>" .
                "<div id='placeholder' data-bgcolor='".$mainChartColor."' data-linewidth='".$lineWidth."'></div>" .
                "<div id='legend'></div>" .
                "</div>" .
                "<div id='loadingBox' class='LoadingBox'>" .
                "<img src='stylesheets/basicImg/loading.gif'/>" .
                "</div>".
                "</div>");
    }

    public function PutWorkspace()
    {
        $userId = $this->_user->GetUserIdByName($this->_user->username);

        $O = new UserOptions();
        $mainChartColor = $O->GetOptionValue($userId, 'mainChartColor');
        $lineWidth = $O->GetOptionValue($userId, 'lineWidth');
        unset($O);

        $workspace = "<div id='chartWorkspace' class='WorkSpace'>".
                        "<div id='graphContainer' class='GraphContainer'>" .
                        "<div id='placeholder' data-bgcolor='".$mainChartColor."' data-linewidth='".$lineWidth."'></div>" .
                        "<div id='legend'></div>" .
                            "</div>" .
                        "<div id='loadingBox' class='LoadingBox'>" .
                            "<img src='stylesheets/basicImg/loading.gif'/>" .
                        "</div>".
                    "</div>";
        return $workspace;
    }

    public function GetApParamValue($flightId,
        $startFrame, $endFrame, $seriesCount,
        $code, $isPrintPage)
    {
        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        $startCopyTime = $flightInfo['startCopyTime'];
        unset($Fl);

        $Bru = new Bru();
        $bruType = $flightInfo['bruType'];
        $bruInfo = $Bru->GetBruInfo($bruType);
        $prefixArr = $Bru->GetBruApCycloPrefixes($flightInfo['bruType']);
        $cycloApTableName = $bruInfo["gradiApTableName"];
        $cycloBpTableName = $bruInfo["gradiBpTableName"];

        $Frame = new Frame();
        $framesCount = $Frame->GetFramesCount($apTableName, $prefixArr[0]); //giving just some prefix
        unset($Frame);

        if($startFrame == null) {
            $startFrame = 0;
        }

        if($startFrame == null) {
            $endFrame = $framesCount;
        }

        if($endFrame > $framesCount) {
            $endFrame = $framesCount;
        }

        if($seriesCount == null) {
            $seriesCount = 1;
        }

        $Ch = new Channel();

        $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName,
            $code, PARAM_TYPE_AP);

        $prefix = $paramInfo["prefix"];
        $freq = $paramInfo["freq"];

        $compression = Channel::$compressionTypes['none'];
        if (!$isPrintPage && (($startFrame !== 0) || ($endFrame !== $framesCount))) {
            $compression = Channel::$compressionTypes['aroundRange'];
        } else if (!$isPrintPage && ($framesCount * $seriesCount > POINT_MAX_COUNT)) {
            $compression = Channel::$compressionTypes['general'];
        }

        $syncParam = $Ch->GetChannel($apTableName,
            $code,
            $prefix,
            $startFrame,
            $endFrame,
            $seriesCount,
            $framesCount,
            $compression
        );

        return $syncParam;
    }

    public static function getBoolean($value)
    {
       if ($value === 'true') {
          return true;
       } else {
          return false;
       }
    }

    public function GetBpParamValue($extFlightId, $extParamCode)
    {
        $flightId = $extFlightId;
        $code = $extParamCode;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        unset($Fl);

        $Bru = new Bru();
        $bruType = $flightInfo['bruType'];
        $bruInfo = $Bru->GetBruInfo($bruType);
        $cycloApTableName = $bruInfo["gradiApTableName"];
        $cycloBpTableName = $bruInfo["gradiBpTableName"];
        $stepLength = $bruInfo["stepLength"];

        $Ch = new Channel();
        $paramValuesArr = array();

        $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $code, PARAM_TYPE_BP);
        $bpTableName = $bpTableName . "_" . $paramInfo['prefix'];
        $freq = $paramInfo['freq'];

        $syncParam = $Ch->GetBinaryParam($bpTableName, $code, $stepLength, $freq);

        return $syncParam;
    }

    public function GetParamColor($extFlightId, $extParamCode)
    {
        $flightId = $extFlightId;
        $paramCode = $extParamCode;

        $color = 'ffffff';

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $bruType = $flightInfo['bruType'];
        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $gradiApTableName = $bruInfo['gradiApTableName'];
        $gradiBpTableName = $bruInfo['gradiBpTableName'];

        $paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);

        if($paramInfo["paramType"] == PARAM_TYPE_AP)
        {
            $color = $Bru->GetParamColor($gradiApTableName, $paramCode);
        }
        else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
        {
            $color = $Bru->GetParamColor($gradiBpTableName, $paramCode);
        }

        unset($Bru);

        return $color;
    }

    public function GetParamInfo($extFlightId, $extParamCode)
    {
        $flightId = $extFlightId;
        $paramCode = $extParamCode;

        $color = 'ffffff';

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $bruType = $flightInfo['bruType'];
        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $gradiApTableName = $bruInfo['gradiApTableName'];
        $gradiBpTableName = $bruInfo['gradiBpTableName'];

        $paramInfo = $Bru->GetParamInfoByCode($gradiApTableName, $gradiBpTableName, $paramCode);

        if($paramInfo["paramType"] == PARAM_TYPE_AP)
        {
            $color = $Bru->GetParamColor($gradiApTableName, $paramCode);
        }
        else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
        {
            $color = $Bru->GetParamColor($gradiBpTableName, $paramCode);
        }

        $paramInfo['color'] = $color;

        unset($Bru);

        return $paramInfo;
    }

    public function GetLegend($extFlightId, $extCodes)
    {
        $flightId = $extFlightId;
        $paramCodeArray = $extCodes;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $bruType = $flightInfo['bruType'];
        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $cycloApTableName = $bruInfo['gradiApTableName'];
        $cycloBpTableName = $bruInfo['gradiBpTableName'];

        for($i = 0; $i < count($paramCodeArray); $i++)
        {
            $paramCode = $paramCodeArray[$i];
            if(!empty($paramCode)) {
                $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);

                if($paramInfo["paramType"] == PARAM_TYPE_AP)
                {
                    $infoArray[] = $paramInfo['name'].", ".
                    $paramInfo['dim'];
                }
                else if ($paramInfo["paramType"] == PARAM_TYPE_BP)
                {
                    $infoArray[] = $paramInfo['name'];
                }
            }
        }
        unset($Bru);

        return $infoArray;
    }

    public function GetParamMinmax($exFlightId, $extParamCode, $extTplName)
    {
        $flightId = $exFlightId;
        $paramCode = $extParamCode;
        $tplName = $extTplName;
        $user = $this->_user->username;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        unset($Fl);

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
        $PSTTableName = $bruInfo['paramSetTemplateListTableName'];
        unset($Bru);

        $PSTempl = new PSTempl();
        $minMax = $PSTempl->GetParamMinMax($PSTTableName, $tplName,
                $paramCode, $user);
        unset($PSTempl);

        if($minMax == '')
        {
            $minMax = array(
                    'min' => -1,
                    'max' => 1);
        }

        return $minMax;
    }

    public function SetParamMinmax($exFlightId, $extParamCode, $extTplName, $extMin, $extMax)
    {
        $flightId = $exFlightId;
        $paramCode = $extParamCode;
        $tplName = $extTplName;
        $min = $extMin;
        $max = $extMax;
        $user = $this->_user->username;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        unset($Fl);

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($flightInfo['bruType']);
        $PSTTableName = $bruInfo['paramSetTemplateListTableName'];
        unset($Bru);

        $PSTempl = new PSTempl();
        $PSTempl->UpdateParamMinMax($PSTTableName, $tplName, $paramCode, $min, $max, $user);
        unset($PSTempl);

        return "ok";
    }

    public function GetFlightExceptions($extFlightId, $extRefParam)
    {
        $flightId = $extFlightId;
        $refParam = $extRefParam;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $excTableName = $flightInfo['exTableName'];

        if($excTableName != '')
        {
            $bruType = $flightInfo['bruType'];
            $startCopyTime = $flightInfo['startCopyTime'];
            $apTableName = $flightInfo['apTableName'];

            $Bru = new Bru();
            $bruInfo = $Bru->GetBruInfo($bruType);
            $stepLength = $bruInfo['stepLength'];
            $cycloApTableName = $bruInfo['gradiApTableName'];
            $cycloBpTableName = $bruInfo['gradiBpTableName'];
            $excListTableName = $bruInfo['excListTableName'];
            $paramType = $Bru->GetParamType($refParam,
                    $cycloApTableName,$cycloBpTableName);
            $excList = array();
            if($paramType == PARAM_TYPE_AP)
            {
                $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName,
                        $cycloBpTableName, $refParam, PARAM_TYPE_AP);

                $prefix = $paramInfo["prefix"];
                $apTableName = $apTableName . "_" . $prefix;

                $FEx = new FlightException();
                $excList = (array)$FEx->GetExcApByCode($excTableName,
                        $refParam, $apTableName, $excListTableName);
                unset($FEx);
            }
            else if($paramType == PARAM_TYPE_BP)
            {
                $FEx = new FlightException();
                $excList = (array)$FEx->GetExcBpByCode($excTableName, $refParam,
                        $stepLength, $startCopyTime, $excListTableName);
                unset($FEx);
            }
            unset($Bru);
            return $excList;
        }
        else
        {
            return 'null';
        }

    }

    public function GetTableRawData($extFlightId, $extParams, $extFromTime, $extToTime)
    {
        $flightId = $extFlightId;
        $paramCodeArr = $extParams;
        $fromTime = $extFromTime;
        $toTime = $extToTime;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        $startCopyTime = $flightInfo['startCopyTime'];
        unset($Fl);

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $stepLength = $bruInfo['stepLength'];
        $stepDivider = $bruInfo['stepDivider'];
        $startCopyTime = $flightInfo['startCopyTime'];
        $cycloApTableName = $bruInfo['gradiApTableName'];
        $cycloBpTableName = $bruInfo['gradiBpTableName'];

        if($fromTime < $startCopyTime)
        {
            $fromTime = $startCopyTime;
        }

        $startFrame = floor(($fromTime - $startCopyTime) / $stepLength);
        $endFrame = ceil(($toTime - $startCopyTime) / $stepLength);
        $framesCount = $endFrame - $startFrame;

        $Ch = new Channel();
        $normParam = $Ch->NormalizeTime($stepDivider, $stepLength,
            $framesCount, $startCopyTime, $startFrame, $endFrame);
        $globalRawParamArr = array();
        array_push($globalRawParamArr, $normParam);

        for($i = 0; $i < count($paramCodeArr); $i++)
        {
            $paramType = $Bru->GetParamType($paramCodeArr[$i],
                $cycloApTableName, $cycloBpTableName);

            if($paramType == PARAM_TYPE_AP)
            {
                $paramInfo = $Bru->GetParamInfoByCode($cycloApTableName, '',
                        $paramCodeArr[$i], PARAM_TYPE_AP);

                $normParam = $Ch->GetNormalizedApParam($apTableName,
                    $stepDivider, $paramInfo["code"], $paramInfo["freq"], $paramInfo["prefix"],
                    $startFrame, $endFrame);

                array_push($globalRawParamArr, $normParam);
            }
            else if($paramType == PARAM_TYPE_BP)
            {
                $paramInfo = $Bru->GetParamInfoByCode('', $cycloBpTableName,
                        $paramCodeArr[$i], PARAM_TYPE_BP);
                $normParam = $Ch->GetNormalizedBpParam($bpTableName,
                        $stepDivider, $paramInfo["code"], $paramInfo["freq"], $paramInfo["prefix"],
                        $startFrame, $endFrame);
                array_push($globalRawParamArr, $normParam);

            }
        }

        unset($Ch);
        unset($Bru);

        return $globalRawParamArr;
    }

    public function GetExportFileName($extFlightId)
    {
        $flightId = $extFlightId;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);

        $fileGuid = uniqid();

        $exportedFileDir = $_SERVER['DOCUMENT_ROOT'] . "/fileUploader/files/exported/";

        if (!file_exists($exportedFileDir)) {
            mkdir($exportedFileDir, 0755);
        }

        $exportedFileName = $flightInfo['bort'] . "_" .
                date("Y-m-d", $flightInfo['startCopyTime'])  . "_" .
                $flightInfo['voyage'] . "_" . $fileGuid  . "_" . $this->_user->username . ".csv";

        return array(
            'name' => $exportedFileName,
            'path' => $exportedFileDir . $exportedFileName
        );
    }

    public function GetTableStep($flightId)
    {
        $F = new Flight();
        $flightInfo = $F->GetFlightInfo($flightId);
        unset($F);

        $FDR = new Bru();
        $FDRinfo = $FDR->GetBruInfo($flightInfo['bruType']);
        unset($FDR);

        $userId = $this->_user->GetUserIdByName($this->_user->username);

        $O = new UserOptions();
        $step = $O->GetOptionValue($userId, 'printTableStep');
        unset($O);

        if($step === null) {
            $step = 0;
        } else {
            $step = $step * $FDRinfo['stepDivider'];
        }

        return $step;
    }
}