<?php

namespace App\Service;

use Dompdf\Dompdf;

class PdfGenerateur
{
    public function generationDepuisHTML(string $html): string 
    {
        $dompdf = new Dompdf(); 
        $dompdf->loadHtml($html);
        $dompdf->render();
        return $dompdf->output();
    }

}