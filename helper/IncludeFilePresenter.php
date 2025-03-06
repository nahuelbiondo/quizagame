<?php
class IncludeFilePresenter
{
    public function __construct()
    {
    }

    public function show($view, $data = []){
        //include_once('view/header.php');
        echo "<script>console.log('pasa por helper/includeFilePresenter.php/show');</script>";

        include_once('view/'. $view . "View.php");
        //include_once('view/footer.php');
    }
}