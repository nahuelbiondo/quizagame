
<?php
class AdminController {
    private $usuarioModel;
    private $presenter;
    private $adminModel;
    private $pdf;
    private $file;
    public function __construct($usuarioModel, $presenter, $adminModel, $pdf) {
        $this->usuarioModel = $usuarioModel;
        $this->presenter = $presenter;
        $this->adminModel = $adminModel;
        $this->pdf = $pdf;
        

    }

    public function mostrarAdminView() {
        
        $filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'month';


        $cantidadJugadores = $this->adminModel->obtenerCantidadJugadores($filtro);
        $cantidadUsuariosNuevos = $this->adminModel->obtenerCantidadUsuariosNuevos($filtro); // Ejemplo de filtro mensual
         $usuariosPorSexo = $this->adminModel->obtenerUsuariosPorSexo();
        $usuariosPorSexo = array_map(function($fila) {
            return [
                'sexo' => $fila['nombre'] ?? $fila['sexo'], 
                'cantidad' => $fila['cantidad']
            ];
        }, $this->adminModel->obtenerUsuariosPorSexo($filtro));
        $usuariosPorEdad = $this->adminModel->obtenerUsuariosPorGrupoEdad($filtro);


        $data = [
            'cantidad_jugadores' => $cantidadJugadores,
            'cantidad_usuarios_nuevos' => $cantidadUsuariosNuevos,
            'cantidad_preguntas_juego' => $this->adminModel->obtenerCantidadPreguntasEnJuego(),
            'cantidad_preguntas_creadas' => $this->adminModel->obtenerCantidadPreguntasCreadas(),
            'cantidad_partidas_jugadas' => $this->adminModel->obtenerCantidadPartidasJugadas($filtro),
            'usuarios_por_pais' => $this->adminModel->obtenerUsuariosPorPais($filtro),
            "esUsuario" => $this->verificarQueUsuarioEs(),
            'usuarios_por_sexo' => json_encode($usuariosPorSexo),
            'usuarios_por_edad' => json_encode($usuariosPorEdad),
            'admin' => true,
            'esUsuario' => false,
            'user' => $_SESSION['user'],
            'jugadores' => $this->adminModel->porcentajePorUsuario()
        ];
    
        // Renderiza la vista
        if($this->existeUsuario()){
        $this->presenter->show('admin', $data);
        }
    }
      private function existeUsuario() {
        return isset($_SESSION['user']);
    }
    private function verificarQueUsuarioEs(){
        return $_SESSION['user']['admin'] == 0 ? true : false;
    }

    public function generarPdf() {
   
    $path = './public/grafico/' . $_SESSION['image1'];
    $path1 = './public/grafico/' . $_SESSION['image2'];

    $imageBase64 = $this->imageToBase64($path);
    $imageBase641 = $this->imageToBase641($path1);
          
    $inputData = json_decode(file_get_contents('php://input'), true);
    $imageData = ['imageBase64' =>  $imageBase64, 'imageBase641' =>  $imageBase641];   

$this->pdf->loadHtml($inputData, $imageData);
$this->pdf->setPaper('A4', 'portrait');


$this->pdf->renderAndStream(); 
}
   


function imageToBase64($imagePath) {
   
    $imageData = file_get_contents($imagePath);
    
    return 'data:image/png;base64,' . base64_encode($imageData);
}
function imageToBase641($imagePath) {
   
    $imageData = file_get_contents($imagePath);
    
    return 'data:image/png;base64,' . base64_encode($imageData);
}


    public function guardarGraficos() {
        
        if (isset( $_POST['image1']) && isset( $_POST['image2'])) {
         
            $imageData1 = $_POST['image1'];
            $imageData2 = $_POST['image2'];
    

            $imageData1 = str_replace('data:image/png;base64,', '', $imageData1);
            $imageData2 = str_replace('data:image/png;base64,', '', $imageData2);
    
   
            $imageData1 = base64_decode($imageData1);
            $imageData2 = base64_decode($imageData2);
    

            $fileName1 = 'grafico1_' . time() . '.png';
            $fileName2 = 'grafico2_' . time() . '.png';
    
    
            $filePath1 = './public/grafico/' . $fileName1;
            $filePath2 = './public/grafico/' . $fileName2;
    
  
            if (file_put_contents($filePath1, $imageData1) === false) {
                echo "Error al guardar la imagen 1.";
            }
            if (file_put_contents($filePath2, $imageData2) === false) {
                echo "Error al guardar la imagen 2.";
            }
    
            echo "Imágenes guardadas correctamente como: " . $fileName1 . " y " . $fileName2;
        } else {
            echo "Las imágenes no se han recibido correctamente.";
        }

        $_SESSION['image1'] = $fileName1;
        $_SESSION['image2'] = $fileName2;
        
    }
    
}