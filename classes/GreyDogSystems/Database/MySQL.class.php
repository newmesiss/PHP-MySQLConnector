<?php
// MySQL connection class (2nd rewrite)
// Version 3.5
// Copyright Moises Rodriguez P (SDogo WildDog) - GreyDog Systems 2015

namespace GreyDogSystems\Database;

class MySQL{
	private $MySQLObject = null;
	// Global connection config variables for the class
	private $Server = null;
	private $User = null;
	private $Pass = null;
	private $Database = null;
	private $Port = null;
	private $Socket = null;
	// Filtering and errors variables
	private $InputEncoding = null;
	private $OutputEncoding = null;
	private $RaiseErrors = true;
	private $Status = 'OK';
	private $Prop_IsAvailable = false;
	private $ErrorStack = array();
	// Variables for statements
	private $StmtObject = null;
	private $StmtParams = array();
	private $StmtParamTypes = array();

	private $Filters = array(
		'numbers' => '/[^0-9]/',
		'alphans' => '/[^a-zA-ZñÑ]/',
		'alphaws' => '/[^a-zA-ZñÑ ]/',
		'alphanumns' => '/[^0-9a-zA-ZñÑáéíóúàèìòùÁÉÍÓÚÀÈÌÒÙäëïöüÄËÏÖÜâêîôûÂÊÎÔÛ]/',
		'alphanumws' => '/[^0-9a-zA-ZñÑáéíóúàèìòùÁÉÍÓÚÀÈÌÒÙäëïöüÄËÏÖÜâêîôûÂÊÎÔÛ ]/',
		'printable' => '/[^0-9a-zA-ZñÑáéíóúàèìòùÁÉÍÓÚÀÈÌÒÙäëïöüÄËÏÖÜâêîôûÂÊÎÔÛ,.-;:_´ç+`¨Ç*^¡\'?¿ºª!"·$%&/()="*@|\\{}[]<> ]/',
		'javascript' => '@<script [^>]*?>.*?@si',
		'htmltags' => '@< [/!]*?[^<>]*?>@si',
		'styles' => '@<style [^>]*?>.*?</style>@siU',
		'multiline' => '@< ![sS]*?--[ tnr]*>@',
		'float' => '/[^0-9.,]/',
		'hexadecimal' => '/[^0-9a-fA-F]/'
		);
	Private $Errors = array(1 => 'Server unavailable',
		2 => 'Wrong credentials',
		3 => 'Database not found',
		4 => 'The conection already exist!',
		5 => 'Conection already closed!',
		6 => "The connection is not initialized",
		7 => 'The extension \'mysqli\' doesn\'t exist. Please check your php.ini',
		10 => 'The query has an error',
		11 => 'Unknown query format output',
		12 => 'Statement prepare was specting a string for query value',
		13 => 'Statement creation failed. Please check your query',
		14 => 'Statement execution failed',
		15 => 'Statement error',
		16 => 'Bind failed. You need at least one parameter',
		17 => 'Statement object not initialized',
		18 => 'Variable type unknown',
		19 => 'Blob type not implemented yet. Sorry.',
		20 => 'Type definition name must be an string'
		);
	// Constructor and basic functions
	public function __construct($aConfigData){
		if (in_array('mysqli',get_loaded_extensions() )) {
			// Parsing of settings
			if (is_array($aConfigData)){
				// MySQL code
				if(array_key_exists("server", $aConfigData)){
					if (is_string($aConfigData['server'])) {
						$this->Server = $aConfigData['server'];
					}
				}

				if(array_key_exists("user", $aConfigData)){
					if (is_string($aConfigData['user'])) {
						$this->User = $aConfigData['user'];
					}
				}

				if(array_key_exists("pass", $aConfigData)){
					if (is_string($aConfigData['pass'])) {
						$this->Pass = $aConfigData['pass'];
					}
				}

				if(array_key_exists("database", $aConfigData)){
					if (is_string($aConfigData['database'])) {
						$this->Database = $aConfigData['database'];
					}
				}

				if(array_key_exists("port", $aConfigData)){
					if (is_string($aConfigData['port']) or is_int($aConfigData['port'])){
						$this->Port = $aConfigData['port'];
					}
				}

				if(array_key_exists("socket", $aConfigData)){
					if (is_string($aConfigData['socket'])) {
						$this->Socket = $aConfigData['socket'];
					}
				}

				if(array_key_exists("raiseerrors", $aConfigData)){
					if (is_bool($aConfigData['raiseerrors'])) {
						$this->RaiseErrors = $aConfigData['raiseerrors'];
					}

				}

				// Creating the connection
				if ($this->MySQLObject === null){
					\mysqli_report(MYSQLI_REPORT_STRICT);
					if ($this->User !=NULL and $this->Server!=null and $this->Pass!=NULL) {
						try{
							if ($this->Database === null){
								$this->MySQLObject = new \mysqli($this->Server, $this->User, $this->Pass);
							}else{
								if ($this->Port != null){
									$this->MySQLObject = new \mysqli($this->Server, $this->User, $this->Pass, $this->Database, $this->Port);
								}else{
									if ($this->Port != null and $this->Socket != null){
										$this->MySQLObject = new \mysqli($this->Server, $this->User, $this->Pass, $this->Database, $this->Port, $this->Socket);
									}else{
										$this->MySQLObject = new \mysqli($this->Server, $this->User, $this->Pass, $this->Database);
									}
								}
							}
						}catch (\mysqli_sql_exception $e){
							switch ($e->getCode()){
								case 2002:
									$this->RaiseError(1, 'Unable to connecto to server "' . $this->Server . '"');
									break;
								case 1045:
									$this->RaiseError(2, 'Access denied. Check your credentials.');
									break;
							}
						}
					}

					if ($this->MySQLObject!=NULL and $this->Database!=NULL) {
						try{
							\mysqli_select_db($this->MySQLObject, $this->Database);
						}catch (\mysqli_sql_exception $e){
							echo "Service unavailable ". $e->getCode() ."<br>";
						}
					}
					if (count($this->ErrorStack) === 0 and $this->MySQLObject!=NULL){
						$this->Prop_IsAvailable = true;
					}
				}else{
					$this->RaiseError(4);
				}
			}
		}else{
			$this->RaiseError(7);
		}

	}

	private function RaiseError($iErrCode, $sErrMSG = null){
		$this->Status = 'WITHERRORS';
		if (key_exists($iErrCode, $this->Errors)){
			if ($sErrMSG === null){
				$this->ErrorStack[] = array('MSG' => $this->Errors[$iErrCode], 'CODE' => $iErrCode);
			}else{
				$this->ErrorStack[] = array('MSG' => $sErrMSG, 'CODE' => $iErrCode);
				$this->Errors[$iErrCode] = $sErrMSG;
			}
		}else{
			$this->ErrorStack[] = array('MSG' => $sErrMSG, 'CODE' => $iErrCode);
		}
		if ($this->RaiseErrors == true){
			echo $this->Errors[$iErrCode];
		}
	}

	private function ResetErrors(){
		$this->Status = 'OK';
		$this->ErrorStack = array();
	}

	public function ReleaseConection(){
		if(@mysqli_close($this->MySQLObject)){
			$this->Prop_IsAvailable = false;
			return true;
		}else{
			$this->RaiseError(5);
			return false;
		}
	}
	// Code for "normal" querys
	private function ExecuteQuery($sQuery){
		if ($this->IsAvailable()) {
			$Result = \mysqli_query($this->MySQLObject, $sQuery);
			if(!$Result){
				$QueryError = 'MySQL Error: ' . \mysqli_error($this->MySQLObject) . "<br><br>Query:<br>" . $sQuery;
				$this->RaiseError(10, $QueryError);
				exit;
			}else{
				@mysqli_next_result($this->MySQLObject);
				return $Result;
			}
		}else{
			$this->RaiseError(6);
			return false;
		}
	}

	public function GetQueryResults($soQuery, $sFormat = 'ARRAY'){
		if ($this->IsAvailable()) {
			$sFormat = strtoupper($sFormat);
			if (is_string($soQuery)){
				$soQuery = $this->ExecuteQuery($soQuery);
			}
			switch($sFormat){
				case 'ARRAY':
					// Returns the query result as a array
					$Results = array();
					while ($row = $this->GetQueryResults($soQuery, 'RAW')){
						$Results[] = $row;
					}
					return $Results;
					break;
				case 'JSON':
					// Returns the query result as a JSON string
					$Results = $this->GetQueryResults($soQuery, 'ARRAY');
					return json_encode($Results);
					break;
				case 'RAW':
					// Returns a memory object in mysqli format
					$Result = \mysqli_fetch_array($soQuery);
					if ($Result){
						return $Result;
					}else{
						return false;
					}
					break;
				default:
					$this->RaiseError(11);
					return false;
					break;
			}
		}else{
			$this->RaiseError(6);
			return false;
		}
	}
	// Code for statements
	// The constructor for statements
	public function StatementBindValue($sType, $oParam){
		if ($this->IsAvailable()){
			if ($this->StmtObject === null){
				$this->RaiseError(17);
				return false;
			}else{
				if (is_string($sType)){
					$sType = strtolower(substr($sType, 0, 1));
					switch($sType){
						case 'i':
							$this->StmtParamTypes[0] .= 'i';
							$this->StmtParams[] = $this->FilterString($oParam, $this->Filters['numbers']);
							return true;
							break;
						case 'd':
							$this->StmtParamTypes[0] .= 'd';
							$this->StmtParams[] = $this->FilterString($oParam, $this->Filters['float']);
							return true;
							break;
						case 's':
							$this->StmtParamTypes[0] .= 's';
							$this->StmtParams[] = mysqli_real_escape_string($this->MySQLObject, $oParam);
							return true;
							break;
						case 'b':
							$this->RaiseError(19);
							return false;
							break;
						default:
							$this->RaiseError(18);
							return false;
							break;
					}
				}else{
					$this->RaiseError(20);
					return false;
				}
			}
		}else{
			$this->RaiseError(6);
			return false;
		}
	}

	public function StatementPrepare($sQuery){
		if ($this->IsAvailable()){
			$this->StatementDestroy();
			if (is_string($sQuery)){
				if ($this->StmtObject = mysqli_prepare($this->MySQLObject, $sQuery)){
					return true;
				}else{
					$this->RaiseError(13);
					return false;
				}
			}else{
				$this->RaiseError(12);
				return false;
			}
		}else{
			$this->RaiseError(6);
			return false;
		}
	}

	public function StatementExecute(){
		if ($this->IsAvailable()){
			if ($this->StmtObject === null){
				$this->RaiseError(17);
				return false;
			}else{
				$this->StatementParamMerge();
				$Result = @mysqli_stmt_execute($this->StmtObject);
				if ($Result){
					return true;
				}else{
					$this->RaiseError(14);
					return false;
				}
			}
		}else{
			$this->RaiseError(6);
			return false;
		}
	}

	public function StatementStoreResult(){
		if ($this->IsAvailable()){
			if ($this->StmtObject === null){
				$this->RaiseError(17);
				return false;
			}else{
				mysqli_stmt_store_result($this->StmtObject);
				return true;
			}
		}else{
			$this->RaiseError(6);
			return false;
		}
	}

	public function StatementFetchResult($sOutputFormat = 'ARRAY'){
		if ($this->IsAvailable()){
			if ($this->StmtObject === null){
				$this->RaiseError(17);
				return false;
			}else{
				if (!is_bool($this->StmtObject)){
					$Metadata = mysqli_stmt_result_metadata($this->StmtObject);
					$Fields = mysqli_fetch_fields($Metadata);
					$statementParams = '';
					$Result = array();

					foreach($Fields as $Key => $Field){
						if(empty($statementParams)){
							$statementParams .= "\$Result['" . $Field->name . "']";
						}else{
							$statementParams .= ", \$Result['" . $Field->name . "']";
						}
					}
					$statment = 'mysqli_stmt_bind_result($this->StmtObject,' . $statementParams . ');';
					eval($statment);
					$Ret = array();
					$Counter = 0;
					while (mysqli_stmt_fetch($this->StmtObject)){
						foreach($Result as $Key => $Value){
							$Ret[$Counter][] = $Result[$Key];
							$Ret[$Counter][$Key] = $Result[$Key];
						}
						$Counter = $Counter + 1;
					}
					// Selecting the output format. Only two options available
					$sOutputFormat = strtolower($sOutputFormat);
					if ($sOutputFormat == 'json'){
						return json_encode($Ret);
					}else{
						return $Ret;
					}
				}else{
					return false;
				}
			}
		}else{
			$this->RaiseError(6);
			return false;
		}
	}

	private function StatementReferenceParams($aParams){
		$refs = array();
		foreach($aParams as $key => $value){
			$refs[$key] = &$aParams[$key];
		}
		return $refs;
	}

	private function StatementParamMerge(){
		if (strlen($this->StmtParamTypes[0]) > 0){
			$Params = array_merge(array($this->StmtObject), $this->StmtParamTypes, $this->StmtParams);
			$Result = @call_user_func_array('mysqli_stmt_bind_param', $this->StatementReferenceParams($Params));
			if ($Result){
				return true;
			}else{
				$this->RaiseError(15);
				return false;
			}
		}
	}

	private function StatementDestroy(){
		// Destroy the items
		unset($this->StmtParams);
		unset($this->StmtParamTypes);
		unset($this->StmtObject);
		// Recreate the variables
		$this->StmtParams = array();
		$this->StmtParamTypes = array();
		$this->StmtParamTypes[0] = '';
		$this->StmtObject = null;
	}

	private function StatementCreateArray(){
		if (count($this->StmtParams) > 0){
			$NewArray = array();
			foreach($this->StmtParams as $Key => $Value){
				$NewArray[$Key] = null;
			}
			return $NewArray;
		}
	}

	// Cleaning functions
	public function EncodeString($sInput, $sFormat = 'UTF8'){
		$sFormat = strtoupper($sFormat);
		switch($sFormat){
			case 'UTF':

				break;
			default:
				return $sInput;
				break;
		}
	}

	public function RecodeOutput($oData){
		if ($this->OutputEncoding != null){
			$Type = $this->GetVarType($oData);
			switch ($oData){
				case 'string':

					break;
				case 'array':

					break;
				default:

					break;
			} // switch
			foreach($aData as $Key => $Value){
			}
		}
	}

	private function GetVarType($oVar){
		return strtolower(gettype($oVar));
	}

	private function FilterString($sInput, $sMask = 'default'){
		if (is_string($sInput)){
			if($sMask === 'default'){
				return preg_replace($this->Filters['printable'], "", $sInput);
			}else{
				if (is_string($sMask) or is_array($sMask)){
					return preg_replace($sMask, "", $sInput);
				}else{
					return $sInput;
				}
			}
		}else{
			return $sInput;
		}
	}

	public function IsAvailable(){
		return $this->Prop_IsAvailable;
	}
	// Debug functions
	public function DebugServerInfo(){
		$ServerInfo = array(
			'SOFTWARE' => @mysqli_get_server_info($this->MySQLObject),
			'PROTOCOL' => @mysqli_get_proto_info($this->MySQLObject),
			'HOSTINFO' => @mysqli_get_host_info($this->MySQLObject)
			);
		return $ServerInfo;
	}

	public function DebugConfig(){
		$Config = array(
			'SERVER' => $this->Server,
			'USER' => $this->User,
			'PASSWORD' => $this->Pass,
			'DATABASE' => $this->Database,
			'PORT' => $this->Port,
			'SOCKET' => $this->Socket
			);
		return $Config;
	}

	public function DebugEncodings(){
		$Encodings = array(
			'INPUT' => $this->InputEncoding,
			'OUTPUT' => $this->OutputEncoding
			);
		return $Encodings;
	}

	public function DebugErrorList(){
		return $this->ErrorStack;
	}

	public function DebugStatus(){
		return $this->Status;
	}

	public function DebugErrorCodes(){
		return $this->Errors;
	}

	public function DebugFilters(){
		return $this->Filters;
	}

	public function GetDebugInfoArray(){
		return array(
			'CONFIGURATION' => $this->DebugConfig(),
			'ENCODING' => $this->DebugEncodings(),
			'STATUS' => $this->DebugStatus(),
			'CONNECTIONACTIVE' => $this->IsAvailable(),
			'ERRORS' => $this->DebugErrorList(),
			'ERRORCODES' => $this->DebugErrorCodes(),
			'SERVER' => $this->DebugServerInfo()
			);
	}
}

?>