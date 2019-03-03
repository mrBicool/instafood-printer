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
            $p = new Printing('pos-58');
            $helper = new Helper;

            $length = 58;



            //===================
            foreach( $request->items as $item){ 
  
                Log::debug(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            ''.$item['order_type'],
                            ''
                        ],$length - 4) 
                    , $length)
                );
                
                $netamount = ($item['ordered_qty'] * $item['item']['srp']);
                Log::debug(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            $item['ordered_qty'].'x '.$item['item']['description'],
                            $helper->currencyFormat('Php', $netamount)
                        ],$length - 4) 
                    , $length)
                );

 
                if( isset($item['components']) ){
                    //reading components
                    foreach( $item['components'] as $components){
                         
                        if( $components['item']['quantity'] > 0){    
                            $netamount = 0;
                            Log::debug(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        '   + ('.$components['item']['quantity'].') '.$components['item']['description'],
                                        $helper->currencyFormat('Php', $netamount)
                                    ],$length - 4) 
                                , $length)
                            );
                        }

                        foreach( $components[ 'selectable_items'] as $sitems){
                            if($sitems['qty'] > 0){  
                                $netamount = $sitems['qty'] * $sitems['price'];
                                Log::debug(
                                    $helper->EjCenterAlign(
                                        $helper->EjJustifyAlign([
                                            '   + ('.$sitems['qty'].') '.$sitems['short_code'],
                                            $helper->currencyFormat('Php', $netamount)
                                        ],$length - 4) 
                                    , $length)
                                );
                            }
                        }
                        
                    }
                }
                
                if( $item['instruction'] != null || $item['instruction'] != ''){
                    Log::debug(
                        $helper->EjCenterAlign(
                            $helper->EjJustifyAlign([
                                '   + '.$item['instruction'],
                                ''
                            ],$length - 4) 
                        , $length)
                    );
                }

            }
            //===================


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
