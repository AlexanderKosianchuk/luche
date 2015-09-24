<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php"); 

//================================================================
//╔══╗
//║╔╗║
//║╚╝╚╦═╦╗╔╗
//║╔═╗║╔╣║║║
//║╚═╝║║║╚╝║
//╚═══╩╝╚══╝
//================================================================
class BruModel
{
	public $curPage = 'bruTypesPage';
	
	private $ulogin;
	private $username;
	
	public $privilege;
	public $lang;

	public $action;
	public $data;

	function __construct($post, $session)
	{
		$L = new Language();
		$this->lang = $L->GetLanguage("ru", $this->curPage);
		$this->bruActions = (array)$L->GetServiceStrs($this->curPage);
		unset($L);

		$this->ulogin = new uLogin();
		$this->ulogin->Autologin();
		if(isset($session['username']))
		{
			$this->username = $session['username'];
		}
		else
		{
			$this->username = '';
		}
		
		//even if flight was selected if file send this variant will be processed
		if((isset($post['action']) && ($post['action'] != '')) && 
			(isset($post['data']) && ($post['data'] != '')))
		{
			$this->action = $post['action'];
			$this->data = $post['data'];			
		}
		else
		{
			$msg = "Incorect input. Data: " . json_encode($post['data']) . 
				" . Action: " . json_encode($post['action']) . 
				" . Page: " . $this->curPage. ".";
			echo($msg);
			error_log($msg);
		}
	}
	
	public function IsAppLoggedIn()
	{
		return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
	}
	
	public function GetUserPrivilege()
	{
		$this->username = $_SESSION['username'];
		$Usr = new User();
		$this->privilege = $Usr->GetUserPrivilege($this->username);
		unset($Usr);
	}
	
	public function PutTopMenu()
	{	
		$topMenu = "<div id='topMenuBruType' class='TopMenu'></div>";	
		return $topMenu;
	}
	
	public function PutLeftMenu()
	{
		$leftMenu = sprintf("<div id='leftMenuBruType' class='LeftMenu'>");
	
		$Usr = new User();	
		/*if(in_array($Usr->bruTypesPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[1], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[2], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[3], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[4], $this->privilege))*/
		if(false)
		{
			$leftMenu .= "<div id='editBruGeneralInfoLeftMenuRow' class='LeftMenuRowOptions '>" .
					"<img class='LeftMenuRowIcon' src='stylesheets/basicImg/generalInfo.png'></img>" .
					" " . $this->lang->generalInfo .
					"</div>";
		}

		if(in_array($Usr->bruTypesPrivilegeArr[0], $this->privilege) ||
				in_array($Usr->bruTypesPrivilegeArr[3], $this->privilege))
		{
			$leftMenu .= "<div id='editBruTplsLeftMenuRow' class='LeftMenuRowOptions'>" .
					"<img class='LeftMenuRowIcon' src='stylesheets/basicImg/templates.png'></img>" .
					" " . $this->lang->templates .
					"</div>";
		}

		if(false)
		{
			$leftMenu .= "<div id='editBruCycloLeftMenuRow' class='LeftMenuRowOptions'>" .
					"<img class='LeftMenuRowIcon' src='stylesheets/basicImg/paramsList.png'></img>" .
					" " . $this->lang->cyclo .
					"</div>";
		}
			
		if(false)
		{
			$leftMenu .= "<div id='editBruEventsLeftMenuRow' class='LeftMenuRowOptions'>" .
					"<img class='LeftMenuRowIcon' src='stylesheets/basicImg/events.png'></img>" .
					" " . $this->lang->events .
					"</div>";
		}
			
		$leftMenu .= sprintf("</div>");
	
		return $leftMenu;
	}	
	
	public function PutWorkspace()
	{
		//MainContainer
		$workspace = "<div id='bruTypeWorkspace' class='WorkSpace'></div>";
	
		return $workspace;
	}
	
	public function GetUserInfo()
	{
		$U = new User();
		$uId = $U->GetUserIdByName($this->username);
		$userInfo = $U->GetUserInfo($uId);
		unset($U);
	
		return $userInfo;
	}
	
	public function GetTplsList($extBruTypeId)
	{
		$bruTypeId = $extBruTypeId;
		$tplsListWithControlButtns = '';
		
		/*$U = new User();
		$uId = $U->GetUserIdByName($this->username);
		$userInfo = $U->GetUserInfo($uId);
		unset($U);*/
				
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfoById($bruTypeId);
		$bruType = $bruInfo['bruType'];
		$paramSetTemplateListTableName = $bruInfo['paramSetTemplateListTableName'];
		$cycloApTableName = $bruInfo['gradiApTableName'];
		$cycloBpTableName = $bruInfo['gradiBpTableName'];
		$stepLength = $bruInfo['stepLength'];
		//$this->info = array_merge($this->info, $flightInfo, $bruInfo);
		$prefixArr = $Bru->GetBruApCycloPrefixes($bruType);
		unset($Bru);
		
		$PSTempl = new PSTempl();
		//if no template table - create it
		$PSTTableName = $paramSetTemplateListTableName;
		if($PSTTableName == "")
		{
			$dummy = substr($cycloApTableName, 0, -3);
			$paramSetTemplateListTableName = $dummy . "_pst";
			$PSTTableName = $paramSetTemplateListTableName;
			$PSTempl->CreatePSTTable($PSTTableName);
			$PSTempl->AddPSTTable($bruType, $PSTTableName);
		}
		
		//here builds template options list
		$tplsListWithControlButtns .= $this->BuildTplOptionList($paramSetTemplateListTableName, $bruType);
		
		$foundedEventsTplName = $this->lang->foundedEventsTplName;
		
		//if performed exception search and isset events
		if(!(empty($excEventsList)))
		{
			$params	= "";
			$paramsToAdd = array();
			for($i = 0; $i < count($excEventsList); $i++)
			{
				$params .= $excEventsList[$i] . ", ";
					$paramsToAdd[] = $excEventsList[$i];
			}
			$params = substr($params, 0, -2);
				
			$Bru = new Bru();
			$paramNamesStr = $Bru->GetParamNames($bruType, $paramsToAdd);
		
			$tplsListWithControlButtns .= "<option id='tplOption' " .
					"name='".EVENTS_TPL_NAME."'  " .
							"data-comment='".$paramNamesStr."'  " .
							"data-params='".$params."'  " .
							"data-defaulttpl='true'  " .
					"selected> " .
							$foundedEventsTplName . " - ".$params."</option>";
		
			$this->CreateTemplate($flightId, $paramsToAdd, EVENTS_TPL_NAME);
		}
		
		unset($PSTempl);
	
		return $tplsListWithControlButtns;
	}
	
	private function BuildTplOptionList($extParamSetTemplateListTableName, $extBruType) {
		$username = $this->username;
		$bruType = $extBruType;
		$paramSetTemplateListTableName = $extParamSetTemplateListTableName;
		$PSTempl = new PSTempl ();
		$PSTList = $PSTempl->GetPSTList ( $paramSetTemplateListTableName, $username );
		$defaultPSTName = $PSTempl->GetDefaultPST($paramSetTemplateListTableName, $username);
		unset ( $PSTempl );
		
		$optionsStr = "";
		
		$Bru = new Bru ();
		for($i = 0; $i < count ( $PSTList ); $i ++) {
			$PSTRow = $PSTList [$i];
			$paramsArr = $PSTRow [1];
			$params = implode ( ", ", $paramsArr );
			
			$paramNamesStr = $Bru->GetParamNames ( $bruType, $paramsArr );
			
			if ($PSTRow [0] == $defaultPSTName) {
				$optionsStr .= "<option id='tplOption' " . 
					"name='" . $PSTRow [0] . "'  " . 
					"title='" . $params . "' " . 
					"data-comment='" . $paramNamesStr . "'  " . 
					"data-params='" . $params . "'  " . 
					"data-defaulttpl='true'  " . "selected> " . 
					"(" . $this->lang->defaultTpl . ") " . $PSTRow [0] . " - " . $params . "</option>";
			} else {
				if (($PSTRow [0] != EVENTS_TPL_NAME) && ($PSTRow [0] != PARAMS_TPL_NAME)) {
					$optionsStr .= "<option id='tplOption' " . 
						"name='" . $PSTRow [0] . "'  " . 
						"title='" . $params . "' " . 
						"data-comment='" . $paramNamesStr . "'  " . 
						"data-params='" . $params . "'  " . 
						"data-defaulttpl='true'  " . 
						"selected> " . $PSTRow [0] . " - " . $params . "</option>";
				}
			}
		}
		unset ( $Bru );
		
		return $optionsStr;
	}
	
	public function ShowParamList($extBruTypeId)
	{
		$bruTypeId = $extBruTypeId;
	
		$Bru = new Bru();
		$bruInfo = $Bru->GetBruInfoById($bruTypeId);
		$bruType = $bruInfo['bruType'];
		$flightApHeaders = $Bru->GetBruApHeaders($bruType);
		$flightBpHeaders= $Bru->GetBruBpHeaders($bruType);
		unset($Bru);
	
		$paramList = sprintf ("<div class='BruTypeTemplatesParamsListContainer'>");
		$paramList .= sprintf ("<div class='BruTypeTemplatesApList'>");
	
		for ($i = 0; $i < count($flightApHeaders); $i++)
		{
			$paramList .= sprintf ("
				<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
					data-colorpicker='false' readonly/>
				<label style='display:inline;'><input type='checkbox' class='ParamsCheckboxGroup' value='%s'/>
				%s, %s </label>
				</br>",
					$flightApHeaders[$i]['color'],
					$flightApHeaders[$i]['color'],
					$flightApHeaders[$i]['code'],
					$flightApHeaders[$i]['color'],
					$flightApHeaders[$i]['code'],
					$flightApHeaders[$i]['name'],
					$flightApHeaders[$i]['code']);
		}
	
			$paramList .= sprintf ("</div><div class='BruTypeTemplatesBpList'>");
	
		for ($i = 0; $i < count($flightBpHeaders); $i++)
		{
			$paramList .= sprintf ("<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
				data-colorpicker='false' readonly/>
			<label style='display:inline;'>
			<input type='checkbox' class='ParamsCheckboxGroup' value='%s'/>
			%s, %s</label></br>",
					$flightBpHeaders[$i]['color'],
					$flightBpHeaders[$i]['color'],
					$flightBpHeaders[$i]['code'],
					$flightBpHeaders[$i]['color'],
					$flightBpHeaders[$i]['code'],
					$flightBpHeaders[$i]['name'],
					$flightBpHeaders[$i]['code']);
		}
	
		$paramList .= sprintf("</div></div></div></br>");
		return $paramList;
	}
	
	public function CreateTemplate($extBruTypeId, $extTplName, $extParams)
	{
		$bruTypeId = $extBruTypeId;
		$tplName = $extTplName;
		$paramsToAdd = $extParams;
		$username = $this->username;
		
		$Bru = new Bru ();
		$bruInfo = $Bru->GetBruInfoById ($bruTypeId);
		$gradiApTableName = $bruInfo ['gradiApTableName'];
		$gradiBpTableName = $bruInfo ['gradiBpTableName'];
		$PSTTableName = $bruInfo ['paramSetTemplateListTableName'];
		
		$paramsWithType = array ();
		$Ch = new Channel ();
		
		for($i = 0; $i < count ( $paramsToAdd ); $i ++) {
			$paramInfo = $Bru->GetParamInfoByCode ( $gradiApTableName, $gradiBpTableName, $paramsToAdd [$i] );
			if ($paramInfo ['paramType'] == PARAM_TYPE_AP) {
				
				$paramsWithType [PARAM_TYPE_AP] [] = array (
						'code' => $paramsToAdd [$i],
						'min' => -1,
						'max' => 1
				);
			} else if ($paramInfo ['paramType'] == PARAM_TYPE_BP) {
				$paramsWithType [PARAM_TYPE_BP] [] = array (
						'code' => $paramsToAdd [$i] 
				);
			}
		}
		unset ( $Bru );
		
		$PSTempl = new PSTempl ();
		$PSTempl->DeleteTemplate ( $PSTTableName, $tplName, $username);
		
		$apCount = count ( $paramsWithType [PARAM_TYPE_AP] );
		
		for($i = 0; $i < count ( $paramsWithType [PARAM_TYPE_AP] ); $i ++) {
			$paramCode = $paramsWithType [PARAM_TYPE_AP] [$i];
			$yMax = $paramsWithType [PARAM_TYPE_AP] [$i] ['max'];
			$yMin = $paramsWithType [PARAM_TYPE_AP] [$i] ['min'];
			$curCorridor = 0;
			
			if ($yMax > 0) {
				$curCorridor = ($yMax - $yMin);
			} else {
				$curCorridor = - ($yMin - $yMax);
			}
			
			$axisMax = $yMax + ($i * $curCorridor);
			$axisMin = $yMin - (($apCount - $i) * $curCorridor);
			
			$PSTempl->AddParamToTemplateWithMinMax ( $PSTTableName, $tplName, $paramCode ['code'], $axisMin, $axisMax, $username);
		}
		
		if (isset ( $paramsWithType [PARAM_TYPE_BP] )) {
			$busyCorridor = (($apCount - 1) / $apCount * 100);
			$freeCorridor = 100 - $busyCorridor; // 100%
			
			$bpCount = count ( $paramsWithType [PARAM_TYPE_BP] );
			$curCorridor = $freeCorridor / $bpCount;
			$j = 0;
			
			for($i = $apCount; $i < $apCount + $bpCount; $i ++) {
				
				$axisMax = 100 - ($curCorridor * $j);
				$axisMin = 0 - ($curCorridor * $j);
				
				$PSTempl->AddParamToTemplateWithMinMax ( $PSTTableName, $tplName, $paramsWithType [PARAM_TYPE_BP] [$j] ['code'], $axisMin, $axisMax, $username);
				$j ++;
			}
		}
		
		unset ( $Ch );
		unset ( $PSTempl );
		
		return "ok";
	}
	
	public function DeleteTemplate($extBruTypeId, $extTplName)
	{
		$bruTypeId = $extBruTypeId;
		$tplName = $extTplName;
		$username = $this->username;
		
		$Bru = new Bru ();
		$bruInfo = $Bru->GetBruInfoById ($bruTypeId);
		$PSTTableName = $bruInfo ['paramSetTemplateListTableName'];
		unset ( $Bru );
		
		$PSTempl = new PSTempl ();
		$PSTempl->DeleteTemplate ( $PSTTableName, $tplName, $username);
		unset($PSTempl);
		
		return "ok";
	}
	
	public function SetDefaultTemplate($extBruTypeId, $extTplName)
	{
		$bruTypeId = $extBruTypeId;
		$tplName = $extTplName;
		$username = $this->username;
	
		$Bru = new Bru ();
		$bruInfo = $Bru->GetBruInfoById ($bruTypeId);
		$PSTTableName = $bruInfo ['paramSetTemplateListTableName'];
		unset ( $Bru );
	
		$PSTempl = new PSTempl ();
		$PSTempl->SetDefaultTemplate($PSTTableName, $tplName, $username);
		unset($PSTempl);
	
		return "ok";
	}
	
	public function RegisterActionExecution($extAction, $extStatus,
			$extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
	{
		$action = $extAction;
		$status = $extStatus;
		$senderId = $extSenderId;
		$senderName = $extSenderName;
		$targetId = $extTargetId;
		$targetName = $extTargetName;
	
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
	
		$U = new User();
		$U->RegisterUserAction($action, $status, $userId,
				$senderId, $senderName, $targetId, $targetName);
	
		unset($U);
	}
	
	public function RegisterActionReject($extAction, $extStatus,
			$extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
	{
		$action = $extAction;
		$status = $extStatus;
		$senderId = $extSenderId;
		$senderName = $extSenderName;
		$targetId = $extTargetId;
		$targetName = $extTargetName;
		$userInfo = $this->GetUserInfo();
		$userId = $userInfo['id'];
	
		$U = new User();
		$U->RegisterUserAction($action, $status, $userId,
				$senderId, $senderName, $targetId, $targetName);
	
		unset($U);
	}
}

?>