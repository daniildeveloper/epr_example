<?php

namespace App\ParsingHelpers;

use Illuminate\Support\Facades\DB;

class ProposalHelper
{
    /**
     * Store proposal data
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function store($data)
    {
        $proposal_id = DB::table('proposals')->insertGetId([
            [
                'created_at'       => $data['created_at'],
                'updated_at'       => $data['created_at'],
                'client'           => $data['client'],
                'client_phone'     => $data['phone'],
                'object'           => $data['object'],
                'is_with_docs'     => $data['is_with_docs'],
                'tax'              => $data['tax'],
                'client_deadline'  => $data['created_at'],
                'workers_deadline' => $data['created_at'],
                'partner_payment'  => $data['partner_payment'],
                'partner_notes'    => $data['partner_notes'],
            ],
        ]);

        // foreach ($data['wares'] as $ware) {
        //     DB::table('proposal_wares')->insert([
        //         'proposal_id'     => $proposal_id,
        //         'ware_id'         => $ware['id'],
        //         'price_per_count' => $ware['price_per_count'],
        //         'count'           => $ware['count'],
        //         'color'           => $ware['color'],
        //         'color_price'     => $wre['color_price'],
        //         'created_at'      => $data['created_at'],
        //         'updated_at'      => $data['created_at'],
        //     ]);
        // }

        return $proposal_id;
    }
}
