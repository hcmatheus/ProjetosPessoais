<?php

//TESTE COMMIT 1

require_once dirname(__FILE__) . '/utilsDebug.php';

class browserCURL {

	private $proxy;
	protected $url;
	private $response;
	private $headerRequest;
	private $cookies;
	private $sslVerifyPeer;
	private $sslVerifyHost;
	private $ch;
	private $erroRetorno = false;
	private $followLocation = 1;
	private $http11 = false;
	private $limiteTentativas = 1;
	private $qtdTentativasLastRequest = 0;
	private $debug;
	public $utilsDebug;
	
	public $lastHtml;
	
    public function __construct($debug=false) {
		$this->proxy = false;
		$this->url = false;
		$this->body = false;
		$this->response = false;
		$this->cookies = false;
		$this->ch = false;
		$this->lastHtml = false;		
		$this->sslVerifyPeer = false;
		$this->sslVerifyHost = false;
		$this->timeout = 500;
		$this->gzip = false;
		$this->connectTimeout = 0;
		$this->debug = $debug;
		$this->expectContinue = false;

		$this->headerRequest =  array(
			'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36', 
		);

		$this->utilsDebug = new utilsDebug(false, $debug);
		
    }
	
	public function getTentativasRequest(){
		return $this->qtdTentativasLastRequest;
	}
	
	public function setLimiteTentativas($val){
		$this->limiteTentativas = $val;
	}
	
	public function setTimeout($val){
		return $this->timeout = $val;
	}
	
	public function setConnectionTimeout($val){
		$this->connectTimeout = $val;
	}
	
	private function atualizarParametros() {
		
		preg_match_all('/^Set-Cookie:\s*(.*)/mi', $this->response['completa'], $matches);
		foreach($matches[1] as $item) {
			$valor = trim($item);
			if(substr($valor, -1) != ';')
				$valor = $valor.';';
			$ret = explode('=', $valor, 2);
			if(!preg_match_all('/^;\s.*;/mi', $ret[0], $matches) && count($ret) == 2) {
				$retVal = explode(';', $ret[1], 2);
				$this->cookies[$ret[0]] = $retVal[0];
			}
		}

	}
	
	/**
     * O parametro deve ser booleano indicando se esta propriedade esta ativa, ou não.
    */
	public function setSSLVerifyPeer($val) {
		$this->sslVerifyPeer = $val;
	}

	/**
     * O parametro deve ser booleano indicando se esta propriedade esta ativa, ou não.
    */
	public function setSSLVerifyHost($val) {
		$this->sslVerifyHost = $val;
	}
	
	
	public function clearCookies() {
		$this->cookies = array();
	}
	
	public function setFollowLocation($val) {
		$this->followLocation = $val;
	}
	
	public function setHttpVersion($val) {
		if($val == "1.1") {
			$this->http11 = true;
		} else {
			$this->http11 = false;
		}
	}
	
	
	public function getBody() {
		return $this->response['body'];
	}
	
	public function getResponseHeader() {
		return $this->response['header'];
	}
	
	public function getRequestHeader() {
		return $this->headerRequest;
	}
	
	public function getCookies() {
		return $this->cookies;
	}
	
	public function setProxy($ip, $login = false, $senha = false) {
		
		if($ip != false){
			$this->proxy['ip'] = $ip;
			$this->proxy['credenciais'] = false;
			if($login)
				$this->proxy['credenciais'] = $login.':'.$senha;
		} else {
			$this->proxy = false;
		}
		
	}
	
	public function addHeader($cab){
		array_push($this->headerRequest, $cab);
	}
	
	public function setGzip($val){
		$this->gzip = $val;
	}
	
	public function clearHeaders(){
		$this->headerRequest = array();
	}
	
	public function changeHeaders($headers){
		$this->headerRequest = $headers;
	}
	
	public function getCurrentCookieValue($nome){

		return isset($this->cookies[$nome]) ? $this->cookies[$nome] : false;

	}
	
	public function setCookie($nome,$valor){

		$this->cookies[$nome] = $valor;

	}
	
    protected function initCurl($url){
    
        $this->ch = curl_init($url);
            
    }
        
    public function get( $url, $params = false, $contentType = false ) {
        
		$contTentativas = 0;

		while($contTentativas == 0 || ($contTentativas < $this->limiteTentativas  && $this->erroRetorno)) {

			$this->erroRetorno = false;
			
	        $this->initCurl( $url );
	        
	        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
	        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);
	        
	        $this->request($url);
        
			if($this->erroRetorno){
				sleep(3);
			}
			
			$contTentativas++;
			
		}
		
		$this->qtdTentativasLastRequest = $contTentativas;
		
    }
    
    public function post($url, $params, $contentType) {
        
		$contTentativas = 0;

		while($contTentativas == 0 || ($contTentativas < $this->limiteTentativas  && $this->erroRetorno)) {
				
			$this->erroRetorno = false;

		    $this->initCurl( $url );
	        
	        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
	        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
	        
	        array_push($this->headerRequest, 'Content-Type: '.$contentType);
	        array_push($this->headerRequest, 'Content-Length: ' . strlen($params));
	                   
	        $this->request($url, $params, $contentType);
        	
			if($this->erroRetorno){
				sleep(3);
			}
			
			$contTentativas++;
			
		}
		
		$this->qtdTentativasLastRequest = $contTentativas;
		
	}
	
	public function put($url, $params, $contentType) {
        
		$contTentativas = 0;

		while($contTentativas == 0 || ($contTentativas < $this->limiteTentativas  && $this->erroRetorno)) {
				
			$this->erroRetorno = false;

		    $this->initCurl( $url );
	        
	        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
	        
	        array_push($this->headerRequest, 'Content-Type: '.$contentType);
	        array_push($this->headerRequest, 'Content-Length: ' . strlen($params));
	                   
	        $this->request($url, $params, $contentType);
        	
			if($this->erroRetorno){
				sleep(3);
			}
			
			$contTentativas++;
			
		}
		
		$this->qtdTentativasLastRequest = $contTentativas;
		
    }

	public function getCookieStr() {
		$cookieStr = "";
			
		foreach ($this->cookies as $key => $value) {
			if(is_array($value)) {
				foreach ($value as $valueD) {
					$cookieStr = $cookieStr.$key.'='.$valueD['valor'].";";
				}
			} else {
				$cookieStr = $cookieStr.$key.'='.$value.";";
			}
		}

		return $cookieStr;
		
	}
    
	private function request($url, $params = false, $contentType = false) {
 				
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifyPeer);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $this->sslVerifyHost);	
		
		curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
		curl_setopt($this->ch, CURLOPT_HEADER, 1);        
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT,$this->connectTimeout);  
		curl_setopt($this->ch, CURLOPT_TIMEOUT,$this->timeout);

		if($this->http11){
			
			if(!isset($this->headerRequest)){
				$this->headerRequest = array();
			}
			
			curl_setopt($this->ch, CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
			array_unshift($this->headerRequest, "Connection: keep-alive");
		}
               	   
		if($this->headerRequest)
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headerRequest);
        else
            echo 'Nao tem headers!<br />';    
		
		if($this->expectContinue)
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Expect:'));

		if($this->proxy) {
			curl_setopt($this->ch, CURLOPT_PROXY, $this->proxy['ip']);
			if($this->proxy['credenciais']) {
				curl_setopt($this->ch, CURLOPT_PROXYUSERPWD, $this->proxy['credenciais']);
			}
		}	
		
		if($this->gzip)
			curl_setopt($this->ch,CURLOPT_ENCODING , "gzip");
		
		if($this->cookies) {
			curl_setopt($this->ch, CURLOPT_COOKIE, $this->getCookieStr());
		}
		
		
		
		$this->response['completa'] = curl_exec($this->ch);
		
		$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		$this->response['header'] = substr($this->response['completa'], 0, $header_size);
		$this->response['body'] = substr($this->response['completa'], $header_size);

		$headerSent = curl_getinfo($this->ch, CURLINFO_HEADER_OUT );

		$this->lastHtml = $this->response['completa'];

		if(!$this->response['completa']){
			$this->erroRetorno = true;
		}
		
		if($contentType){
			array_pop($this->headerRequest);
			array_pop($this->headerRequest);
		}
		
		if($this->http11){
			unset($this->headerRequest[0]);
		}
		
		curl_close($this->ch);
		
		$this->atualizarParametros();
		
		if($this->debug)
			$this->utilsDebug->showPrettyLastRequest($params, $headerSent, $this->response['header'], $this->lastHtml);//echo "GET: '$url'<br/>";
		
    }
	
    /**
     * pesquira por regex no lastHtml
     */
    public function getByRegex($expr, $index = 1, $modifier=null) {
        if(preg_match("^$expr^$modifier", $this->lastHtml, $ret)) {

            //echo "$expr RET: ";
            //print_r($ret);
            if($index === false)
                return $ret;
            else
                return $ret[$index];
        }else
			return false;
    }

	public function showRequestResponse(){
		echo "<pre>";
		print_r($this->headerRequest);
		print_r($this->cookies);
		echo "\n\n\n";
		print_r($this->response['header']);
		echo "</pre>";
	}
}


//$teste = new browserCURL();
//$teste->get("https://meuespacocorretor.libertyseguros.com.br/Inicio/login.aspx");
//$teste->get("http://livedoc.wakanda.org/Datastore/Transactions/rollBack.301-594817.en.html");
//print $teste->lastHtml;


?>
