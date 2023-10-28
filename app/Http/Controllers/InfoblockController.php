<?php

namespace App\Http\Controllers;

use App\Models\Infoblock;
use Illuminate\Http\Request;

class InfoblockController extends Controller
{
    public static function setInfoBlocks($infoblocks)
    {
        $result = [];
        $resultCode = 1;
        $message = 'something wrong with infogroups online';
      

        

        foreach ($infoblocks as $block) {

            $newBlock = Infoblock::where('code', $block['code'])->first();
            if (!$newBlock) {
                $newBlock = new Infoblock();
            }

            $newBlock['number'] = $block['number'];
          
            $newBlock['name'] = $block['name'];
            $newBlock['title'] = $block['title'];
            $newBlock['description'] = $block['description'];
            $newBlock['descriptionForSale'] = $block['descriptionForSale'];
            $newBlock['shortDescription'] = $block['shortDescription'];
            $newBlock['weight'] = $block['weight'];

            $newBlock['code'] = $block['code'];
            $newBlock['inGroupId'] = $block['inGroupId'];
            $newBlock['groupId'] = $block['groupId'];
            $newBlock['isLa'] = $block['isLa'];
            $newBlock['isFree'] = $block['isFree'];
            $newBlock['isShowing'] = $block['isShowing'];
            $newBlock['isSet'] = $block['isSet'];

            $newBlock->save();
            array_push($result, $newBlock );
        }
       
        

        if(count($result) > 0){
            $resultCode = 0;
            $message = null;
           
        }


        
        return response([
            'resultCode' => $resultCode,
            'data' => $result,
            'message' => $message
        ]);
    }
}
