<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class TestPrintController extends Controller
{
    //
    public function test(Request $request){

        /**
         * Install the printer using USB printing support, and the "Generic / Text Only" driver,
         * then share it (you can use a firewall so that it can only be seen locally).
         *
         * Use a WindowsPrintConnector with the share name to print.
         *
         * Troubleshooting: Fire up a command prompt, and ensure that (if your printer is shared as
         * "Receipt Printer), the following commands work:
         *
         *  echo "Hello World" > testfile
         *  copy testfile "\\%COMPUTERNAME%\Receipt Printer"
         *  del testfile
         */
        try {
            // Enter the share name for your USB printer here
            $connector = null;
            $connector = new WindowsPrintConnector("POS-58");
            /* Print a "Hello world" receipt" */
            $printer = new Printer($connector);
            $printer->text("Hello World!\n");
            $printer->feed();
            // Most simple example  
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->qrCode('qrcode value', Printer::QR_ECLEVEL_L,8);   
            $printer->setJustification(); // Reset
            $printer->feed(3); 

            /* Close printer */
            $printer->close();
        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e->getMessage() . "\n";
        }

        dd('hello world');
    }
}
