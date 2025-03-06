<?php
include_once("helper/MysqlDatabase.php");
include_once("helper/MysqlObjectDatabase.php");
include_once("helper/IncludeFilePresenter.php");
include_once("helper/Router.php");
include_once("helper/MustachePresenter.php");
include_once("helper/SendEmail.php");
include_once("helper/ImagenUploader.php");
include_once('helper/GenerarPdf.php');
include_once('helper/Permisos.php');
include_once("controller/UsuarioController.php");
include_once("controller/JuegoController.php");
include_once("controller/AuthController.php");
include_once("controller/EditorController.php");
include_once("controller/AdminController.php");
include_once("controller/HomeController.php");
include_once("model/UsuarioModel.php");
include_once("model/JuegoModel.php");
include_once("model/AdminModel.php");
include_once('vendor/mustache/src/Mustache/Autoloader.php');
include_once('vendor/phpqrcode/qrlib.php');

class Configuration
{
    public function __construct()
    {
    }

    public function getRankingModel(){
        return new RankingModel($this->getDatabase());
    }

    public function getUsuarioController(){

        return new UsuarioController($this->getUsuarioModel(), $this->getPresenter());
    }

    public function getJuegoController(){
        return new JuegoController($this->getJuegoModel(), $this->getPresenter());
    }

    public function getEditorController(){
        return new EditorController($this->getUsuarioModel(), $this->getPresenter());
    }

    public function getHomeController(){
        return new HomeController($this->getUsuarioModel(),$this->getPresenter());
    }
    public function getAdminController(){
        return new AdminController($this->getUsuarioModel(),$this->getPresenter(),$this->getAdminModel(),$this->getPdf());
    }


    public function getAuthController(){
        return new AuthController($this->getUsuarioModel(), $this->getPresenter(), $this->getSendEmail(), $this->getImagenUploader());
    }

    public function getAdminModel(){
        return new AdminModel($this->getDatabase());
    }

    public function getJuegoModel(){
        return new JuegoModel($this->getDatabase());
    }

    public function getPdf(){
        return new GenerarPdf();
    }
  
    private function getPresenter()
    {
        return new MustachePresenter("view");
    }
    private function getDatabase()
    {
        $config = parse_ini_file('configuration/config.ini');
        return new MysqlObjectDatabase(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
            $config["database"]
        );
    }

    private function getPermisos()
    {
        return new Permisos();
    }
    public function getRouter()
    {
        return new Router($this, "getAuthController", "login", $this->getPermisos());
    }

    private function getUsuarioModel()
    {
        return new UsuarioModel($this->getDatabase());
    }
    public function getSendEmail(){
        return new SendEmail();
    }
    public function getImagenUploader(){
        return new ImagenUploader();
    }
}