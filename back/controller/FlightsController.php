<?php

namespace Controller;

use Framework\Application as App;

use Entity\Flight as FlightEntity;

use Evenement\EventEmitter;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Exception;
use ZipArchive;

class FlightsController extends BaseController
{
    public function getFlightsAction()
    {
        $userId = $this->user()->getId();

        $items = [];
        $flightsToFolders = $this->em()->getRepository('Entity\FlightToFolder')
            ->findBy(['userId' => $userId]);

        foreach ($flightsToFolders as $flightToFolders) {
            $items[] = $this->em()
                ->getRepository('Entity\FlightToFolder')
                ->getTreeItem(
                    $flightToFolders->getFlightId(), $userId
                );
        }

        return json_encode($items);
    }

    public function changeFlightPath($data)
    {
        if (!isset($data['id'])
            || !isset($data['parentId'])
            || !is_int(intval($data['id']))
            || !is_int(intval($data['parentId']))
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $userId = intval($this->_user->userInfo['id']);
        $sender = intval($data['id']);
        $target = intval($data['parentId']);

        $Fd = new Folder;
        $result = $Fd->ChangeFlightFolder($sender, $target, $userId);
        unset($Fd);

        return json_encode([
            'id' => $sender,
            'parentId' => $target
        ]);
    }

   public function deleteFlight($data)
   {
       if (!isset($data['id'])
           || !(is_int(intval($data['id'])) || is_array($data['id']))
       ) {
           throw new BadRequestException(json_encode($data));
       }

       $flights = $data['id'];
       if (!is_array($data['id'])) {
           $flights = [];
           $flights[] = intval($data['id']);
       }

       $userId = intval($this->_user->userInfo['id']);

       $FC = new FlightComponent;
       foreach ($flights as $flightId) {
           $FC->DeleteFlight(intval($flightId), $userId);
       }

       unset($FC);

       return json_encode('ok');
   }

    public function processFlight($data)
    {
        if (!isset($data['id'])
            || !(is_int(intval($data['id'])))
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['id']);

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $apTableName = $flightInfo["apTableName"];
        $bpTableName = $flightInfo["bpTableName"];
        $excEventsTableName = $flightInfo["exTableName"];
        $startCopyTime = $flightInfo["startCopyTime"];
        $tableGuid = substr($apTableName, 0, 14);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo(intval($flightInfo["id_fdr"]));
        $excListTableName = $fdrInfo["excListTableName"];
        $apGradiTableName = $fdrInfo["gradiApTableName"];
        $bpGradiTableName = $fdrInfo["gradiBpTableName"];
        $stepLength = $fdrInfo["stepLength"];

        if ($excListTableName != "") {
           $excListTableName = $fdrInfo["excListTableName"];
           $apGradiTableName = $fdrInfo["gradiApTableName"];
           $bpGradiTableName = $fdrInfo["gradiBpTableName"];

           $FEx = new FlightException;
           $FEx->DropFlightExceptionTable($excEventsTableName);
           $flightExTableName = $FEx->CreateFlightExceptionTable($flightId, $tableGuid);
           //Get exc refParam list
           $excRefParamsList = $FEx->GetFlightExceptionRefParams($excListTableName);

           $exList = $FEx->GetFlightExceptionTable($excListTableName);

           //file can be accesed by ajax what can cause warning
           error_reporting(E_ALL ^ E_WARNING);
           set_time_limit (0);

           //perform proc be cached table
           for($i = 0; $i < count($exList); $i++)
           {
              $curExList = $exList[$i];
              $FEx->PerformProcessingByExceptions($curExList,
                    $flightInfo, $flightExTableName,
                    $apTableName, $bpTableName,
                    $startCopyTime, $stepLength);
           }

           EventProcessingComponent::processEvents($flightId, new EventEmitter);

           error_reporting(E_ALL);
        }

        unset($fdr);
        return json_encode('ok');
   }

   public function ExportFlightsAndFolders($flightIds, $folderDest)
   {
      $Fd = new Folder;

      $userId = intval($this->_user->userInfo['id']);
      $allFolders = [];

      foreach ($folderDest as $dest) {
         $allFolders = $Fd->SubfoldersDeepScan($dest, $userId, $adminRole);
      }

      foreach ($allFolders as $folderId) {
         $flightIds = array_merge($flightIds,
               $Fd->GetFlightsByFolder($folderId, $userId, $adminRole));
      }
      unset($Fd);

      $exportedFiles = [];

      $Fl = new Flight;
      $C = new DataBaseConnector;
      $Fdr = new Fdr;

      foreach ($flightIds as $flightId) {
         $flightInfo = $Fl->GetFlightInfo($flightId);

         $fileGuid = uniqid();
         $exportedFileName = $flightInfo['bort']
            . "_" . date("Y-m-d", $flightInfo['startCopyTime'])
            . "_" . $flightInfo['voyage']
            . "_" . $fileGuid;

         $exportedFileDir = RuntimeManager::getExportFolder();
         $exportedFilePath = RuntimeManager::createExportedFile($exportedFileName);

         $headerFile = [];
         $headerFile['filename'] = "header_".$flightInfo['bort']."_".$flightInfo['voyage'].$fileGuid.".json";
         $headerFile['root'] = $exportedFileDir.DIRECTORY_SEPARATOR.$headerFile['filename'];
         $exportedFiles[] = $headerFile;

         $apPrefixes = $Fdr->GetBruApCycloPrefixes(intval($flightInfo["id_fdr"]));

         for ($i = 0; $i < count($apPrefixes); $i++) {
            $exportedTable = $C->ExportTable(
                $flightInfo["apTableName"]."_".$apPrefixes[$i],
                $flightInfo["apTableName"]."_".$apPrefixes[$i] . "_" . $fileGuid,
                $exportedFileDir
            );

            $exportedFiles[] = $exportedTable;

            $flightInfo["apTables"][] = array(
                  "pref" => $apPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         $bpPrefixes = $Fdr->GetBruBpCycloPrefixes(intval($flightInfo["id_fdr"]));

         for ($i = 0; $i < count($bpPrefixes); $i++) {
            $exportedTable = $C->ExportTable(
                $flightInfo["bpTableName"]."_".$apPrefixes[$i],
                $flightInfo["bpTableName"]."_".$bpPrefixes[$i] . "_" . $fileGuid,
                $exportedFileDir
            );

            $exportedFiles[] = $exportedTable;

            $flightInfo["bpTables"][] = array(
                  "pref" => $bpPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         $eventTables = [
            ['table' => $flightInfo["exTableName"], 'label' => "exTables"],
            ['table' => $flightInfo["guid"].'_events', 'label' => "eventsTable"],
            ['table' => $flightInfo["guid"].'_settlements', 'label' => "settlementsTable"],
         ];

         foreach ($eventTables as $item) {
             $this->exportEventTable(
                 $flightInfo,
                 $item['table'],
                 $item['label'],
                 $fileGuid,
                 $exportedFiles,
                 $exportedFileDir
             );
         }

         $exportedFileDesc = fopen($headerFile['root'], "w");
         fwrite ($exportedFileDesc , json_encode($flightInfo));
         fclose($exportedFileDesc);
      }

      unset($Fl);
      unset($C);
      unset($Fdr);

      $zip = new ZipArchive;
      if ($zip->open($exportedFilePath, ZipArchive::CREATE) === TRUE) {
         for($i = 0; $i < count($exportedFiles); $i++) {
            $zip->addFile($exportedFiles[$i]['root'], $exportedFiles[$i]['filename']);
         }
         $zip->close();
      } else {
         error_log('Failed zipping flight.');
      }

      for ($i = 0; $i < count($exportedFiles); $i++) {
         if(file_exists($exportedFiles[$i]['root'])) {
            //unlink($exportedFiles[$i]['root']);
         }
      }

      error_reporting(E_ALL);
      return RuntimeManager::getExportedUrl($exportedFileName);
   }

   private function exportEventTable(
       &$flightInfo,
       $tableName,
       $flightInfolabel,
       $fileGuid,
       &$exportedFiles,
       $exportedFileDir
   ) {
       $C = new DataBaseConnector;

       if ($C->checkTableExist($tableName)) {
           $exportedTable = $C->ExportTable(
             $tableName,
             $tableName . "_" . $fileGuid,
             $exportedFileDir
           );
           $exportedFiles[] = $exportedTable;

           $flightInfo[$flightInfolabel] = $exportedTable["filename"];

           return $tableName . "_" . $fileGuid;
       }

       unset($C);

       return false;
   }

   public function GetEvents()
   {
       $list = [];
       $userId = intval($this->_user->userInfo['id']);
       $Fd = new Folder;
       $flightsInFolders = $Fd->GetAllFlightsInFolders($userId);
       unset($Fd);

       $firstRow = true;
       $excTables = [];
       $FEx = new FlightException;
       foreach($flightsInFolders as $flightInFolder)
       {
           $flight = $Fl->GetFlightInfo(intval($flightInFolder['flightId']));
           $excTable = $flight['exTableName'];
           $rows = $FEx->GetFlightEventsList($excTable);

           foreach ($rows as $row) {
               $falseAlarm = $row['falseAlarm'];

               if($falseAlarm) {
                   continue;
               }

               $row = array(
                   "code" => $row['code'],
                   "startTime" => date('Y-m-d H:i:s', ($row['startTime'] / 1000)),
                   "endTime" => date('Y-m-d H:i:s', ($row['endTime'] / 1000)),
                   "excAditionalInfo" => $row['excAditionalInfo'],
                   "userComment" => $row['userComment']
               );

               if ($firstRow) {
                   $firstRow = false;

                   $plainRow = [];
                   foreach ($row as $key => $val) {
                       if (gettype($key) === 'string') {
                           $plainRow[] = $key;
                       }
                   }
                   array_push($list, $plainRow);
               }

               $plainRow = [];
               foreach ($row as $key => $val) {
                   if (isset($val)) {
                       $val = str_replace([PHP_EOL, ',', ';'], ' ', $val);

                       $plainRow[] = $val;
                   } else {
                       $plainRow[] = '';
                   }
               }

               array_push($list, $plainRow);
           }
       }
       unset($FEx);

       return $list;
   }

   public function GetCoordinates($flightId)
   {
       if (!is_int(intval($flightId))) {
           throw new Exception("Incorrect flightId passed into GetCoordinates FlightsController." . $flightId, 1);
       }

       $Fl = new Flight;
       $flight = $Fl->GetFlightInfo($flightId);
       $fdrId = intval($flightInfo['id_fdr']);
       unset($Fl);

       $apTableName = $flight['apTableName'];
       $bpTableName = $flight['bpTableName'];

       $fdr = new Fdr;
       $fdrInfo = $fdr->getFdrInfo($fdrId);
       unset($fdr);

       $kmlScript = $fdrInfo['kml_export_script'];
       $kmlScript = str_replace("[ap]", $apTableName, $kmlScript);
       $kmlScript = str_replace("[bp]", $bpTableName, $kmlScript);

       $c = new DataBaseConnector;
       $link = $c->Connect();

       $info = [];
       $averageLat = 0;
       $averageLong = 0;

       if (!$link->multi_query($kmlScript)) {
           //err log
           error_log("Impossible to execute multiquery: (" .
               $kmlScript . ") " . $link->error);
       }

       do
       {
           if ($res = $link->store_result())  {
               while($row = $res->fetch_array()) {
                   $lat = $row['LAT'];
                   $long = $row['LONG'];
                   $h = $row['H'];

                   $averageLat += $lat;
                   $averageLong += $long;
                   $averageLat /= 2;
                   $averageLong /= 2;

                   if ($h < 0) {
                       $h = 10.00;
                   }
                   $h = round($h, 2);
                   $info[] = [
                       $long,
                       $lat,
                       $h,
                   ];
               }

               $res->free();
           }
       } while ($link->more_results() && $link->next_result());

       $c->Disconnect();

       unset($c);

       return $info;
   }

   public function GetFlightTiming($flightId)
   {
       $Fl = new Flight;
       $flightInfo = $Fl->GetFlightInfo($flightId);
       $fdrId = intval($flightInfo['id_fdr']);
       unset($Fl);

       $fdr = new Fdr;
       $fdrInfo = $fdr->getFdrInfo($fdrId);
       $stepLength = $fdrInfo['stepLength'];

       $prefixArr = $fdr->GetBruApCycloPrefixes($fdrInfo['id']);
       unset($fdr);

       $Frame = new Frame;
       $framesCount = $Frame->GetFramesCount($flightInfo['apTableName'], $prefixArr[0]); //giving just some prefix
       unset($Frame);

       $stepsCount = $framesCount * $stepLength;
       $flightTiming['duration'] = $stepsCount;
       $flightTiming['startCopyTime'] = $flightInfo['startCopyTime'];
       $flightTiming['stepLength'] = $stepLength;

       return $flightTiming;
   }

   public function itemExport($data)
   {
       if (!isset($data['id']) && !isset($data['folderDest'])) {
           throw new BadRequestException(json_encode($data));
       }

       $flightIds = [];
       $folderDest = [];
       if (isset($data['id'])) {
           if(is_array($data['id'])) {
               $flightIds = array_merge($flightIds, $data['id']);
           } else {
               $flightIds[] = $data['id'];
           }
       }

       $folderDest = [];
       if(isset($data['folderDest']) &&
           is_array($data['folderDest'])) {
               $folderDest = array_merge($folderDest, $data['folderDest']);
       }

       $zipUrl = $this->ExportFlightsAndFolders($flightIds, $folderDest);

       $answ = [];

       if ($zipUrl) {
           $answ = [
               'status' => 'ok',
               'zipUrl' => $zipUrl
           ];

           $answ = [
               'status' => 'empty',
               'info' => 'No flights to export'
           ];
       }

       return json_encode($answ);
    }

    public function getFlightFdrId($data)
    {
        if (!isset($data['flightId'])) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        unset($Fl);

        $data = array(
            'bruTypeId' => $fdrId
        );

        $answ["status"] = "ok";
        $answ["data"] = $data;

        json_encode($answ);
    }

    public function coordinates($data)
    {
        if (!isset($data['id'])) {
            throw new BadRequestException(json_encode($data));
        }

        header("Content-Type: text/comma-separated-values; charset=utf-8");
        header("Content-Disposition: attachment; filename=coordinates.kml");  //File name extension was wrong
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);

        $id = $data['id'];
        $list = $this->GetCoordinates($id);

        $figPrRow = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
         .'<kml xmlns="http://www.opengis.net/kml/2.2"' . PHP_EOL
         .' xmlns:gx="http://www.google.com/kml/ext/2.2"> <!-- required when using gx-prefixed elements -->' . PHP_EOL
        .'<Placemark>' . PHP_EOL
          .'<name>gx:altitudeMode Example</name>' . PHP_EOL
          .'<LineString>' . PHP_EOL
            .'<extrude>1</extrude>' . PHP_EOL
            .'<gx:altitudeMode>absolute </gx:altitudeMode>' . PHP_EOL
            .'<coordinates>' . PHP_EOL;

        foreach ($list as $fields) {
            for($i = 0; $i < count($fields); $i++) {
                $figPrRow .= $fields[$i] . ",";
            }

            $figPrRow = substr($figPrRow, 0, -1);
            $figPrRow .= PHP_EOL;
        }

        $figPrRow .= '</coordinates>' . PHP_EOL
            .'</LineString>' . PHP_EOL
            .'</Placemark>' . PHP_EOL
            .'</kml>';

        unset($U);
        return $figPrRow;
    }

    public function getFlightInfo($data)
    {
        if (!isset($data['flightId'])) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);

        $em = EM::get();

        $items = [];
        $flight = $em->getRepository('Entity\Flight')
            ->findOneBy(['id' => $flightId]);

        if (!$flight) {
            throw new NotFoundException("requested flight not found. Flight id: ". $flightId);
        }

        $flightTiming = $this->GetFlightTiming($flightId);

        return json_encode([
            'data' => array_merge(
                $flight->get(),
                [
                    'fdrId' => $flight->getFdr()->getName(),
                    'fdrName' => $flight->getFdr()->getName(),
                    'startCopyTimeFormated' => date('d/m/y H:i:s', $flight->getStartCopyTime()),
                ]
            ),
            'duration' => $flightTiming['duration'],
            'startFlightTime' => $flightTiming['startCopyTime'],
            'stepLength' => $flightTiming['stepLength'],
        ]);
    }
}
