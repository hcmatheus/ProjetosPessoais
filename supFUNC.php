<?php

class supFUNC 
{

    public $erros = [];
    public $alertas = [];

    protected function printErros(){
        foreach( $this->erros as $key => $value )
            echo '<font color="red">Erro => ' . $value . '</font><br />';
    }
    
    protected function adicionaAlerta($mensagem) {
        $mensagem = strip_tags(utf8_encode($mensagem));
        if(!in_array($mensagem, $this->alertas))
            $this->alertas[] = $mensagem;
    }
    
    protected function printAlertas(){
        foreach( $this->alertas as $key => $value )
            echo ' Alerta => ' . $value . '<br />';
    }
    
    
    protected function adicionaErro($mensagem) {
        $mensagem = strip_tags(utf8_encode(trim($mensagem)));
        if( !in_array($mensagem, $this->erros) && !empty($mensagem))
            $this->erros[] = $mensagem;
    }
    
}

?>