<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/FlightsController.php");

$c = new FlightsController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action === "flightGeneralElements") {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege)) {
            if(isset($c->data['data'])) {
                $topMenu = $c->PutTopMenu();
                $leftMenu = $c->PutLeftMenu();
                $fileUploadBlock = $c->FileUploadBlock();
                $c->RegisterActionExecution($c->action, "executed");

                $answ = array(
                        'status' => 'ok',
                        'data' => array(
                            'topMenu' => $topMenu,
                            'leftMenu' => $leftMenu,
                            'fileUploadBlock' => $fileUploadBlock
                        )
                );

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "flightLastView")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $lastViewType = $c->GetLastViewType();
                $answ = array();

                if($lastViewType == null)
                {
                        $targetId = 0;
                        $targetName = 'root';
                        $viewAction = "flightListTree";
                        $flightsListTileView = $c->BuildFlightsInTree($targetId);
                        $c->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);

                        $answ["status"] = "ok";
                        $answ["type"] = $viewAction;
                        $answ["lastViewedFolder"] = $targetId;
                        $answ["data"] = $flightsListTileView;
                }
                else
                {
                    $flightsListByPath = "";
                    $viewAction = $lastViewType["action"];
                    if($viewAction === "flightListTree")
                    {
                        $actionsInfo = $c->GetLastViewedFolder();
                        $targetId = 0;
                        if($actionsInfo == null)
                        {
                            $targetName = 'root';
                            $flightsListTileView = $c->BuildFlightsInTree($targetId);
                            $c->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
                        }
                        else
                        {
                            $targetId = $actionsInfo['targetId'];
                            $targetName = $actionsInfo['targetName'];

                            $Fd = new Folder();
                            $folderInfo = $Fd->GetFolderInfo($targetId);
                            unset($Fd);

                            if(empty($folderInfo))
                            {
                                $targetId = 0;
                                $targetName = 'root';
                            }

                            $flightsListTileView = $c->BuildFlightsInTree($targetId);
                            $c->RegisterActionExecution($viewAction, "executed", 0, 'treeViewPath', $targetId, $targetName);
                        }

                        $answ["status"] = "ok";
                        $answ["type"] = $viewAction;
                        $answ["lastViewedFolder"] = $targetId;
                        $answ["data"] = $flightsListTileView;

                    }
                    else if($viewAction === "flightListTable")
                    {
                        $action = "flightListTable";

                        $table = $c->BuildTable();
                        $c->RegisterActionExecution($action, "executed", 0, 'tableView', '', '');
                        $actionsInfo = $c->GetLastSortTableType();

                        if(empty($actionsInfo)){
                            $actionsInfo['senderId'] = 3; // colunm 3 - start copy time
                            $actionsInfo['targetName'] = 'desc';
                        }

                        $answ["status"] = "ok";
                        $answ["type"] = $viewAction;
                        $answ["data"] = $table;
                        $answ["sortCol"] = $actionsInfo['senderId'];
                        $answ["sortType"] = $actionsInfo['targetName'];
                    }
                }

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "flightTwoColumnsListByPathes")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $lastViewType = $c->GetLastViewType();

                if($lastViewType == null)
                {
                    $targetId1 = 0; // root path
                    $targetId2 = 0;
                    $flightsListByPath = $c->BuildFlightListInTwoColumns($targetId1, $targetId2);
                    $c->RegisterActionExecution($c->action, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');
                }
                else
                {
                    $targetId1 = $lastViewType['senderId'];
                    $targetId2 = $lastViewType['targetId'];

                    $Fd = new Folder();
                    $folderInfo1 = $Fd->GetFolderInfo($targetId1);
                    $folderInfo2 = $Fd->GetFolderInfo($targetId2);
                    unset($Fd);

                    if(empty($folderInfo1))
                    {
                        $targetId1 = 0;
                    }

                    if(empty($folderInfo2))
                    {
                        $targetId2 = 0;
                    }

                    $flightsListByPath = $c->BuildFlightListInTwoColumns($targetId1, $targetId2);
                    $c->RegisterActionExecution($c->action, "executed", $targetId1, 'leftColumnFolderShown', $targetId2, 'rightColumnFolderShown');
                }

                $answ["status"] = "ok";
                $answ["data"] = $flightsListByPath;

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "flightListTree")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $flightsListTile = "";

                $actionsInfo = $c->GetLastViewedFolder();
                $targetId = 0;
                if($actionsInfo == null)
                {
                    $targetName = 'root';
                    $flightsListTileView = $c->BuildFlightsInTree($targetId);
                    $c->RegisterActionExecution($c->action, "executed", 0, 'treeViewPath', $targetId, $targetName);
                }
                else
                {
                    $targetId = $actionsInfo['targetId'];
                    $targetName = $actionsInfo['targetName'];

                    $Fd = new Folder();
                    $folderInfo = $Fd->GetFolderInfo($targetId);
                    unset($Fd);

                    if(empty($folderInfo))
                    {
                        $targetId = 0;
                        $targetName = 'root';
                    }

                    $flightsListTileView = $c->BuildFlightsInTree($targetId);
                    $c->RegisterActionExecution($c->action, "executed", 0, 'treeViewPath', $targetId, $targetName);
                }

                $answ["status"] = "ok";
                $answ["lastViewedFolder"] = $targetId;
                $answ["data"] = $flightsListTileView;

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "receiveTree")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $action = "receiveTree";

                $folderid = 0;
                $folderName = $c->lang->root;

                $relatedNodes = "";
                $actionsInfo = $c->GetLastViewedFolder();

                if($actionsInfo == null)
                {
                    $targetId = $folderid;
                    $targetName = 'root';
                    $relatedNodes = $c->PrepareTree($targetId);
                    $c->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
                }
                else
                {
                    $targetId = $actionsInfo['targetId'];
                    $targetName = $actionsInfo['targetName'];

                    $Fd = new Folder();
                    $folderInfo = $Fd->GetFolderInfo($targetId);
                    unset($Fd);

                    if(empty($folderInfo))
                    {
                        $targetId = 0;
                        $targetName = 'root';
                    }

                    $relatedNodes = $c->PrepareTree($targetId);
                    $c->RegisterActionExecution($action, "executed", 0, 'treeViewPath', $targetId, $targetName);
                }

                $tree[] = array(
                        "id" => (string)$folderid,
                        "text" => $folderName,
                        'type' => 'folder',
                        'state' =>  array(
                                "opened" => true
                        ),
                        'children' => $relatedNodes
                );

                if(($actionsInfo == null) || ($actionsInfo['targetId'] == 0))
                {
                    $tree[0]["state"] =  array(
                            "opened" => true,
                            "selected" => true
                    );
                }

                echo json_encode($tree);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "flightListTable")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $table = $c->BuildTable();
                $c->RegisterActionExecution($c->action, "executed", 0, 'tableView', '', '');

                $actionsInfo = $c->GetLastSortTableType();

                if(empty($actionsInfo)){
                    $actionsInfo['senderId'] = 3; // colunm 3 - start copy time
                    $actionsInfo['targetName'] = 'desc';
                }

                $answ = array(
                    'status' => 'ok',
                    'data' => $table,
                    'sortCol' => $actionsInfo['senderId'],
                    'sortType' => $actionsInfo['targetName']
                );

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == "segmentTable")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['data']))
            {
                $aoData = $c->data['data'];
                $sEcho = $aoData[sEcho]['value'];
                $iDisplayStart = $aoData[iDisplayStart]['value'];
                $iDisplayLength = $aoData[iDisplayLength]['value'];

                $sortValue = count($aoData) - 3;
                $sortColumnName = 'id';
                $sortColumnNum = $aoData[$sortValue]['value'];
                $sortColumnType = strtoupper($aoData[$sortValue + 1]['value']);

                switch ($sortColumnNum){
                    case(1):
                    {
                        $sortColumnName = 'bort';
                        break;
                    }
                    case(2):
                    {
                        $sortColumnName = 'voyage';
                        break;
                    }
                    case(3):
                    {
                        $sortColumnName = 'startCopyTime';
                        break;
                    }
                    case(4):
                    {
                        $sortColumnName = 'uploadingCopyTime';
                        break;
                    }
                    case(5):
                    {
                        $sortColumnName = 'bruType';
                        break;
                    }
                    case(6):
                    {
                        $sortColumnName = 'arrivalAirport';
                        break;
                    }
                    case(7):
                    {
                        $sortColumnName = 'departureAirport';
                        break;
                    }
                    case(8):
                    {
                        $sortColumnName = 'performer';
                        break;
                    }
                    case(9):
                    {
                        $sortColumnName = 'exTableName';
                        break;
                    }
                }

                $totalRecords = -1;
                $aaData["sEcho"] = $sEcho;
                $aaData["iTotalRecords"] = $totalRecords;
                $aaData["iTotalDisplayRecords"] = $totalRecords;

                $c->RegisterActionExecution($c->action, "executed", $sortColumnNum, "sortColumnNum", 0, $sortColumnType);

                $tableSegment = $c->BuildTableSegment($sortColumnName, $sortColumnType);
                $aaData["aaData"] = $tableSegment;

                echo(json_encode($aaData));
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == "showFolderContent")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['folderId']))
            {
                $folderid = intval($c->data['folderId']);
                $result = $c->BuildSelectedFolderContent($folderid);

                $folderContent = $result['content'];
                $targetId = $folderid;
                $targetName = $result['folderName'];
                $c->RegisterActionExecution($c->action, "executed", 0, 'treeViewPath', $targetId, $targetName);

                $answ = array(
                    'status' => 'ok',
                    'data' => $folderContent
                );

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "flightShowFolder")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['position']) &&
                    isset($c->data['fullpath']))
            {
                $position = $c->data['position'];
                $fullpath = $c->data['fullpath'];

                $flightsListByPath = "";

                $actionsInfo = $c->GetLastFlightTwoColumnsListPathes();
                if($position == 'Left')
                {
                    $targetId = $actionsInfo['targetId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($c->action, "executed", $fullpath, 'leftColumnFolderShown', $targetId, 'rightColumnFolderShown');
                }
                else if ($position == 'Right')
                {
                    $senderId = $actionsInfo['senderId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($c->action, "executed", $senderId, 'leftColumnFolderShown', $fullpath, 'rightColumnFolderShown');
                }

                $answ["status"] = "ok";
                $answ["data"] = $flightsListByPath;

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {

            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "flightGoUpper")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['position']) &&
                    isset($c->data['fullpath']))
            {
                $position = $c->data['position'];
                $fullpath = $c->data['fullpath'];

                $flightsListByPath = "";

                $Fd = new Folder();
                $folderInfo = $Fd->GetFolderInfo($fullpath);
                $fullpath = $folderInfo['path'];

                $actionsInfo = $c->GetLastFlightTwoColumnsListPathes();
                if($position == 'Left')
                {
                    $targetId = $actionsInfo['targetId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($c->action, "executed", $fullpath, 'leftColumnFolderShown', $targetId, 'rightColumnFolderShown');
                }
                else if ($position == 'Right')
                {
                    $senderId = $actionsInfo['senderId'];
                    $flightsListByPath = $c->BuildFlightColumnFromTwoColumns($fullpath, $position);
                    $c->RegisterActionExecution($c->action, "executed", $senderId, 'leftColumnFolderShown', $fullpath, 'rightColumnFolderShown');
                }

                $answ["status"] = "ok";
                $answ["data"] = $flightsListByPath;

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "folderCreateNew")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['folderName']) &&
                    isset($c->data['fullpath']))
            {
                $folderName = $c->data['folderName'];
                $fullpath = $c->data['fullpath'];

                $res = $c->CreateNewFolder($folderName, $fullpath);
                $c->RegisterActionExecution($c->action, "executed", 0, 'folderCreation', $fullpath, $folderName);

                $answ["status"] = "ok";
                $folderId = $res['folderId'];

                $answ["data"] = $res;
                $answ["data"]['folderId'] = $folderId;

                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "flightChangePath")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['sender']) &&
                    isset($c->data['target']))
            {
                $sender = $c->data['sender'];
                $target = $c->data['target'];

                $result = $c->ChangeFlightPath($sender, $target);
                $c->RegisterActionExecution($c->action, "executed", $sender, 'flightId', $target, "newPath");

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['error'] = 'Error during flight change path.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "folderChangePath")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['sender']) &&
                    isset($c->data['target']))
            {
                $sender = $c->data['sender'];
                $target = $c->data['target'];

                $result = $c->ChangeFolderPath($sender, $target);
                $c->RegisterActionExecution($c->action, "executed", $sender, 'folderId', $target, "newPath");

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['error'] = 'Error during folder change path.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "folderRename")
    {
        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['folderId']) &&
                    isset($c->data['folderName']))
            {
                $folderId = $c->data['folderId'];
                $folderName = $c->data['folderName'];

                $result = $c->RenameFolder($folderId, $folderName);
                $c->RegisterActionExecution($c->action, "executed", $folderId, 'folderId', $folderName, "newName");

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['error'] = 'Error during folder rename.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "itemDelete")
    {
        if(in_array(User::$PRIVILEGE_DEL_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['type']) &&
                    isset($c->data['id']))
            {
                $type = $c->data['type'];
                $id = intval($c->data['id']);

                if($type == 'folder')
                {
                    $result = $c->DeleteFolderWithAllChildren($id);

                    $answ = array();
                    if($result)
                    {
                        $answ['status'] = 'ok';
                        $c->RegisterActionExecution($c->action, "executed", $id, "itemId", $type, 'typeDeletedItem');
                    }
                    else
                    {
                        $answ['status'] = 'err';
                        $answ['data']['error'] = 'Error during folder deleting.';
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                    }
                    echo json_encode($answ);
                }
                else if($type == 'flight')
                {
                    $result = $c->DeleteFlight($id);

                    $answ = array();
                    if($result)
                    {
                        $answ['status'] = 'ok';
                        $c->RegisterActionExecution($c->action, "executed", $id, "itemId", $type, 'typeDeletedItem');
                    }
                    else
                    {
                        $answ['status'] = 'err';
                        $answ['data']['error'] = 'Error during flight deleting.';
                        $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                    }
                    echo json_encode($answ);
                }
                else
                {
                    $answ["status"] = "err";
                    $answ["error"] = "Incorect type. Post: ".
                            json_encode($_POST) . ". Page flights.php";
                    echo(json_encode($answ));
                }
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
    }
    else if($c->action === "itemProcess")
    {
        if(in_array(User::$PRIVILEGE_DEL_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['id']))
            {
                $id = intval($c->data['id']);
                $result = $c->ProcessFlight($id);

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                    $c->RegisterActionExecution($c->action, "executed", $id, "itemId");
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['data']['error'] = 'Error during flight process.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action == 'itemExport')
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['flightIds']) || isset($c->data['folderDest']))
            {
                $flightIds = [];
                $folderDest = [];
                if(isset($c->data['flightIds'])) {
                    if(is_array($c->data['flightIds'])) {
                        $flightIds = array_merge($flightIds, $c->data['flightIds']);
                    } else {
                        $flightIds[] = $c->data['flightIds'];
                    }
                }

                $folderDest = [];
                if(isset($c->data['folderDest']) &&
                    is_array($c->data['folderDest'])) {
                        $folderDest = array_merge($folderDest, $c->data['folderDest']);
                }

                $zipUrl = $c->ExportFlightsAndFolders($flightIds, $folderDest);

                $answ = array();
                if($zipUrl)
                {
                    $answ = [
                        'status' => 'ok',
                        'zipUrl' => $zipUrl
                    ];

                    $c->RegisterActionExecution($c->action, "executed", json_encode(array_merge($flightIds, $flightIds)), "itemId");
                }
                else
                {
                    $answ = [
                        'status' => 'empty',
                        'info' => 'No flights to export'
                    ];
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === "syncItemsHeaders")
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['ids']))
            {
                $ids = $c->data['ids'];
                $result = $c->SyncFlightsHeaders($ids);

                $answ = array();
                if($result)
                {
                    $answ['status'] = 'ok';
                    $c->RegisterActionExecution($c->action, "executed", implode(",", $ids), "itemsId");
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['data']['error'] = 'Error during flights headerSync.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". Page flights.php";
                $c->RegisterActionReject($c->action, "rejected", 0, $answ["error"]);
                echo(json_encode($answ));
            }
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }
    }
    else if($c->action === 'results')
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            header("Content-Type: text/comma-separated-values; charset=utf-8");
            header("Content-Disposition: attachment; filename=results.csv");  //File name extension was wrong
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);

            $list = $c->GetResults();

            $figPrRow = '';
            foreach ($list as $fields) {
                for($i = 0; $i < count($fields); $i++) {
                    $figPrRow .= $fields[$i] . ";";
                }

                $figPrRow = substr($figPrRow, 0, -1);
                $figPrRow .= PHP_EOL;
            }

            echo $figPrRow;
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }

        unset($U);
    }
    else if($c->getAction === 'events')
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            header("Content-Type: text/comma-separated-values; charset=utf-8");
            header("Content-Disposition: attachment; filename=events.csv");  //File name extension was wrong
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);

            $list = $c->GetEvents();

            $figPrRow = '';
            foreach ($list as $fields) {
                for($i = 0; $i < count($fields); $i++) {
                    $figPrRow .= $fields[$i] . ";";
                }

                $figPrRow = substr($figPrRow, 0, -1);
                $figPrRow .= PHP_EOL;
            }

            echo $figPrRow;
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }

        unset($U);
    }
    else if($c->getAction === 'coordinates')
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if(!isset($c->data['id']))
            {
                echo 'error';
            }

            header("Content-Type: text/comma-separated-values; charset=utf-8");
            header("Content-Disposition: attachment; filename=coordinates.kml");  //File name extension was wrong
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);

            $id = $c->data['id'];
            $list = $c->GetCoordinates($id);

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

            echo $figPrRow;
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = $c->lang->notAllowedByPrivilege;
            $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
            echo(json_encode($answ));
        }

        unset($U);
    }
    else
    {
        $msg = "Undefined action. Data: " . json_encode($_POST['data']) .
                " . Action: " . json_encode($_POST['action']) .
                " . Page: " . $c->curPage. ".";
        $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
        error_log($msg);
        echo($msg);
    }
}
else
{
    $msg = "Authorization error. Page: " . $c->curPage;
    $c->RegisterActionReject("undefinedAction", "rejected", 0, $msg);
    error_log($msg);
    echo($msg);
}
