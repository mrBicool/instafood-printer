<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log; 
use App\AppServices\Helper;
 
use App\AppServices\Printing;

class PrintController extends Controller
{
    // 

     public function salesOrder(Request $request){ 

        try {
            
            $printer_name = config( 'maintenance.printer_name');
            $printer_width= config( 'maintenance.printer_width');

            $p = new Printing('POS80 Printer');
            $helper = new Helper;

            $length = $printer_width;

            $p->setTitleHeader( 
                'Enchanted Kingdom' 
            );

            $p->setQrCode(''.$request->os_number);
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    'Order slip no. : '.$request->os_number 
                , $length)
            );  
            $p->feed();

            /**
             * Customer Information
             */
            if($request->data['others']['mobile_number'] != null){

                $p->setText(
                    $helper->EjCenterAlign(
                        'Customer Information' 
                    , $length)
                );

                $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            'Name',
                            ''.$request->data['others']['customer_name']
                        ],$length-4) 
                    , $length)
                );

                $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            'Mobile No.',
                            ''.$request->data['others']['mobile_number']
                        ],$length-4) 
                    , $length)
                );

            }

            /**
             * CURRENCY
             */
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        '',
                        '('.config('maintenance.currency').')'
                    ],$length) 
                , $length)
            );

            //===================
            foreach( $request->data['items'] as $item){ 
  
                // $p->setText(
                //     $helper->EjCenterAlign(
                //         $helper->EjJustifyAlign([
                //             ''.$item['order_type'],
                //             ''
                //         ],$length) 
                //     , $length)
                // );
                
                $netamount = ($item['ordered_qty'] * $item['item']['srp']);
                 $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            $item['ordered_qty'].'x '.$item['item']['description'],
                            ''.$item['order_type']
                        ],$length) 
                    , $length) 
                ); 
                $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            '',
                            $helper->currencyFormat('', $netamount)
                        ],$length) 
                    , $length) 
                );

 
                if( isset($item['components']) ){
                    //reading components
                    foreach( $item['components'] as $components){
                         
                        if( $components['item']['quantity'] > 0){    
                            $netamount = 0;
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        '   + ('.$components['item']['quantity'].') '.$components['item']['description'],
                                        ''
                                    ],$length) 
                                , $length)
                            );
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        '',
                                        $helper->currencyFormat('', $netamount)
                                    ],$length) 
                                , $length)
                            );
                        }

                        foreach( $components[ 'selectable_items'] as $sitems){
                            if($sitems['qty'] > 0){  
                                $netamount = $sitems['qty'] * $sitems['price'];
                                $p->setText(
                                    $helper->EjCenterAlign(
                                        $helper->EjJustifyAlign([
                                            '  +('.$sitems['qty'].')'.$sitems['short_code'],
                                            ''
                                        ],$length) 
                                    , $length)
                                );
                                $p->setText(
                                    $helper->EjCenterAlign(
                                        $helper->EjJustifyAlign([
                                            '',
                                            $helper->currencyFormat('', $netamount)
                                        ],$length) 
                                    , $length)
                                );
                            }
                        }
                        
                    }
                }
                
                if( $item['instruction'] != null || $item['instruction'] != ''){
                    $p->setText(
                        $helper->EjCenterAlign(
                            $helper->EjJustifyAlign([
                                '  + '.$item['instruction'],
                                ''
                            ],$length) 
                        , $length)
                    );
                }

            }
            //===================
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    '============================='
                , $length)
            );
            $p->feed(2); 
            $p->close();

            return response()->json([
                'success'       => true,
                'message'       =>  'test',
                'data'          => $request->others,
                'data2'         => $request->items
            ]); 
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success'   => false,
                'error'     => $e->getMessage()
            ]); 
        }
     }

}
