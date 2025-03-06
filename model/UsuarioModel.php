<?php

class UsuarioModel
{
    private $database;
 
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function validate($user, $pass)
    {
        $sql = "SELECT *
                FROM usuario 
                WHERE usuario = '" . $user. "' 
                AND password = '" . $pass . "'";

        return $this->database->query($sql);
    }
    public function registrarUsuario($data){
            $token = rand(100000, 999999);
            $sql = "INSERT INTO usuario (nombre, apellido, usuario, genero, email, password, estado, token, fotoPerfil, ciudad, pais) 
            VALUES ('" . $data['nombre'] . "', '" . $data['apellido'] . "', '" . $data['usuario'] . "', '" . $data['genero'] . "', '" . $data['email'] . "', '" . $data['password'] . "', 0, '" . $token . "', '" . $data['fotoPerfil'] . "', '" . $data['ciudad'] . "', '" . $data['pais'] . "')";
            
            return $this->database->query($sql);
    }

    public function enviarCorreoVerificacion($mail, $nombre, $usuario){
        
       $this->guardarToken($usuario, $codigoVerificacion);

    }

    public function partidasJugadas($id){
        $sql = "SELECT COUNT(*) AS total FROM partida WHERE idUsuario = ". $id . "";
        $query = $this->database->query($sql);
        return $query[0]['total'];
    }

    public function ultimaPartida($id){
        $sql = "SELECT fecha_partida FROM partida WHERE idUsuario = $id ORDER BY fecha_partida DESC LIMIT 1";


        $query = $this->database->query($sql);

        $query1 = isset($query[0]['fecha_partida']) ? $query[0]['fecha_partida'] : "";
        return $query1;
    }
    public function buscarUsuario($nombreUsuario) {
        return $this->database->query("SELECT * FROM `usuario` WHERE usuario = '$nombreUsuario'");
    }
    public function guardarToken($username, $codigoVerificacion) {
         $usuarioResult = $this->buscarUsuario($username);
        
        if (isset($usuarioResult)) {
            $idUsuario = $usuarioResult[0]['id']; 
            $this->database->query("UPDATE usuario SET token = $codigoVerificacion WHERE id= '$idUsuario'");
        }
    }

    public function activarUsuario($user, $token) {
        $usuarioResult = $this->buscarUsuario($user);
        
        if (!$usuarioResult || $usuarioResult[0]['token'] !== $token) {
            throw new Exception("Token inválido o usuario no encontrado.");
        }
        $this->cambiarEstadoUsuario($usuarioResult[0]['id']);
    }

    public function verificarSiTieneUnaPartidaActiva($id){
        $sql = "SELECT * FROM partida WHERE idUsuario =" . $id . " AND estado = 1";

        $query = $this->database->query($sql);

        if(!empty($query)){
            return $query;
        }

        return false;
    }

    public function puntajeTotal($id){
        $sql = "SELECT puntaje FROM usuario WHERE id = " . $id . " ";

        return $this->query($sql);
    }
    public function obtenerTodasLasPreguntas()
    {
        $sql = "SELECT p.id AS preguntaId, p.pregunta, p.estado, c.descripcion AS categoria, c.color, 
           GROUP_CONCAT(o.opcion ORDER BY o.id SEPARATOR ', ') AS opciones, 
           MAX(CASE WHEN r.opcionID = o.id THEN o.opcion ELSE NULL END) AS es_correcta 
        FROM preguntas p 
        JOIN categoria c ON p.idCategoria = c.id 
        JOIN opciones o ON p.id = o.preguntaID 
        LEFT JOIN respuesta r ON r.preguntaID = p.id 
        WHERE p.verificado = 'aprobado'
        GROUP BY p.id, p.pregunta, p.estado, c.descripcion, c.color 
        ORDER BY p.id;";
    

        $resultado = $this->database->query($sql);
   

        $preguntas = [];
        foreach ($resultado as $fila) {
            $preguntas[] = [
                'id' => $fila['preguntaId'],
                'pregunta' => $fila['pregunta'],
                'estado' => $fila['estado'],
                'categoria' => $fila['categoria'],
                'color' => $fila['color'],
                'opciones' => explode(', ', $fila['opciones']),
                'es_correcta' => $fila['es_correcta'],
            ];
          
        }

        $preguntas = [];
        $count = 0;
    foreach ($resultado as $fila) {

    $opciones = explode(', ', $fila['opciones']);
    $opcionesConIndex = [];

    foreach ($opciones as $index => $opcion) {
        $opcionesConIndex[] = [
            'texto' => $opcion,
            'index' => $index 
        ];
    }

    $preguntas[] = [
        'id' => $fila['preguntaId'],
        'pregunta' => $fila['pregunta'],
        'estado' => $this->estadoLeible($fila['estado']),
        'categoria' => $fila['categoria'],
        'color' => $fila['color'],
        'opciones' => $opcionesConIndex,  
        'index' => $count,  
        'es_correcta' => $fila['es_correcta'],
    ];
}
        return $preguntas;
    }

    public function eliminarReporte($pregunta_id){

        $sql = "DELETE FROM reporte 
        WHERE idPregunta = {$pregunta_id}";

    
        $this->database->query($sql);
    }
    public function cambiarEstadoPregunta($pregunta_id) {
        
       $sql = "UPDATE preguntas 
        SET estado = NOT estado, verificado = 'aprobado'
        WHERE id = {$pregunta_id}";
        
        $this->database->query($sql);
    }
    public function rechazarPregunta($pregunta_id) {
        
        $sql = "UPDATE preguntas 
         SET verificado = 'rechazada'
         WHERE id = {$pregunta_id}";

         $this->database->query($sql);
     }
    
    public function getUser($id){
        $sql = "SELECT * FROM usuario WHERE id = " . $id ." ";

        return $this->database->query($sql);
    }
    public function obtenerRankingUsuarios() {
     
        $queryUsuarios = "SELECT u.usuario, u.id, u.fotoPerfil, MAX(p.puntaje_obtenido) AS puntaje, p.fecha_partida AS fecha
                          FROM partida p
                          JOIN usuario u ON u.id = p.idUsuario
                          GROUP BY u.id
                          ORDER BY puntaje DESC, p.fecha_partida DESC
                       ";
        
      
        $usuarios = $this->database->query($queryUsuarios);
        
       
        $rankingUsuarios = [];
        
        
        foreach ($usuarios as $index => $usuario) {
            $rankingUsuarios[] = [
                'posicion' => $index + 1,
                'usuario' => $usuario['usuario'],
                'id' => $usuario['id'],
                'puntaje' => $usuario['puntaje'],
                'fecha' => $usuario['fecha'],
                'fotoPerfil' => $usuario['fotoPerfil']
            ];
        }
        return $rankingUsuarios; 
    }

    public function sugerencia($data){

        $sql = "INSERT INTO preguntas (pregunta, estado, idUsuario, idCategoria, verificado) 
        VALUES ('" . $data['pregunta'] . "', '0', '" . $data['idUsuario'] . "', '" . $data['categoria'] . "', 'pendiente')";

         $pregunta = $this->database->query($sql);
       
         $this->agregarOpciones($pregunta, $data['opciones']);
         $this->agregarRespuestaCorrecta($pregunta,$data['respuesta']);
    }

    public function estadoReporte($accion,$id,$pregunta_id){
        $sql = "UPDATE reporte SET verificado = '{$accion}' WHERE idUsuarioReporte = {$id} AND idPregunta = {$pregunta_id}";

        $this->database->query($sql);
    }
    public function nuevaPregunta($data){

        $sql = "INSERT INTO preguntas (pregunta, estado, idUsuario, idCategoria, verificado) 
        VALUES ('" . $data['pregunta'] . "', '1', '" . $data['idUsuario'] . "', '" . $data['categoria'] . "', 'aprobado')";

         $pregunta = $this->database->query($sql);
       
         $this->agregarOpciones($pregunta, $data['opciones']);
         $this->agregarRespuestaCorrecta($pregunta,$data['respuesta']);
    }

    private function agregarOpciones($id,$opciones){

        for($i = 0; $i < 4; $i++){
            $sql = "INSERT INTO opciones (preguntaID, opcion) 
        VALUES ('" . $id . "', '" . $opciones[$i] ."')";
            $this->database->query($sql);
        }

    }

    private function agregarRespuestaCorrecta($id,$respuesta){

        $sql = "SELECT * FROM opciones WHERE preguntaID = " . $id . "";

        $query = $this->database->query($sql);

        foreach($query as $opciones){
            
            if($opciones['opcion'] == $respuesta){
                $this->agregar($id,$opciones['id']);
                break;
            }
        }
    }

    public function cantidadNuevosReportes(){
        $sql = "SELECT COUNT(*) AS cantidad FROM reporte WHERE verificado = 'pendiente'";

        $query = $this->database->query($sql);

        return $query[0]['cantidad'];
    }

    public function cantidadNuevasSugerencias(){
        $sql = "SELECT COUNT(*) AS cantidad FROM preguntas WHERE verificado = 'pendiente'";

        $query = $this->database->query($sql);

        return $query[0]['cantidad'];
    }

    public function edadQuiz($id){
        $sql = "SELECT created_at FROM usuario WHERE id = " . $id . " ";

        $query = $this->database->query($sql);

      
            $dateCreated = new DateTime($query[0]['created_at']);

           
            $currentDate = new DateTime();

          
            $interval = $dateCreated->diff($currentDate);
            $totalDays = ($interval->y * 365) + ($interval->m * 30) + $interval->d;
            
            return  $totalDays; 
    }

    public function cantidadPreguntasSugeridasPorUsuario($id){

        $sql = "SELECT COUNT(*) AS cantidad FROM preguntas WHERE idUsuario = " . $id . "";

        $query = $this->database->query($sql);
        return $query[0]['cantidad'];
    }

    public function preguntasPendientes(){
        $sql = "SELECT p.id AS preguntaId, p.pregunta, p.estado, c.descripcion AS categoria, c.color, u.usuario AS usuario, u.id AS idUsuario,
        GROUP_CONCAT(o.opcion ORDER BY o.id SEPARATOR ', ') AS opciones, 
        MAX(CASE WHEN r.opcionID = o.id THEN o.opcion ELSE NULL END) AS es_correcta 
     FROM preguntas p 
     JOIN categoria c ON p.idCategoria = c.id 
     JOIN opciones o ON p.id = o.preguntaID 
     JOIN usuario u on u.id = p.idUsuario
     LEFT JOIN respuesta r ON r.preguntaID = p.id 
     WHERE p.verificado = 'pendiente'
     GROUP BY p.id, p.pregunta, p.estado, c.descripcion, c.color 
     ORDER BY p.id;";
 

     $resultado = $this->database->query($sql);


     $preguntas = [];
     foreach ($resultado as $fila) {
         $preguntas[] = [
             'id' => $fila['preguntaId'],
             'pregunta' => $fila['pregunta'],
             'estado' => $fila['estado'],
             'categoria' => $fila['categoria'],
             'color' => $fila['color'],
             'usuario' => $fila['usuario'],
             'idUsuario' => $fila['idUsuario'],
             'opciones' => explode(', ', $fila['opciones']),
             'es_correcta' => $fila['es_correcta'],
         ];
       
     }

        return $preguntas;
    }

    public function notificaciones($idUsuario){
        $sql = "SELECT * FROM notificacion WHERE idUsuario = " . $idUsuario . " ORDER BY fecha DESC";

      
        $data = [
            'notificacion' => $this->database->query($sql),
            'cantidad' => $this->cantidadNoLeidas($idUsuario)
        ];
        return $data;
    }

    public function leer($id){
      
    
        $sql = "UPDATE notificacion SET leido = 1 WHERE idUsuario = $id";
    
        $this->database->query($sql);
    }
    

    private function cantidadNoLeidas($idUsuario){
         $sql = "SELECT COUNT(*) AS cantidad FROM notificacion WHERE idUsuario = " . $idUsuario . " AND leido = 0";
         $query =$this->database->query($sql);

          return  $query[0]['cantidad'];
        }

    public function notificar($idUsuario, $mensaje,$tipo) {
    
        $fecha = date('Y-m-d H:i:s'); 
        
        $sql = "INSERT INTO notificacion (idUsuario, mensaje, leido, fecha, tipo) VALUES ($idUsuario, '$mensaje', 0, '$fecha', '$tipo')";

    
    
        $this->database->query($sql);
    }
    
        private function cambiarEstadoUsuario($id) {
        $estado = 1;
        $this->database->query("UPDATE usuario SET estado = '$estado' WHERE id = '$id'");
    }

    private function agregar($id,$opcionID){

        $sql = "INSERT INTO respuesta (preguntaID, opcionID) VALUES( '" . $id . "', '" . $opcionID ."')";

        $this->database->query($sql);
    }
    private function estadoLeible($estado){

        if($estado == 1){
            return true;
        }else{
            return false;
        }
    }



    public function modificarPregunta($data){
        $ids = $this->idDeOpciones($data['id']);

        $opciones = $data['opciones'];

        $sql = "UPDATE preguntas p
                JOIN opciones o ON o.preguntaID = p.id
                JOIN respuesta r ON r.preguntaId = p.id
                SET 
                    o.opcion = CASE 
                        WHEN o.id = {$ids[0]['id']} THEN '$opciones[0]'
                        WHEN o.id = {$ids[1]['id']} THEN '$opciones[1]'
                        WHEN o.id = {$ids[2]['id']} THEN '$opciones[2]'
                        WHEN o.id = {$ids[3]['id']} THEN '$opciones[3]'
                    END,
                    p.pregunta = '{$data['pregunta']}',
                    p.idCategoria = '{$data['categoria']}'
                WHERE p.id = '{$data['id']}'";
    
 
        $this->database->query($sql);
        $this->respuestaCorrecta($data['respuesta'], $data['id']);
    }

    private function respuestaCorrecta($respuesta, $id){

        $sql = "SELECT id, opcion FROM opciones WHERE preguntaID = " . $id . "";

        $opciones = $this->database->query($sql);

        
        foreach($opciones as $op){
            var_dump($op['opcion']);
            if($op['opcion'] == $respuesta){
                $this->modificar($op['id'], $id);
                break;
            }

        }

    }
    
    private function modificar($id, $preguntaId){

        $sql = "UPDATE respuesta
        SET opcionID = $id 
        WHERE preguntaID = $preguntaId";

        $this->database->query($sql);

    }
    private function idDeOpciones($idPregunta){

        $sql = "SELECT id FROM opciones WHERE preguntaID = " . $idPregunta . " ";

        return $this->database->query($sql);
    }

    public function eliminarPregunta($id){
       $this->eliminarRespuesta($id);
       $this->eliminarOpciones($id);
       $this->eliminar($id);
    }

    private function eliminarRespuesta($id){
        $sql = "DELETE r
                FROM respuesta r
                JOIN opciones o ON r.opcionID = o.id
                WHERE o.preguntaID = " . $id . "";

        $this->database->query($sql);
    }
    private function eliminarOpciones($id){
        $sql = "DELETE o
                FROM opciones o
                WHERE o.preguntaID = " . $id . "";
                
        $this->database->query($sql);
    }
    private function eliminar($id){
        $sql = "DELETE p
        FROM preguntas p
        WHERE id = " . $id . "";
        
        $this->database->query($sql);
    }

    public function obtenerPreguntasReportadas()
    {

        $sql = "SELECT p.id AS preguntaId, p.pregunta, p.estado, c.descripcion AS categoria, c.color, re.detalleReporte, u.usuario AS usuario, u.id AS idUsuario,
        GROUP_CONCAT(o.opcion ORDER BY o.id SEPARATOR ', ') AS opciones, 
        MAX(CASE WHEN r.opcionID = o.id THEN o.opcion ELSE NULL END) AS es_correcta 
 FROM preguntas p 
 JOIN categoria c ON p.idCategoria = c.id 
 JOIN opciones o ON p.id = o.preguntaID
 JOIN reporte re ON p.id = re.idPregunta 
 JOIN usuario u on u.id = re.idUsuarioReporte
 LEFT JOIN respuesta r ON r.preguntaID = p.id 
 WHERE re.verificado = 'pendiente'
 GROUP BY p.id, p.pregunta, p.estado, c.descripcion, c.color 
 ORDER BY p.id;";

    

        $resultado = $this->database->query($sql);
   

        $preguntas = [];
        foreach ($resultado as $fila) {
            $preguntas[] = [
                'idPreg' => $fila['preguntaId'],
                'pregunta' => $fila['pregunta'],
                'estado' => $fila['estado'],
                'detalleReporte' => $fila['detalleReporte'],
                'categoria' => $fila['categoria'],
                'color' => $fila['color'],
                'opciones' => explode(', ', $fila['opciones']),
                'es_correcta' => $fila['es_correcta'],
            ];
          
        }

        $preguntas = [];
        $count = 0;
    foreach ($resultado as $fila) {

    $opciones = explode(', ', $fila['opciones']);
    $opcionesConIndex = [];

    foreach ($opciones as $index => $opcion) {
        $opcionesConIndex[] = [
            'texto' => $opcion,
            'index' => $index 
        ];
    }

    $preguntas[] = [
        'idPreg' => $fila['preguntaId'],
        'pregunta' => $fila['pregunta'],
        'estado' => $this->estadoLeible($fila['estado']),
        'detalleReporte' => $fila['detalleReporte'],
        'categoria' => $fila['categoria'],
        'usuario' => $fila['usuario'],
        'idUsuario' => $fila['idUsuario'],
        'color' => $fila['color'],
        'opciones' => $opcionesConIndex,  
        'index' => $count,  
        'es_correcta' => $fila['es_correcta'],
    ];
}
        return $preguntas;

    }
    
}