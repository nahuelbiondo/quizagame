<?php

class AdminModel {

    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    // 1. Obtener cantidad de jugadores
    // public function obtenerCantidadJugadores($filtro) {
    //     $interval = $this->determinarIntervalo($filtro);
    //     $sql = "SELECT COUNT(*) as total FROM usuario WHERE admin = 0 AND editor = 0";
    //     $resultado = $this->database->query($sql);
    //     return $resultado[0]['total'];
    // }

    public function obtenerCantidadJugadores($filtro = null) {
        // Base de la consulta
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE admin = 0 AND editor = 0";
        
        // Si se pasa un filtro, agregamos la condición para el intervalo de tiempo
        if ($filtro && $filtro !== 'history') {
            $intervalo = $this->determinarIntervalo($filtro);
            $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)";
        }
    
        $resultado = $this->database->query($sql);
        return $resultado[0]['total'];
    }

    // 2. Obtener cantidad de partidas jugadas
    public function obtenerCantidadPartidasJugadas() {
        $sql = "SELECT COUNT(*) as total FROM partida";
        
        $resultado = $this->database->query($sql);
        return $resultado[0]['total'];
    }

    // 3. Obtener cantidad de preguntas en el juego
    public function obtenerCantidadPreguntasEnJuego() {
        $sql = "SELECT COUNT(*) as total FROM preguntas WHERE estado = '1'";
        $resultado = $this->database->query($sql);
        return $resultado[0]['total'];
    }

    // 4. Obtener cantidad de preguntas creadas
    public function obtenerCantidadPreguntasCreadas() {
        $sql = "SELECT COUNT(*) as total FROM preguntas";
        $resultado = $this->database->query($sql);
        return $resultado[0]['total'];
    }

    // 5. Obtener cantidad de usuarios nuevos (filtrados por periodo)
    public function obtenerCantidadUsuariosNuevos($filtro = null) {
        // $intervalo = $this->determinarIntervalo($periodo);
        
        $sql = "SELECT COUNT(*) as total FROM usuario WHERE estado = 1 AND admin = 0 AND editor = 0";
        if ($filtro && $filtro !== 'history') {
            $intervalo = $this->determinarIntervalo($filtro);
            $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)";
        }
        $resultado = $this->database->query($sql);
        return $resultado[0]['total'];
    }

    // 6. Obtener porcentaje de respuestas correctas
    public function obtenerPorcentajeRespuestasCorrectas() {

        $sql = "SELECT 
                    (SUM(CASE WHEN o.id = r.opcionID THEN 1 ELSE 0 END) / COUNT(*)) * 100 as porcentaje
                FROM respuesta r
                JOIN opciones o ON r.preguntaID = o.preguntaID";
        $resultado = $this->database->query($sql);
        return $resultado[0]['porcentaje'];
    }

    public function porcentajePorUsuario(){

        $queryUsuarios = "SELECT u.usuario, u.id, u.fotoPerfil, MAX(p.puntaje_obtenido) AS puntaje, p.fecha_partida AS fecha
                          FROM partida p
                          JOIN usuario u ON u.id = p.idUsuario
                          GROUP BY u.id
                          ORDER BY puntaje DESC, p.fecha_partida ASC
                       ";
        
      
        $usuarios = $this->database->query($queryUsuarios);
        
        
       
        $rankingUsuarios = [];
        
        
        foreach ($usuarios as $index => $usuario) {
            $rankingUsuarios[] = [
                'posicion' => $index + 1,
                'usuario' => $usuario['usuario'],
                'id' => $usuario['id'],
                'porcentaje' => $this->calcularPorcentaje($usuario['id']),
                'puntaje' => $usuario['puntaje'],
                'fecha' => $usuario['fecha'],
                'fotoPerfil' => $usuario['fotoPerfil']
            ];
        }
        return $rankingUsuarios; 
    }
    
    private function calcularPorcentaje($id){

        $sql = "SELECT COUNT(*) AS cantidad FROM dificultad 
        
        
         WHERE veces_correctas > 0 AND idUsuario = " . $id . "";

        $query = $this->database->query($sql);
        $porcentaje = $this->calcular($query);

        return $porcentaje;
    }
    private function calcular($query){

        $sql = "SELECT COUNT(*) AS cantidadPreguntas FROM preguntas";

        $preguntas = $this->database->query($sql);  

        $calculo = ($query[0]['cantidad'] / $preguntas[0]['cantidadPreguntas']) * 100;

        $calculo = number_format($calculo, 2);
        return $calculo;
    }
    // 7. Obtener usuarios por país
    public function obtenerUsuariosPorPais($filtro = null) {
        

        $sql = "SELECT pais AS nombre, COUNT(*) AS cantidad FROM usuario GROUP BY pais";
        if ($filtro && $filtro !== 'history') {
            $intervalo = $this->determinarIntervalo($filtro);
            $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)";
        }
        return $this->database->query($sql);
    }

    // 8. Obtener usuarios por género
    // public function obtenerUsuariosPorGenero($filtro = null) {

        
    //     $sql = "SELECT genero, COUNT(*) as total FROM usuario GROUP BY genero";
    //     if ($filtro) {
    //         $intervalo = $this->determinarIntervalo($filtro);
    //         $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)";
    //     }
    //     return $this->database->query($sql);
    // }

    // public function obtenerUsuariosPorGenero($filtro = null) {
    //     $sql = "SELECT genero, COUNT(*) as total FROM usuario GROUP BY genero"; // Asegúrate de que 'genero' sea el nombre correcto de la columna en tu base de datos
    
    //     // if ($filtro) {
    //     //     $intervalo = $this->determinarIntervalo($filtro);
    //     //     $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)"; // Aplica el filtro correctamente
    //     // }
    
    //     return $this->database->query($sql);
    // }

    // 9. Obtener usuarios por grupo de edad
    // public function obtenerUsuariosPorGrupoEdad($filtro = null) {
    //     $sql = "SELECT CASE WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 0 AND 17 THEN '0-17' WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 25 THEN '18-25' WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 26 AND 35 THEN '26-35' WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 36 AND 50 THEN '36-50' ELSE '51+' END AS grupo, COUNT(*) AS cantidad FROM usuario GROUP BY grupo";
    //     if ($filtro) {
    //         $intervalo = $this->determinarIntervalo($filtro);
    //         $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)";
    //     }
    //     return $this->database->query($sql);
    // }


    public function obtenerUsuariosPorGrupoEdad($filtro = null) {
        // Crea la consulta base sin el filtro
        $sql = "SELECT 
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 0 AND 17 THEN '0-17'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 18 AND 25 THEN '18-25'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 26 AND 35 THEN '26-35'
                        WHEN TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) BETWEEN 36 AND 50 THEN '36-50'
                        ELSE '51+' 
                    END AS grupo, 
                    COUNT(*) AS cantidad 
                FROM usuario";
    
        // Si hay un filtro de fecha, aplícalo después de la selección y agrupación
        if ($filtro && $filtro !== 'history') {
            $intervalo = $this->determinarIntervalo($filtro);
            $sql .= " WHERE created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)"; // Filtro de fecha solo para 'created_at'
        }
    
        // Agrupación por grupo de edad
        $sql .= " GROUP BY grupo";
    
        // Ejecuta la consulta
        return $this->database->query($sql);
    }
    // Método privado para determinar intervalo según el periodo
    // private function determinarIntervalo($periodo) {
    //     switch ($periodo) {
    //         case 'day': return '1 DAY';
    //         case 'week': return '1 WEEK';
    //         case 'month': return '1 MONTH';
    //         case 'year': return '1 YEAR';
    //         default: return '1 DAY';
    //     }
    // }

    // public function obtenerUsuariosPorSexo() {
    //     $sql = "SELECT genero AS nombre, COUNT(*) AS cantidad FROM usuario GROUP BY genero";
        
      
    //     return $this->database->query($sql);
    // }


    public function obtenerUsuariosPorSexo($filtro = null) {
        $sql = "SELECT genero AS nombre, COUNT(*) AS cantidad FROM usuario";
        
        // Aplica el filtro solo si es necesario
        if ($filtro && $filtro !== 'history') {
            $intervalo = $this->determinarIntervalo($filtro);
            $sql .= " WHERE created_at >= DATE_SUB(NOW(), INTERVAL $intervalo)";
        }
    
        // Agrega la agrupación por sexo
        $sql .= " GROUP BY genero";
        
        return $this->database->query($sql);
    }
    private function determinarIntervalo($filtro) {
        switch ($filtro) {
            case 'day':
                return '1 DAY';
            case 'year':
                return '1 YEAR';
            case 'month':
            default:
                return '1 MONTH';
        }
    }
}
