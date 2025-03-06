<?php
require_once 'vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;
class GenerarPdf
{
    private $dompdf;

    public function __construct()
    {
        $this->dompdf = new Dompdf();
    }

    public function loadHtml($inputData, $imageData): void
    {

        $cantidadJugadores = $inputData['cantidad_jugadores'];
        $cantidadUsuariosNuevos = $inputData['cantidad_usuarios_nuevos'];
        $cantidadPreguntasJuego = $inputData['cantidad_preguntas_juego'];
        $cantidadPreguntasCreadas = $inputData['cantidad_preguntas_creadas'];
        $cantidadPartidasJugadas = $inputData['cantidad_partidas_jugadas'];

        $imageBase64 = $imageData['imageBase64'];
        $imageBase641 = $imageData['imageBase641'];


        $htmlContent = '
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            margin: 0;
            padding: 0;
            background-color: #1f1f2e;
        }

        h1 { 
            color: #ffffff; 
            margin-bottom: 20px; 
        }

        h2 {
            color: #ffffff;
            margin-bottom: 15px;
        }

        .grafico { 
            margin: 20px 0; 
            background-color: #23233b;
            padding: 20px;
            border-radius: 10px;
        }
        tr{
        color:white;
    }

        table {
            width: 80%; /* Ancho de la tabla */
            margin: 0 auto; /* Centrar la tabla */
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #333;
            font-size: 16px;
        }

        thead {
            background-color: #2c2c47;
        }

        tbody tr:nth-child(odd) {
            background-color: #23233b;
        }

        tbody tr:nth-child(even) {
            background-color: #2c2c47;
        }

        td {
            color: white;
        }

        .gráfico-container {
            display: flex;
            justify-content: center;
            gap: 40px; /* Espaciado entre los gráficos */
        }

        img {
            max-width: 35%; /* Limitar el tamaño de las imágenes */
            height: auto;
            border-radius: 8px;
        }

        /* Estilos para los gráficos */
        .grafico h2 {
            margin-bottom: 15px;
        }
            .graph2{
                 max-width: 65%;
            }
    </style>
</head>
<body>
    <h1>Panel de Administrador</h1>
    <div class="mb-5">
        <h2 class="mb-3">Estadísticas Generales</h2>
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Cantidad de usuarios</td>
                    <td>' . $cantidadJugadores .'</td>
                </tr>
                <tr>
                    <td>Cantidad de usuarios nuevos autenticados</td>
                    <td>' . $cantidadUsuariosNuevos .'</td>
                </tr>
                <tr>
                    <td>Cantidad de preguntas en el juego</td>
                    <td>' . $cantidadPreguntasJuego .'</td>
                </tr>
                <tr>
                    <td>Cantidad de preguntas creadas</td>
                    <td>' . $cantidadPreguntasCreadas .'</td>
                </tr>
                <tr>
                    <td>Cantidad de partidas jugadas</td>
                    <td>' . $cantidadPartidasJugadas .'</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Contenedor para los gráficos -->
    <div class="gráfico-container">
        <div class="grafico">
            <h2>Gráfico 1: Usuarios por Sexo</h2>
            <img src="' . $imageBase64 . '" alt="Gráfico 1" />
        </div>

        <div class="grafico">
            <h2>Gráfico 2: Usuarios por Edad</h2>
            <img class="graph2" src="' . $imageBase641 . '" alt="Gráfico 2" />
        </div>
    </div>

</body>
</html>

';

        $this->dompdf->loadHtml($htmlContent);
    }

    public function setPaper(string $paperSize = 'A4', string $orientation = 'portrait'): void
    {
        $this->dompdf->setPaper($paperSize, $orientation);
    }

    
    public function renderAndStream()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $this->dompdf->setOptions($options);
        
    
        $this->dompdf->render();
        
        $this->dompdf->stream("document.pdf" , ['Attachment' => 0]);
    }
}
    
