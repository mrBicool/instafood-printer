<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
//use Mike42\Escpos\Printer;

class TestPrintController extends Controller
{
    // 
    public function test(Request $request){

        $connector = new NetworkPrintConnector("192.168.1.105", 9100);  
        //$connector = new WindowsPrintConnector(config( 'maintenance.printer_name'));
        $printer = new Printer($connector);

         
        $printer->text('1234567890123456789012345678901234567890123456');
        
        // $printer->feed(); 
        // $printer->setJustification(Printer::JUSTIFY_CENTER);
        // $printer->qrCode('47', Printer::QR_ECLEVEL_L,8);
        // $printer->setJustification(); // Reset
        // $printer->feed(); 

        // $printer->text('123456789 123456789 123456789 12'."\n");   
        $printer->feed();
        $printer->cut();

        $printer->getPrintConnector()->write(PRINTER::RS);
        $printer->close();
    }
}
