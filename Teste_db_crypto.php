<?php

require_once '../browserCURL.php';
require_once '../supFUNC.php';

if (isset($_POST['insert']) || isset($_POST['busca']))
{
   
    class BotBitcoio extends supFUNC
    {

        protected $host = 'localhost';
        protected $user = 'root';
        protected $password = '';
        protected $database = 'db_cryptos';

        public $connection = null;

        function inicializa() {
            echo "ROBO INICIALIZADO" . '<br><br>';
            
            $this->addDados = isset($_POST['insert']);
            $this->buscaDados = isset($_POST['busca']);
            // $debug = true;
            // $this->debug = $debug;

            $this->browserCurl = new browserCURL();
            // $this->browserCurl = new browserCURL($this->debug);
            // $this->browserCurl->utilsDebug->debugIgnoreLastHtml(true);

            date_default_timezone_set('America/Sao_Paulo');
            
        }

        function validaLogin() {

            return true;
        }

        function getInfoAPI() {

            $this->getInfoIndex();

            $this->getInfoCrypto($_POST['nome']);

        }

        function getInfoIndex() {

            $this->browserCurl->get('https://api.alternative.me/fng');

            $retornoIndex = json_decode($this->browserCurl->getBody(), true);
            $result['value'] = $retornoIndex['data']['0']['value'];
            $result['classification'] = $retornoIndex['data']['0']['value_classification'];

            $this->pacote['infoIndex'] = $result;
        }
        
        function getInfoCrypto($nomeCrypto) {

            if (preg_match('/(,)/' ,$nomeCrypto)) {
                $nomeCrypto = explode(',', $nomeCrypto);
            }

            if (is_array($nomeCrypto) && count($nomeCrypto) > 1) {
                foreach ($nomeCrypto as $key => $value) {

                    if ($value == "Bitcoin") {
                        $id = 1;
                    } elseif ($value == "Ethereum") {
                        $id = 1027;
                    } 
        
                    $this->browserCurl->get("https://api.alternative.me/v2/ticker/{$value}/");
        
                    $retornoCrypto = json_decode($this->browserCurl->getBody(), true);
        
                    $result = array();
                    $result['id'] = $retornoCrypto['data'][$id]['id'];
                    $result['nome'] = $retornoCrypto['data'][$id]['name'];
                    $result['simbolo'] = $retornoCrypto['data'][$id]['symbol'];
                    $result['valor'] = $retornoCrypto['data'][$id]['quotes']['USD']['price'];
                    $result['horaMin'] = date('Y-m-d H:i:s');
        
                    $this->pacote['crypto'][] = $result;
                }        
            }

        }

        function conexaoBanco($nomeCrypto = false) {
            
            $this->connection = mysqli_connect($this->host, $this->user, $this->password, $this->database);

            if ($this->connection->connect_error){
                $this->adicionaAlerta("Failed to connect to MySQL: " . $this->connection->connect_error);
            } else {
                $this->adicionaAlerta("Connection Successful!");
            }

        }

        function conexaoBancoNew($nomeCrypto = false) {
            
            
            try {
                $this->connection = new PDO("mysql:host={$this->host};dbname=" . $this->database, $this->user, $this->password);
            
                echo "Conexão com banco de dados realizado com sucesso!";
            } catch (PDOException $erro) {
                echo "Erro: Conexão com banco de dados falhou." . $erro->getMessage();
            }
            // die();
        }
        
        protected function addDados() {

            foreach ($this->pacote['crypto'] as $key => $value) {
                $id = $value['id'];
                $nome = $value['nome'];
                $simbolo = $value['simbolo'];
                $valor = $value['valor'];
                $horaMin = $value['horaMin'];

                // Insere os dados na tabela Bitcoio do BD DB_INICIAL
                $query = "INSERT INTO Bitcoio VALUES (
                    '$id','$nome','$simbolo','$valor','$horaMin')";

                $result = mysqli_query($this->connection, $query);

                if ($result){
                    $this->adicionaAlerta("Data Inserted for $nome - $horaMin");
                } else {
                    $this->adicionaAlerta("Data not Inserted for $nome - $horaMin");
                }
            }
        }

        protected function buscaDados() {

            foreach ($this->InfoQuery['buscas']['query'] as $key => $value) {

                $result = mysqli_query($this->connection, $value);
                $registroGeral = mysqli_fetch_all($result);

                // var_dump($registro);
               
                $registro['dadosBanco'] = $registroGeral;
                $registro['quantidade'] = count($registro['dadosBanco']);
                preg_match("/nome`\s=\s{0,2}'(.*)'/iU", $value, $nome);
                $registro['id'] = $registro['dadosBanco'][0][0];
                $registro['nome'] = $registro['dadosBanco'][0][1];
                $registro['primeiroEncontro'] = $registro['dadosBanco'][0][4];
                $registro['ultimoEncontro'] = $registro['dadosBanco'][$registro['quantidade']-1][4];
                $registro['simbolo'] = $registro['dadosBanco'][0][2];
                // var_dump($registro['dadosBanco']);
                $array = [];
                // var_dump($registro['dadosBanco']);
                foreach ($registro['dadosBanco'] as $key => $value) {
                    $arrayValores[] = $value[3];
                }
                $registro['evolucaoValor'] = $arrayValores;
                rsort($arrayValores);
                $registro['meiorValor'] = $arrayValores[0];
                $registro['menorValor'] = $arrayValores[$registro['quantidade']-1];
                          
                $teste = date('Y-m-d H:i:s', strtotime('2021-11-20 18:03:49'));
                // var_dump($teste);
                // die();
                var_dump($registro);

                if (empty($registro)) {
                    $this->adicionaAlerta('NENHUM RESULTADO ENCONTRADO NO BANCO DE DADOS');
                }

                $this->registros[] = $registro;
                
            }
            // var_dump($this->registros);
            // var_dump(json_encode($this->registros));

        }

        protected function preparaInfoQuery() {

            $dataIni = isset($_POST['dateIni']) ? $_POST['dateIni'] : '';
            $dataFim = isset($_POST['dateFim']) ? $_POST['dateFim'] : '';

            $timeIni = isset($_POST['timeIni']) ? $_POST['timeIni'] : '';
            $timeFim = isset($_POST['timeFim']) ? $_POST['timeFim'] : '';

            $nomeCrypto = $_POST['nome'];

            if (preg_match('/(,)/' ,$nomeCrypto)) {
                $nomeCrypto = explode(',', $nomeCrypto);
            }

            if (is_array($nomeCrypto) && count($nomeCrypto) > 1) {
                
                foreach ($nomeCrypto as $key => $value) {

                    
                    $query_usuarios = "SELECT * FROM bitcoio WHERE nome = '$value'";
                    var_dump($query_usuarios);

                    $result_usuarios = $this->connection->prepare($query_usuarios);
                    $result_usuarios->execute();

                    $row_usuarios = $result_usuarios->fetch(PDO::FETCH_ASSOC);
                    var_dump($row_usuarios);
                    die();

                    $query = "SELECT * 
                    FROM 
                        `bitcoio` 
                    WHERE 
                        `nome` = '$value' 
                        AND `dia` <= '$dataFim $timeFim:00'
                        AND `dia` >= '$dataIni $timeIni:00'";


                    $this->InfoQuery['buscas']['query'][] = $query_usuarios;
                    // $this->InfoQuery['buscas']['nome'][] = $value;
                    // $this->InfoQuery['dataIni'] = $dataIni;
                    // $this->InfoQuery['dataFim'] = $dataFim;
                    // $this->InfoQuery['timeIni'] = "$timeIni:00";
                    // $this->InfoQuery['timeFim'] = "$timeFim:00";

                    var_dump($this->InfoQuery);
                }
            
            } else {
                
                $query = "SELECT * 
                    FROM 
                        `bitcoio` 
                    WHERE 
                        `nome` = '$nomeCrypto' 
                        AND `dia` <= '$dataFim $timeFim:00'
                        AND `dia` >= '$dataIni $timeIni:00'";
 
                $this->query[] = $query;
            }

            return true;

        }

        protected function pegaSaida() {

            $resultados = array();
            foreach ($this->registros as $key => $value) {

                $result = array();

                // $result['id'] = ;
                // $result['nome'] = ;
                // $result['menorValor'];
                // $result['maiorValor'];
                // $result[''];
            }

            array_push($resultados, $result);

            // $this->retornaCalculoAoCliente($resultados);

        }


        public function run() {

            $time_start = microtime(true);

            $this->inicializa();

            if ($this->validaLogin()) {

                if ($this->addDados) {

                    $this->getInfoAPI();
    
                    if (!empty($this->pacote)) {
                        $this->conexaoBancoNew();
                        $this->addDados();
                    } else {
                        $this->adicionaErro('Erro ao pegar Pacote. (Code: 1000)');
                    }

                } elseif ($this->buscaDados) {
                    $this->conexaoBancoNew();
                    if ($this->preparaInfoQuery()) {

                        $this->conexaoBancoNew();
                        $this->buscaDados();
                        $this->pegaSaida();
                    }

                }

            }
            

            $this->printErros();
            $this->printAlertas();
            // var_dump($this->resultados);
            /* */
            
            $this->time = number_format(microtime(true) - $time_start, 4);
        }
        
    }

    $bot = new BotBitcoio();
    $bot->run();

    echo '<br />' . "Tempo de execução: $bot->time" . '<br />';
    /**/
}
?>



