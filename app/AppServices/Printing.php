<?php

namespace App\AppServices;

use Illuminate\Support\Facades\Log; 

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class Printing {

    /**
     * PRINTING FOR WINDOWS
     */
    protected $printer;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct($printer_name)
    {
        //$connector = new WindowsPrintConnector($printer_name);

        //$this->printer = new Printer($connector);
    }

    public function setText($val){
        //$this->printer->text($val."\n");
        Log::debug($val);
    }

    public function setQrCode($val){
        $this->printer->feed();
        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->qrCode(''.$val, Printer::QR_ECLEVEL_L,8);   
        $this->printer->setJustification(); // Reset
        $this->printer->feed(); 
    }

    public function close(){
        $this->printer->close();
    }

}