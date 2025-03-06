<?php

class AuthController {
    private $model;
    private $presenter;
    private $mailer;
    private $img;
    public function __construct($model,$presenter,$mailer, $img){
        $this->model = $model;
        $this->presenter = $presenter;
        $this->mailer = $mailer;
        $this->img = $img;
    }

    public function login()
    {
      if(!isset($_SESSION['user'])){
        $data = [];
        $this->setDatos($data);
        $this->presenter->show('login', $data);
      }else{
        $this->cerrarSesion();
      }
    }
    public function registrar()
    {
        $data = [];
        $this->setDatos($data);
        $this->presenter->show('register', $data);
    }   
    
    public function register() {
        try {
            $data = $_POST;
            $this->validateRegisterInput($data);
            
            $usuario = $this->registerUser($data);
    
           $this->sendVerificationEmail($data['email'], $data['nombre'], $data['usuario']);
            
           $this->manejarError("Te hemos enviado un correo para verificar tu cuenta", '/quizgame/login');

        } catch (Exception $e) {
            $this->manejarError($e->getMessage(), '/quizgame/auth/registrar');
        }
    }

    public function registerUser($data) {
        $usuarioExistente = $this->model->buscarUsuario($data['usuario']);
        if ($usuarioExistente) {
            throw new Exception('Usuario existente');
        }
      
        $this->saveImage($data);
        return $this->model->registrarUsuario($data);
    }

    public function saveImage(&$data){
        $data['fotoPerfil'] = $this->img->guardarImagen($data['usuario']);
    }
    
    public function validateRegisterInput($data) {
        if ($data['password'] !== $data['repeatPassword']) {
            throw new Exception('Las contraseñas no coinciden');
        }
    }
    public function sendVerificationEmail($email, $nombre, $usuario) {
        $token = $this->getVerificationToken($usuario);
        $this->mailer->sendVerificationEmail($email, $nombre, $usuario, $token);
    }

    public function cerrarSesion(){
        session_destroy();
        header('Location: /quizgame/auth/login');
        exit();

    }
    public function authenticate()
    {

        try{ 
            if(isset($_GET['token']) && isset($_GET['usuario'])){
            $token = $_GET['token'];
            $usuario = $_GET['usuario'];
            $this->model->activarUsuario($usuario,$token);
            header('Location: /quizgame/auth/login');
            exit();
        }
         
        }catch (Exception $e) {
            $this->manejarError($e->getMessage(), '/quizgame/auth/login');
        }
    }

    public function procesarUsuario() {
        $user = $_POST['user'];
        $pass = $_POST['password'];
    
        $usuario = $this->model->validate($user, $pass);
        
        if ($usuario) {
            if ($usuario[0]['admin'] == 1){
                $this->manejarSesion($usuario);
                header('Location: /quizgame/admin/mostrarAdminView');
            }else if($usuario[0]['editor'] == 1){
                $this->manejarSesion($usuario);
              
                header('Location: /quizgame/editor/mostrarEditorView');
            }else if ($usuario[0]['estado'] == 1) {
                $this->manejarSesion($usuario);
                $this->redirigirUsuarioLogeado();
            } else {
                $this->manejarError("Verifica tu bandeja de correo y activa tu cuenta", '/quizgame/login');
            }
        } else {
            $this->manejarError("Usuario o contraseña incorrectos", '/quizgame/login');
        }
    }

    public function setDatos(&$data){
        if(!empty($_SESSION['error'])){
            $data["error"] = $_SESSION['error'];
            unset( $_SESSION['error']);
        }if(!empty($_SESSION['user'])){
            $data["user"] = $_SESSION['user'];
        }if(!empty($_SESSION['editorPreguntas'])){
            $data["editorPreguntas"] = $_SESSION['editorPreguntas'];
        }
    }

    private function manejarSesion($usuario) {
        $_SESSION['user'] = $usuario[0];
    }
    private function manejarError($mensaje,$redirectUrl) {
        $_SESSION['error'] = $mensaje;
        header("Location: $redirectUrl");
        exit();
    }
    private function redirigirUsuarioLogeado() {
        header('Location: /quizgame/home/lobby');
        exit();
    }

    private function getVerificationToken($usuario) {
        $usuarioData = $this->model->buscarUsuario($usuario);
        return $usuarioData[0]['token'] ?? null;
    }



}