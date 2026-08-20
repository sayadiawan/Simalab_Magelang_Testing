<?php
    $menu_id = SmtHelp::get_menuid();
    
    $linkmenu = SmtHelp::get_linkmenu();
    $link = request()->segment(1);
    $get_artikel = \Smt\Masterweb\Models\Content::where([['publish', '1'],['type', '1'],['menu_id',$menu_id]])->first();
    if($get_artikel == NULL)
    {
        ?>
        <div class="container">
            <div class="alert alert-danger"><p>konten belum dimasukkan!</p></div>
        </div>
        <?php
    }else{
        //update views
        $content = \Smt\Masterweb\Models\Content::findOrFail($get_artikel->id_content);
        $content->views = $get_artikel->views+1;
        $content->save();
        ?>
        <div class="container">
            <div class="col-sm-12">
                <h3 class="gotham-medium-black"><a href="#">{{ $get_artikel->title }}</a></h3>
                {!! $get_artikel->content !!}
            </div>
        </div>
        <?php
    }
?>