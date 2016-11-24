<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class FlightsController extends CController
{
   public $curPage = 'flightsPage';
   public $flightActions;

   function __construct()
   {
       $this->IsAppLoggedIn();
       $this->setAttributes();

       $get = $_GET;
       if(isset($get['action']) && ($get['action'] != '')) {
           $this->getAction = $get['action'];
       }

       $L = new Language();
       $this->flightActions = (array)$L->GetServiceStrs($this->curPage);
       unset($L);
   }

   public function PutTopMenu()
   {
      $this->_user->username = $this->_user->username . "";
      $this->_user->usernameLen = strlen($this->_user->username);
      $styleFontSize = 24 - $this->_user->usernameLen / 2.2;
      $styleWidth = 20 + $this->_user->usernameLen * $styleFontSize / 2;
      $styleTop = 8 + $this->_user->usernameLen / 3;

      $topMenu = sprintf("<div id='topMenuFlightList' class='TopMenu'>

            <label id='logo' class='Logo' style='background-image:url(stylesheets/basicImg/logo.png)'>
               <span style='position:absolute; margin-top:8px;'>Luch</span>
            </label>

            <img class='Separator'></img>

            <label id='currentUploadingTopButt' class='CurrentUploadingTopButt' style='background-image:url(stylesheets/basicImg/add.png)'>
            </label>

            <label id='uploadTopButt' class='UploadButt'>
               <span style='position:absolute; margin-top:8px;'>%s</span>
            </label>

            <label id='userTopButt' class='UserButt' style='background-image:url(stylesheets/basicImg/userPreferences.png); " .
            "width:%spx; font-size:%spx;'
               data-username='%s'>
               <span style='position:absolute; margin-top:%spx;'>%s</span>
            </label>

            <div id='view' style='display:none;'><img class='Separator2'></img>
               <label class='ViewItem' style='background-image:url(stylesheets/basicImg/view.png);'>
               <span style='position:absolute; margin-top:8px;'>%s</span>
            </label></div>

            </div>", $this->lang->upload,
            $styleWidth,
            $styleFontSize,
            $this->_user->username,
            $styleTop,
            $this->_user->username,
            $this->lang->viewItem);

      return $topMenu;
   }

   public function PutLeftMenu()
   {
       $leftMenu = sprintf("<div id='leftMenuFlightList' class='LeftMenu'>");
       $leftMenu .= sprintf("<input class='SearchBox' value='' size='24' style='visibility: hidden;'></input>");

       if(in_array($this->_user->flightPrivilegeArr[0], $this->_user->privilege) ||
            in_array($this->_user->flightPrivilegeArr[1], $this->_user->privilege) ||
            in_array($this->_user->flightPrivilegeArr[2], $this->_user->privilege) ||
            in_array($this->_user->flightPrivilegeArr[3], $this->_user->privilege) ||
            in_array($this->_user->flightPrivilegeArr[4], $this->_user->privilege) ||
            in_array($this->_user->flightPrivilegeArr[5], $this->_user->privilege))
      {
         $leftMenu .= sprintf("<div id='flightLeftMenuRow' class='LeftMenuRow LeftMenuRowSelected' data-selected='true'>
               <img class='LeftMenuRowIcon' src='stylesheets/basicImg/flight.png'></img>
               %s&nbsp;
               </div>", $this->lang->flightsItem);
      }

      if(in_array($this->_user->flightPrivilegeArr[0], $this->_user->privilege))
      {
         $leftMenu .= sprintf("<div id='searchLeftMenuRow' class='LeftMenuRow'>
               <img class='LeftMenuRowIcon' src='stylesheets/basicImg/search.png'></img>
               %s&nbsp;
               </div>", $this->lang->searchItem);
      }

      if(in_array($this->_user->userPrivilegeArr[0], $this->_user->privilege) ||
            in_array($this->_user->userPrivilegeArr[1], $this->_user->privilege) ||
            in_array($this->_user->userPrivilegeArr[2], $this->_user->privilege) ||
            in_array($this->_user->userPrivilegeArr[3], $this->_user->privilege) ||
            in_array($this->_user->userPrivilegeArr[4], $this->_user->privilege))
      {
         $leftMenu .= sprintf("<div id='usersLeftMenuRow' class='LeftMenuRow'>
               <img class='LeftMenuRowIcon' src='stylesheets/basicImg/user.png'></img>
               %s&nbsp;
               </div>", $this->lang->usersItem);
      }

      $leftMenu .= sprintf("</div>");

      return $leftMenu;
   }


   public function FileUploadBlock()
   {
      $avalibleBruTypes = $this->_user->GetAvaliableBruTypes($this->_user->username);

      $Bru = new Bru();
      $bruList = $Bru->GetBruList($avalibleBruTypes);
      unset($Bru);

      $optionString = "";

      foreach($bruList as $bruInfo)
      {
         $optionString .="<option data-id='".$bruInfo['id']."'>".$bruInfo['bruType']."</option>";
      }

      $fileUploadBlock = sprintf("<div id='fileUploadDialog' class='OptionBlock' title='%s'><br>
         <div id='importConvertRadio'>
            <input type='radio' id='%s' name='radio' checked='checked'><label for='%s'>%s</label>
               <input type='radio' id='%s' name='radio'><label for='%s'>%s</label>
         </div>
            <br>
         <span class='btn btn-success fileinput-button'>
         <i class='glyphicon'>%s</i>
         <input id='chooseFileBut' type='file' name='files[]' multiple>
         </span>

         <div id='previewCheckBoxDiv' class='FlightUploadingInputs'><label><input checked='checked' id='previewCheckBox' type='checkbox'></input>%s</label></div>

         <div id='bruTypeSelectForUploadingDiv'>
            <select id='bruTypeSelectForUploading' name='bruType' class='FlightUploadingInputs'>%s</select>
         </div>

         <div id='progress' class='progress' style='padding-top:10px;'>
               <div class='progress-bar progress-bar-success'></div>
            </div>
         <div id='files' class='files'></div>
         <br></div>",
            $this->lang->flightUpload,

         $this->flightActions['flightFileConvert'],
            $this->flightActions['flightFileConvert'],
            $this->lang->fileConvert,
            $this->flightActions['flightFileImport'],
            $this->flightActions['flightFileImport'],
            $this->lang->fileImport,
            $this->lang->chooseFile,
            $this->lang->filePreview,
            $optionString);

      return $fileUploadBlock;
   }

   public function BuildFlightListInTwoColumns($extShownFolder1, $extShownFolder2)
   {
      $shownFolder1 = $extShownFolder1;
      $shownFolder2 = $extShownFolder2;

      $flightsInTwoContainers = "<table class='TwoColumnsTable'><tr>" .
            "<td id='filesContainerLeft' class='TwoColumnsTableColumn'>";
      $flightsInTwoContainers .= $this->BuildFlightColumnFromTwoColumns($shownFolder1, "Left");
      $flightsInTwoContainers .= "</td><td id='filesContainerRight' class='TwoColumnsTableColumn'>";
      $flightsInTwoContainers .= $this->BuildFlightColumnFromTwoColumns($shownFolder2, "Right");
      $flightsInTwoContainers .= "</td></tr></table>";

      return $flightsInTwoContainers;
   }

   public function BuildFlightColumnFromTwoColumns($extFolder, $extPosition)
   {
      $shownFolderId = $extFolder;
      $position = $extPosition;

      $flightColumn = "";
      $flightsInPath = (array)$this->GetFlightsByPath($shownFolderId);
      $subFolders = (array)$this->GetFoldersByPath($shownFolderId);

      $Fd = new Folder();
      $shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
      $shownFolder = $shownFolderInfo['name'];
      unset($Fd);

      //left container
      if($shownFolderId != 0) //if not root
      {
         $flightColumn .= "<div class='FolderPathInTwoColumnContainer' " .
                     "data-path='" .$shownFolderId. "' " .
                     "data-position='" .$position. "'>" .
            "<img id='toRootFromPath' " .
               "class='ui-icon ui-icon-home ui-state-default' style='float:left; margin-top:7px;'/>" .
            "<img id='upperFromPath' " .
               "class='ui-icon ui-icon-carat-1-n ui-state-default' style='float:left; margin-top:7px;'/>" .
            "<img id='refreshFolder' " .
               "class='ui-icon ui-icon-refresh ui-state-default' style='float:left; margin-top:7px;'/>" .
            "<img id='newFolderInPath' " .
               "class='ui-icon ui-icon-folder-collapsed ui-state-default' style='float:left; margin-top:7px;'/>&nbsp; " .
               $shownFolder . "</div>";
      }
      else
      {
         $flightColumn .= "<div class='FolderPathInTwoColumnContainer' " .
                     "data-path='" .$shownFolderId. "' " .
                     "data-position='" .$position. "'>" .
            "<img id='refreshFolder' " .
               "class='ui-icon ui-icon-refresh ui-state-default' style='float:left; margin-top:7px;'/>" .
            "<img id='newFolderInPath' " .
               "class='ui-icon ui-icon-folder-collapsed ui-state-default' style='float:left; margin-top:7px;'/>&nbsp; " .
               $this->lang->root . "</div>";
      }

      //if no folders we should also add NonSortableList list container
      if(count($subFolders) == 0)
      {
         $flightColumn .= "<ul class='NonSortableList' " .
                  "data-position='".$position."' " .
                  "data-folderpath='".$shownFolderId."'></ul>";
      }

      for($i = 0; $i < (count($flightsInPath) + count($subFolders)); $i++)
      {
         if($i < count($subFolders))
         {
            if($i == 0)
            {
               $flightColumn .= "<ul class='NonSortableList' " .
                  "data-position='".$position."' " .
                  "data-folderpath='".$shownFolderId."'>";
            }

            $flightColumn .=
               "<li id='draggable".$position."' class='FolderInTwoColumnContainer' " .
                  "data-position='".$position."' " .
                  "data-folderpath='".$shownFolderId."' " .
                  "data-folderdestination='". $subFolders[$i]['id'] . "'>" .
                  "<table><tr><td style='width:100%;'>" .
                  $subFolders[$i]['name'] .
                  "</td><td style='width:15px; vertical-align:top;'><input class='ItemsCheck' type='checkbox'" .
                  "data-type='folder' data-position='".$position."' data-folderpath='".$shownFolderId."' " .
                  "data-folderdestination='". $subFolders[$i]['id'] . "'/>".
                  "</td><tr></table>" .
                  "</li>";

            if($i == count($subFolders) - 1)
            {
               $flightColumn .= "</ul>";
            }
         }
         else
         {
            $j = $i - count($subFolders);

            if($j == 0)
            {
               $flightColumn .= "<div id='dropable'><ul id='sortable".$position."' data-curpath='".$shownFolderId."'>";
            }

            $procStatus = $this->lang->notPerformed;
            if($flightsInPath[$j]["exTableName"] != "")
            {
               $procStatus = $this->lang->performed;
            }

            $flightColumn .= "<li id='draggable".$position."' class='FlightInTwoColumnContainer' " .
                     "data-position='".$position."'
                     data-flightid='".$flightsInPath[$j]["id"]."'
                     data-folderpath='".$shownFolderId."'>" .
               "<table><tr><td style='width:100%;'>" .
               "<p>" . $this->lang->bort . ": " . $flightsInPath[$j]["bort"] . "; " .
                     $this->lang->voyage . ": " . $flightsInPath[$j]["voyage"] .
               "</p>" .
               "<p>" . $this->lang->flightTime . ": " .
                  date ("H:i:s d/m/y", $flightsInPath[$j]["startCopyTime"]) . "</p>" .
               "<p>" . $this->lang->uploadTime . ": " .
                  date ("H:i:s d/m/y", $flightsInPath[$j]["uploadingCopyTime"]) . "</p>" .
               "<p>" . $this->lang->bruType . ": " .
                  $flightsInPath[$j]["bruType"] ."</p>" .
               "<p>" . $this->lang->performer . ": " .
                  $flightsInPath[$j]["performer"] ."</p>" .
               "<p>" . $this->lang->status . ": " .
                  $procStatus ."</p>" .
               "</td><td style='width:15px; vertical-align:top;'>".
               "<input class='ItemsCheck' type='checkbox' ".
                  "data-type='flight' data-position='".$position."' data-flightid='".$flightsInPath[$j]["id"]."'" .
                  "data-folderpath='".$shownFolderId."'/></td><tr></table>" .
               "</li>";

            if($j == count($flightsInPath) - 1)
            {
               $flightColumn .= "</ul></div>";
            }
         }
      }

      //if no flights we should also add dropable area
      if(count($flightsInPath) == 0)
      {
         $flightColumn .= "<div id='dropable'><ul id='sortable".$position."' data-curpath='".$shownFolder."'>";
         $flightColumn .= "</ul></div>";
      }

      return $flightColumn;
   }

   private function GetFlightsByPath($extFolderId)
   {
      $folderId = $extFolderId;
      $avalIds = $this->_user->GetAvaliableFlights($this->_user->username);
      $userId = $this->_user->GetUserIdByName($this->_user->username);
      $role = $this->_user->userInfo['role'];
      $adminRole = User::isAdmin($role);
      unset($U);

      $flightIdsArr = [];

      $Fl = new Flight();
      $Fd = new Folder();
      if(User::isAdmin($role)) {
         $flightIdsArr = $Fd->GetFlightsByFolder($folderId, $userId, $adminRole);
      } else if(User::isModerator($role)) {
         $userIds = $this->_user->GetUserIdsByAuthor($this->_user->username);
         $flightIdsArr = $Fd->GetFlightsByFolder($folderId, $userIds);
      } else {
         $flightIdsArr = $Fd->GetFlightsByFolder($folderId, $userId);
      }

      $flightsInfoArr = [];
      foreach ($flightIdsArr as $id)
      {
         $flightsInfoArr[] = $Fl->GetFlightInfo($id);
      }

      unset($Fd);
      unset($Fl);

      return $flightsInfoArr;
   }

   private function GetFoldersByPath($extFolderId)
   {
      $folderId = $extFolderId;

      $userId = $this->_user->userInfo['id'];
      $role = $this->_user->userInfo['role'];
      $adminRole = User::isAdmin($role);
      unset($U);
      $subFoldersArr = [];
      $Fd = new Folder();

      if(User::isAdmin($role)) {
         $subFoldersArr = $Fd->GetSubfoldersByFolder($folderId, $userId, $adminRole);
      } else if(User::isModerator($role)) {
         $userIds = $this->_user->GetUserIdsByAuthor($this->_user->username);
         $flightIdsArr = $Fd->GetSubfoldersByFolder($folderId, $userIds);
      } else {
         $flightIdsArr = $Fd->GetSubfoldersByFolder($folderId, $userId);
      }
      unset($Fd);

      return $subFoldersArr;
   }

   public function CreateNewFolder($extName, $extPath)
   {
      $name = $extName;
      $path = $extPath;

      $userId = $this->_user->GetUserIdByName($this->_user->username);
      unset($U);

      $Fd = new Folder();
      $result = $Fd->CreateFolder($name, $path, $userId);
      unset($Fd);

      return $result;
   }

   public function ChangeFlightPath($extSender, $extTarget)
   {
      $sender = $extSender;
      $target = $extTarget;


      $userId = $this->_user->GetUserIdByName($this->_user->username);
      unset($U);

      $Fd = new Folder();
      $result = $Fd->ChangeFlightFolder($sender, $target, $userId);
      unset($Fd);

      return $result;
   }

   public function ChangeFolderPath($extSender, $extTarget)
   {
      $sender = $extSender;
      $target = $extTarget;


      $userId = $this->_user->GetUserIdByName($this->_user->username);
      unset($U);

      $Fd = new Folder();
      $result = $Fd->ChangeFolderPath($sender, $target, $userId);
      unset($Fd);

      return $result;
   }

   public function RenameFolder($extFolderId, $extFolderName)
   {
      $folderId = $extFolderId;
      $folderName = $extFolderName;


      $userId = $this->_user->GetUserIdByName($this->_user->username);
      unset($U);

      $Fd = new Folder();
      $result = $Fd->RenameFolder($folderId, $folderName, $userId);
      unset($Fd);

      return $result;
   }

   public function DeleteFolderWithAllChildren($extId)
   {
      if(is_int($extId))
      {
         $id = $extId;


         $userId = intval($this->_user->GetUserIdByName($this->_user->username));
         unset($U);

         $Fd = new Folder();
         $avaliableFolders = $Fd->GetAvaliableFolders($userId);
         $result = array();

         if(in_array($id, $avaliableFolders))
         {
            $nodeTree = $this->PrepareTree(0); // here PrepareTree argument is not important
            $children = $nodeTree[0]['children'];
            $matches = array(
               0 => $id
            );
            $this->recursiveCollectChildren($children, $id, $matches);

            $Fl = new Flight();
            foreach ($matches as $id)
            {
               $id = intval($id);
               $flightInfo = $Fl->GetFlightInfo($id);
               if(!empty($flightInfo))
               {
                  $this->DeleteFlight($id);
               }

               if(in_array($id, $avaliableFolders))
               {
                  $folderInfo = $Fd->GetFolderInfo($id);
                  if(!empty($folderInfo))
                  {
                     $result[] = $Fd->DeleteFolder($id, $userId);
                  }
               }
            }
            unset($Fd);
            $result['status'] = true;
            return $result;
         }
         else
         {
            unset($Fd);
            $dat = "Not avaliable for current user. DeleteFolder id - " . $id . ". " .
               "Username - " . $this->_user->username . ". Page FlightsController.php";
            error_log($dat);
            $result['status'] = false;
            $result['data'] = $dat;
            return $result;
         }
      }
      else
      {
         error_log("Incorrect input data. DeleteFolder id - " . json_encode($extId) . ". Page FlightsController.php");
         $result['status'] = false;
         return $result;
      }
   }

   public function DeleteFlight($id)
   {
      if(is_int($id)) {
         $avaliableFlights = $this->_user->GetAvaliableFlights($this->_user->username);
         unset($U);

         if(in_array($id, $avaliableFlights))
         {
            $Fl = new Flight();
            $flightInfo = $Fl->GetFlightInfo($id);
            $bruType = $flightInfo["bruType"];

            $Bru = new Bru();
            $bruInfo = $Bru->GetBruInfo($bruType);
            $prefixApArr = $Bru->GetBruApCycloPrefixes($bruType);
            $prefixBpArr = $Bru->GetBruBpCycloPrefixes($bruType);

            $result = $Fl->DeleteFlight($id, $prefixApArr, $prefixBpArr);

            $this->_user->UnsetFlightAvaliable($id);

            $Fd = new Folder();
            $Fd->DeleteFlightFromFolders($id);
            unset($Fd);

            return $result;
         }
         else
         {
            error_log("Not avaliable for current user. DeleteFlight id - " . $id . ". " .
                  "Username - " . $this->_user->username . ". Page FlightsController.php");
            $result['status'] = false;
            return $result['status'];
         }
      }
      else
      {
         error_log("Incorrect input data. DeleteFlight id - " . json_encode($extId) . ". Page FlightsController.php");
         $result['status'] = false;
         return $result['status'];
      }
   }

   public function SyncFlightsHeaders($extIds)
   {
      $idsArr = $extIds;
      $info = array();

      $Fl = new Flight();
      foreach ($idsArr as $flightId)
   {
         $flightInfo = $Fl->GetFlightInfo($flightId);
         $bruType = $flightInfo["bruType"];

      if($bruType == 'BUR-92A_An-148(158)')
         {
            $info['voyage'] = $flightInfo['voyage'];
            $info['departureAirport'] = $flightInfo['departureAirport'];
            $info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//            $info['capitan'] = $flightInfo['capitan'];
//            $info['weightto'] = $flightInfo['weightto'];
//            $info['weightlndg'] = $flightInfo['weightlndg'];
         }
//      else if($bruType == 'ER_BSTO_An-148(158)')
//         {
//            $info['voyage'] = $flightInfo['voyage'];
//            $info['departureAirport'] = $flightInfo['departureAirport'];
//            $info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//         }
//         else if($bruType == 'RPP_Fly_An-148(158)')
//         {
//            $info['voyage'] = $flightInfo['voyage'];
//            $info['departureAirport'] = $flightInfo['departureAirport'];
//            $info['arrivalAirport'] = $flightInfo['arrivalAirport'];
//         }
   }

      foreach ($idsArr as $flightId) {
         $Fl->UpdateFlightInfo($flightId, $info);
      }

      unset($Fl);

      return true;
   }

   public function ProcessFlight($extId)
   {
      if(is_int($extId))
      {
         $flightId = $extId;


         $avaliableFlights = $this->_user->GetAvaliableFlights($this->_user->username);
         unset($U);

         if(in_array($flightId, $avaliableFlights))
         {
            $Fl = new Flight();
            $flightInfo = $Fl->GetFlightInfo($flightId);
            $apTableName = $flightInfo["apTableName"];
            $bpTableName = $flightInfo["bpTableName"];
            $excEventsTableName = $flightInfo["exTableName"];
            $startCopyTime = $flightInfo["startCopyTime"];
            $tableGuid = substr($apTableName, 0, 14);
            unset($Fl);

            $Bru = new Bru();
            $bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
            $excListTableName = $bruInfo["excListTableName"];
            $apGradiTableName = $bruInfo["gradiApTableName"];
            $bpGradiTableName = $bruInfo["gradiBpTableName"];
            $stepLength = $bruInfo["stepLength"];

            if ($excListTableName != "")
            {
               $bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
               $excListTableName = $bruInfo["excListTableName"];
               $apGradiTableName = $bruInfo["gradiApTableName"];
               $bpGradiTableName = $bruInfo["gradiBpTableName"];

               $FEx = new FlightException();
               $FEx->DropFlightExceptionTable($excEventsTableName);
               $flightExTableName = $FEx->CreateFlightExceptionTable($flightId, $tableGuid);
               //Get exc refParam list
               $excRefParamsList = $FEx->GetFlightExceptionRefParams($excListTableName);

               $exList = $FEx->GetFlightExceptionTable($excListTableName);

               //file can be accesed by ajax what can cause warning
               error_reporting(E_ALL ^ E_WARNING);

               //perform proc be cached table
               for($i = 0; $i < count($exList); $i++)
               {
//                   $fp = fopen($tempFilePath, "w");
//                   fwrite($fp, json_encode($exList[$i]["code"]));
//                         fclose($fp);

                  $curExList = $exList[$i];
                  $FEx->PerformProcessingByExceptions($curExList,
                        $flightInfo, $flightExTableName,
                        $apTableName, $bpTableName,
                        $startCopyTime, $stepLength);
               }

               error_reporting(E_ALL);

//                unlink($tempFilePath);
            }
            else
            {
//                unlink($tempFilePath);
            }

            unset($Bru);
            $result = true;
            return $result;
         }
         else
         {
            error_log("Not avaliable for current user. ProcessFlight id - " . $id . ". " .
                  "Username - " . $this->_user->username . ". Page FlightsController.php");
            $result['status'] = false;
            return $result['status'];
         }
      }
      else
      {
         error_log("Incorrect input data. DeleteFlight id - " . json_encode($extId) . ". Page FlightsController.php");
         $result['status'] = false;
         return $result['status'];
      }
   }

   public function GetUserInfo()
   {

      $uId = $this->_user->GetUserIdByName($this->_user->username);
      $this->_user->userInfo = $this->_user->GetUserInfo($uId);
      unset($U);

      return $this->_user->userInfo;
   }

   public function GetLastViewType()
   {
      $viewTypes = array(
         $this->flightActions["flightTwoColumnsListByPathes"],
         $this->flightActions["flightListTree"],
         $this->flightActions["flightListTable"]
      );

      $userId = $this->_user->userInfo['id'];
      $lastView = $this->_user->GetLastActionFromRange($userId, $viewTypes);
      unset($U);

      return $lastView;
   }

   public function GetLastFlightTwoColumnsListPathes()
   {
      $viewType = $this->flightActions["flightTwoColumnsListByPathes"];

      $userId = $this->_user->userInfo['id'];
      $actionsInfo = $this->_user->GetLastAction($userId, $viewType);
      unset($U);

      return $actionsInfo;
   }

   public function GetLastViewedFolder()
   {
      $viewType = $this->flightActions["showFolderContent"];

      $userId = $this->_user->userInfo['id'];
      $actionsInfo = $this->_user->GetLastAction($userId, $viewType);
      unset($U);

      return $actionsInfo;
   }

   public function BuildFlightsInTree($extFolder)
   {
      $shownFolderId = $extFolder;

      $flightColumn = "";

      $userId = $this->_user->userInfo['id'];
      unset($U);

      $Fd = new Folder();
      $shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
      $shownFolder = $shownFolderInfo['name'];
      unset($Fd);

      $flightColumn .= "<div class='FlightsListTileView'>" .
            "<div id='jstree' class='Tree'></div>".
            "<div id='jstreeContent' class='TreeContent'></div>".
            "</div>";

      return $flightColumn;
   }

   public function PrepareTree($extFolder)
   {
      $shownFolderId = $extFolder;

      $userId = $this->_user->userInfo['id'];
      $role = $this->_user->userInfo['role'];
      $adminRole = User::isAdmin($role);
      unset($U);

      $Fd = new Folder();
      if(User::isAdmin($role)) {
         $content = $Fd->GetAvaliableContent($shownFolderId, $userId, $adminRole);
      } else if(User::isModerator($role)) {
         $userIds = $this->_user->GetUserIdsByAuthor($this->_user->username);
         $content = $Fd->GetAvaliableContent($shownFolderId, $userIds);
      } else {
         $content = $Fd->GetAvaliableContent($shownFolderId, $userId);
      }

      unset($Fd);

      $relatedNodes = false;
      if(count($content) > 0)
      {
         $relatedNodes = $this->makeRecursive($content);
      }

      return $relatedNodes;
   }

   public function BuildSelectedFolderContent($extFolder)
   {
      $shownFolderId = $extFolder;

      $flightColumn = "";

      $Fd = new Folder();
      $flightsInPath = $this->GetFlightsByPath($shownFolderId);
      $subFolders = (array)$this->GetFoldersByPath($shownFolderId);
      $shownFolderInfo = $Fd->GetFolderInfo($shownFolderId);
      $shownFolder = $shownFolderInfo['name'];
      unset($Fd);
      foreach($subFolders as $key => $val)
      {
         $input = '<input class="ItemsCheck" type="checkbox" data-type="folder" data-folderpath="'.$shownFolderId.'" data-folderdestination="'.$val['id'].'">';
         $flightColumn .= "<div class='JstreeContentItemFolder'><label>" . $input . " " . $val['name']."</label></div>";
      }

      foreach($flightsInPath as $key => $val)
      {
         $name = $val['bort'] . ", " .  $val['voyage']  . ", " . date('d/m/y H:i', $val['startCopyTime'])  .
         ", " . $val['bruType']  . ", " . $val['departureAirport']  . "-" . $val['arrivalAirport'] ;

         $input = '<input class="ItemsCheck" type="checkbox" data-type="flight" data-folderpath="'.$shownFolderId.'" data-flightid="'.$val['id'].'">';
         $flightColumn .= "<div class='JstreeContentItemFlight'><label>" . $input . " " . $name . "</label></div>";
      }

      if((count($flightsInPath) == 0) && (count($subFolders) == 0))
      {
         $flightColumn = "<div>" . $this->lang->noContent . "</div>";
      }

      $result = array(
         'folderName' => $shownFolder,
         'content' => $flightColumn
      );

      return $result;
   }

   private function makeRecursive($d, $r = 0, $pk = 'parent', $k = 'id', $c = 'children') {
      $m = array();
      foreach ($d as $e) {
         isset($m[$e[$pk]]) ?: $m[$e[$pk]] = array();
         isset($m[$e[$k]]) ?: $m[$e[$k]] = array();
         $m[$e[$pk]][] = array_merge($e, array($c => &$m[$e[$k]]));
      }

      return $m[$r];//[0]; // remove [0] if there could be more than one root nodes
   }

   private function recursiveCollectChildren($branch, $parentId, &$childIds)
   {
      foreach ($branch as $childBranch)
      {
         if($childBranch['parent'] == $parentId)
         {
            $childIds[] = $childBranch['id'];
            if(!empty($childBranch['children']))
            {
               $searchedNewParentId = $childBranch['id'];
               $searchedNewBranch = $childBranch['children'];
               $this->recursiveCollectChildren($searchedNewBranch, $searchedNewParentId, $childIds);
            }
         }
         else
         {
            if(!empty($childBranch['children']))
            {
               $searchedNewBranch = $childBranch['children'];
               $this->recursiveCollectChildren($searchedNewBranch, $parentId, $childIds);
            }
         }
      }
   }

   public function BuildTable()
   {
      $table = sprintf("<table id='flightTable' cellpadding='0' cellspacing='0' border='0'>
            <thead><tr>");

      $table .= sprintf("<th name='checkbox' style='width:%s;'>%s</th>", "1%", "<input id='tableCheckAllItems' type='checkbox'/>");
      $table .= sprintf("<th name='bort'>%s</th>", $this->lang->bort);
      $table .= sprintf("<th name='voyage'>%s</th>", $this->lang->voyage);
      $table .= sprintf("<th name='flightTime'>%s</th>", $this->lang->flightTime);
      $table .= sprintf("<th name='uploadTime'>%s</th>", $this->lang->uploadTime);
      $table .= sprintf("<th name='bruType'>%s</th>", $this->lang->bruType);
      $table .= sprintf("<th name='departureAirport'>%s</th>", $this->lang->departureAirport);
      $table .= sprintf("<th name='arrivalAirport'>%s</th>", $this->lang->arrivalAirport);
      $table .= sprintf("<th name='performer'>%s</th>", $this->lang->performer);
      $table .= sprintf("<th name='status'>%s</th>", $this->lang->status);

      $table .= sprintf("</tr></thead><tfoot style='display: none;'><tr>");

      for($i = 0; $i < 10; $i++)
      {
         $table .= sprintf("<th></th>");
      }

      $table .= sprintf("</tr></tfoot><tbody></tbody></table>");
      return $table;
   }

   public function BuildTableSegment($extOrderColumn, $extOrderType)
   {
      $orderColumn = $extOrderColumn;
      $orderType = $extOrderType;

      $avaliableFlightIds = $this->_user->GetAvaliableFlights($this->_user->username);
      unset($U);
      $Fl = new Flight();
      $flights = $Fl->GetFlights($avaliableFlightIds, $orderColumn, $orderType);
      unset($Fl);

      $tableSegment = array();

      foreach($flights as $flight)
      {
         $execution = "-";
         if($flight['exTableName'] != '')
         {
            $execution = "+";
         }

         $tableSegment[] = array(
            "<input class='ItemsCheck' data-type='flight' data-flightid='".$flight['id']."' type='checkbox'/>",
            $flight['bort'],
            $flight['voyage'],
            date('d/m/y H:i', $flight['startCopyTime']),
            date('d/m/y H:i', $flight['uploadingCopyTime']),
            $flight['bruType'],
            $flight['departureAirport'],
            $flight['arrivalAirport'],
            $flight['performer'],
            $execution
         );
      }

      return $tableSegment;
   }

   public function ExportFlightsAndFolders($flightIds, $folderDest)
   {
      $Fd = new Folder();

      $uId = $this->_user->userInfo['id'];
      $role = $this->_user->userInfo['role'];
      $adminRole = User::isAdmin($role);
      unset($U);
      $allFolders = [];

      if(User::isModerator($role)) {
         $uId = $this->_user->GetUserIdsByAuthor($this->_user->username);
      }

      foreach ($folderDest as $dest) {
         $allFolders = $Fd->SubfoldersDeepScan($dest, $uId, $adminRole);
      }

      foreach ($allFolders as $folderId) {
         $flightIds = array_merge($flightIds,
               $Fd->GetFlightsByFolder($folderId, $uId, $adminRole));
      }
      unset($Fd);
      unset($U);

      $exportedFiles = array();
      $exportedFileName = '';
      error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
      $Fl = new Flight();
      $C = new DataBaseConnector();
      $Bru = new Bru();

      foreach ($flightIds as $flightId) {

         $flightInfo = $Fl->GetFlightInfo($flightId);

         $fileGuid = uniqid();

         $exportedFileDir = UPLOADED_FILES_PATH;
         $exportedFileName = $flightInfo['bort'] . "_" .
            date("Y-m-d", $flightInfo['startCopyTime'])  . "_" .
            $flightInfo['voyage'] . "_" . $fileGuid;
         $exportedFileRoot = $exportedFileDir . $exportedFileName;

         $headerFile['dir'] = $exportedFileDir;
         $headerFile['filename'] = "header_".$flightInfo['bort']."_".$flightInfo['voyage'].$fileGuid.".json";
         $headerFile['root'] = $headerFile['dir'].$headerFile['filename'];

         $exportedFiles[] = $headerFile;

         $apPrefixes = $Bru->GetBruApCycloPrefixes($flightInfo["bruType"]);

         for($i = 0; $i < count($apPrefixes); $i++)
         {
            $exportedTable = $C->ExportTable($flightInfo["apTableName"]."_".$apPrefixes[$i],
                  $flightInfo["apTableName"]."_".$apPrefixes[$i] . "_" . $fileGuid, $exportedFileDir);

            $exportedFiles[] = $exportedTable;

            $flightInfo["apTables"][] = array(
                  "pref" => $apPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         $bpPrefixes = $Bru->GetBruBpCycloPrefixes($flightInfo["bruType"]);

         for($i = 0; $i < count($bpPrefixes); $i++)
         {
            $exportedTable = $C->ExportTable($flightInfo["bpTableName"]."_".$apPrefixes[$i],
                  $flightInfo["bpTableName"]."_".$bpPrefixes[$i] . "_" . $fileGuid, $exportedFileDir);

            $exportedFiles[] = $exportedTable;

            $flightInfo["bpTables"][] = array(
                  "pref" => $bpPrefixes[$i],
                  "file" => $exportedTable["filename"]);
         }

         if($flightInfo["exTableName"] != "")
         {
            $exportedTable = $C->ExportTable($flightInfo["exTableName"],
                  $flightInfo["exTableName"] . "_" . $fileGuid, $exportedFileDir);
            $exportedFiles[] = $exportedTable;

            $flightInfo["exTables"] = $exportedTable["filename"];
         }

         $exportedFileDesc = fopen($headerFile['root'], "w");
         fwrite ($exportedFileDesc , json_encode($flightInfo));
         fclose($exportedFileDesc);

      }

      unset($Fl);
      unset($C);
      unset($Bru);

      $zip = new ZipArchive;
      if ($zip->open($exportedFileRoot . '.zip', ZipArchive::CREATE) === TRUE)
      {
         for($i = 0; $i < count($exportedFiles); $i++)
         {
            $zip->addFile($exportedFiles[$i]['root'], $exportedFiles[$i]['filename']);
         }
         $zip->close();
      }
      else
      {
         error_log('Failed zipping flight. Page asyncFileProcessor.php"');
      }

      for($i = 0; $i < count($exportedFiles); $i++)
      {
         if(file_exists($exportedFiles[$i]['root'])) {
            unlink($exportedFiles[$i]['root']);
         }
      }

      $zipURL = 'http';
      if (isset($_SERVER["HTTPS"]) &&  ($_SERVER["HTTPS"] == "on"))
      {
         $zipURL .= "s";
      }
      $zipURL .= "://";
      if ($_SERVER["SERVER_PORT"] != "80") {
         $zipURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
      }
      else
      {
         $zipURL .= $_SERVER["SERVER_NAME"];
      }
      $zipURL .=  UPLOADED_FILES_DIR . $exportedFileName . '.zip';

      error_reporting(E_ALL);

      if($exportedFileName == '') {
         return false;
      }

      return $zipURL;
   }

   public function GetLastSortTableType()
   {
      $viewType = $this->flightActions["segmentTable"];

      $userId = $this->_user->userInfo['id'];
      $actionsInfo = $this->_user->GetLastAction($userId, $viewType);
      unset($U);

      return $actionsInfo;
   }
}