<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log; 
use App\AppServices\Helper;
 
use App\AppServices\Printing;
use Carbon\Carbon;
use function GuzzleHttp\json_encode;

class PrintController extends Controller
{
    // 

     public function salesOrder(Request $request){  

        try {
            
            $printer_name = config( 'maintenance.printer_name');
            $printer_width= config( 'maintenance.printer_width');

            $p = new Printing($printer_name);
            $helper = new Helper;  
            $length = $printer_width; 

            $p->setTitleHeader( 
                'Datalogic Systems Corp.' 
            );

            /**
             * QR Code for OS no.
             */
            $p->setQrCode(''.$request->os_number);

            /**
             * ORDER SLIP NO.
             */
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    'Order slip no. : '.$request->os_number 
                , $length)
            );   
            
            /**
             * SERVER NAME
             */
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Server:',
                        ''.$request->server_name
                    ],$length - 16) 
                , $length)
            );

            /**
             * DATE CREATED
             */
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Date&Time:',
                        ''.$request->created_at
                    ],$length - 16)
                , $length)
            );

            /**
             * CURRENCY
             */
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Currency:',
                        '('.config('maintenance.currency').')'
                    ] , $length - 16) 
                 , $length)
            );

            /**
             * Customer Information
             */
             $p->feed();
            if($request->data['others']['mobile_number'] != null){
               
                $p->setText(
                    $helper->EjCenterAlign(
                        '--- Customer Information ---' 
                    , $length)
                );

                $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            'Name',
                            ''.$request->data['others']['customer_name']
                        ],$length-16) 
                    , $length)
                );

                $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            'Mobile No.',
                            ''.$request->data['others']['mobile_number']
                        ],$length-16) 
                    , $length)
                );
            }

            /**
             * Head Count
             */
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->charDuplicator('-', 32)
                , $length)
            );
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'HeadCount :',
                        ''.$request->data['others']['headcounts']['regular']
                    ],$length - 16) 
                 , $length)
            );
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->charDuplicator('-', 32)
                , $length)
            );

            /**
             * ITEMs INITIALIZATION
             */
            $itemCollections    = collect( $request->data['items']);
            $dine_in            = $itemCollections->where('order_type','dine-in');
            $take_out           = $itemCollections->where('order_type','take-out'); 
            $sub_total          = 0;

            /**
             * DINE IN
             */
            if( $dine_in->count() > 0){
                $p->feed();
                $p->setText(
                    $helper->EjCenterAlign(
                    '---- Dine In ----' 
                    , $length)
                );
            }
            foreach ($dine_in as $key => $item) {

                $sub_total += $item['net_amount'];

                # code...  
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
                            ''.$helper->currencyFormat('', $netamount)
                        ],$length) 
                    , $length) 
                ); 
                // $p->setText(
                //     $helper->EjCenterAlign(
                //         $helper->EjJustifyAlign([
                //             '',
                //             $helper->currencyFormat('', $netamount)
                //         ],$length) 
                //     , $length) 
                // );

 
                if( isset($item['components']) ){
                    //reading components
                    foreach( $item['components'] as $components){
                         
                        if( $components['item']['quantity'] > 0){    
                            $netamount = 0;
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        '  + ('.$components['item']['quantity'].')'.$components['item']['description'],
                                        ''.$helper->currencyFormat('', $netamount)
                                    ],$length) 
                                , $length)
                            );
                            // $p->setText(
                            //     $helper->EjCenterAlign(
                            //         $helper->EjJustifyAlign([
                            //             '',
                            //             $helper->currencyFormat('', $netamount)
                            //         ],$length) 
                            //     , $length)
                            // );
                        }

                        foreach( $components[ 'selectable_items'] as $sitems){
                            if($sitems['qty'] > 0){  
                                $netamount = $sitems['qty'] * $sitems['price'];
                                $p->setText(
                                    $helper->EjCenterAlign(
                                        $helper->EjJustifyAlign([
                                            '  + ('.$sitems['qty'].')'.$sitems['short_code'],
                                            ''.$helper->currencyFormat('', $netamount)
                                        ],$length) 
                                    , $length)
                                );
                                // $p->setText(
                                //     $helper->EjCenterAlign(
                                //         $helper->EjJustifyAlign([
                                //             '',
                                //             $helper->currencyFormat('', $netamount)
                                //         ],$length) 
                                //     , $length)
                                // );
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

            /**
             * TAKE OUT
             */
            if( $take_out->count() > 0){
                $p->feed();
                $p->setText(
                    $helper->EjCenterAlign(
                    '---- Take Out ----' 
                    , $length)
                );
            }
            foreach ($take_out as $key => $item){

                $sub_total += $item['net_amount'];

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
                            $item['ordered_qty'] . 'x ' . $item['item']['description'],
                            ''.$helper->currencyFormat('', $netamount)
                        ], $length),
                        $length
                    )
                );
                // $p->setText(
                //     $helper->EjCenterAlign(
                //         $helper->EjJustifyAlign([
                //             '',
                //             $helper->currencyFormat('', $netamount)
                //         ], $length),
                //         $length
                //     )
                // );


                if (isset($item['components'])) {
                    //reading components
                    foreach ($item['components'] as $components) {

                        if ($components['item']['quantity'] > 0) {
                            $netamount = 0;
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        '  + (' . $components['item']['quantity'] . ')' . $components['item']['description'],
                                        '' . $helper->currencyFormat('', $netamount)
                                    ], $length),
                                    $length
                                )
                            );
                            // $p->setText(
                            //     $helper->EjCenterAlign(
                            //         $helper->EjJustifyAlign([
                            //             '',
                            //             $helper->currencyFormat('', $netamount)
                            //         ],$length) 
                            //     , $length)
                            // );
                        }

                        foreach ($components['selectable_items'] as $sitems) {
                            if ($sitems['qty'] > 0) {
                                $netamount = $sitems['qty'] * $sitems['price'];
                                $p->setText(
                                    $helper->EjCenterAlign(
                                        $helper->EjJustifyAlign([
                                            '  + (' . $sitems['qty'] . ')' . $sitems['short_code'],
                                            '' . $helper->currencyFormat('', $netamount)
                                        ], $length),
                                        $length
                                    )
                                );
                                // $p->setText(
                                //     $helper->EjCenterAlign(
                                //         $helper->EjJustifyAlign([
                                //             '',
                                //             $helper->currencyFormat('', $netamount)
                                //         ],$length) 
                                //     , $length)
                                // );
                            }
                        }
                    }
                }

                if ($item['instruction'] != null || $item['instruction'] != '') {
                    $p->setText(
                        $helper->EjCenterAlign(
                            $helper->EjJustifyAlign([
                                '  + ' . $item['instruction'],
                                ''
                            ], $length),
                            $length
                        )
                    );
                }
            } 

            /**
             * Sub Total
             */ 
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Sub Total :',
                        ''.$helper->currencyFormat('', $sub_total)
                    ],$length - 16) 
                , $length)
            );

            $p->feed(2);
            $p->setText(
                $helper->EjCenterAlign(
                '!!! THANK YOU !!!' 
                , $length)
            );
            $p->feed(); 

            $p->setText(
                $helper->EjCenterAlign(
                    $helper->charDuplicator('=', 32)
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

     public function orderSlip(Request $request){
        try { 
            $printer_name = config( 'maintenance.printer_name');
            $printer_width= config( 'maintenance.printer_width');

            $p = new Printing($printer_name);
            $helper = new Helper;  
            $length = $printer_width;

            $header     = (object)$request->os['header'];
            $details    = (object)$request->os['details'];  

            $p->setTitleHeader( 
                $request->header 
            );

            /**
             * QR Code for OS no.
             */
            //$p->setQrCode(''.$header->orderslip_header_id);

            /**
             * ORDER SLIP NO.
             */
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    'Order slip no. : '.$header->orderslip_header_id
                , $length)
            );   

            /**
             * SERVER NAME
             */
            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Server :',
                        ''.$request->server_info['name']
                    ],$length - 2) 
                , $length)
            );

            /**
             * DATE CREATED
             */
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Date&Time :',
                        ''.now()
                    ],$length - 2)
                , $length)
            );

            /**
             * CURRENCY
             */
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Currency :',
                        '('.$request->currency.')'
                    ] , $length - 2) 
                , $length)
            );


            /**
             * Customer Information
             */
            //$p->feed(); 
            if($header->mobile_number != null){
           
                $p->setText(
                    $helper->EjCenterAlign(
                        '--- Customer Information ---' 
                    , $length)
                );
    
                $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            'Name',
                            ''.$header->customer_name
                        ],$length-2) 
                    , $length)
                );
    
                $p->setText(
                    $helper->EjCenterAlign(
                        $helper->EjJustifyAlign([
                            'Mobile No.',
                            ''.$header->mobile_number
                        ],$length-2) 
                    , $length)
                );
            }

            /**
             * Head Count
             */
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->charDuplicator('-', 28)
                , $length)
            );
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        "No. of Guest(s) :",
                        ''.$header->total_hc
                    ],$length - 6)
                , $length)
            );
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        "Table No. :",
                        ''.$header->total_hc
                    ],$length - 6)
                , $length)
            );
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->charDuplicator('-', 28)
                , $length)
            );

            /**
             * ITEMs INITIALIZATION
             */
            $itemCollections    = collect( $details );
            $sub_total = 0;

            // dont know what to do here haha
            $dine_in    = $this->returnFilteredByOrderType($itemCollections,1);
            $dine_in    = collect($dine_in)->groupBy(['main_product_id','sequence']);

            $take_out   = $this->returnFilteredByOrderType($itemCollections,2);
            $take_out   = collect($take_out)->groupBy(['main_product_id','sequence']);

            /**
             * DINE IN
             */
            if( $dine_in->count() > 0){
                //$p->feed();
                $p->setText(
                    $helper->EjCenterAlign(
                    '---- Dine In ----' 
                    , $length)
                );
            }

            $remarks = '';
            $guest_no = '';
            $guest_type = '';

            foreach($dine_in as $__item){
                foreach($__item as $_item){
                     foreach($_item as $item){
                         if($item->product_id == $item->main_product_id){  
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        $item->qty.'x '.$item->name,
                                        ''.$helper->currencyFormat('', $item->amount)
                                    ],$length) 
                                , $length) 
                            ); 
                            
                            // $p->setText(
                            //     $helper->EjCenterAlign(
                            //         $helper->EjJustifyAlign([
                            //             '',
                            //             ''.$helper->currencyFormat('', $item->amount)
                            //         ],$length)
                            //     , $length)
                            // );

                            $remarks = $item->remarks;
                            $guest_no = $item->guest_no;
                            $sub_total += $item->amount;
                         }else{
                            $netamount = $item->qty * $item->srp;
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        '  + ('.$item->qty.')'.$item->name,
                                        ''.$helper->currencyFormat('', $item->amount)
                                    ],$length) 
                                , $length)
                            );

                            // $p->setText(
                            //     $helper->EjCenterAlign(
                            //         $helper->EjJustifyAlign([
                            //             '',
                            //             ''.$helper->currencyFormat('', $item->amount)
                            //         ],$length)
                            //     , $length)
                            // );

                            $sub_total += $netamount;
                         } 
                     }

                    if($remarks != null || $remarks != ''){
                        $p->setText(
                            $helper->EjCenterAlign(
                                $helper->EjJustifyAlign([
                                    '  + '.$remarks,
                                    ''
                                ],$length) 
                            , $length)
                        );
                    }

                    $p->setText(
                        $helper->EjCenterAlign(
                            $helper->EjJustifyAlign([
                                '  + Guest No.('.$guest_no.')',
                                ''
                            ],$length) 
                        , $length)
                    );




                }
            }

            // if($remarks != null || $remarks != ''){
            //     $p->setText(
            //         $helper->EjCenterAlign(
            //             $helper->EjJustifyAlign([
            //                 '  + '.$remarks,
            //                 ''
            //             ],$length) 
            //         , $length)
            //     );
            // }

            /**
             * TAKE OUT
             */
            if( $take_out->count() > 0){
                //$p->feed();
                $p->setText(
                    $helper->EjCenterAlign(
                    '---- Take Out ----' 
                    , $length)
                );
            }

            $remarks = '';
            foreach($take_out as $__item){
                foreach($__item as $_item){
                     foreach($_item as $item){
                         if($item->product_id == $item->main_product_id){ 
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        $item->qty.'x '.$item->name,
                                        ''.$helper->currencyFormat('', $item->amount)
                                    ],$length) 
                                , $length) 
                            ); 

                            // $p->setText(
                            //     $helper->EjCenterAlign(
                            //         $helper->EjJustifyAlign([
                            //             '',
                            //             ''.$helper->currencyFormat('', $item->amount)
                            //         ],$length)
                            //     , $length)
                            // );


                            $remarks = $item->remarks;
                            $sub_total += $item->amount;
                         }else{
                            $netamount = $item->qty * $item->srp;
                            $p->setText(
                                $helper->EjCenterAlign(
                                    $helper->EjJustifyAlign([
                                        '  + ('.$item->qty.')'.$item->name,
                                        ''.$helper->currencyFormat('', $netamount)
                                    ],$length) 
                                , $length)
                            );

                            // $p->setText(
                            //     $helper->EjCenterAlign(
                            //         $helper->EjJustifyAlign([
                            //             '',
                            //             ''.$helper->currencyFormat('', $netamount)
                            //         ],$length)
                            //     , $length)
                            // );

                            $sub_total += $netamount;
                         } 
                     }

                     if($remarks != null || $remarks != ''){
                        $p->setText(
                            $helper->EjCenterAlign(
                                $helper->EjJustifyAlign([
                                    '  + '.$remarks,
                                    ''
                                ],$length) 
                            , $length)
                        );
                    }
                }
            }

            // if($remarks != null || $remarks != ''){
            //     $p->setText(
            //         $helper->EjCenterAlign(
            //             $helper->EjJustifyAlign([
            //                 '  + '.$remarks,
            //                 ''
            //             ],$length) 
            //         , $length)
            //     );
            // }
             
            /**
             * Sub Total
             */ 
           // $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->charDuplicator('-', 16)
                , $length)
            );
            $p->setText(
                $helper->EjCenterAlign(
                    $helper->EjJustifyAlign([
                        'Sub Total :',
                        ''.$helper->currencyFormat('', $sub_total)
                    ],$length - 2) 
                , $length)
            );
            

            $p->feed();
            $p->setText(
                $helper->EjCenterAlign(
                '!!! THANK YOU !!!' 
                , $length)
            );
            //$p->feed(); 

            $p->setText(
                $helper->EjCenterAlign(
                    $helper->charDuplicator('=', 16)
                , $length)
            );

            $p->feed();
            $p->cut();
            $p->close();
            return json_encode($request->all());
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success'   => false,
                'error'     => $e->getMessage()
            ]); 
        }
         
     }

     private function returnFilteredByOrderType($items, $type){
        $newItems = []; 
        foreach($items as $__item){
            foreach($__item as $_item){
                 foreach($_item as $item){
                    $obj = (object)$item; 
                    if($obj->order_type == $type){
                        array_push($newItems, $obj);
                    }
                 }
            }
        } 
        return $newItems;
     }
}
