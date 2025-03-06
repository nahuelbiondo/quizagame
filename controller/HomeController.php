<?php

class HomeController{
    private $presenter;
    private $model;
 
    public function __construct($model,$presenter){
        $this->presenter = $presenter;
        $this->model = $model;

    }

    public function ranking(){
        if($this->existeUsuario()){
        $data = [];
        
        $this->dataRanking($data);
    
        $this->presenter->show('ranking', $data);
        }
    }

    public function lobby()
    {
        $data = [];
        $this->data($data);
        unset($_SESSION['preguntas_data'],$_SESSION['respuesta_incorrecta']);
     
        $this->presenter->show('lobby', $data);
    }
    private function dataRanking(&$data){
        $rank = $this->model->obtenerRankingUsuarios();

        $usuarioLogueado = $_SESSION['user']['usuario'];
      
      
        foreach ($rank as &$usuario) {
            $usuario['isUserLogged'] = ($usuario['usuario'] === $usuarioLogueado);
        }
        
        $data = [
            "ranking" => $rank,
            "user" => $_SESSION['user'],
            "esUsuario" => $this->verificarQueUsuarioEs(),
            "notificaciones" => $this->model->notificaciones($this->idUsuario())
        ];
    }

    private function data(&$data){

        $rank = $this->model->obtenerRankingUsuarios();
        $fecha = $this->model->ultimaPartida($this->idUsuario());

        $usuarioLogueado = $_SESSION['user']['usuario'];
        $newRank = $this->queAparezcaElUsuarioEnElRankingSinImportarLaPosicion($rank, $usuarioLogueado);
      
        foreach ($newRank as &$usuario) {
            $usuario['isUserLogged'] = ($usuario['usuario'] === $usuarioLogueado);
        }

        $data = [
            "ranking" => $newRank,
            "user" => $_SESSION['user'],
            "esUsuario" => $this->verificarQueUsuarioEs(),
            "partida" => $this->model->verificarSiTieneUnaPartidaActiva($this->idUsuario()),
            "fecha" => $fecha,
            "notificaciones" => $this->model->notificaciones($this->idUsuario())
         
        ];

    }

    public function leer(){
        $this->model->leer($this->idUsuario());
    
    }

    private function verificarQueUsuarioEs(){
        return $_SESSION['user']['editor'] == 0 ? true : false;
    }


    private function queAparezcaElUsuarioEnElRankingSinImportarLaPosicion($rank, $usuarioLogueado) {
        $newRank = [];
        $contador = 0;

    foreach ($rank as $ranking) {
     
        if ($contador < 5) {
            $newRank[] = $ranking; 
          
        } else if($ranking['usuario'] === $usuarioLogueado){
            $newRank[] = $ranking; 
        }
        $contador++; 
    }
    
        return $newRank; 
    }

    private function idUsuario(){
        return isset($_SESSION['user']) ? $_SESSION['user']['id'] : 0;
      }
          
    private function existeUsuario() {
        return isset($_SESSION['user']);
    }

}