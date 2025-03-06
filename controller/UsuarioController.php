<?php

class UsuarioController
{
    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }
        public function mostrarUserView()
        {
            $data = [];
            $this->setDatos($data);
            $this->presenter->show('user', $data);
        }

  public function perfil(){
    if (isset($_GET['id'])) {
        $userId = $_GET['id'];
        $partidas = $this->model->partidasJugadas($userId);
        $userData = $this->model->getUser($userId);

        if ($userData) {
            $data = [
                'userVist' => $userData,
                'user' => isset($_SESSION['user']) ? $_SESSION['user'] : 0,
                "esUsuario" => $this->verificarQueUsuarioEs(),
                "notificaciones" =>  isset($_SESSION['user']) ? $this->model->notificaciones($this->idUsuario()) : 0,
                "partidas" => $partidas,
                "cantidadSugeridas" => $this->model->cantidadPreguntasSugeridasPorUsuario($userId),
                "edadQuiz" => $this->model->edadQuiz($userId)

                
            ];
            $this->generarQrPerfil();
            $this->presenter->show('user', $data);
        } 
    }
}

    public function sugerirPregunta(){
        
        $correcta = $_POST['es_correcta'];
        $opciones = [];
             
            $respuesta = "";
            foreach($_POST['opciones'] as $index => $opcion){
                $opciones[] = $opcion;
                if($index == $correcta){
                    $respuesta = $opcion;
                }
            }
        
        $data = [
            'pregunta' => $_POST['pregunta'],
            'opciones' => $opciones,
            'idUsuario' => $this->idUsuario(),
            'categoria' => $_POST['categoria'],
            'respuesta' => $respuesta
        ];


        $this->model->sugerencia($data);
        header('Location: /quizgame/home/lobby');
    
}

  
    private function setDatos(&$data){
        if(!empty($_SESSION['error'])){
            $data["error"] = $_SESSION['error'];
            unset( $_SESSION['error']);
        }if(!empty($_SESSION['user'])){
            $this->generarQrPerfil();
            $partidas = $this->model->partidasJugadas($this->idUsuario());
            $data = [
                "user" => $_SESSION['user'],
                "userVist" => $_SESSION['user'],
                "esUsuario" => $this->verificarQueUsuarioEs(),
                "notificaciones" => $this->model->notificaciones($this->idUsuario()),
                "partidas" => $partidas,
                "cantidadSugeridas" => $this->model->cantidadPreguntasSugeridasPorUsuario($this->idUsuario()),
                "edadQuiz" => $this->model->edadQuiz($this->idUsuario())
            ];

        }
        if(!empty($_SESSION['editorPreguntas'])){
            $data["editorPreguntas"] = $_SESSION['editorPreguntas'];
        }
    }

    function generarQrPerfil()
    {
        if (isset($_GET['id'])) {
            $url = "http://localhost/quizgame/usuario/perfil?id=".$_GET['id'];
            QRcode::png($url, './public/image/QRUsers/'.$_GET['id'].'.png', QR_ECLEVEL_H, 2, 2);
        }else{
            $url = "http://localhost/quizgame/usuario/perfil?id=".$_SESSION['user']['id'];
            QRcode::png($url, './public/image/QRUsers/'.$_SESSION['user']['id'].'.png', QR_ECLEVEL_H, 2, 2);
        }
    }
    public function verificarQueUsuarioEs(){

         return $_SESSION['user']['editor'] == 0 && $_SESSION['user']['admin'] == 0 ? true : false;
     
    }

    
    private function idUsuario(){
        return $this->existeUsuario() ? $_SESSION['user']['id'] : null;
      }
      private function existeUsuario() {
        return isset($_SESSION['user']);
    }

}
  

   





