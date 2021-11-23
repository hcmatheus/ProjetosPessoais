<?php

class utilsDebug {

	protected $start;
	protected $showLogs;
	protected $debugLastHtml;
	protected $debug;
	protected $lastHtml;

    public function __construct($insertHeadersInit, $debug) {
		$this->start = time();
		$this->showLogs = true;
		$this->debugLastHtml = true;
		$this->debug = $debug;
		if($insertHeadersInit)
			$this->debugInit();
	}
	
	/**
	 * Função para mostrar ou não as requests
	 * @param $showLogs boolean pra setar valor
	*/
	public function setShowLogs($showLogs) {
		$this->showLogs = $showLogs;
	}

	/**
	 * Ignorar a colocação do HTML na pagina, para evitar redirects e coisas do tipo
	 * @param $val boolean pra setar valor
	*/
	public function debugIgnoreLastHtml($val){
		return $this->debugLastHtml	= !$val;
	}

	/** 
	 * Atalho do debugDie, para sempre printar o lastHtml de maneira facil
	 * @param $msg Mensagem a ser exibida (dentro do container)
	 * @param $dump Por default, executa um 'echo', caso seja enviado true, um 'var_dump'
	*/
	public function debugDieLastHtml($msg=false, $dump=false) {
        $this->debugDie($msg, $dump, $this->lastHtml);
	}
	
	/**
	 * Executa algo semelhante o getByRegex em uma string especifica
	 * @param $exp expressao regular
	 * @param $str string a ser analisada
	*/
	public function getStrKeyRegex($exp, $str) {
        $ret = preg_match($exp, $str, $saida);
        return $ret ? $saida[1] : false;
    }

	/**
	 * Executa algo semelhante o getByRegex em uma string especifica
	 * Porém, retorna todos valores compativeis
	 * @param $exp expressao regular
	 * @param $str string a ser analisada
	*/
    public function getStrKeyRegexAll($exp, $str) {
        $ret = preg_match_all($exp, $str, $saida);
        return $ret ? $saida : false;
    }

	/**
	 * Marca o inicio da execução dos bots
	*/
	public function debugInit() {
		
		if($this->debug) {
			echo '<head>';
			echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">';
			echo '<script src="/multi-bots/ws/jquery-3.5.1.slim.min.js"></script>';
			echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>';
			echo '</head>';
			echo '<body>';

			if($this->showLogs) {
				echo '<div class="alert alert-success" role="alert">';
				echo '[DEBUG] ROBÔ INICIALIZADO!';
				echo '</div>';
			}
		}
		
	}

	/**
	 * Marca o término de execução dos bots
	*/
	public function debugEnd() {
        if($this->showLogs && $this->debug) {
            $horario = date('i:s',time() - $this->start);
            echo '<div class="alert alert-success" role="alert">';
            echo "[DEBUG: {$horario}] ROBÔ FINALIZADO!";
            echo '</div>';
        }
	}
	

	/**
	 * Printa na dela uma variavel no ambiente de debug
	 * @param $msg Mensagem a ser exibida
	 * @param $dump Por default, executa um 'echo', caso seja enviado true, um 'var_dump'
	 * @param $html Caso seja enviado, exibe o conteudo "limpo", logo abaixo do warning de die
	*/
    public function debugVar($msg, $dump=false, $html=false) {
        if($this->showLogs && $this->debug) {
            $horario = date('i:s',time() - $this->start);
            echo '<div class="alert alert-primary m-2" role="alert">';
            echo "[DEBUG: {$horario}] ";
            echo $dump ? var_dump($msg) : $msg;
			echo '</div>';
			echo $html;
        }
	}
	
	/**
	 * Executa um die, podendo conter msg e lastHtml
	 * @param $msg Mensagem a ser exibida (dentro do container)
	 * @param $dump Por default, executa um 'echo', caso seja enviado true, um 'var_dump'
	 * @param $html Caso seja enviado, exibe o conteudo "limpo", logo abaixo do warning de die
	*/
	public function debugDie($msg=false, $dump=false, $html=false) {
        if($this->showLogs) {
			$horario = date('i:s',time() - $this->start);
			echo '<div class="alert alert-danger m-2" role="alert">';
			echo "[DIE: {$horario}] ";
            echo $dump ? var_dump($msg) : $msg;
			echo '</div>';
		}
		if($this->debug)
			die($html);
	}

	public function showPrettyLastRequest($parameters=false, $pHeaderRequest, $pHeaderResponse, $lastHtml) {

		$this->lastHtml = $lastHtml;
		//var_dump($this->showLogs, '$this->showLogs');
		if($this->showLogs) {
			$headersRequestString = $pHeaderRequest;

			if($headersRequestString) {
				//var_dump($headersRequestString, '$headersRequestString');
				$headersResponseString = $pHeaderResponse;
				if($headersResponseString) {
					$headersResponseString = trim($headersResponseString);
					$headersResponse = preg_split("/\n/", $headersResponseString);
				}
				$headersRequestString = trim($headersRequestString);
				$headersRequest = preg_split("/\n/", $headersRequestString);
				$urlR = array_shift($headersRequest);
				$detailsUrl = explode(" ", $urlR);
				$type = $detailsUrl[0];
				$path = $detailsUrl[1];
				$version = $detailsUrl[2];
				$detailsPath = explode("?", $path);
				$pathClear = $detailsPath[0];
				$pathQuery = isset($detailsPath[1]) ? "?{$detailsPath[1]}" : '';
				$detailsPathQuery = explode("&", substr($pathQuery, 1));
				
				
				$colIdDetal = $this->getIdCollapse();

				$horario = date('i:s',time() - $this->start);
				echo '<div class="alert alert-warning m-2" role="alert">';
				echo "<a class=\"text-decoration-none text-dark\" data-toggle=\"collapse\" href=\"#detalhes{$colIdDetal}\" role=\"button\" aria-expanded=\"false\" aria-controls=\"detalhes{$colIdDetal}\">";
				echo "<p class=\"m-0\">[DEBUG REQUEST: {$horario}] <span class=\"font-weight-bold\"> {$type} {$pathClear}</span>{$pathQuery}</p>";
				echo '</a>';
				echo "<div class=\"collapse mt-3 mb-3\" id=\"detalhes{$colIdDetal}\">";

				$colId1 = $this->getIdCollapse();
				$colId2 = $this->getIdCollapse();
				$colId3 = $this->getIdCollapse();
				$colId4 = $this->getIdCollapse();
				$colId5 = $this->getIdCollapse();
				$colId6 = $this->getIdCollapse();
				$colId7 = $this->getIdCollapse();

				$this->addButtonDebug("collapse{$colId1}", "Headers (requests)");
				if($pathQuery != "") {
					$this->addButtonDebug("collapse{$colId2}", "Query Params (parâmetros da URL)");
				}
				if(($type == "POST" || $type == "PUT") && $parameters){
					$this->addButtonDebug("collapse{$colId3}", "PostData");
				}
			
				$strHR = "";
				foreach ($headersRequest as $value) {
					$keyVal = explode(":", $value,2);
					if(isset($keyVal[1])){
						if(strtolower($keyVal[0]) == "content-type") {
							$contenttype = $keyVal[1];
						} else if(strtolower($keyVal[0]) == "cookie") {
							$cookies = $keyVal[1];
						}
						$strHR = $strHR . "<p><span class=\"font-weight-bold\">{$keyVal[0]}:</span> {$keyVal[1]}</p>";
					}
				}

				if(isset($cookies)) {
					$this->addButtonDebug("collapse{$colId4}", "Cookies");
				}

				$this->addButtonDebug("collapse{$colId5}", "Headers (response)");

				if($this->debugLastHtml)
					$this->addButtonDebug("collapse{$colId6}", "HTML");

				//if($this->debugLastHtml)
					$this->addButtonDebug("collapse{$colId7}", "HTML (codigo-fonte)");

				$this->addCardDebug("collapse{$colId1}", "Headers (requests)", $strHR);
				if($pathQuery != "") {
					$strDPQ = '';
					foreach ($detailsPathQuery as $value) {
						$keyVal = explode("=", $value,2);
						if(isset($keyVal[1]))
							$strDPQ = $strDPQ . "<p><span class=\"font-weight-bold\">{$keyVal[0]}:</span> {$keyVal[1]}</p>";
					}	
					$this->addCardDebug("collapse{$colId2}", "Query Params (parâmetros da URL)", $strDPQ);
				}
				if(($type == "POST" || $type == "PUT") && $parameters){
					$strPParam = '';
					
					//application/json e application/x-www-form-urlencoded FAZER MATCH
					if(preg_match('/application\/x-www-form-urlencoded/', $contenttype)) {
						$parameters = explode("&", $parameters);
						foreach ($parameters as $value) {
							$keyVal = explode("=", $value,2);
							if(isset($keyVal[1])) {
								$keyVal[0] = urldecode($keyVal[0]);
								$keyVal[1] = urldecode($keyVal[1]);
								$strPParam = $strPParam . "<p><span class=\"font-weight-bold\">{$keyVal[0]}:</span> {$keyVal[1]}</p>";
							}
						}
					} else if(preg_match('/application\/json/', $contenttype)) {
						// var_dump($parameters);
						$strPParam = '<pre>';
						$strPParam = $strPParam . json_encode(json_decode($parameters), JSON_PRETTY_PRINT);
						$strPParam = $strPParam. '</pre>';
						//prettyPrintJson.toHtml(data, options)
						
					} else {
						$strPParam = $parameters;
					}
					
					$this->addCardDebug("collapse{$colId3}", "PostData ({$contenttype})", $strPParam);
				}
				if(isset($cookies)){
					$strC="";
					$cookiesStr = explode(";", $cookies);
					foreach ($cookiesStr as $value) {
						$keyVal = explode("=", $value,2);
						if(isset($keyVal[1]))
							$strC = $strC . "<p><span class=\"font-weight-bold\">{$keyVal[0]}:</span> {$keyVal[1]}</p>";
					}
					$this->addCardDebug("collapse{$colId4}", "Cookies", $strC);
				}

				if(isset($headersResponseString) && $headersResponseString) {
					$strHResponse = "";
					$status = array_shift($headersResponse);
					foreach ($headersResponse as $value) {
						$keyVal = explode(":", $value,2);
						if(isset($keyVal[1])){
							$strHResponse = $strHResponse . "<p><span class=\"font-weight-bold\">{$keyVal[0]}:</span> {$keyVal[1]}</p>";
						}
					}
					$this->addCardDebug("collapse{$colId5}", "Headers (response): {$status}", $strHResponse);
				}

				$striframe = '<script>';
				$striframe = $striframe . "var doc = document.getElementById('iframe{$colId6}').contentWindow.document;";
				$striframe = $striframe . 'doc.open();';

				$corrigeLastHtml = str_replace("window.parent.location.reload();", "// COMENTADO PARA NAO RECARREGAR A PAGINA DE DEBUG: window.parent.location.reload();",$lastHtml);

				// $corrigeLastHtml = str_ireplace("/(window.*location)/", "// COMENTADO PARA NAO RECARREGAR A PAGINA DE DEBUG: $1;",$this->lastHtml);
				// $corrigeLastHtml = str_ireplace("/(document.*location)/", "// COMENTADO PARA NAO RECARREGAR A PAGINA DE DEBUG: $1;",$this->lastHtml);
				// $corrigeLastHtml = str_ireplace("/(baseURL.resolve)/", "// COMENTADO PARA NAO RECARREGAR A PAGINA DE DEBUG: $1;",$this->lastHtml);

				$striframe = $striframe . "doc.write('{$corrigeLastHtml}');";
				$striframe = $striframe . 'doc.close();';
				$striframe = $striframe . '</script>';
				// $this->addCardDebug("collapse{$colId6}", "HTML", "<iframe id=\"myframe\"> </iframe>".$striframe);

				if($this->debugLastHtml)
					$this->addCardDebug("collapse{$colId6}", "HTML", "<iframe id=\"iframe{$colId6}\"></iframe>{$striframe}");


				
				if(json_decode($lastHtml)) {
					$rawHtml = '<pre>';
					$rawHtml = $rawHtml . json_encode(json_decode($lastHtml), JSON_PRETTY_PRINT);
					$rawHtml = $rawHtml. '</pre>';
				} else {
					$rawHtml = str_replace('&', '&amp;', $lastHtml);
					$rawHtml = str_replace('<', '&lt;', $rawHtml);
					$rawHtml = str_replace('>', '&gt;', $rawHtml);
					$rawHtml = "<pre><code>".$rawHtml."</code></pre>";
				}

				//if($this->debugLastHtml)
					$this->addCardDebug("collapse{$colId7}", "HTML (codigo-fonte)", $rawHtml);

				echo '</div>';
				echo '</div>';
			} else {
				var_dump('ERRO REQUEST',
					$parameters, 
					$pHeaderRequest,
					$pHeaderResponse, 
					$lastHtml
				);
			}

		}

	}

	// Funções auxiliares

	protected function addButtonDebug($id, $title) {
		echo "<button class=\"btn btn-primary btn-sm mr-2\" type=\"button\" data-toggle=\"collapse\" data-target=\"#{$id}\" aria-expanded=\"false\" aria-controls=\"{$id}\">";
		echo $title;
		echo '</button>';
	}

	protected function addCardDebug($id, $title, $html) {
		echo "<div class=\"collapse mb-3 mt-3\" id=\"{$id}\">";
		echo '<div class="card card-body">';
		echo "<p><u><span class=\"font-weight-bold\">{$title}</span></u></p>";
		echo $html;
		echo '</div>';
		echo '</div>';
	}

	protected function getIdCollapse() {
		return time().rand(0,500000);
	}

}

?>
