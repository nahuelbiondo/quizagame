<?php
class Permisos{

    private $rutasPermitidas = [
        'auth/login',
        'auth/registrar',
        'auth/procesarUsuario',
        'auth/cerrarSesion',
    ];

    private $rutasPermitidasPorRol = [
        'usuario' => [
            'home/lobby',
            'home/ranking',
            'usuario/perfil',
            'usuario/mostrarUserView',
            'usuario/sugerirPregunta',
            'home/leer',
            'juego/preguntas',
            'juego/empezarPartida',
            'juego/partida',
            'juego/finalizarPartida',
            'juego/reportePregunta',
            'juego/esCorrecta',
            

        ],
        'editor' => [
            'editor/mostrarEditorView',
            'editor/alterarPregunta',
            'editor/verificacion',
            'editor/modificarPregunta',
            'editor/eliminarPregunta',
            'editor/nuevaPregunta',

        ],
        'admin' => [
            'admin/mostrarAdminView',
            'usuario/generarEstadisticasPDF',
            'admin/generarPdf',
            'admin/guardarGraficos',
            'usuario/perfil',
        ]
    ];
    public function procesarSolicitud($controller, $methodName, $controllerName)
    {
        $rol = 'usuario';
        $rutaActual = $controllerName . '/' . $methodName;
        if(isset($_SESSION['user'])) {
            if ($_SESSION['user']['admin']==1){
                $rol = 'admin';
            }elseif ($_SESSION['user']['editor']==1){
                $rol = 'editor';
            }
        }

        if (!in_array($rutaActual, $this->rutasPermitidas)) {

            if (!isset($_SESSION['user']) || !in_array($rutaActual, $this->rutasPermitidasPorRol[$rol])){
                header('Location: /quizgame/auth/login');
                exit;
            }

        }
        return array($controller, $methodName);
    }
}


