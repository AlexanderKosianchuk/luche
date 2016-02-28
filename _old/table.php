<?php  

require_once("includes.php"); 
	
$V = new TableView($_POST);

if ($V->IsAppLoggedIn())
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();
	$V->GetUserPrivilege();

	$V->PutHeader();
	$V->PutMainMenu();
	
	if(in_array(PRIVILEGE_VIEW_FLIGHTS, $V->privilege))
	{
		$V->PutInfo();
		$V->PutTable();
	}
	else
	{
		echo($V->lang->notAllowedByPrivilege);
	}
	
	
	$V->PutScripts();
	$V->PutFooter();
}
else
{
	$V->PutCharset();
	$V->PutTitle();
	$V->PutStyleSheets();

	$V->PutHeader();

	$V->ShowLoginForm();

	$V->PutFooter();
}	
unset($V);

?>