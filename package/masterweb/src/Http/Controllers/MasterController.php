<?php

namespace Smt\Masterweb\Http\Controllers;


class MasterController extends CrudController
{


    public function type()
    {
        $set_tb['table'] = "tb_content";
        // $set_tb['display'] = ['id','views','content','menu_id','deleted_at','created_at','updated_at'];
        // //join type ,nama tabel,id = ref, colom yg di tampilkan
        // $set_tb['join']['type'] = ['ms_type','id','name'];
        // //upload, nama filed, folder
        // $set_tb['upload']['img_thumbnail'] = ['public/images/logo'];
        // $set_tb['upload']['author'] = ['public/images/logo'];
        return $this->get_data($set_tb);
    }


}
