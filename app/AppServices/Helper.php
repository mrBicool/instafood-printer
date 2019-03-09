<?php 

namespace App\AppServices;

use Carbon\Carbon; 
use Storage;

class Helper
{  
    public function getClarionDate(Carbon $date){
        $start_date = '1801-01-01';
        $start_from = Carbon::parse($start_date);
        $diff = $date->diffInDays($start_from) + 4;
        return $diff;
    }

    public function getClarionTime(Carbon $date){
        $startOfTheDay = Carbon::create($date->year, $date->month, $date->day, 0, 0, 0);

        $result = $startOfTheDay->diffInSeconds($date);
        //$result = $startOfTheDay->diffInRealMilliseconds($date);

        return $result * 100;
    }

    public function EJournalEntry($header,$obj){
        $paper_size = 42;

        $txt = [
            '02/20/2019 01:17PM  or #: 000 - 000000074',
            'WEB Tran #: 000000074',
            '',
            '** * CUSTOMER COPY ** *',
            '',
            'TOTAL',
            'WALLET / POINTS',
            'PAYMENT',
            'CHANGE',
            '',
            'Customer Name:____________________________',
            'Address:____________________________________',
            'TIN:_________________________________________',
            'Business Style:______________________________',
            'POS PROVIDER:\n',
            'DATALOGIC SYSTEMS CORPORATION\n',
            'Unit 1202 Asian Star Bldg., Asean Drive,\n',
            'cor, Singapura Lane, Filinvest Corporate\n',
            'City, Muntinlupa\n',
            'TIN: 53B-202396939-000012\n',
            'Date Issued: March 18, 2005\n',
            'Valid Until: July 31, 2020\n',
            'PTU#: XXXXXXXX-XXX-XXXXX-XXXX',
            'THIS INVOICE/RECEIPT SHALL BE VALID FOR\n',
            'FIVE (5) YEARS FROM THE DATE OF THE\n',
            'PERMIT USE',
            'Thank you! Please come again :-)\n',
            'www.datalogicorp.com\n',
            '',
            '=== END OF RECEIPT ===',
            ''
        ];

        $this->EjWriter($this->EjCenterAlign('ENCHANTED KINGDOM', $paper_size));
        $this->EjWriter($this->EjCenterAlign('San Lorenzo South, Sta. Rosa Laguna', $paper_size));
        $this->EjWriter($this->EjCenterAlign('VAT Registered TIN: 004-149-597-0000', $paper_size));
        $this->EjWriter($this->EjCenterAlign('Tel No. 584-3535 (Sta Rosa Park)', $paper_size));
        $this->EjWriter($this->EjCenterAlign('Tel no. 830-3535 (Makati Sales Office)', $paper_size));
        $this->EjWriter($this->EjCenterAlign('MIN: XXXXXXXX SN: XXXXXXXX', $paper_size));
        $this->EjWriter($this->EjCenterAlign('', $paper_size));
        $this->EjWriter($this->EjCenterAlign('FOR EVALUATION PURPOSES ONLY', $paper_size));
        $this->EjWriter($this->EjCenterAlign('', $paper_size));
        $this->EjWriter($this->EjCenterAlign('========================================', $paper_size));
        $this->EjWriter($this->EjCenterAlign('', $paper_size));
        $this->EjWriter($this->EjCenterAlign( $this->EjJustifyAlign([$header->created_at, 'or #: 000 - '.$FormatNumberLength() ], $paper_size - 4), $paper_size) );
        $this->EjWriter($this->EjCenterAlign('', $paper_size));
        $this->EjWriter($this->EjCenterAlign('', $paper_size));
        $this->EjWriter($this->EjCenterAlign('', $paper_size));
    }

    private function EjWriter($content){
        Storage::disk('local')->append('file.txt', $content);
    }

    public function EjCenterAlign($string,$maxLength) {

        $stringLength   = strlen($string); 
        $remainingLenth = $maxLength - $stringLength;

        $modResult      = fmod($remainingLenth, 2);

        $halfRemaining  = (int)($remainingLenth/2);

        $output = '';
        for ($i=0; $i < $halfRemaining; $i++) { 
            $output = $output.' ';
        }
 
        $output = $output.$string;

        for ($i = 0; $i < $halfRemaining; $i++) {
            $output = $output.' ';
        }

        if($modResult == 1){
            $output = $output.' ';
        }

        return $output;
    }

    public function EjJustifyAlign($arr, $maxLength){
        $output = ''; 

        $stringLength = 0;
        for ($i=0; $i < count($arr); $i++) {
            $stringLength = $stringLength + strlen($arr[$i]);
        }

        $remainingLenth = $maxLength - $stringLength; 

        $output = $output . $arr[0];
        for ($i = 0; $i < $remainingLenth; $i++) {
            $output = $output . ' ';
        }

        $output = $output . $arr[1];

        return $output;
    }

    public function FormatNumberLength($num, $length)
    { 
        $r = "" + $num;
        while ( strlen($r) < $length) {
            $r = "0" + $r;
        }
        return $r;
    }

    public function charDuplicator($char, $length){
        $output = '';
        for($i = 0; $i < $length; $i++){
            $output = $output.$char;
        } 
        return $output;
    }

    public function currencyFormat($sign,$amount){
        return $sign.' '.number_format( $amount , 2, '.', ',');
    }

}