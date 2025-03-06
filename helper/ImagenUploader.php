<?php

class ImagenUploader{
    private $carpetaDestino = "./public/image/perfil/";

    public function guardarImagen($usuario) {
        try {

            if ($this->esArchivoValido()) {
            $nombreImagen = $this->generarNombreImagen($usuario);
            $rutaDestino = $this->obtenerRutaDestino($nombreImagen);
            $this->moverArchivo($rutaDestino);
        
            return $rutaDestino;
            } 
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    

    private function esArchivoValido() {
        if (!isset($_FILES["fotoPerfil"]) || $_FILES["fotoPerfil"]["error"] !== UPLOAD_ERR_OK) {
            return false;
        }

        $tipoArchivo = $_FILES["fotoPerfil"]["type"];
        return $this->extensionPermitida($tipoArchivo);
    }

    private function extensionPermitida($tipoArchivo) {
        $tiposPermitidos = ["image/jpg", "image/jpeg", "image/png"];

        if(in_array($tipoArchivo, $tiposPermitidos)){
            return true;
        }
       throw new Exception("Debe elegir otro formato de imagen (jpg,jpeg,png)");
    }

    private function generarNombreImagen($usuario) {
        $imagenOriginal = basename($_FILES['fotoPerfil']['name']);
        $extension = pathinfo($imagenOriginal, PATHINFO_EXTENSION);
        return $usuario . "." . $extension;
    }

    private function obtenerRutaDestino($nombreImagen) {
        return $this->carpetaDestino . $nombreImagen;
    }
    private function moverArchivo($rutaDestino) {
        return move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], $rutaDestino);
    }
}