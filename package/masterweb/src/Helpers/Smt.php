<?php

namespace Smt\Masterweb\Helpers;

use FontLib\Table\Type\loca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exports\EsignBsre as ExportsEsignBsre;
use Request;

class Smt
{

  //generator helper
  public static function set_input_crud($val_type, $val_name, $value = null, $relation = null)
  {
    //check kalo upload
    if ($val_type == "upload") {
      $ar =  '<input type="file" class="form-control dropify" name="' . $val_name . '" id="' . $val_name . '">';
      return $ar;
    }

    if (isset($relation[$val_name])) {
      $get_relation = $relation[$val_name];
      $data_relation = DB::table($get_relation[0])->whereNull('deleted_at')->get(); //get tabel


      $ar =   '<select name="' . $val_name . '" id="' . $val_name . '" class="form-control selected2">
                        <option value="">Pilih</option>';
      foreach ($data_relation as $item) {
        $id_relation = $get_relation[1];
        $val_relation = $get_relation[2];

        if ($item->$id_relation == $value) {
          $is_selected = "selected";
        } else {
          $is_selected = null;
        }


        $ar .= '<option value="' . $item->$id_relation . '" ' . $is_selected . '>' . $item->$val_relation . '</option>';
      }
      $ar .=  '</select>';
    } elseif ($val_type == "\BigInt") {
      $ar =  '<input type="number" class="form-control" name="' . $val_name . '" value="' . $value . '" id="' . $val_name . '" required>';
    } elseif ($val_type == "\String") {
      $ar = '<input type="text" class="form-control" name="' . $val_name . '" value="' . $value . '" id="' . $val_name . '" required>';
    } elseif ($val_type == "\Text") {
      $ar = '<textarea class="form-control" name="' . $val_name . '" id="' . $val_name . '" cols="30" rows="10" required>' . $value . '</textarea>';
    } elseif ($val_type == "\Date") {
      $ar = '<input type="date" class="form-control" name="' . $val_name . '" id="' . $val_name . '" value="' . $value . '" required>';
    } elseif ($val_type == "\Boolean") {
      //$cekhed = null;
      if ($value == "1") {
        $cekhed = "checked";
      } else {
        $cekhed = null;
      }


      $ar = '<div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" name="' . $val_name . '" id="' . $val_name . '" value="1" class="form-check-input" ' . $cekhed . '>
                            Aktif
                            <i class="input-helper"></i>
                        </label>
                    </div>';
    } else {
      $ar = "belum terdefinisi " . $val_type;
    }

    return $ar;
  }
  //end generator helper

  public static function create_link($url)
  {
    $url = strip_tags($url);
    $url = str_replace(" ", "-", $url);
    $url = str_replace("!", "", $url);
    $url = str_replace("@", "", $url);
    $url = str_replace("#", "", $url);
    $url = str_replace("$", "", $url);
    $url = str_replace("%", "", $url);
    $url = str_replace("^", "", $url);
    $url = str_replace("&", "", $url);
    $url = str_replace("*", "", $url);
    $url = str_replace("(", "", $url);
    $url = str_replace(")", "", $url);
    $url = str_replace("_", "", $url);
    $url = str_replace("+", "", $url);
    $url = str_replace("=", "", $url);
    $url = str_replace("{", "", $url);
    $url = str_replace("}", "", $url);
    $url = str_replace("[", "", $url);
    $url = str_replace("]", "", $url);
    $url = str_replace("|", "", $url);
    $url = str_replace('"', "", $url);
    $url = str_replace(";", "", $url);
    $url = str_replace(">", "", $url);
    $url = str_replace('<', "", $url);
    $url = str_replace("?", "", $url);
    $url = str_replace("/", "", $url);
    $url = str_replace('~', "", $url);
    $url = str_replace("`", "", $url);
    $url = str_replace(".", "", $url);
    $url = str_replace(",", "", $url);
    $url = str_replace(":", "", $url);
    $url = str_replace("'", "", $url);
    $url = addslashes($url);
    $url = strtolower($url);

    return $url;
  }

  //function fbulan
  public static function fbulan($bulan)
  {
    if ($bulan == "01") {
      $bln = "Januari";
    } else if ($bulan == "02") {
      $bln = "Februari";
    } else if ($bulan == "03") {
      $bln = "Maret";
    } else if ($bulan == "04") {
      $bln = "April";
    } else if ($bulan == "05") {
      $bln = "Mei";
    } else if ($bulan == "06") {
      $bln = "Juni";
    } else if ($bulan == "07") {
      $bln = "Juli";
    } else if ($bulan == "08") {
      $bln = "Agustus";
    } else if ($bulan == "09") {
      $bln = "September";
    } else if ($bulan == "10") {
      $bln = "Oktober";
    } else if ($bulan == "11") {
      $bln = "November";
    } else if ($bulan == "12") {
      $bln = "Desember";
    } else {
      $bln = "";
    }
    return $bln;
  }

  //function fdate
  public static function fdate($value, $format)
  {
    if ($value != "") {
      list($thn, $bln, $tgl) = explode("-", $value);

      switch ($format) {
        case "DDMMYYYY":
          $return = $tgl . " " . Smt::fbulan($bln) . " " . $thn;
          break;
          //new case
        case "DDMM":
          $return = $tgl . " " . Smt::fbulan($bln);
          break;
        case "DD":
          $return = $tgl;
          break;
        case "MM":
          $return = $bln;
          break;
        case "YYYYY":
          $return = $thn;
          break;
        case "mm":
          $return = Smt::fbulan($bln);
          break;
        case "HHDDMMYYYY":
          $jam = explode(" ", $value)[1];
          $tgl = explode(" ", $tgl)[0];
          list($H, $M, $S) = explode(":", $jam);
          $return = $tgl . " " . Smt::fbulan($bln) . " " . $thn . " | " . $H . ":" . $M;
          break;
      }
    } else {
      $return = "";
    }
    return $return;
  }

  public static function get_num_phone($nohp)
  {
    // kadang ada penulisan no hp 0811 239 345
    $nohp = str_replace(" ", "", $nohp);
    // kadang ada penulisan no hp (0274) 778787
    $nohp = str_replace("(", "", $nohp);
    // kadang ada penulisan no hp (0274) 778787
    $nohp = str_replace(")", "", $nohp);
    // kadang ada penulisan no hp 0811.239.345
    $nohp = str_replace(".", "", $nohp);

    // cek apakah no hp mengandung karakter + dan 0-9
    if (!preg_match('/[^+0-9]/', trim($nohp))) {
      // cek apakah no hp karakter 1-3 adalah +62
      if (substr(trim($nohp), 0, 3) == '+62') {
        $hp = trim($nohp);
      }
      // cek apakah no hp karakter 1 adalah 0
      elseif (substr(trim($nohp), 0, 1) == '0') {
        $hp = '+62' . substr(trim($nohp), 1);
      }
    }
    return $hp;
  }

  public static function get_img($news_content = NULL)
  {


    $dom = new \DOMDocument();





    if ($news_content == "") {


      return NULL;
    }





    libxml_use_internal_errors(true);


    $dom->loadHTML($news_content);


    libxml_use_internal_errors(false);


    $img_nodes = $dom->getElementsByTagName('img');





    $img_link = NULL;


    foreach ($img_nodes as $link) {


      $img_link = $link->getAttribute('src');
      break;
    }





    return $img_link;
  }

  public static function img_empty($asset, $value)
  {
    if (empty($value)) {
      $img = asset('assets/public/images/intro/blank.png');
    } else {
      $img = asset($asset . $value);
    }
    return $img;
  }
  //get title
  public static function name_url($url_segment)
  {
    $get_name_control = DB::table('ms_menus')->where('link', '=', $url_segment)->first();
    $url = $get_name_control->name;
    return $url;
  }

  //get link controller
  public static function name_link($url_segment)
  {
    if ($url_segment == NULL) {
      $url_segment = '/';
    }
    $get_name_control = DB::table('ms_menus')->where('link', '=', $url_segment)->first();
    $url = $get_name_control->link;
    return $url;
  }

  public static function name_controller($value = null)
  {
    $get_name = DB::table('ms_menus')->where('link', '=', Request::segment(1))->where('deleted_at', NULL)->first();
    $name = $get_name->type;
    return $name;
  }

  public static function get_type($value = null)
  {
    if (Request::segment(1) == NULL) {
      return '1';
    }
    $get_name = DB::table('ms_menus')->where('link', '=', Request::segment(1))->where('deleted_at', NULL)->first();
    $name = $get_name->type;
    return $name;
  }

  public static function get_menuid($type = null)
  {
    if ($type == NULL) {
      $get_name = DB::table('ms_menus')->where('link', '=', Request::segment(1))->where('deleted_at', NULL)->first();
    } else {
      $get_name = DB::table('ms_menus')->where('type', '=', $type)->where('deleted_at', NULL)->first();
    }
    $name = $get_name->id;
    return $name;
  }

  public static function get_linkmenu($type = null)
  {
    if ($type == NULL) {
      $get_name_control = DB::table('ms_menus')->where('link', '=', Request::segment(1))->first();
    } else {
      $get_name_control = DB::table('ms_menus')->where('type', '=', $type)->first();
    }
    $url = $get_name_control->link;
    return $url;
  }

  public static function get_linkname()
  {
    $url_segment = Request::segment(1);
    if ($url_segment == NULL) {
      $url_segment = '';
    }
    $get_name_control = DB::table('ms_menus')->where('link', '=', $url_segment)->where('deleted_at', NULL)->first();
    $url = $get_name_control->name;
    return $url;
  }

  public static function getLayout($type, $module)
  {
    if ($type == "1") {
?>
Lorem, ipsum dolor sit amet consectetur adipisicing elit. Temporibus, commodi aut! Aperiam, alias? Cumque omnis
quibusdam nostrum maiores ipsum, quasi officia inventore doloremque accusamus quis doloribus sit quos quae dolorem?
<?php
    }
  }

  public static function GetLayoutModule($column, $modules)
  {
    return view('masterweb::module.admin.layoutmodule.columns', compact('column', 'modules'));
  }

  public static function GetLayoutModulePublic($column, $modules)
  {
    return view('masterweb::module.admin.layoutmodule.column_modules', compact('column', 'modules'));
  }

  public static function getModule($module)
  {
    $getModule = DB::table('ms_module')->where('id', '=', $module)->first();
    ?>
<div class="card rounded border mb-2">
  <div class="card-body p-3 moduleId" data-id="<?= $module ?>">
    <div class="media">
      <i class="fa fa-news icon-sm text-primary align-self-center mr-3"></i>
      <div class="media-body">
        <h6 class="mb-1"><?= $getModule->name ?></h6>
        <p class="mb-0 text-muted">
          <?= $getModule->module ?>
        </p>
      </div>
    </div>
  </div>
</div>
<?php
  }

  public static function smt_reference($kode, $value = '')
  {
    $data = array();
    switch ($kode) {
      case 'PUBLISH':
        $data = array(
          '1' => 'Aktif',
          '0' => 'Tidak Aktif'
        );
        break;

      case 'CONTENTREF':
        $data = array(
          '1' => 'Full',
          '0' => 'List'
        );
        break;

      case 'SEKS':
        $data = array(
          'L' => 'Laki-laki',
          'P' => 'Perempuan',
          'l' => 'Laki-laki',
          'p' => 'Perempuan'
        );
        break;

      case 'AGAMA':
        $data = array(
          '1' => 'Islam',
          '2' => 'Kristen Protestan',
          '3' => 'Katolik',
          '4' => 'Hindu',
          '5' => 'Budha',
          '6' => 'Konghuchu'
        );
        break;

      case 'JENJANG':
        $data = array(
          'SD' => 'SD/MI Sederajat',
          'SMP' => 'SMP/MTS Sederajat',
          'SMA' => 'SMA/SMK/MK Sederajat',
        );

      case 'TYPE_PRODUCT':
        $data = array(
          'A' => 'Alat',
          'B' => 'Bahan Habis Pakai'
        );
      case 'STATUS_SAMPLE':
        $data = array(
          '0' => 'Menunggu',
          'A' => 'Permintaan Pemeriksaan',
          'B' => 'Persiapan Sampel',
          'C' => 'Pengambilan Sampel	',
          'D' => 'Penerimaan Sampel',
          'E' => 'Penanganan Sampel',
          'F' => 'Persiapan Reagen',
          'G' => 'Pipetase / Inokulasi',
          'H' => 'Preparasi',
          'I' => 'Inkubasi',
          'J' => 'Pemeriksaan Alat',
          'K' => 'Baca Hasil',
          'L' => 'Pelaporan Hasil',
          'M' => 'Pengetikan Hasil',
          'N' => 'Verifikasi Hasil',
          'O' => 'Pengesahan Hasil',
        );
    }
    if ($value == '') {
      return $data;
    } else {
      return $data[$value];
    }
  }

  public static function isSelected($a, $b)
  {
    if ($a == $b) {
      return "selected";
    }
  }

  function cekValue($form_result, $params, $num)
  {
    if (isset($form_result[$num][$params])) {
      if ($params == "date_test") {
        return Carbon\Carbon::createFromFormat('Y-m-d H:i:s',  $form_result[$num][$params])->format('d/m/Y');
      } else {
        return $form_result[$num][$params];
      }
    } else {
      if ($params == "date_test") {
        return Carbon\Carbon::now()->format('d/m/Y');
      } else {
        return '';
      }
    }
  }

  public static function Info_umum($value)
  {
    # code...
    if ($value == 1) {
      return "Search Engine";
    } elseif ($value == 2) {
      return "Mailing Partner";
    } elseif ($value == 3) {
      return "News Letter";
    } elseif ($value == 4) {
      return "Facebook";
    } else {
      return "Twitter";
    }
  }

  public static function getAction($action)
  {
    $user = Auth()->user();
    $level = $user->level;


    $role = \Smt\Masterweb\Models\Role::where(['privilege_id' => $level])
      ->join('ms_menuadm', function ($join) {
        $join->on('tb_role.menu_id', '=', 'ms_menuadm.id')
          ->where('ms_menuadm.link', '=',  "/" . Request::segment(1));
      })
      ->first();

    if ($role[$action]) {
      return true;
    } else {
      return false;
    }


    // return view('masterweb::module.admin.laboratorium.coba',compact('role'));
    // return Excel::download(new UsersExport, 'users.xlsx');
  }

  public static function template_email($url, $nama_member, $status_member, $opt)
  {
    $html = '

        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><!--[if IE]><html xmlns="http://www.w3.org/1999/xhtml" class="ie"><![endif]--><!--[if !IE]><!--><html style="margin: 0;padding: 0;" xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]--><head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title></title>
            <!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge" /><!--<![endif]-->
            <meta name="viewport" content="width=device-width" /><style type="text/css">
            @media only screen and (min-width: 620px){.wrapper{min-width:600px !important}.wrapper h1{}.wrapper h1{font-size:26px !important;line-height:34px !important}.wrapper h2{}.wrapper h2{font-size:20px !important;line-height:28px !important}.wrapper h3{}.column{}.wrapper .size-8{font-size:8px !important;line-height:14px !important}.wrapper .size-9{font-size:9px !important;line-height:16px !important}.wrapper .size-10{font-size:10px !important;line-height:18px !important}.wrapper .size-11{font-size:11px !important;line-height:19px !important}.wrapper .size-12{font-size:12px !important;line-height:19px !important}.wrapper .size-13{font-size:13px !important;line-height:21px !important}.wrapper .size-14{font-size:14px !important;line-height:21px !important}.wrapper .size-15{font-size:15px !important;line-height:23px !important}.wrapper .size-16{font-size:16px !important;line-height:24px
            !important}.wrapper .size-17{font-size:17px !important;line-height:26px !important}.wrapper .size-18{font-size:18px !important;line-height:26px !important}.wrapper .size-20{font-size:20px !important;line-height:28px !important}.wrapper .size-22{font-size:22px !important;line-height:31px !important}.wrapper .size-24{font-size:24px !important;line-height:32px !important}.wrapper .size-26{font-size:26px !important;line-height:34px !important}.wrapper .size-28{font-size:28px !important;line-height:36px !important}.wrapper .size-30{font-size:30px !important;line-height:38px !important}.wrapper .size-32{font-size:32px !important;line-height:40px !important}.wrapper .size-34{font-size:34px !important;line-height:43px !important}.wrapper .size-36{font-size:36px !important;line-height:43px !important}.wrapper .size-40{font-size:40px !important;line-height:47px !important}.wrapper
            .size-44{font-size:44px !important;line-height:50px !important}.wrapper .size-48{font-size:48px !important;line-height:54px !important}.wrapper .size-56{font-size:56px !important;line-height:60px !important}.wrapper .size-64{font-size:64px !important;line-height:63px !important}}
            </style>
                <meta name="x-apple-disable-message-reformatting" />
                <style type="text/css">
            body {
            margin: 0;
            padding: 0;
            }
            table {
            border-collapse: collapse;
            table-layout: fixed;
            }
            * {
            line-height: inherit;
            }
            [x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            }
            .wrapper .footer__share-button a:hover,
            .wrapper .footer__share-button a:focus {
            color: #ffffff !important;
            }
            .btn a:hover,
            .btn a:focus,
            .footer__share-button a:hover,
            .footer__share-button a:focus,
            .email-footer__links a:hover,
            .email-footer__links a:focus {
            opacity: 0.8;
            }
            .preheader,
            .header,
            .layout,
            .column {
            transition: width 0.25s ease-in-out, max-width 0.25s ease-in-out;
            }
            .preheader td {
            padding-bottom: 8px;
            }
            .layout,
            div.header {
            max-width: 400px !important;
            -fallback-width: 95% !important;
            width: calc(100% - 20px) !important;
            }
            div.preheader {
            max-width: 360px !important;
            -fallback-width: 90% !important;
            width: calc(100% - 60px) !important;
            }
            .snippet,
            .webversion {
            Float: none !important;
            }
            .stack .column {
            max-width: 400px !important;
            width: 100% !important;
            }
            .fixed-width.has-border {
            max-width: 402px !important;
            }
            .fixed-width.has-border .layout__inner {
            box-sizing: border-box;
            }
            .snippet,
            .webversion {
            width: 50% !important;
            }
            .ie .btn {
            width: 100%;
            }
            .ie .stack .column,
            .ie .stack .gutter {
            display: table-cell;
            float: none !important;
            }
            .ie div.preheader,
            .ie .email-footer {
            max-width: 560px !important;
            width: 560px !important;
            }
            .ie .snippet,
            .ie .webversion {
            width: 280px !important;
            }
            .ie div.header,
            .ie .layout {
            max-width: 600px !important;
            width: 600px !important;
            }
            .ie .two-col .column {
            max-width: 300px !important;
            width: 300px !important;
            }
            .ie .three-col .column,
            .ie .narrow {
            max-width: 200px !important;
            width: 200px !important;
            }
            .ie .wide {
            width: 400px !important;
            }
            .ie .stack.fixed-width.has-border,
            .ie .stack.has-gutter.has-border {
            max-width: 602px !important;
            width: 602px !important;
            }
            .ie .stack.two-col.has-gutter .column {
            max-width: 290px !important;
            width: 290px !important;
            }
            .ie .stack.three-col.has-gutter .column,
            .ie .stack.has-gutter .narrow {
            max-width: 188px !important;
            width: 188px !important;
            }
            .ie .stack.has-gutter .wide {
            max-width: 394px !important;
            width: 394px !important;
            }
            .ie .stack.two-col.has-gutter.has-border .column {
            max-width: 292px !important;
            width: 292px !important;
            }
            .ie .stack.three-col.has-gutter.has-border .column,
            .ie .stack.has-gutter.has-border .narrow {
            max-width: 190px !important;
            width: 190px !important;
            }
            .ie .stack.has-gutter.has-border .wide {
            max-width: 396px !important;
            width: 396px !important;
            }
            .ie .fixed-width .layout__inner {
            border-left: 0 none white !important;
            border-right: 0 none white !important;
            }
            .ie .layout__edges {
            display: none;
            }
            .mso .layout__edges {
            font-size: 0;
            }
            .layout-fixed-width,
            .mso .layout-full-width {
            background-color: #ffffff;
            }
            @media only screen and (min-width: 620px) {
            .column,
            .gutter {
                display: table-cell;
                Float: none !important;
                vertical-align: top;
            }
            div.preheader,
            .email-footer {
                max-width: 560px !important;
                width: 560px !important;
            }
            .snippet,
            .webversion {
                width: 280px !important;
            }
            div.header,
            .layout,
            .one-col .column {
                max-width: 600px !important;
                width: 600px !important;
            }
            .fixed-width.has-border,
            .fixed-width.x_has-border,
            .has-gutter.has-border,
            .has-gutter.x_has-border {
                max-width: 602px !important;
                width: 602px !important;
            }
            .two-col .column {
                max-width: 300px !important;
                width: 300px !important;
            }
            .three-col .column,
            .column.narrow,
            .column.x_narrow {
                max-width: 200px !important;
                width: 200px !important;
            }
            .column.wide,
            .column.x_wide {
                width: 400px !important;
            }
            .two-col.has-gutter .column,
            .two-col.x_has-gutter .column {
                max-width: 290px !important;
                width: 290px !important;
            }
            .three-col.has-gutter .column,
            .three-col.x_has-gutter .column,
            .has-gutter .narrow {
                max-width: 188px !important;
                width: 188px !important;
            }
            .has-gutter .wide {
                max-width: 394px !important;
                width: 394px !important;
            }
            .two-col.has-gutter.has-border .column,
            .two-col.x_has-gutter.x_has-border .column {
                max-width: 292px !important;
                width: 292px !important;
            }
            .three-col.has-gutter.has-border .column,
            .three-col.x_has-gutter.x_has-border .column,
            .has-gutter.has-border .narrow,
            .has-gutter.x_has-border .narrow {
                max-width: 190px !important;
                width: 190px !important;
            }
            .has-gutter.has-border .wide,
            .has-gutter.x_has-border .wide {
                max-width: 396px !important;
                width: 396px !important;
            }
            }
            @supports (display: flex) {
            @media only screen and (min-width: 620px) {
                .fixed-width.has-border .layout__inner {
                display: flex !important;
                }
            }
            }
            @media only screen and (-webkit-min-device-pixel-ratio: 2), only screen and (min--moz-device-pixel-ratio: 2), only screen and (-o-min-device-pixel-ratio: 2/1), only screen and (min-device-pixel-ratio: 2), only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
            .fblike {
                background-image: url(https://i7.createsend1.com/static/eb/master/13-the-blueprint-3/images/fblike@2x.png) !important;
            }
            .tweet {
                background-image: url(https://i8.createsend1.com/static/eb/master/13-the-blueprint-3/images/tweet@2x.png) !important;
            }
            .linkedinshare {
                background-image: url(https://i9.createsend1.com/static/eb/master/13-the-blueprint-3/images/lishare@2x.png) !important;
            }
            .forwardtoafriend {
                background-image: url(https://i10.createsend1.com/static/eb/master/13-the-blueprint-3/images/forward@2x.png) !important;
            }
            }
            @media (max-width: 321px) {
            .fixed-width.has-border .layout__inner {
                border-width: 1px 0 !important;
            }
            .layout,
            .stack .column {
                min-width: 320px !important;
                width: 320px !important;
            }
            .border {
                display: none;
            }
            .has-gutter .border {
                display: table-cell;
            }
            }
            .mso div {
            border: 0 none white !important;
            }
            .mso .w560 .divider {
            Margin-left: 260px !important;
            Margin-right: 260px !important;
            }
            .mso .w360 .divider {
            Margin-left: 160px !important;
            Margin-right: 160px !important;
            }
            .mso .w260 .divider {
            Margin-left: 110px !important;
            Margin-right: 110px !important;
            }
            .mso .w160 .divider {
            Margin-left: 60px !important;
            Margin-right: 60px !important;
            }
            .mso .w354 .divider {
            Margin-left: 157px !important;
            Margin-right: 157px !important;
            }
            .mso .w250 .divider {
            Margin-left: 105px !important;
            Margin-right: 105px !important;
            }
            .mso .w148 .divider {
            Margin-left: 54px !important;
            Margin-right: 54px !important;
            }
            .mso .size-8,
            .ie .size-8 {
            font-size: 8px !important;
            line-height: 14px !important;
            }
            .mso .size-9,
            .ie .size-9 {
            font-size: 9px !important;
            line-height: 16px !important;
            }
            .mso .size-10,
            .ie .size-10 {
            font-size: 10px !important;
            line-height: 18px !important;
            }
            .mso .size-11,
            .ie .size-11 {
            font-size: 11px !important;
            line-height: 19px !important;
            }
            .mso .size-12,
            .ie .size-12 {
            font-size: 12px !important;
            line-height: 19px !important;
            }
            .mso .size-13,
            .ie .size-13 {
            font-size: 13px !important;
            line-height: 21px !important;
            }
            .mso .size-14,
            .ie .size-14 {
            font-size: 14px !important;
            line-height: 21px !important;
            }
            .mso .size-15,
            .ie .size-15 {
            font-size: 15px !important;
            line-height: 23px !important;
            }
            .mso .size-16,
            .ie .size-16 {
            font-size: 16px !important;
            line-height: 24px !important;
            }
            .mso .size-17,
            .ie .size-17 {
            font-size: 17px !important;
            line-height: 26px !important;
            }
            .mso .size-18,
            .ie .size-18 {
            font-size: 18px !important;
            line-height: 26px !important;
            }
            .mso .size-20,
            .ie .size-20 {
            font-size: 20px !important;
            line-height: 28px !important;
            }
            .mso .size-22,
            .ie .size-22 {
            font-size: 22px !important;
            line-height: 31px !important;
            }
            .mso .size-24,
            .ie .size-24 {
            font-size: 24px !important;
            line-height: 32px !important;
            }
            .mso .size-26,
            .ie .size-26 {
            font-size: 26px !important;
            line-height: 34px !important;
            }
            .mso .size-28,
            .ie .size-28 {
            font-size: 28px !important;
            line-height: 36px !important;
            }
            .mso .size-30,
            .ie .size-30 {
            font-size: 30px !important;
            line-height: 38px !important;
            }
            .mso .size-32,
            .ie .size-32 {
            font-size: 32px !important;
            line-height: 40px !important;
            }
            .mso .size-34,
            .ie .size-34 {
            font-size: 34px !important;
            line-height: 43px !important;
            }
            .mso .size-36,
            .ie .size-36 {
            font-size: 36px !important;
            line-height: 43px !important;
            }
            .mso .size-40,
            .ie .size-40 {
            font-size: 40px !important;
            line-height: 47px !important;
            }
            .mso .size-44,
            .ie .size-44 {
            font-size: 44px !important;
            line-height: 50px !important;
            }
            .mso .size-48,
            .ie .size-48 {
            font-size: 48px !important;
            line-height: 54px !important;
            }
            .mso .size-56,
            .ie .size-56 {
            font-size: 56px !important;
            line-height: 60px !important;
            }
            .mso .size-64,
            .ie .size-64 {
            font-size: 64px !important;
            line-height: 63px !important;
            }
            .btn {
                border-radius: 3px;
                box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
                color: #fff;
                display: inline-block;
                text-decoration: none;
                -webkit-text-size-adjust: none;
            }
            .btn-red,
            .btn-error {
                background-color: #e3342f;
                border-top: 10px solid #e3342f;
                border-right: 18px solid #e3342f;
                border-bottom: 10px solid #e3342f;
                border-left: 18px solid #e3342f;
            }
            </style>

            <!--[if !mso]><!--><style type="text/css">
            @import url(https://fonts.googleapis.com/css?family=Roboto:400,700,400italic,700italic);
            </style><link href="https://fonts.googleapis.com/css?family=Roboto:400,700,400italic,700italic" rel="stylesheet" type="text/css" /><!--<![endif]--><style type="text/css">
            body{background-color:#fbfbfb}.logo a:hover,.logo a:focus{color:#1e2e3b !important}.mso .layout-has-border{border-top:1px solid #c8c8c8;border-bottom:1px solid #c8c8c8}.mso .layout-has-bottom-border{border-bottom:1px solid #c8c8c8}.mso .border,.ie .border{background-color:#c8c8c8}.mso h1,.ie h1{}.mso h1,.ie h1{font-size:26px !important;line-height:34px !important}.mso h2,.ie h2{}.mso h2,.ie h2{font-size:20px !important;line-height:28px !important}.mso h3,.ie h3{}.mso .layout__inner,.ie .layout__inner{}.mso .footer__share-button p{}.mso .footer__share-button p{font-family:Georgia,serif}
            </style><meta name="robots" content="noindex,nofollow" />
            <meta property="og:title" content="My First Campaign" />
            </head>
            <!--[if mso]>
            <body class="mso">
            <![endif]-->
            <!--[if !mso]><!-->
            <body class="full-padding" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;">
            <!--<![endif]-->
                <table class="wrapper" style="border-collapse: collapse;table-layout: fixed;min-width: 320px;width: 100%;background-color: #fbfbfb;" cellpadding="0" cellspacing="0" role="presentation"><tbody><tr><td>
                <div role="banner">
                    <div class="preheader" style="Margin: 0 auto;max-width: 560px;min-width: 280px; width: 280px;width: calc(28000% - 167440px);">
                    <div style="border-collapse: collapse;display: table;width: 100%;">
                    <!--[if (mso)|(IE)]><table align="center" class="preheader" cellpadding="0" cellspacing="0" role="presentation"><tr><td style="width: 280px" valign="top"><![endif]-->
                        <div class="snippet" style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 140px; width: 140px;width: calc(14000% - 78120px);padding: 10px 0 5px 0;color: #999;font-family: Georgia,serif;">

                        </div>
                    <!--[if (mso)|(IE)]></td><td style="width: 280px" valign="top"><![endif]-->
                        <div class="webversion" style="display: table-cell;Float: left;font-size: 12px;line-height: 19px;max-width: 280px;min-width: 139px; width: 139px;width: calc(14100% - 78680px);padding: 10px 0 5px 0;text-align: right;color: #999;font-family: Georgia,serif;">
                        </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                    </div>
                    <div class="header" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);" id="emb-email-header-container">
                    <!--[if (mso)|(IE)]><table align="center" class="header" cellpadding="0" cellspacing="0" role="presentation"><tr><td style="width: 600px"><![endif]-->
                    <div class="logo emb-logo-margin-box" style="font-size: 26px;line-height: 32px;Margin-top: 6px;Margin-bottom: 20px;color: #41637e;font-family: Avenir,sans-serif;Margin-left: 20px;Margin-right: 20px;" align="center">
                    <div class="logo-center" align="center" id="emb-email-header"><img style="display: block;height: auto;width: 100%;border: 0;max-width: 254px;" src="" alt="" width="254" /></div>
                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                </div>
                <div>
                <div class="layout one-col fixed-width stack" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
                    <div class="layout__inner" style="border-collapse: collapse;display: table;width: 100%;background-color: #ffffff;">
                    <!--[if (mso)|(IE)]><table align="center" cellpadding="0" cellspacing="0" role="presentation"><tr class="layout-fixed-width" style="background-color: #ffffff;"><td style="width: 600px" class="w560"><![endif]-->
                    <div class="column" style="text-align: left;color: #565656;font-size: 14px;line-height: 21px;font-family: Georgia,serif;">

                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 24px;Margin-bottom: 24px;">
                <div style="mso-line-height-rule: exactly;mso-text-raise: 11px;vertical-align: middle;">
                    <h1 class="size-30" style="Margin-top: 0;Margin-bottom: 0;font-style: normal;font-weight: normal;color: #565656;font-size: 26px;line-height: 34px;font-family: Avenir,sans-serif;" lang="x-size-30">Hai ' . $nama_member . ' !</h1>
                    <p class="size-20" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;" lang="x-size-20"><span class="font-roboto">Terimakasih telah mendaftarkan diri di ' . $opt->title . '.</span></p>
                    <p class="size-20" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;" lang="x-size-20"><span class="font-roboto">Silahkan Klik tombol dibawah ini untuk memverifikasi akun Anda.</span></p>

                    <a href="' . $url . '" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;color:#000;  border-radius: 3px;
                    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);color: #fff;display: inline-block;text-decoration: none;-webkit-text-size-adjust: none;background-color: #e3342f;border-top: 10px solid #e3342f;border-right: 18px solid #e3342f;border-bottom: 10px solid #e3342f;border-left: 18px solid #e3342f;" target="_blank"><span class="font-roboto">  verifikasi </span></a>

                    <p class="size-20" style="Margin-top: 20px;Margin-bottom: 0;font-family: roboto,tahoma,sans-serif;font-size: 17px;line-height: 26px;" lang="x-size-20"><span class="font-roboto">Hormat kami,</span><br><br><span class="font-roboto">Tim ' . $opt->title . '.</span></p>
                </div>
                </div>

                    </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                </div>

                <div style="mso-line-height-rule: exactly;line-height: 20px;font-size: 20px;">&nbsp;</div>


                <div role="contentinfo">
                    <div class="layout email-footer stack" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
                    <div class="layout__inner" style="border-collapse: collapse;display: table;width: 100%;">
                    <!--[if (mso)|(IE)]><table align="center" cellpadding="0" cellspacing="0" role="presentation"><tr class="layout-email-footer"><td style="width: 400px;" valign="top" class="w360"><![endif]-->
                        <div class="column wide" style="text-align: left;font-size: 12px;line-height: 19px;color: #999;font-family: Georgia,serif;Float: left;max-width: 400px;min-width: 320px; width: 320px;width: calc(8000% - 47600px);">
                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 10px;Margin-bottom: 10px;">
                            <table class="email-footer__links" style="border-collapse: collapse;table-layout: fixed;" role="presentation" emb-web-links><tbody><tr role="navigation">

                            </tr></tbody></table>
                            <div style="font-size: 12px;line-height: 19px;Margin-top: 20px;font-family: roboto,tahoma,sans-serif;">
                            <div class="font-roboto">Copyright SIMKRAF &#169; 2020 All rights reserved.</div>
                            </div>
                            <div style="font-size: 12px;line-height: 19px;Margin-top: 18px;">

                            </div>
                            <!--[if mso]>&nbsp;<![endif]-->
                        </div>
                        </div>
                    <!--[if (mso)|(IE)]></td><td style="width: 200px;" valign="top" class="w160"><![endif]-->
                        <div class="column narrow" style="text-align: left;font-size: 12px;line-height: 19px;color: #999;font-family: Georgia,serif;Float: left;max-width: 320px;min-width: 200px; width: 320px;width: calc(72200px - 12000%);">
                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 10px;Margin-bottom: 10px;">

                        </div>
                        </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                    </div>
                    <div class="layout one-col email-footer" style="Margin: 0 auto;max-width: 600px;min-width: 320px; width: 320px;width: calc(28000% - 167400px);overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;">
                    <div class="layout__inner" style="border-collapse: collapse;display: table;width: 100%;">
                    <!--[if (mso)|(IE)]><table align="center" cellpadding="0" cellspacing="0" role="presentation"><tr class="layout-email-footer"><td style="width: 600px;" class="w560"><![endif]-->
                        <div class="column" style="text-align: left;font-size: 12px;line-height: 19px;color: #999;font-family: Georgia,serif;">
                        <div style="Margin-left: 20px;Margin-right: 20px;Margin-top: 10px;Margin-bottom: 10px;">
                            <div style="font-size: 12px;line-height: 19px;">

                            </div>
                        </div>
                        </div>
                    <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
                    </div>
                    </div>
                </div>
                <div style="line-height:40px;font-size:40px;">&nbsp;</div>
                </div></td></tr></tbody></table>

        </body></html>

        ';

    return $html;
  }

  public static function takeIt($module)
  {
    if (isset($module[0]) and $module[0] == "html") {
      echo $module[1];
    } else {
      $getModule = DB::table('ms_module')->where('id', '=', $module)->first();
      echo view('masterweb::' . $getModule->module);
    }
  }

  function rupiah($angka)
  {

    $hasil_rupiah = "Rp. " . number_format($angka, 0, ',', '.');
    return $hasil_rupiah;
  }

  // BSRE

  private static function createTemporaryPDF($pdf)
  {

    $temporaryPath = storage_path('app/temp/');

    if (!File::isDirectory($temporaryPath)){
      File::makeDirectory($temporaryPath, 0775, true);
    }

    $fileName = 'temporary_' . time() .'.pdf';

    $pdf->save($temporaryPath . $fileName);


    return response()->json([
        'path' => $temporaryPath . $fileName,
        'name' => $fileName
      ]);
  }

  public static function saveBase64AsPDF($base64String, $filename = null)
  {
    $filename = $filename ?? 'file_' . Str::random(10) . '.pdf';

    // Decode Base64
    $fileData = base64_decode($base64String);

    // Simpan file ke storage (misalnya ke dalam folder 'public/pdf')
    $filePath = 'pdf/' . $filename;
    Storage::disk('public')->put($filePath, $fileData);

    // Mengembalikan path file yang disimpan

    return Storage::disk('public')->path($filePath);
  }


  /**
   * @param $pdf   pdf file to be signed.
   * @param $datas array of associative arrays containing data for each signer's signature.
   *               Each associative array in $datas should include:
   *               - 'nik' (string)         : National identification number of the signer.
   *               - 'passPhrase' (string)  : Passphrase used for signing.
   *               - 'tampilan' (string)    : Display mode for the signature on the PDF.
   *               - 'reason' (string)      : Reason for signing the document.
   *               - 'location' (string)    : Geographical location where the signature is made.
   *               - 'text' (string)        : Text associated with the signature (can be used
   *                                          as annotation or display text, depending on BSRE API).
   * @return array Returns an array containing status and data result signature
   */
  public static function signBSRE($pdf, $datas)
  {
    $options = [
      'baseUrl' => config('services.bsre.base_url'),
      'username' => config('services.bsre.username'),
      'password' => config('services.bsre.password'),
    ];

    $pathPdf = self::createTemporaryPDF($pdf);
    $fileName = $pathPdf->original['path'];

    $esignBsre = new ExportsEsignBsre($options);
    $resultDataSign = null;
    $previousSignedFile = $fileName;

    foreach ($datas as $data) {
      $signData = [
        'file' => $previousSignedFile,
        'nik' => $data['nik'],
        'passphrase' => $data['passPhrase'],
        'tampilan' => $data['tampilan'],
        'reason' => $data['reason'],
        'location' => $data['location'],
        'text' => $data['text']
      ];

      try {
        $result = $esignBsre->signInvisible($signData);
        if ($result['status'] == 500){
          Log::error('bsre', [$result]);
          return $result;
        }

        $resultData = $result['file'];

        Storage::delete($previousSignedFile);
        unlink($previousSignedFile);

        $previousSignedFile = self::saveBase64AsPDF(base64_encode($resultData));

        $resultDataSign = [
          'status' => 'success',
          'data' => $result
        ];
      } catch (\Exception $exception) {
        $resultDataSign = [
          'nik' => $data['nik'],
          'status' => 'error',
          'message' => $exception->getMessage()
        ];
      }
    }

    Storage::delete($previousSignedFile);
    unlink($previousSignedFile);

    return $resultDataSign;
  }

  public static function verifySignBSRE($data): array
  {
    $options = [
      'baseUrl' => config('services.bsre.base_url'),
      'username' => config('services.bsre.username'),
      'password' => config('services.bsre.password'),
    ];

    try {
      $pathPdf = self::saveBase64AsPDF($data);
      $data = [
        'signed_file' => $pathPdf,
      ];


      $esignBsre = new ExportsEsignBsre($options);
      $data = $esignBsre->verify($data);
      Storage::delete($pathPdf);
      unlink($pathPdf);

      $result = $data;

    }catch (\Exception $exception){
      $result = [
        'status' => 'error',
        'message' => $exception->getMessage()
      ];
    }

    return $result;
  }

  public static function verifySignBSRE2($pathPdf): array
  {
    $options = [
      'baseUrl' => config('services.bsre.base_url'),
      'username' => config('services.bsre.username'),
      'password' => config('services.bsre.password'),
    ];

    try {
      $data = [
        'signed_file' => $pathPdf,
      ];


      $esignBsre = new ExportsEsignBsre($options);
      $data = $esignBsre->verify($data);
      Storage::delete($pathPdf);
      unlink($pathPdf);

      $result = $data;

    }catch (\Exception $exception){
      $result = [
        'status' => 'error',
        'message' => $exception->getMessage()
      ];
    }

    return $result;
  }

  /**
   * Format tindakan medis khusus untuk tampilan (JSON array / array / string).
   *
   * @param mixed $value
   * @param string $default
   * @return string
   */
  public static function formatTindakanMedisKhususDisplay($value, $default = '-')
  {
    if ($value === null || $value === '') {
      return $default;
    }

    if (is_array($value)) {
      $items = array_filter(array_map('trim', $value), static function ($item) {
        return $item !== '';
      });

      return !empty($items) ? implode(', ', $items) : $default;
    }

    if (is_string($value)) {
      $decoded = json_decode($value, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $items = array_filter(array_map('trim', $decoded), static function ($item) {
          return $item !== '';
        });

        return !empty($items) ? implode(', ', $items) : $default;
      }

      $trimmed = trim($value);
      return $trimmed !== '' ? $trimmed : $default;
    }

    return $default;
  }

  /**
   * Petakan daftar jenis sampel → tindakan medis khusus yang sesuai.
   * Darah/Serum/Plasma digabung menjadi "Pengambilan Darah Vena" (bukan majority terpisah),
   * agar tidak salah pilih "Pengumpulan Urin Spontan" hanya karena count Urine lebih tinggi.
   *
   * @param  array<int, string>  $jenisList
   * @return list<string>
   */
  public static function mapJenisSampelToTindakanMedis(array $jenisList): array
  {
    $tindakan = [];
    $hasBlood = false;
    $hasUrine = false;
    $hasFeses = false;
    $hasSwab = false;
    $hasOther = false;

    foreach ($jenisList as $jenis) {
      $j = strtolower(trim((string) $jenis));
      if ($j === '') {
        continue;
      }

      if (
        $j === 'darah' || $j === 'serum' || $j === 'plasma'
        || strpos($j, 'darah') !== false
        || strpos($j, 'serum') !== false
        || strpos($j, 'plasma') !== false
      ) {
        $hasBlood = true;
      } elseif ($j === 'urine' || $j === 'urin' || strpos($j, 'urin') !== false) {
        $hasUrine = true;
      } elseif (
        $j === 'feses' || strpos($j, 'feses') !== false
        || strpos($j, 'faec') !== false || strpos($j, 'feces') !== false
      ) {
        $hasFeses = true;
      } elseif ($j === 'swab' || strpos($j, 'swab') !== false) {
        $hasSwab = true;
      } else {
        $hasOther = true;
      }
    }

    // Urutan: darah dulu (prioritas klinis), lalu urine/feses/swab.
    if ($hasBlood) {
      $tindakan[] = 'Pengambilan Darah Vena';
    }
    if ($hasUrine) {
      $tindakan[] = 'Pengumpulan Urin Spontan';
    }
    if ($hasFeses) {
      $tindakan[] = 'Pengumpulan Feses Spontan';
    }
    if ($hasSwab) {
      $tindakan[] = 'Pengambilan Swab Rektal';
    }
    if ($hasOther && empty($tindakan)) {
      $tindakan[] = 'Lainnya';
    }

    return $tindakan;
  }

  /**
   * Gabungkan tindakan tersimpan dengan tindakan yang wajib ada menurut jenis sampel.
   *
   * @param  array<int, string>  $tindakanSelected
   * @param  array<int, string>  $jenisList
   * @return list<string>
   */
  public static function reconcileTindakanMedisWithJenisSampel(array $tindakanSelected, array $jenisList): array
  {
    $needed = self::mapJenisSampelToTindakanMedis($jenisList);
    if (empty($needed)) {
      return array_values(array_filter(array_map('trim', $tindakanSelected)));
    }
    if (empty($tindakanSelected)) {
      return $needed;
    }

    $merged = array_values(array_unique(array_merge(
      array_values(array_filter(array_map('trim', $tindakanSelected))),
      $needed
    )));

    // Jaga urutan standar (darah → urine → feses → swab → lainnya)
    $order = [
      'Pengambilan Darah Vena' => 1,
      'Pengumpulan Urin Spontan' => 2,
      'Pengumpulan Feses Spontan' => 3,
      'Pengambilan Swab Rektal' => 4,
      'Lainnya' => 5,
    ];
    usort($merged, static function ($a, $b) use ($order) {
      return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
    });

    return $merged;
  }

  /**
   * Menghitung tindakan medis khusus dari jenis sampel parameter.
   * Mengembalikan SEMUA tindakan yang cocok (bukan hanya majority), dipisah koma.
   *
   * @param string $id_permohonan_uji_klinik ID permohonan uji klinik
   * @param string|null $tindakan_medis_khusus_saved Tindakan medis khusus yang sudah disimpan (optional)
   * @return string Tindakan medis khusus yang direkomendasikan
   */
  public static function getTindakanMedisKhususFromParameter($id_permohonan_uji_klinik, $tindakan_medis_khusus_saved = null)
  {
    try {
      $jenisList = self::getJenisSampelFromParameter($id_permohonan_uji_klinik);
      $derived = self::mapJenisSampelToTindakanMedis(is_array($jenisList) ? $jenisList : []);

      if (!empty($tindakan_medis_khusus_saved)) {
        $savedDisplay = self::formatTindakanMedisKhususDisplay($tindakan_medis_khusus_saved, '');
        $savedList = array_values(array_filter(array_map('trim', explode(',', (string) $savedDisplay))));
        if (is_string($tindakan_medis_khusus_saved)) {
          $decoded = json_decode($tindakan_medis_khusus_saved, true);
          if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $savedList = array_values(array_filter(array_map('trim', $decoded)));
          } elseif (count($savedList) <= 1 && strpos((string) $tindakan_medis_khusus_saved, '[') !== 0) {
            $savedList = [trim((string) $tindakan_medis_khusus_saved)];
          }
        } elseif (is_array($tindakan_medis_khusus_saved)) {
          $savedList = array_values(array_filter(array_map('trim', $tindakan_medis_khusus_saved)));
        }

        // Selaraskan dengan jenis sampel (mis. jenis ada Darah tapi tindakan hanya Urine)
        $merged = self::reconcileTindakanMedisWithJenisSampel($savedList, is_array($jenisList) ? $jenisList : []);
        return self::formatTindakanMedisKhususDisplay($merged, 'Lainnya');
      }

      if (!empty($derived)) {
        return self::formatTindakanMedisKhususDisplay($derived, 'Lainnya');
      }
    } catch (\Throwable $e) {
      Log::error('Error calculating tindakan medis khusus: ' . $e->getMessage());
    }

    return 'Lainnya';
  }

  /**
   * Decode JSON map (volume_sampel / penerimaan_sampel) ke associative array.
   * Menangani double-encoded JSON, stripslashes, HTML entities, dan JSON rusak/terpotong.
   *
   * @param  mixed  $value
   * @return array<string, mixed>
   */
  public static function decodeJsonMap($value): array
  {
    if (is_array($value)) {
      return self::unwrapLegacyDataKey($value);
    }

    if (is_object($value)) {
      $asArray = json_decode(json_encode($value), true);
      return is_array($asArray) ? self::unwrapLegacyDataKey($asArray) : [];
    }

    if (!is_string($value)) {
      return [];
    }

    $raw = trim($value);
    if ($raw === '' || $raw === 'null' || $raw === '[]' || $raw === '{}') {
      return [];
    }

    $raw = trim(html_entity_decode($raw, ENT_QUOTES, 'UTF-8'));

    // 1) Coba decode apa adanya (jangan stripslashes dulu — bisa merusak escape JSON valid)
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
      return self::unwrapLegacyDataKey($decoded);
    }

    // 2) Double-encoded: hasil decode berupa string JSON
    if (is_string($decoded) && $decoded !== '') {
      $inner = json_decode($decoded, true);
      if (is_array($inner)) {
        return self::unwrapLegacyDataKey($inner);
      }
      $raw = trim($decoded);
    }

    // 3) Coba stripslashes hanya jika decode sebelumnya gagal
    $stripped = trim(stripslashes($raw));
    if ($stripped !== $raw) {
      $decoded = json_decode($stripped, true);
      if (is_array($decoded)) {
        return self::unwrapLegacyDataKey($decoded);
      }
      if (is_string($decoded) && $decoded !== '') {
        $inner = json_decode($decoded, true);
        if (is_array($inner)) {
          return self::unwrapLegacyDataKey($inner);
        }
        $raw = trim($decoded);
      } else {
        $raw = $stripped;
      }
    }

    // 4) Coba tutup JSON yang terpotong (mis. akibat kolom VARCHAR terlalu pendek)
    $repaired = self::attemptRepairTruncatedJsonObject($raw);
    if ($repaired !== null) {
      return self::unwrapLegacyDataKey($repaired);
    }

    // 5) Fallback: ekstrak "key":"value" dari JSON yang rusak/terpotong
    $recovered = [];
    if (preg_match_all('/"([^"\\\\]+)"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/u', $raw, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $m) {
        $key = $m[1];
        $val = stripcslashes($m[2]);
        if ($key !== '' && strcasecmp($key, 'Data') !== 0) {
          $recovered[$key] = $val;
        }
      }
    }

    // Pasangan terakhir yang terpotong: "Key":"val  (tanpa penutup)
    if (preg_match('/"([^"\\\\]+)"\s*:\s*"((?:\\\\.|[^"\\\\])*)$/u', $raw, $tail)) {
      $key = $tail[1];
      $val = rtrim(stripcslashes($tail[2]));
      if ($key !== '' && strcasecmp($key, 'Data') !== 0 && $val !== '' && !array_key_exists($key, $recovered)) {
        $recovered[$key] = $val;
      }
    }

    if ($recovered === [] && preg_match_all('/"([^"\\\\]+)"\s*:\s*([0-9]+(?:\.[0-9]+)?)/u', $raw, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $m) {
        $recovered[$m[1]] = $m[2];
      }
    }

    return $recovered;
  }

  /**
   * Coba perbaiki JSON object yang terpotong di tengah string value.
   *
   * @param  string  $raw
   * @return array<string, mixed>|null
   */
  private static function attemptRepairTruncatedJsonObject(string $raw): ?array
  {
    $trimmed = rtrim($raw);
    if ($trimmed === '' || $trimmed[0] !== '{') {
      return null;
    }

    // Sudah terlihat seperti object lengkap
    if (substr($trimmed, -1) === '}') {
      return null;
    }

    $candidates = [
      $trimmed . '"}',
      $trimmed . '}',
    ];

    foreach ($candidates as $candidate) {
      $open = substr_count($candidate, '{');
      $close = substr_count($candidate, '}');
      if ($close < $open) {
        $candidate .= str_repeat('}', $open - $close);
      }

      $decoded = json_decode($candidate, true);
      if (is_array($decoded) && $decoded !== []) {
        return $decoded;
      }
    }

    return null;
  }

  /**
   * Data lama kadang tersimpan {"Data":"{...json...}"} akibat fallback tampilan.
   *
   * @param  array<string, mixed>  $decoded
   * @return array<string, mixed>
   */
  private static function unwrapLegacyDataKey(array $decoded): array
  {
    if (count($decoded) === 1 && isset($decoded['Data']) && is_string($decoded['Data'])) {
      $inner = self::decodeJsonMap($decoded['Data']);
      if (!empty($inner)) {
        return $inner;
      }
    }

    return $decoded;
  }

  /**
   * Ambil raw jenis_sampel dari baris parameter sesuai konteks haji/non-haji.
   * Haji: pakai jenis_sampel_haji jika terisi, else fallback jenis_sampel.
   *
   * @param  object|array  $parameterRow
   * @param  int|bool      $isHaji
   * @return mixed
   */
  public static function pickJenisSampelRawForContext($parameterRow, $isHaji)
  {
    if ((int) $isHaji === 1) {
      $haji = null;
      if (is_object($parameterRow)) {
        $haji = $parameterRow->jenis_sampel_haji ?? null;
      } elseif (is_array($parameterRow)) {
        $haji = $parameterRow['jenis_sampel_haji'] ?? null;
      }

      if (!empty($haji)) {
        return $haji;
      }
    }

    if (is_object($parameterRow)) {
      return $parameterRow->jenis_sampel ?? null;
    }

    return is_array($parameterRow) ? ($parameterRow['jenis_sampel'] ?? null) : null;
  }

  /**
   * Deteksi flag haji dari permohonan (object) atau nilai boolean/int.
   *
   * @param  mixed  $permohonanOrIsHaji
   * @return int 0|1
   */
  public static function resolvePermohonanIsHaji($permohonanOrIsHaji): int
  {
    if (is_object($permohonanOrIsHaji)) {
      if (!empty($permohonanOrIsHaji->id_permohonan_uji_klinik_haji)) {
        return 1;
      }

      return (int) ($permohonanOrIsHaji->is_haji ?? 0) === 1 ? 1 : 0;
    }

    return ((int) $permohonanOrIsHaji === 1) ? 1 : 0;
  }

  /**
   * Ambil attribute dari object/array parameter.
   *
   * @param  object|array|null  $row
   * @param  string  $key
   * @return mixed
   */
  private static function attrFromRow($row, string $key)
  {
    if (is_object($row)) {
      return $row->{$key} ?? null;
    }

    return is_array($row) ? ($row[$key] ?? null) : null;
  }

  /**
   * Metode master sesuai konteks haji/non-haji.
   * Haji: metode_parameter_satuan_klinik_haji jika terisi (bukan '-' / kosong), else fallback metode biasa.
   *
   * @param  object|array|null  $parameterRow
   * @param  mixed  $permohonanOrIsHaji
   * @return string|null
   */
  public static function pickMetodeForContext($parameterRow, $permohonanOrIsHaji): ?string
  {
    $isHaji = self::resolvePermohonanIsHaji($permohonanOrIsHaji);

    if ($isHaji === 1) {
      $haji = self::attrFromRow($parameterRow, 'metode_parameter_satuan_klinik_haji');
      $haji = is_string($haji) ? trim($haji) : $haji;
      if (!empty($haji) && $haji !== '-') {
        return $haji;
      }
    }

    $normal = self::attrFromRow($parameterRow, 'metode_parameter_satuan_klinik');

    return is_string($normal) ? $normal : ($normal ?? null);
  }

  /**
   * Pecah metode master (CSV) menjadi daftar opsi dropdown.
   *
   * @return list<string>
   */
  public static function parseMetodeOptionsList(?string $metodeRaw): array
  {
    $metodeRaw = trim((string) $metodeRaw);
    if ($metodeRaw === '' || $metodeRaw === '-') {
      return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $metodeRaw)), function ($m) {
      return $m !== '' && $m !== '-';
    }));
  }

  /**
   * Metode yang ditampilkan/dipilih: hanya dari master parameter satuan.
   */
  public static function resolveMethodSelectedForDisplay(?string $methodSaved, ?string $metodeMasterRaw): string
  {
    $methodSaved = trim((string) $methodSaved);
    $options = self::parseMetodeOptionsList($metodeMasterRaw);

    if (!empty($options)) {
      if ($methodSaved !== '' && $methodSaved !== '-' && in_array($methodSaved, $options, true)) {
        return $methodSaved;
      }

      return $options[0];
    }

    return '-';
  }

  /**
   * Metode awal saat simpan permohonan: hanya dari master parameter satuan (bukan acuan baku mutu).
   */
  public static function resolveInitialMethodForParameter($parameterRow, $permohonanOrIsHaji): string
  {
    $metodeRaw = self::pickMetodeForContext($parameterRow, $permohonanOrIsHaji);
    $options = self::parseMetodeOptionsList(is_string($metodeRaw) ? $metodeRaw : null);
    if (!empty($options)) {
      return $options[0];
    }

    return '-';
  }

  /**
   * LOINC sesuai konteks haji/non-haji (untuk SatuSehat Observation/ServiceRequest).
   * Haji: loinc_parameter_satuan_klinik_haji jika terisi, else fallback LOINC biasa, else $fallback.
   *
   * @param  object|array|null  $parameterRow
   * @param  mixed  $permohonanOrIsHaji
   * @param  string  $fallback
   * @return string
   */
  public static function pickLoincForContext($parameterRow, $permohonanOrIsHaji, string $fallback = '31100-1'): string
  {
    $isHaji = self::resolvePermohonanIsHaji($permohonanOrIsHaji);

    if ($isHaji === 1) {
      $haji = self::attrFromRow($parameterRow, 'loinc_parameter_satuan_klinik_haji');
      $haji = is_string($haji) ? trim($haji) : $haji;
      if (!empty($haji) && $haji !== '-') {
        return $haji;
      }
    }

    $normal = self::attrFromRow($parameterRow, 'loinc_parameter_satuan_klinik');
    $normal = is_string($normal) ? trim($normal) : $normal;
    if (!empty($normal) && $normal !== '-') {
      return $normal;
    }

    return $fallback;
  }

  /**
   * Normalisasi kumpulan nilai jenis_sampel (dari ms_parameter_satuan_klinik): cabang array sama seperti
   * kode asli {@see self::getJenisSampelFromParameter}; string dipecah keyword (Plasma, Serum, …).
   *
   * @param array<int, mixed> $rawTypes
   * @return list<string>
   */
  public static function canonicalJenisListFromParameterRawTypes(array $rawTypes): array
  {
    $auto_jenis_sampel = [];
    foreach ($rawTypes as $raw) {
      $canon = [];
      if (is_array($raw)) {
        foreach ($raw as $item) {
          $p = strtolower(trim((string) $item));
          if ($p === '') {
            continue;
          }
          if (strpos($p, 'blood cell') !== false || strpos($p, 'sel darah') !== false) {
            $canon[] = 'Blood Cell';
          } elseif (
            strpos($p, 'plasma') !== false
            && (
              strpos($p, 'naf') !== false
              || strpos($p, 'na-f') !== false
              || strpos($p, 'fluoride') !== false
              || strpos($p, 'fluorida') !== false
            )
          ) {
            $canon[] = 'Plasma NaF';
          } elseif (strpos($p, 'plasma') !== false) {
            $canon[] = 'Plasma';
          } elseif (strpos($p, 'serum') !== false) {
            $canon[] = 'Serum';
          } elseif (strpos($p, 'darah') !== false) {
            $canon[] = 'Darah';
          } elseif (strpos($p, 'urin') !== false || strpos($p, 'urine') !== false) {
            $canon[] = 'Urine';
          } elseif (
            strpos($p, 'feses') !== false ||
            strpos($p, 'faec') !== false ||
            strpos($p, 'feces') !== false
          ) {
            $canon[] = 'Feses';
          } elseif (strpos($p, 'swab') !== false) {
            $canon[] = 'Swab';
          }
        }
      } else {
        $str = strtolower((string) $raw);
        $parts = array_map('trim', preg_split('/[,;]+/', $str));
        foreach ($parts as $p) {
          if ($p === '') {
            continue;
          }
          if (strpos($p, 'blood cell') !== false || strpos($p, 'sel darah') !== false) {
            $canon[] = 'Blood Cell';
          } elseif (
            strpos($p, 'plasma') !== false
            && (
              strpos($p, 'naf') !== false
              || strpos($p, 'na-f') !== false
              || strpos($p, 'fluoride') !== false
              || strpos($p, 'fluorida') !== false
            )
          ) {
            $canon[] = 'Plasma NaF';
          } elseif (strpos($p, 'plasma') !== false) {
            $canon[] = 'Plasma';
          } elseif (strpos($p, 'serum') !== false) {
            $canon[] = 'Serum';
          } elseif (strpos($p, 'darah') !== false) {
            $canon[] = 'Darah';
          } elseif (strpos($p, 'urin') !== false || strpos($p, 'urine') !== false) {
            $canon[] = 'Urine';
          } elseif (
            strpos($p, 'feses') !== false ||
            strpos($p, 'faec') !== false ||
            strpos($p, 'feces') !== false
          ) {
            $canon[] = 'Feses';
          } elseif (strpos($p, 'swab') !== false) {
            $canon[] = 'Swab';
          }
        }
      }
      foreach (array_unique($canon) as $c) {
        $auto_jenis_sampel[] = $c;
      }
    }

    return array_values(array_unique($auto_jenis_sampel));
  }

  /**
   * True jika semua parameter permohonan hanya membutuhkan sampel Urine.
   */
  public static function isUrineOnlySample($id_permohonan_uji_klinik): bool
  {
    $jenis = self::getJenisSampelFromParameter($id_permohonan_uji_klinik);
    if (empty($jenis)) {
      return false;
    }

    $normalized = array_map(function ($j) {
      return strtolower(trim((string) $j));
    }, $jenis);

    return count($normalized) === 1 && in_array('urine', $normalized, true);
  }

  /**
   * Alamat singkat: kabupaten/kota dari wilayah pasien.
   */
  public static function alamatKabupatenKotaPasien($pasien): string
  {
    if (!$pasien) {
      return '-';
    }

    $wilayahId = $pasien->wilayah_id ?? null;
    if ($wilayahId) {
      $kab = \Smt\Masterweb\Models\Wilayah::resolveKabupatenKotaFromWilayahId($wilayahId);
      if ($kab) {
        // Pakai label master (Kabupaten … / Kota …) apa adanya, sesuai input wilayah.
        return trim((string) $kab);
      }
    }

    return '-';
  }

  /**
   * Deteksi nilai alamat yang sebenarnya tanggal lahir (dari formula/kesalahan input Excel).
   */
  public static function isAlamatPasienTanggalLahir($alamat, $tglLahir = null): bool
  {
    $alamat = trim((string) $alamat);
    if ($alamat === '' || strpos($alamat, '=') === 0) {
      return $alamat !== '';
    }

    if (is_numeric($alamat)) {
      try {
        $parsed = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $alamat);
        if ($parsed) {
          return true;
        }
      } catch (\Throwable $e) {
        // Bukan serial tanggal Excel.
      }
    }

    $bulanIndonesia = 'Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember';
    $bulanEnglish = 'January|February|March|April|May|June|July|August|September|October|November|December';
    if (preg_match('/^\d{1,2}\s+(' . $bulanIndonesia . '|' . $bulanEnglish . ')\s+\d{4}$/iu', $alamat)) {
      return true;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $alamat)) {
      return true;
    }

    if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/', $alamat)) {
      return true;
    }

    if ($tglLahir) {
      try {
        $carbon = \Carbon\Carbon::parse($tglLahir);
        $candidates = [
          $carbon->isoFormat('DD MMMM YYYY'),
          $carbon->format('d F Y'),
          $carbon->format('j F Y'),
          $carbon->format('d/m/Y'),
          $carbon->format('Y-m-d'),
        ];
        foreach ($candidates as $formatted) {
          if (strcasecmp($alamat, $formatted) === 0) {
            return true;
          }
        }

        // Samakan tanggal walau format teks berbeda (Januari vs January)
        try {
          if (\Carbon\Carbon::parse($alamat)->isSameDay($carbon)) {
            return true;
          }
        } catch (\Throwable $e) {
          // Bukan tanggal yang bisa di-parse.
        }
      } catch (\Throwable $e) {
        // Abaikan jika parsing gagal.
      }
    }

    return false;
  }

  /**
   * Bersihkan nilai alamat dari import/input agar tidak menyimpan tanggal lahir.
   */
  public static function sanitizeAlamatPasien($alamat, $tglLahir = null): ?string
  {
    $alamat = trim((string) $alamat);
    if ($alamat === '' || self::isAlamatPasienTanggalLahir($alamat, $tglLahir)) {
      return null;
    }

    return $alamat;
  }

  /**
   * Alamat lengkap pasien: detail alamat + hierarki wilayah sesuai input.
   *
   * @param  bool  $includeProvinsi  false = tanpa nama provinsi (untuk cetak)
   */
  /**
   * Buang sisa label wilayah yang sudah tertanam di kolom alamat_pasien
   * (mis. "..., DIY, Kabupaten Kendal, Jawa Tengah") agar kab/kota diambil dari wilayah_id.
   */
  public static function stripEmbeddedWilayahFromDetail(string $detail, array $hierarchyLabels = []): string
  {
    $detail = trim($detail);
    if ($detail === '') {
      return '';
    }

    $parts = array_values(array_filter(array_map('trim', explode(',', $detail)), function ($p) {
      return $p !== '';
    }));

    $hierarchyLookup = [];
    foreach ($hierarchyLabels as $label) {
      $label = trim((string) $label);
      if ($label !== '') {
        $hierarchyLookup[mb_strtolower($label, 'UTF-8')] = true;
      }
    }

    $provinsiLookup = [];
    try {
      foreach (\Smt\Masterweb\Models\Wilayah::where('tipe', 'PROV')->pluck('wilayah') as $name) {
        $name = trim((string) $name);
        if ($name !== '') {
          $provinsiLookup[mb_strtolower($name, 'UTF-8')] = true;
        }
      }
    } catch (\Throwable $e) {
      // ignore — fallback pattern di bawah
    }

    while (!empty($parts)) {
      $last = end($parts);
      $lastKey = mb_strtolower($last, 'UTF-8');
      $shouldStrip = isset($hierarchyLookup[$lastKey])
        || isset($provinsiLookup[$lastKey])
        || (bool) preg_match('/^(Kabupaten|Kota|Kecamatan|Provinsi)\b/iu', $last)
        || (bool) preg_match('/^(DIY|DKI|NAD|DI)$/iu', $last);

      if (!$shouldStrip) {
        break;
      }

      array_pop($parts);
    }

    return implode(', ', $parts);
  }

  public static function alamatLengkapPasien($pasien, bool $includeProvinsi = true): string
  {
    if (!$pasien) {
      return '-';
    }

    $hierarchy = \Smt\Masterweb\Models\Wilayah::resolveHierarchyLabelsFromWilayahId(
      $pasien->wilayah_id ?? null,
      $includeProvinsi
    );

    $parts = [];
    $detail = self::sanitizeAlamatPasien($pasien->alamat_pasien ?? '', $pasien->tgllahir_pasien ?? null) ?? '';
    $detail = self::stripEmbeddedWilayahFromDetail($detail, $hierarchy);
    if ($detail !== '') {
      $parts[] = $detail;
    }

    foreach ($hierarchy as $label) {
      $label = trim((string) $label);
      if ($label === '') {
        continue;
      }

      // Skip hanya jika detail alamat (bukan hierarki) sudah tepat sama dengan label.
      // Nama desa & kecamatan boleh identik (contoh: Borobudur, Borobudur) — keduanya tetap ditampilkan.
      // Exact match saja — jangan skip "Kabupaten Magelang" hanya karena detail mengandung "Magelang".
      if ($detail !== '' && strcasecmp(trim($detail), $label) === 0) {
        continue;
      }

      $parts[] = $label;
    }

    if (empty($parts)) {
      return '-';
    }

    return implode(', ', $parts);
  }

  /**
   * Format alamat untuk cetak dokumen (informed consent, nota): tanpa provinsi.
   * Menghapus nama provinsi di akhir (semua provinsi, bukan hanya Jawa Tengah).
   */
  public static function formatAlamatCetak(?string $alamat): string
  {
    $alamat = trim((string) $alamat);
    if ($alamat === '' || $alamat === '-') {
      return '-';
    }

    // Hapus suffix kode pos (5 digit) di akhir
    $alamat = preg_replace('/\s+\d{5}\s*$/u', '', $alamat);

    // Hapus nama provinsi di akhir string (dari master wilayah, fallback daftar umum)
    $provinsiNames = [];
    try {
      $provinsiNames = \Smt\Masterweb\Models\Wilayah::where('tipe', 'PROV')
        ->pluck('wilayah')
        ->filter()
        ->map(function ($name) {
          return trim((string) $name);
        })
        ->values()
        ->all();
    } catch (\Throwable $e) {
      $provinsiNames = [];
    }

    if (empty($provinsiNames)) {
      $provinsiNames = [
        'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Jambi', 'Sumatera Selatan',
        'Bengkulu', 'Lampung', 'Kepulauan Bangka Belitung', 'Kepulauan Riau',
        'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Daerah Istimewa Yogyakarta',
        'Jawa Timur', 'Banten', 'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
        'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur',
        'Kalimantan Utara', 'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan',
        'Sulawesi Tenggara', 'Gorontalo', 'Sulawesi Barat', 'Maluku', 'Maluku Utara',
        'Papua', 'Papua Barat', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya',
      ];
    }

    // Urutkan nama terpanjang dulu agar match lebih akurat
    usort($provinsiNames, function ($a, $b) {
      return strlen($b) <=> strlen($a);
    });

    foreach ($provinsiNames as $provinsi) {
      $escaped = preg_quote($provinsi, '/');
      $alamat = preg_replace('/,\s*' . $escaped . '\s*$/iu', '', $alamat);
    }

    $alamat = trim($alamat, " \t\n\r\0\x0B,");

    if ($alamat === '') {
      return '-';
    }

    return $alamat;
  }

  /**
   * Alamat lengkap pasien untuk cetak (informed consent, nota): tanpa provinsi.
   */
  public static function alamatPasienCetak($pasien): string
  {
    return self::formatAlamatCetak(self::alamatLengkapPasien($pasien, false));
  }

  /**
   * Alamat untuk cetak hasil narkoba: detail (dusun/RT/RW) + desa/kelurahan + kabupaten.
   */
  public static function alamatPasienNarkobaCetak($pasien): string
  {
    if (!$pasien) {
      return '-';
    }

    // Hierarki yang ingin ditampilkan: desa/kelurahan (jika node adalah DESA) + kabupaten/kota.
    // Kecamatan sengaja dilewati sesuai format cetak narkoba.
    $hierarchy = [];
    $wilayahId = $pasien->wilayah_id ?? null;
    if ($wilayahId) {
      $current = \Smt\Masterweb\Models\Wilayah::find($wilayahId);
      if ($current) {
        if ($current->tipe === 'DESA') {
          $desaLabel = trim((string) $current->wilayah);
          if ($desaLabel !== '') {
            $hierarchy[] = $desaLabel;
          }
          $kab = \Smt\Masterweb\Models\Wilayah::resolveKabupatenKotaFromWilayahId($wilayahId);
          if ($kab) {
            $hierarchy[] = $kab;
          }
        } else {
          $kab = \Smt\Masterweb\Models\Wilayah::resolveKabupatenKotaFromWilayahId($wilayahId);
          if ($kab) {
            $hierarchy[] = $kab;
          } elseif ($current->tipe === 'KAB') {
            $hierarchy[] = $current->wilayah;
          }
        }
      }
    }

    $parts = [];
    $detail = self::sanitizeAlamatPasien($pasien->alamat_pasien ?? '', $pasien->tgllahir_pasien ?? null) ?? '';
    $detail = self::stripEmbeddedWilayahFromDetail($detail, $hierarchy);
    if ($detail !== '') {
      $parts[] = $detail;
    }

    foreach ($hierarchy as $label) {
      $label = trim((string) $label);
      if ($label === '') {
        continue;
      }

      // Skip hanya jika detail alamat sudah tepat sama; hierarki tetap ditampilkan utuh.
      if ($detail !== '' && strcasecmp(trim($detail), $label) === 0) {
        continue;
      }

      $parts[] = $label;
    }

    if (empty($parts)) {
      return '-';
    }

    return self::formatAlamatCetak(implode(', ', $parts));
  }

  public static function tempatLahirPasienCetak($pasien): string
  {
    if (!$pasien) {
      return '-';
    }

    $val = trim((string) ($pasien->tmpt_lahir ?? ''));

    return $val !== '' ? $val : '-';
  }

  public static function pekerjaanPasienCetak($pasien): string
  {
    if (!$pasien) {
      return '-';
    }

    $val = trim((string) ($pasien->pekerjaan ?? ''));

    return $val !== '' ? $val : '-';
  }

  /**
   * Daftar zat narkoba untuk kesimpulan cetak hasil.
   */
  public static function narkobaSubstanceList(): array
  {
    return [
      'Methamphetamine' => 'Methamphetamine/MET',
      'Cocaine'         => 'Cocaine',
      'Cannabinoids'    => 'Cannabinoids/Marijuana/THC',
      'Morphine'        => 'Morphine/MOP',
      'Amphetamine'     => 'Amphetamine/AMP',
      'Benzodiazepines' => 'Benzodiazepines/BZO',
    ];
  }

  /**
   * Apakah hasil pemeriksaan narkoba dianggap positif.
   */
  public static function isHasilNarkobaPositif($hasil): bool
  {
    $hasil = trim((string) $hasil);
    if ($hasil === '' || $hasil === '-') {
      return false;
    }

    return strcasecmp($hasil, 'Negatif') !== 0;
  }

  /**
   * Teks kesimpulan bagian D untuk cetak hasil narkoba.
   */
  public static function composeKesimpulanNarkobaCetak(array $dataNarkoba, array $mapping): string
  {
    $hasil = [];
    foreach ($dataNarkoba as $item) {
      $key = $mapping[$item['nama_parameter_satuan_klinik'] ?? '']
        ?? ($item['nama_parameter_satuan_klinik'] ?? '');
      if ($key !== '') {
        $hasil[$key] = $item['hasil_permohonan_uji_parameter_klinik'] ?? null;
      }
    }

    $positives = [];
    $negatives = [];
    $allTested = [];

    foreach (self::narkobaSubstanceList() as $label => $mapped) {
      if (!array_key_exists($mapped, $hasil)) {
        continue;
      }

      $val = trim((string) ($hasil[$mapped] ?? ''));
      if ($val === '' || $val === '-') {
        continue;
      }

      $allTested[] = $label;
      if (self::isHasilNarkobaPositif($val)) {
        $positives[] = $label;
      } else {
        $negatives[] = $label;
      }
    }

    if (empty($positives)) {
      $substances = !empty($allTested)
        ? implode(' / ', $allTested)
        : 'Methamphetamine / Cocaine / Cannabinoids / Morphine / Amphetamine / Benzodiazepines';

      return 'Pada saat diperiksa, urine yang bersangkutan <b>tidak mengandung</b> zat jenis '
        . e($substances) . '.';
    }

    $text = 'Pada saat diperiksa, urine yang bersangkutan <b>mengandung</b> zat jenis '
      . '<b>' . e(implode('/', $positives)) . '</b>';

    if (!empty($negatives)) {
      $text .= ' dan <b>tidak mengandung</b> zat jenis ' . e(implode('/', $negatives));
    }

    return $text . '.';
  }

  /**
   * Catatan default untuk hasil pemeriksaan narkoba.
   */
  public static function defaultCatatanHasilNarkoba(): string
  {
    return 'Hasil pemeriksaan ini dibuat untuk dipergunakan seperlunya.';
  }

  /**
   * Normalisasi teks catatan untuk dibandingkan (abaikan HTML/whitespace).
   */
  public static function normalizeCatatanHasilText(?string $html): string
  {
    $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', trim($text));

    return (string) $text;
  }

  /**
   * Bangun catatan hasil default dari master berdasarkan daftar id parameter satuan klinik.
   *
   * @param  array|string[]  $parameterSatuanIds
   */
  public static function buildDefaultCatatanHasilFromParameters(array $parameterSatuanIds): string
  {
    $ids = array_values(array_unique(array_filter(array_map('strval', $parameterSatuanIds))));
    if (empty($ids)) {
      return '';
    }

    $rows = \Smt\Masterweb\Models\DefaultCatatanHasilKlinik::query()
      ->whereIn('parameter_satuan_klinik', $ids)
      ->where('is_active', 1)
      ->whereNull('deleted_at')
      ->orderBy('sort_order', 'asc')
      ->orderBy('created_at', 'asc')
      ->get();

    $parts = [];
    foreach ($rows as $row) {
      $catatan = trim((string) $row->catatan_default);
      if ($catatan === '') {
        continue;
      }
      $parts[] = $catatan;
    }

    return implode('<br><br>', $parts);
  }

  /**
   * Hapus blok "Stadium CKD" yang dobel jika sudah ada "Stadium GFR"
   * (sering terjadi karena JS e-GFR menambahkan CKD di atas master GFR).
   */
  public static function dedupeStadiumCatatanHasil(string $html): string
  {
    $plain = self::normalizeCatatanHasilText($html);
    $hasGfr = (bool) preg_match('/Stadium\s*GFR/i', $plain);
    $hasCkd = (bool) preg_match('/Stadium\s*CKD/i', $plain);

    if (!$hasGfr || !$hasCkd) {
      return $html;
    }

    // Buang blok Stadium CKD di akhir (pola paling umum)
    $cleaned = preg_replace('/(?:<br\s*\/?>|\r?\n|\s)*Stadium\s*CKD\b[\s\S]*$/iu', '', $html);
    if ($cleaned === null) {
      return $html;
    }

    // Jika CKD masih tersisa di tengah, buang sampai sebelum Stadium GFR / akhir
    if (preg_match('/Stadium\s*CKD/i', self::normalizeCatatanHasilText($cleaned))) {
      $mid = preg_replace('/Stadium\s*CKD\b[\s\S]*?(?=Stadium\s*GFR|$)/iu', '', $cleaned);
      if ($mid !== null) {
        $cleaned = $mid;
      }
    }

    $cleaned = preg_replace('/(?:<br\s*\/?>\s*){3,}/i', '<br><br>', (string) $cleaned);
    $cleaned = preg_replace('/(?:<br\s*\/?>\s*)+$/i', '', (string) $cleaned);

    return trim((string) $cleaned);
  }

  /**
   * Ambil id parameter satuan dari struktur arr_permohonan_parameter form analis.
   */
  public static function collectParameterSatuanIdsFromArrPermohonan(array $arrPermohonanParameter): array
  {
    $ids = [];
    foreach ($arrPermohonanParameter as $grup) {
      $items = $grup['item_permohonan_parameter_satuan'] ?? [];
      foreach ($items as $item) {
        $id = $item['id_parameter_satuan_klinik']
          ?? $item['parameter_satuan_klinik']
          ?? optional($item['parametersatuanklinik'] ?? null)->id_parameter_satuan_klinik
          ?? null;
        if (!$id && isset($item['parameter_satuan_klinik_id'])) {
          $id = $item['parameter_satuan_klinik_id'];
        }
        if ($id) {
          $ids[] = (string) $id;
        }
      }
    }

    return array_values(array_unique($ids));
  }

  /**
   * Nilai catatan hasil untuk form analis:
   * - kosong / masih mengikuti master → isi ulang dari master
   * - sudah diedit manual → pertahankan
   */
  public static function resolveCatatanHasilFormValue($permohonan, array $arrPermohonanParameter): string
  {
    $parameterIds = self::collectParameterSatuanIdsFromArrPermohonan($arrPermohonanParameter);
    if (empty($parameterIds) && $permohonan && !empty($permohonan->id_permohonan_uji_klinik)) {
      $parameterIds = \Smt\Masterweb\Models\PermohonanUjiParameterKlinik::query()
        ->where('permohonan_uji_klinik', $permohonan->id_permohonan_uji_klinik)
        ->whereNull('deleted_at')
        ->pluck('parameter_satuan_klinik')
        ->filter()
        ->map(function ($v) {
          return (string) $v;
        })
        ->unique()
        ->values()
        ->all();
    }

    $default = self::buildDefaultCatatanHasilFromParameters($parameterIds);
    $existing = (string) ($permohonan->catatan_hasil ?? '');
    $existingNormalized = self::normalizeCatatanHasilText($existing);
    $fromMaster = (int) ($permohonan->catatan_hasil_from_master ?? 0) === 1;

    if ($existingNormalized === '' || $fromMaster) {
      return $default;
    }

    // Bersihkan dobel Stadium GFR + Stadium CKD pada catatan yang sudah tersimpan
    return self::dedupeStadiumCatatanHasil($existing);
  }

  /**
   * Apakah permohonan memiliki grup parameter narkoba.
   */
  public static function permohonanParameterHasGrupNarkoba(array $arrPermohonanParameter): bool
  {
    foreach ($arrPermohonanParameter as $grup) {
      $nama = strtolower((string) ($grup['name_parameter_jenis_klinik'] ?? ''));
      if (strpos($nama, 'narkoba') !== false || strpos($nama, 'narkotika') !== false) {
        return true;
      }
    }

    return false;
  }

  /**
   * Nilai kesimpulan hasil untuk form analis/verifikasi (prefill default narkoba).
   */
  public static function resolveKesimpulanHasilFormValue($permohonan, array $arrPermohonanParameter): string
  {
    $existing = trim(strip_tags((string) ($permohonan->kesimpulan_hasil ?? '')));
    if ($existing !== '') {
      return (string) $permohonan->kesimpulan_hasil;
    }

    if (self::permohonanParameterHasGrupNarkoba($arrPermohonanParameter)) {
      return self::defaultCatatanHasilNarkoba();
    }

    return '';
  }

  /**
   * Kumpulkan acuan baku mutu (library) dan metode per parameter permohonan.
   */
  public static function collectAcuanBakuMutuPerParameter(array $arrPermohonanParameter, int $isHaji = 0): array
  {
    $parameters = [];
    $uniqueAcuan = [];

    foreach ($arrPermohonanParameter as $jenisGroup) {
      $jenisId = (string) ($jenisGroup['id_parameter_jenis_klinik'] ?? '');

      foreach ($jenisGroup['item_permohonan_parameter_satuan'] ?? [] as $param) {
        $satuanId = (string) ($param['parameter_satuan_klinik'] ?? '');
        $paramName = trim((string) ($param['nama_parameter_satuan_klinik'] ?? '-'));
        if ($paramName === '') {
          $paramName = '-';
        }

        $method = trim(strip_tags((string) ($param['method_permohonan_uji_parameter_klinik'] ?? '')));
        if ($method === '' || $method === '-') {
          $method = trim(strip_tags((string) ($param['metode_parameter_satuan_klinik'] ?? '')));
        }
        if ($method === '') {
          $method = '-';
        }

        $acuans = [];
        if ($jenisId !== '' && $satuanId !== '') {
          $allBm = BakuMutuPermohonanKlinikHelper::loadBakuMutuForParameter($jenisId, $satuanId, $isHaji);
          $allBm->load('library');

          foreach ($allBm as $bm) {
            $title = trim(strip_tags((string) optional($bm->library)->title_library));
            if ($title === '') {
              continue;
            }

            if (!in_array($title, $acuans, true)) {
              $acuans[] = $title;
            }

            $norm = mb_strtolower(preg_replace('/\s+/u', ' ', $title));
            if (!isset($uniqueAcuan[$norm])) {
              $uniqueAcuan[$norm] = $title;
            }
          }
        }

        $parameters[] = [
          'parameter' => $paramName,
          'metode' => $method,
          'acuan' => $acuans,
          'acuan_text' => !empty($acuans) ? implode(', ', $acuans) : '-',
        ];
      }
    }

    return [
      'parameters' => $parameters,
      'unique_acuan' => array_values($uniqueAcuan),
    ];
  }

  /**
   * Susun teks keterangan acuan metode default dari kumpulan acuan unik.
   */
  public static function composeKeteranganMetodeDefault(array $uniqueAcuan): string
  {
    $uniqueAcuan = array_values(array_filter(array_map(function ($item) {
      return trim(strip_tags((string) $item));
    }, $uniqueAcuan)));

    if (empty($uniqueAcuan)) {
      return '';
    }

    return 'Berdasarkan ' . implode(', ', $uniqueAcuan);
  }

  /**
   * Nilai keterangan metode untuk form analis/verifikasi.
   */
  public static function resolveKeteranganMetodeFormValue($permohonan, array $arrPermohonanParameter, int $isHaji = 0): string
  {
    $existing = trim(strip_tags((string) ($permohonan->keterangan_metode ?? '')));
    if ($existing !== '') {
      return (string) $permohonan->keterangan_metode;
    }

    $collected = self::collectAcuanBakuMutuPerParameter($arrPermohonanParameter, $isHaji);

    return self::composeKeteranganMetodeDefault($collected['unique_acuan'] ?? []);
  }

  /**
   * Teks keterangan metode untuk cetak hasil klinik.
   */
  public static function resolveKeteranganMetodeCetak($permohonan, array $arrPermohonanParameter, int $isHaji = 0): string
  {
    $saved = trim(strip_tags((string) ($permohonan->keterangan_metode ?? '')));
    if ($saved !== '') {
      return (string) $permohonan->keterangan_metode;
    }

    $collected = self::collectAcuanBakuMutuPerParameter($arrPermohonanParameter, $isHaji);

    return self::composeKeteranganMetodeDefault($collected['unique_acuan'] ?? []);
  }

  /**
   * Kumpulkan acuan baku mutu dan metode per parameter pada halaman baca-hasil kesmas.
   */
  public static function collectAcuanBakuMutuFromLaboratoriumMethods($laboratoriummethods): array
  {
    $parameters = [];
    $uniqueAcuan = [];

    foreach ($laboratoriummethods as $method) {
      $acuanTitle = trim(strip_tags((string) ($method->acuan_title_library ?? '')));
      $metodeBase = trim(strip_tags((string) ($method->metode ?? '')));
      if ($metodeBase === '') {
        $metodeBase = trim(strip_tags((string) ($method->name_method ?? '')));
      }
      if ($metodeBase === '') {
        $metodeBase = '-';
      }

      $details = $method->detail ?? [];
      if (is_array($details)) {
        $details = collect($details);
      } elseif (!($details instanceof \Traversable)) {
        $details = collect();
      } else {
        $details = collect(iterator_to_array($details));
      }

      if ($details->isNotEmpty()) {
        foreach ($details as $detail) {
          $paramName = trim(strip_tags((string) ($detail->name_sample_result_detail ?? '')));
          if ($paramName === '') {
            $paramName = trim(strip_tags((string) ($method->params_method ?? $method->name_method ?? '-')));
          }

          $acuans = $acuanTitle !== '' ? [$acuanTitle] : [];
          if ($acuanTitle !== '') {
            $norm = mb_strtolower(preg_replace('/\s+/u', ' ', $acuanTitle));
            if (!isset($uniqueAcuan[$norm])) {
              $uniqueAcuan[$norm] = $acuanTitle;
            }
          }

          $parameters[] = [
            'parameter' => $paramName !== '' ? $paramName : '-',
            'metode' => $metodeBase,
            'acuan' => $acuans,
            'acuan_text' => !empty($acuans) ? implode(', ', $acuans) : '-',
          ];
        }

        continue;
      }

      $paramName = trim(strip_tags((string) ($method->params_method ?? $method->name_method ?? '-')));
      if ($paramName === '') {
        $paramName = '-';
      }

      $acuans = $acuanTitle !== '' ? [$acuanTitle] : [];
      if ($acuanTitle !== '') {
        $norm = mb_strtolower(preg_replace('/\s+/u', ' ', $acuanTitle));
        if (!isset($uniqueAcuan[$norm])) {
          $uniqueAcuan[$norm] = $acuanTitle;
        }
      }

      $parameters[] = [
        'parameter' => $paramName,
        'metode' => $metodeBase,
        'acuan' => $acuans,
        'acuan_text' => !empty($acuans) ? implode(', ', $acuans) : '-',
      ];
    }

    return [
      'parameters' => $parameters,
      'unique_acuan' => array_values($uniqueAcuan),
    ];
  }

  /**
   * Nilai keterangan metode untuk form baca-hasil kesmas.
   */
  public static function resolveKeteranganMetodeBacaHasilFormValue($sample, $laboratoriummethods): string
  {
    $existing = trim(strip_tags((string) ($sample->keterangan_metode ?? '')));
    if ($existing !== '') {
      return (string) $sample->keterangan_metode;
    }

    $collected = self::collectAcuanBakuMutuFromLaboratoriumMethods($laboratoriummethods);

    return self::composeKeteranganMetodeDefault($collected['unique_acuan'] ?? []);
  }

  /**
   * Keterangan metode tersimpan untuk cetak hasil kesmas.
   * Prioritas: request preview → sample tersimpan.
   * Kosong = gunakan format lama (acuan dari tb_baku_mutu / library).
   */
  public static function getSavedKeteranganMetodeKesmas($sample): string
  {
    $fromRequest = '';
    try {
      if (function_exists('request') && request()) {
        if (request()->exists('keterangan_metode')) {
          $fromRequest = trim(strip_tags((string) request()->input('keterangan_metode', '')));
        }
      }
    } catch (\Throwable $e) {
      $fromRequest = '';
    }

    if ($fromRequest !== '') {
      return $fromRequest;
    }

    if ($sample === null) {
      return '';
    }

    return trim(strip_tags((string) ($sample->keterangan_metode ?? '')));
  }

  /**
   * Format tanggal Indo aman; invalid/null → '-'.
   */
  public static function safeFormatDateIndo($date): string
  {
    if ($date === null || $date === '') {
      return '-';
    }

    $raw = trim((string) $date);
    if ($raw === '' || strpos($raw, '0000-00-00') === 0) {
      return '-';
    }

    try {
      return \Smt\Masterweb\Helpers\DateHelper::formatDateIndo($raw);
    } catch (\Throwable $e) {
      return '-';
    }
  }

  /**
   * Ambil record VerificationActivitySample Pemeriksaan/Analitik (id=2) untuk sampel.
   * Fallback: cek sampel terkait di $extraSampleIds (mis. grup cetak).
   *
   * @param  mixed  $sample
   * @param  array  $extraSampleIds
   * @return \Smt\Masterweb\Models\VerificationActivitySample|null
   */
  public static function resolveFasePemeriksaanKesmas($sample, array $extraSampleIds = [])
  {
    $sampleIds = [];
    $primaryId = is_object($sample) ? ($sample->id_samples ?? null) : $sample;
    if (!empty($primaryId)) {
      $sampleIds[] = (string) $primaryId;
    }

    foreach ($extraSampleIds as $extraId) {
      if (!empty($extraId)) {
        $sampleIds[] = (string) $extraId;
      }
    }

    $sampleIds = array_values(array_unique(array_filter($sampleIds)));
    if (empty($sampleIds)) {
      return null;
    }

    return \Smt\Masterweb\Models\VerificationActivitySample::query()
      ->whereIn('id_sample', $sampleIds)
      ->where('id_verification_activity', 2)
      ->orderByDesc('is_done')
      ->orderByDesc('stop_date')
      ->orderByDesc('start_date')
      ->first();
  }

  /**
   * Tanggal Diperiksa untuk LHU kesmas.
   * Sumber utama: fase Pemeriksaan/Analitik (stop → start), lalu date_analitik_sample, lalu fase Input Hasil.
   *
   * @param  mixed  $sample
   * @param  bool  $asRange  true = "mulai s/d selesai" bila keduanya ada
   * @param  array  $extraSampleIds
   */
  public static function resolveTanggalDiperiksaKesmas($sample, bool $asRange = false, array $extraSampleIds = []): string
  {
    $fase = self::resolveFasePemeriksaanKesmas($sample, $extraSampleIds);
    $start = self::safeFormatDateIndo($fase->start_date ?? null);
    $stop = self::safeFormatDateIndo($fase->stop_date ?? null);

    if ($asRange) {
      if ($start !== '-' && $stop !== '-' && $start !== $stop) {
        return $start . ' s/d ' . $stop;
      }
      if ($stop !== '-') {
        return $stop;
      }
      if ($start !== '-') {
        return $start;
      }
    } else {
      // Prefer tanggal selesai pemeriksaan (kolom stop di tabel verifikasi)
      if ($stop !== '-') {
        return $stop;
      }
      if ($start !== '-') {
        return $start;
      }
    }

    if (is_object($sample) && !empty($sample->date_analitik_sample)) {
      $fromAnalitik = self::safeFormatDateIndo($sample->date_analitik_sample);
      if ($fromAnalitik !== '-') {
        return $fromAnalitik;
      }
    }

    $sampleId = is_object($sample) ? ($sample->id_samples ?? null) : $sample;
    if (!empty($sampleId)) {
      $faseInput = \Smt\Masterweb\Models\VerificationActivitySample::query()
        ->where('id_sample', $sampleId)
        ->where('id_verification_activity', 3)
        ->orderByDesc('stop_date')
        ->orderByDesc('start_date')
        ->first();

      $stopInput = self::safeFormatDateIndo($faseInput->stop_date ?? null);
      if ($stopInput !== '-') {
        return $stopInput;
      }
      $startInput = self::safeFormatDateIndo($faseInput->start_date ?? null);
      if ($startInput !== '-') {
        return $startInput;
      }
    }

    return '-';
  }

  /**
   * Apakah konteks lab kesmas mikrobiologi (MBI).
   */
  public static function isKesmasMikroLab($lab = null): bool
  {
    if ($lab === null) {
      return false;
    }

    $kode = strtoupper(trim((string) ($lab->kode_laboratorium ?? '')));
    if ($kode === 'MBI') {
      return true;
    }

    $nama = strtolower(trim((string) ($lab->nama_laboratorium ?? '')));

    return $nama !== '' && strpos($nama, 'mikro') !== false;
  }

  /**
   * Default catatan footer LHU kesmas (kimia/mikro).
   * Mikro mendapat baris tambahan tentang tanda (*) di paling atas.
   */
  public static function defaultCatatanHasilKesmas(bool $forMikro = false): string
  {
    $lines = [
      '- Hasil pemeriksaan hanya untuk sampel yang diperiksa',
      '- Hasil pemeriksaan tidak boleh digandakan untuk kepentingan promosi',
      '- Pengaduan hasil pemeriksaan dilayani sampai dengan 10 (sepuluh) hari setelah pengiriman hasil',
    ];

    if ($forMikro) {
      array_unshift(
        $lines,
        '- Hasil pemeriksaan dengan tanda (*) melebihi baku mutu yang dipersyaratkan'
      );
    }

    return implode("\n", $lines);
  }

  /**
   * Nilai field Catatan di form baca-hasil: tersimpan → default.
   * Jika masih teks default lama (tanpa baris *), upgrade ke default mikro.
   *
   * @param  mixed  $sample
   * @param  mixed  $lab  optional lab context (MBI = mikro)
   */
  public static function resolveCatatanHasilKesmasFormValue($sample, $lab = null): string
  {
    $forMikro = self::isKesmasMikroLab($lab);
    $default = self::defaultCatatanHasilKesmas($forMikro);
    $existing = trim((string) ($sample->catatan_hasil ?? ''));

    if ($existing === '') {
      return $default;
    }

    // Sample mikro yang masih menyimpan default lama (3 baris) → upgrade ke default baru
    if ($forMikro) {
      $legacyDefault = self::defaultCatatanHasilKesmas(false);
      if (self::normalizeCatatanHasilText($existing) === self::normalizeCatatanHasilText($legacyDefault)) {
        return $default;
      }
    }

    return (string) $sample->catatan_hasil;
  }

  /**
   * Catatan LHU kesmas untuk cetak/preview.
   * Prioritas: request preview → sample tersimpan → default.
   *
   * @param  mixed  $sample
   * @param  bool|null  $forMikro  null = tebak dari call site (default false)
   */
  public static function getSavedCatatanHasilKesmas($sample = null, $forMikro = null): string
  {
    $forMikro = $forMikro === null ? false : (bool) $forMikro;

    $fromRequest = '';
    try {
      if (function_exists('request') && request()) {
        if (request()->exists('catatan_hasil')) {
          $fromRequest = trim((string) request()->input('catatan_hasil', ''));
        }
      }
    } catch (\Throwable $e) {
      $fromRequest = '';
    }

    if ($fromRequest !== '') {
      return $fromRequest;
    }

    if ($sample !== null) {
      $saved = trim((string) ($sample->catatan_hasil ?? ''));
      if ($saved !== '') {
        if ($forMikro) {
          $legacyDefault = self::defaultCatatanHasilKesmas(false);
          if (self::normalizeCatatanHasilText($saved) === self::normalizeCatatanHasilText($legacyDefault)) {
            return self::defaultCatatanHasilKesmas(true);
          }
        }

        return (string) $sample->catatan_hasil;
      }
    }

    return self::defaultCatatanHasilKesmas($forMikro);
  }

  /**
   * HTML aman untuk footer catatan LHU (nl2br + escape).
   *
   * @param  mixed  $sample
   * @param  bool|null  $forMikro
   */
  public static function formatCatatanHasilKesmasHtml($sample = null, $forMikro = null): string
  {
    $text = self::getSavedCatatanHasilKesmas($sample, $forMikro);

    return nl2br(e($text), false);
  }

  /**
   * Susun teks acuan dari koleksi library baku mutu (format lama).
   */
  public static function formatAcuanBakuMutuTitles($allAcuanBakuMutu, string $separator = ', '): string
  {
    return collect($allAcuanBakuMutu ?? [])
      ->map(function ($item) {
        if (is_array($item)) {
          return trim(strip_tags((string) ($item['title_library'] ?? '')));
        }

        return trim(strip_tags((string) ($item->title_library ?? '')));
      })
      ->filter()
      ->unique()
      ->implode($separator);
  }

  /**
   * Catatan/kesimpulan footer untuk cetak hasil narkoba.
   */
  public static function resolveCatatanFooterNarkobaCetak($permohonan): string
  {
    $raw = trim(strip_tags((string) (
      $permohonan->kesimpulan_hasil
      ?? $permohonan->catatan_hasil
      ?? ''
    )));

    if ($raw === '') {
      return self::defaultCatatanHasilNarkoba();
    }

    return $raw;
  }

  /**
   * Parameter dengan input Positif/Negatif + jenis (+ nama jenis opsional).
   *
   * Aktif untuk:
   * - Kristal / Silinder / Lain-lain (berdasarkan nama parameter)
   * - Parameter lain yang Opsi Hasil-nya memuat Negatif dan Positif
   *   DAN (ada opsi grade/detail lain ATAU butuh nama jenis)
   *
   * Tidak aktif untuk parameter yang hanya Negatif/Positif tanpa grade
   * dan tanpa "Butuh Nama Jenis" (mis. Nitrit, narkoba) → dropdown opsi biasa.
   *
   * Tidak aktif untuk parameter numerik/range tanpa opsi Negatif+Positif
   * (mis. HbA1c, gula darah) → tetap input biasa + baku mutu.
   *
   * @param  string       $parameterName
   * @param  string|null  $masterOptionCsv  isi kolom option parameter satuan klinik
   * @param  bool|null    $requiresNamaJenis  penanda master/permohonan
   */
  public static function urinalisaDualColumnType(
    string $parameterName,
    ?string $masterOptionCsv = null,
    $requiresNamaJenis = null
  ): ?string {
    $name = strtolower(trim($parameterName));
    if (strpos($name, 'kristal') !== false) {
      return 'kristal';
    }
    if (strpos($name, 'silinder') !== false || strpos($name, 'cast') !== false) {
      return 'silinder';
    }
    if (strpos($name, 'lain-lain') !== false || strpos($name, 'lain lain') !== false) {
      return 'lain-lain';
    }

    $needsName = filter_var($requiresNamaJenis, FILTER_VALIDATE_BOOLEAN);

    // Butuh Nama Jenis → selalu dual (Negatif/Positif + grade + ketik nama),
    // meski opsi master berbentuk Negatif/(+)/(++)/(+++) tanpa kata "Positif".
    if ($needsName) {
      return 'grade';
    }

    // Tanpa nama jenis: dual hanya jika ada Negatif+Positif DAN opsi grade/detail lain.
    // Nitrit/narkoba (hanya Negatif, Positif) → dropdown biasa.
    if (self::hasPositifNegatifResultOptions($masterOptionCsv)
      && self::hasUrinalisaExtraDetailOptions($masterOptionCsv)) {
      return 'grade';
    }

    return null;
  }

  /**
   * Ada opsi selain Negatif/Positif di master (grade / nilai kuantitatif)?
   * Tidak memakai default (+)/(++)/(+++) — hanya isi master yang nyata.
   */
  public static function hasUrinalisaExtraDetailOptions(?string $masterOptionCsv): bool
  {
    $raw = trim((string) $masterOptionCsv);
    if ($raw === '') {
      return false;
    }

    foreach (explode(',', $raw) as $option) {
      $option = trim($option);
      if ($option === '') {
        continue;
      }
      if (strcasecmp($option, 'Negatif') === 0 || strcasecmp($option, 'Positif') === 0) {
        continue;
      }

      return true;
    }

    return false;
  }

  /**
   * Pastikan Opsi Hasil master berisi Negatif dan Positif.
   * Opsi kosong / numerik murni → false (hindari dual pada HbA1c dll.).
   */
  public static function hasPositifNegatifResultOptions(?string $masterOptionCsv): bool
  {
    $raw = trim((string) $masterOptionCsv);
    if ($raw === '') {
      return false;
    }

    $hasNegatif = false;
    $hasPositif = false;

    foreach (explode(',', $raw) as $option) {
      $option = trim($option);
      if ($option === '') {
        continue;
      }
      if (strcasecmp($option, 'Negatif') === 0) {
        $hasNegatif = true;
      } elseif (strcasecmp($option, 'Positif') === 0) {
        $hasPositif = true;
      }
    }

    return $hasNegatif && $hasPositif;
  }

  /**
   * Apakah opsi detail (selain Negatif/Positif) seluruhnya grade murni?
   * Contoh: (+), (++), Pos 1 (+) — bukan "10 (+)" / "50 (++)".
   */
  public static function hasUrinalisaPureGradeDetailOptions(?string $masterOptionCsv): bool
  {
    $opts = self::urinalisaDualDetailOptions(null, $masterOptionCsv);
    if (empty($opts)) {
      return false;
    }

    foreach ($opts as $opt) {
      if (!self::isUrinalisaPureGradeOption($opt)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Opsi grade murni: "(+)", "++", "Pos 1 (+)", "Pos 2 (++)", dll.
   */
  public static function isUrinalisaPureGradeOption(?string $opt): bool
  {
    $opt = trim((string) $opt);
    if ($opt === '') {
      return false;
    }

    return (bool) preg_match('/^(?:pos(?:itif)?\s*\d+\s*)?\(\+{1,4}\)$/iu', $opt)
      || (bool) preg_match('/^(?:pos(?:itif)?\s*\d+\s*)?\+{1,4}$/iu', $opt);
  }

  /**
   * Ambil token grade "(+)" / "(++)" / "(+++)" dari teks opsi/hasil.
   */
  public static function extractUrinalisaGradeToken(?string $text): ?string
  {
    $text = trim((string) $text);
    if ($text === '') {
      return null;
    }

    if (preg_match('/\((\+{1,4})\)/', $text, $m)) {
      return '(' . $m[1] . ')';
    }
    if (preg_match('/^(?:\+{1,4})$/', $text, $m)) {
      return '(' . $m[0] . ')';
    }

    return null;
  }

  /**
   * Opsi jenis (kolom 2) dari Daftar Opsi Hasil master, selain Negatif/Positif.
   * Jika daftar kosong → default: (+), (++), (+++).
   *
   * @param  string|null  $dualType  kristal|silinder|lain-lain|grade
   * @param  string|null  $masterOptionCsv  isi kolom option parameter satuan klinik
   */
  public static function urinalisaDualDetailOptions(?string $dualType, ?string $masterOptionCsv = null): array
  {
    $defaults = ['(+)', '(++)', '(+++)'];
    $raw = trim((string) $masterOptionCsv);

    if ($raw === '') {
      return $defaults;
    }

    $parts = array_map('trim', explode(',', $raw));
    $options = [];

    foreach ($parts as $opt) {
      if ($opt === '') {
        continue;
      }
      // Kolom 1 sudah Negatif/Positif — jangan ulangi di kolom jenis
      if (strcasecmp($opt, 'Negatif') === 0 || strcasecmp($opt, 'Positif') === 0) {
        continue;
      }
      $options[] = $opt;
    }

    $options = array_values(array_unique($options));

    return !empty($options) ? $options : $defaults;
  }

  /**
   * Parse hasil Kristal/Silinder: "(+) Ca. Oxalate" / "Ca. Oxalate (+)" / "(++)".
   *
   * @return array{positivity: string, detail: string, name: string}
   */
  public static function parseUrinalisaDualResult(?string $hasil): array
  {
    $hasil = trim((string) $hasil);
    if ($hasil === '' || strcasecmp($hasil, 'negatif') === 0 || $hasil === '-') {
      return ['positivity' => 'Negatif', 'detail' => '', 'name' => ''];
    }

    if (strcasecmp($hasil, 'positif') === 0) {
      return ['positivity' => 'Positif', 'detail' => '', 'name' => ''];
    }

    // Format baru: "(+) Ca. Oxalate" / "(++) Hyaline"
    if (preg_match('/^\((\+{1,4})\)\s*(.*)$/u', $hasil, $m)) {
      return [
        'positivity' => 'Positif',
        'detail' => '(' . $m[1] . ')',
        'name' => trim($m[2]),
      ];
    }

    // Grade saja: "(++)" / "+++" → Positif + jenis = grade
    if (preg_match('/^\((\+{1,4})\)$/', $hasil, $m)) {
      return ['positivity' => 'Positif', 'detail' => '(' . $m[1] . ')', 'name' => ''];
    }

    if (preg_match('/^(\+{1,4})$/', $hasil, $m)) {
      return ['positivity' => 'Positif', 'detail' => '(' . $m[1] . ')', 'name' => ''];
    }

    // Data lama: "Ca. Oxalate (+)" / "Pos 1 (+)"
    if (preg_match('/^(.+?)\s*\((\+{1,4})\)\s*$/u', $hasil, $m)) {
      $prefix = trim($m[1]);
      $grade = '(' . $m[2] . ')';
      // "Pos 1 (+)" → detail opsi master, name kosong
      if (preg_match('/^pos(?:itif)?\s*\d+$/iu', $prefix)) {
        return ['positivity' => 'Positif', 'detail' => trim($m[0]), 'name' => ''];
      }

      return ['positivity' => 'Positif', 'detail' => $grade, 'name' => $prefix];
    }

    // "Ca. Oxalate Positif" → detail tanpa kata Positif di akhir
    if (preg_match('/^(.+?)\s+positif\s*$/iu', $hasil, $m)) {
      return ['positivity' => 'Positif', 'detail' => '', 'name' => trim($m[1])];
    }

    return ['positivity' => 'Positif', 'detail' => $hasil, 'name' => ''];
  }

  /**
   * Positif tanpa jenis (kolom 2) dianggap belum lengkap.
   */
  public static function isUrinalisaDualIncomplete(string $positivity, string $detail): bool
  {
    $positivity = trim($positivity);
    $detail = trim($detail);

    if ($positivity === '' || strcasecmp($positivity, 'Negatif') === 0) {
      return false;
    }

    return $detail === '';
  }

  /**
   * Gabungkan kolom positif/negatif + jenis (+ nama opsional) untuk Kristal/Silinder/dll.
   * Hasil: "NamaJenis (+)" bila nama diisi; jika tidak, grade/detail saja.
   */
  public static function composeUrinalisaDualResult(string $positivity, string $detail, string $name = ''): string
  {
    $positivity = trim($positivity);
    $detail = trim($detail);
    $name = trim($name);

    if ($positivity === '' || strcasecmp($positivity, 'Negatif') === 0) {
      return 'Negatif';
    }

    // Nama diisi tanpa grade → default (+)
    if ($detail === '' && $name !== '') {
      $detail = '(+)';
    }

    if (self::isUrinalisaDualIncomplete($positivity, $detail)) {
      return '';
    }

    $grade = self::extractUrinalisaGradeToken($detail);
    if ($grade !== null) {
      // Dengan nama: "Ca. Oxalate (+)". Tanpa nama: biarkan opsi terpilih ("Pos 1 (+)" / "(+)").
      return $name !== '' ? ($name . ' ' . $grade) : $detail;
    }

    if ($name !== '') {
      return $name . ' ' . $detail;
    }

    return $detail;
  }

  /**
   * Pecah teks yang berisi beberapa grade, contoh "(++) Ca Oxalate (+++) Asam Urat".
   * Hanya jika grade pertama di awal string agar data lama "Ca. Oxalate (+)" tetap utuh.
   *
   * @return list<string>
   */
  private static function splitUrinalisaByGradeTokens(string $text): array
  {
    $text = trim($text);
    if ($text === '') {
      return [];
    }

    if (!preg_match_all('/\((\+{1,4})\)/', $text, $matches, PREG_OFFSET_CAPTURE)) {
      return [$text];
    }

    if (count($matches[0]) <= 1) {
      return [$text];
    }

    $prefix = trim(substr($text, 0, (int) $matches[0][0][1]));
    if ($prefix !== '') {
      // Format baru "Ca Oxalate (++) Asam Urat (+++)": tiap temuan diakhiri grade.
      return self::splitUrinalisaByTrailingGradeTokens($text, $matches[0]);
    }

    $segments = [];
    $count = count($matches[0]);
    $length = strlen($text);
    for ($i = 0; $i < $count; $i++) {
      $start = (int) $matches[0][$i][1];
      $end = ($i + 1 < $count) ? (int) $matches[0][$i + 1][1] : $length;
      $segment = trim(substr($text, $start, $end - $start));
      if ($segment !== '') {
        $segments[] = $segment;
      }
    }

    return $segments !== [] ? $segments : [$text];
  }

  /**
   * Pecah "Ca Oxalate (++) Asam Urat (+++)" — setiap temuan ditutup token grade.
   * Bila ada sisa teks setelah grade terakhir, format dianggap ambigu dan tidak dipecah.
   *
   * @param  list<array{0: string, 1: int}>  $tokens  hasil preg_match_all PREG_OFFSET_CAPTURE
   * @return list<string>
   */
  private static function splitUrinalisaByTrailingGradeTokens(string $text, array $tokens): array
  {
    $segments = [];
    $cursor = 0;

    foreach ($tokens as $token) {
      $end = (int) $token[1] + strlen((string) $token[0]);
      $segment = trim(substr($text, $cursor, $end - $cursor));
      if ($segment === '') {
        return [$text];
      }

      $segments[] = $segment;
      $cursor = $end;
    }

    if (trim(substr($text, $cursor)) !== '') {
      return [$text];
    }

    return $segments;
  }

  /**
   * Pecah hasil Kristal/Silinder/Lain-lain menjadi beberapa temuan.
   * Pemisah: baris baru, " | ", atau beberapa token grade dalam satu string.
   *
   * @return list<string>
   */
  public static function splitUrinalisaDualFindings(?string $hasil): array
  {
    $hasil = trim((string) $hasil);
    if ($hasil === '') {
      return [];
    }

    $chunks = preg_split("/\r\n|\r|\n/", $hasil) ?: [];
    $parts = [];

    foreach ($chunks as $chunk) {
      $chunk = trim((string) $chunk);
      if ($chunk === '') {
        continue;
      }

      foreach (preg_split('/\s+\|\s+/', $chunk) ?: [] as $piece) {
        $piece = trim((string) $piece);
        if ($piece === '') {
          continue;
        }

        foreach (self::splitUrinalisaByGradeTokens($piece) as $finding) {
          $finding = trim((string) $finding);
          if ($finding !== '') {
            $parts[] = $finding;
          }
        }
      }
    }

    return $parts;
  }

  /**
   * Parse satu atau banyak temuan urinalisa dual.
   *
   * @return list<array{positivity: string, detail: string, name: string}>
   */
  public static function parseUrinalisaDualFindings(?string $hasil): array
  {
    $parts = self::splitUrinalisaDualFindings($hasil);
    if ($parts === []) {
      return [self::parseUrinalisaDualResult('')];
    }

    return array_map([self::class, 'parseUrinalisaDualResult'], $parts);
  }

  /**
   * Baris jenis untuk form input (satu grade per jenis).
   * Baris lama tanpa grade mewarisi grade baris sebelumnya.
   *
   * @return list<array{detail: string, name: string}>
   */
  public static function buildUrinalisaJenisRowsForInput(?string $hasil): array
  {
    $findings = self::parseUrinalisaDualFindings($hasil);
    $first = $findings[0] ?? ['positivity' => 'Negatif', 'detail' => '', 'name' => ''];

    if (strcasecmp($first['positivity'] ?? '', 'Negatif') === 0) {
      return [['detail' => '', 'name' => '']];
    }

    $rows = [];
    $lastGrade = '';

    foreach ($findings as $finding) {
      $grade = self::extractUrinalisaGradeToken($finding['detail'] ?? '') ?: '';
      $name = trim((string) ($finding['name'] ?? ''));

      if ($grade === '' && $name === '') {
        $plain = trim((string) ($finding['detail'] ?? ''));
        if ($plain !== '' && self::extractUrinalisaGradeToken($plain) === null) {
          $name = $plain;
        }
      }

      if ($grade !== '') {
        $lastGrade = $grade;
      } elseif ($lastGrade !== '' && $name !== '') {
        $grade = $lastGrade;
      }

      if ($name !== '' || $grade !== '') {
        $rows[] = [
          'detail' => $grade !== '' ? $grade : trim((string) ($finding['detail'] ?? '')),
          'name' => $name,
        ];
      }
    }

    return $rows !== [] ? $rows : [['detail' => '', 'name' => '']];
  }

  /**
   * Data lama menyimpan "(++) Asam Urat"; tampilkan sebagai "Asam Urat (++)".
   * Opsi master seperti "Pos 1 (+)" atau grade polos "(++)" dibiarkan apa adanya.
   */
  public static function reorderUrinalisaGradeAfterName(?string $line): string
  {
    $line = trim((string) $line);
    if ($line === '') {
      return '';
    }

    if (!preg_match('/^\((\+{1,4})\)\s+(.+)$/u', $line, $m)) {
      return $line;
    }

    $name = trim($m[2]);
    if ($name === '' || self::extractUrinalisaGradeToken($name) !== null) {
      return $line;
    }

    return $name . ' (' . $m[1] . ')';
  }

  /**
   * Normalisasi baris temuan untuk tampilan cetak (grade di setiap jenis).
   *
   * @return list<string>
   */
  public static function normalizeUrinalisaFindingLinesForDisplay(?string $value): array
  {
    $parts = self::splitUrinalisaDualFindings($value);
    if ($parts === []) {
      return [];
    }

    if (count($parts) === 1) {
      return [self::reorderUrinalisaGradeAfterName($parts[0])];
    }

    $normalized = [];
    $lastGrade = '';

    foreach ($parts as $part) {
      $parsed = self::parseUrinalisaDualResult($part);
      $grade = self::extractUrinalisaGradeToken($parsed['detail'] ?? '') ?: '';
      $name = trim((string) ($parsed['name'] ?? ''));

      if ($grade === '' && $name === '') {
        $plain = trim((string) ($parsed['detail'] ?? ''));
        if ($plain !== '' && self::extractUrinalisaGradeToken($plain) === null) {
          $name = $plain;
        }
      }

      if ($grade !== '') {
        $lastGrade = $grade;
      } elseif ($lastGrade !== '' && $name !== '') {
        $grade = $lastGrade;
      }

      if ($name !== '') {
        $normalized[] = $grade !== '' ? trim($name . ' ' . $grade) : $name;
      } elseif ($grade !== '') {
        $normalized[] = $grade;
      } elseif ($part !== '') {
        $normalized[] = $part;
      }
    }

    return $normalized !== [] ? $normalized : $parts;
  }

  /**
   * Gabungkan beberapa temuan menjadi string tersimpan (satu baris per temuan).
   * Jika temuan pertama Negatif, hasilnya "Negatif".
   *
   * @param  list<array{positivity?: string, detail?: string, name?: string}>  $rows
   */
  public static function composeUrinalisaDualFindings(array $rows): string
  {
    $composed = [];

    foreach ($rows as $index => $row) {
      $positivity = trim((string) ($row['positivity'] ?? 'Positif'));
      $detail = trim((string) ($row['detail'] ?? ''));
      $name = trim((string) ($row['name'] ?? ''));

      if ($index === 0 && (strcasecmp($positivity, 'Negatif') === 0 || $positivity === '')) {
        return 'Negatif';
      }

      if ($index > 0) {
        $positivity = 'Positif';
      }

      if ($index > 0 && $detail === '' && $name === '') {
        continue;
      }

      $part = self::composeUrinalisaDualResult($positivity, $detail, $name);
      if ($part !== '') {
        $composed[] = $part;
      }
    }

    return implode("\n", $composed);
  }

  /**
   * Tampilkan satu atau banyak temuan urinalisa, ditumpuk vertikal.
   * Setiap jenis menampilkan grade masing-masing.
   */
  public static function formatUrinalisaFindingsHtml(?string $value): string
  {
    $findings = self::normalizeUrinalisaFindingLinesForDisplay($value);
    if (count($findings) <= 1) {
      $single = $findings[0] ?? '';

      return self::toFormatHtml($single !== '' ? $single : $value);
    }

    $lines = array_map(function ($finding) {
      return self::toFormatHtml($finding);
    }, $findings);

    return '<span class="urinalisa-multi-hasil" style="display:inline-block;text-align:left;white-space:nowrap;line-height:1.3;">'
      . implode('<br>', $lines)
      . '</span>';
  }

  /**
   * Tempel tanda abnormal (*) di samping nama jenis pertama, tanpa pindah baris.
   */
  public static function appendAbnormalAsteriskToFirstLine(string $html, string $mark = '&nbsp;*'): string
  {
    $html = (string) $html;
    if ($html === '') {
      return $mark;
    }

    // Cari posisi <br> pertama tanpa regex non-greedy yang bisa mencocokkan string kosong.
    $brPos = self::findFirstBrPosition($html);

    if ($brPos === false) {
      // Tidak ada <br> — cukup tambah mark di akhir (satu baris).
      return $html . $mark;
    }

    [$brStart, $brEnd] = $brPos;
    $before = substr($html, 0, $brStart);
    $brTag  = substr($html, $brStart, $brEnd - $brStart);
    $after  = substr($html, $brEnd);

    // Kasus: html dibuka oleh <span …> (wrapper urinalisa-multi-hasil)
    // "<span …>isi baris 1<br>baris 2</span>"
    // Kita perlu menaruh mark di dalam span, setelah konten baris 1.
    if (preg_match('/^(<span\b[^>]*>)([\s\S]*)$/i', $before, $m)) {
      $spanOpen    = $m[1];
      $firstLine   = $m[2];
      $nowrapSpan  = '<span class="urinalisa-first-jenis" style="white-space:nowrap;">' . $firstLine . $mark . '</span>';

      return $spanOpen . $nowrapSpan . $brTag . $after;
    }

    // Kasus umum: teks biasa atau HTML sebelum <br>.
    return '<span class="urinalisa-first-jenis" style="white-space:nowrap;">'
      . $before . $mark
      . '</span>'
      . $brTag . $after;
  }

  /**
   * Temukan posisi byte awal dan byte akhir <br> pertama dalam $html.
   *
   * @return array{0:int,1:int}|false  [start, end] atau false jika tidak ada.
   */
  private static function findFirstBrPosition(string $html)
  {
    if (preg_match('/(<br\s*\/?>)/i', $html, $m, PREG_OFFSET_CAPTURE)) {
      $start = (int) $m[0][1];
      $end   = $start + strlen($m[0][0]);

      return [$start, $end];
    }

    return false;
  }

  /**
   * Panjang baris hasil terpanjang (karakter) untuk menyesuaikan lebar kolom cetak.
   */
  public static function longestKlinikPrintHasilLineChars(array $arrPermohonanParameter): int
  {
    $max = 0;

    foreach ($arrPermohonanParameter as $group) {
      if (!is_array($group)) {
        continue;
      }

      foreach ($group['item_permohonan_parameter_satuan'] ?? [] as $param) {
        if (!is_array($param)) {
          continue;
        }

        $max = max($max, self::klinikHasilDisplayLineChars($param['hasil_permohonan_uji_parameter_klinik'] ?? ''));

        $subs = $param['data_permohonan_uji_subsatuan_klinik'] ?? [];
        if (isset($subs['id_permohonan_uji_sub_parameter_klinik'])) {
          $subs = [$subs];
        }

        foreach ($subs as $sub) {
          if (!is_array($sub)) {
            continue;
          }
          $max = max($max, self::klinikHasilDisplayLineChars($sub['hasil_permohonan_uji_sub_parameter_klinik'] ?? ''));
        }
      }
    }

    return $max;
  }

  /**
   * Panjang baris tampilan hasil (termasuk jenis urinalisa yang ditumpuk).
   */
  public static function klinikHasilDisplayLineChars($hasil): int
  {
    $findings = self::normalizeUrinalisaFindingLinesForDisplay((string) $hasil);
    if (count($findings) <= 1) {
      $plain = html_entity_decode(strip_tags((string) $hasil), ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $plain = trim(preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', (string) $plain)) ?? '');
      $len = mb_strlen($plain);

      return $len > 0 ? $len + 2 : 0;
    }

    $max = 0;
    foreach ($findings as $line) {
      $max = max($max, mb_strlen(trim((string) $line)));
    }

    return $max + 2;
  }

  /**
   * Lebarkan kolom hasil sesuai isi terpanjang.
   * Ambil sedikit dari satuan, sisanya dari nilai normal.
   *
   * @param  array{hasil: float, satuan: float, nilai_normal?: float}  $widths
   * @return array{hasil: float, satuan: float, nilai_normal: float}
   */
  public static function adjustKlinikPrintHasilSatuanWidths(array $widths, array $arrPermohonanParameter, float $fontSizePt = 12.0): array
  {
    $hasil = (float) ($widths['hasil'] ?? 10);
    $satuan = (float) ($widths['satuan'] ?? 14);
    $nilaiNormal = (float) ($widths['nilai_normal'] ?? 26);

    $minSatuan = 10.0;
    $maxStealSatuan = 4.0;
    $minNilaiNormal = 15.0;

    $stealSatuanBudget = min($maxStealSatuan, max(0.0, $satuan - $minSatuan));
    $stealNilaiBudget = max(0.0, $nilaiNormal - $minNilaiNormal);
    $maxHasil = $hasil + $stealSatuanBudget + $stealNilaiBudget;

    $chars = self::longestKlinikPrintHasilLineChars($arrPermohonanParameter);
    $perChar = max(0.9, min(1.6, $fontSizePt / 10.0));
    $needed = min($maxHasil, max($hasil, 2.5 + ($chars * $perChar)));
    $remain = max(0.0, $needed - $hasil);

    if ($remain > 0) {
      $fromSatuan = min($remain, $stealSatuanBudget);
      $satuan -= $fromSatuan;
      $hasil += $fromSatuan;
      $remain -= $fromSatuan;

      $fromNilai = min($remain, $stealNilaiBudget);
      $nilaiNormal -= $fromNilai;
      $hasil += $fromNilai;
    }

    return [
      'hasil' => round($hasil, 1),
      'satuan' => round($satuan, 1),
      'nilai_normal' => round($nilaiNormal, 1),
    ];
  }

  /**
   * Tampilkan informed consent tindakan medis bila bukan mode dibawa pelanggan.
   * Urine/Feses saja disembunyikan; jika jenis belum diketahui tetap tampil (placeholder di view).
   */
  public static function shouldShowTindakanMedisInformedConsent(?array $jenisSample, $modePengambilanSampel = null): bool
  {
    if ($modePengambilanSampel === 'dibawa_pelanggan') {
      return false;
    }

    $jenisSample = array_values(array_filter($jenisSample ?? []));
    if ($jenisSample === []) {
      return true;
    }

    foreach ($jenisSample as $jenis) {
      $j = trim((string) $jenis);
      if (!in_array($j, ['Urine', 'Feses'], true)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Jenis spesimen yang memerlukan informed consent tindakan medis (bukan Urine/Feses).
   *
   * @return list<string>
   */
  public static function jenisSpesimenTindakanMedisInformedConsent(?array $jenisSample): array
  {
    return array_values(array_filter($jenisSample ?? [], function ($item) {
      return !in_array(trim((string) $item), ['Urine', 'Feses'], true);
    }));
  }

  /**
   * Informed consent tindakan medis — petugas "Yang Menerangkan" selalu pengambil sampel.
   *
   * @return array{label: string, nama: ?string, use_pengambil: bool}
   */
  public static function resolveTindakanMedisConsentPetugas(?string $namaPetugasPengambil): array
  {
    $nama = trim((string) ($namaPetugasPengambil ?? ''));
    if ($nama === '...................') {
      $nama = '';
    }

    return [
      'label' => 'Petugas Pengambil Sampel',
      'nama' => $nama !== '' ? $nama : null,
      'use_pengambil' => true,
    ];
  }

  /**
   * Lembar persetujuan umum (mis. WhatsApp) — petugas registrasi yang menerangkan.
   *
   * @return array{label: string, nama: ?string, use_pengambil: bool}
   */
  public static function resolveInformedConsentPetugas(
    ?array $jenisSample,
    ?string $namaPetugasRegistrasi,
    ?string $namaPetugasPengambil
  ): array {
    $nama = trim((string) ($namaPetugasRegistrasi ?? ''));
    if ($nama === '' || $nama === '...................') {
      $nama = null;
    }

    return [
      'label' => 'Petugas Registrasi',
      'nama' => $nama,
      'use_pengambil' => false,
    ];
  }

  /**
   * Logika nilai normal parameter Lain-lain urinalisa.
   */
  public static function isUrinalisaLainLainNormal(?string $parameterName, ?string $hasil): bool
  {
    if (stripos((string) $parameterName, 'lain-lain') === false && stripos((string) $parameterName, 'lain lain') === false) {
      return false;
    }

    $hasil = trim((string) $hasil);
    return $hasil === '' || strcasecmp($hasil, 'negatif') === 0 || $hasil === '-';
  }

  /**
   * Normalisasi teks untuk bandingkan equal baku mutu (decode &nbsp;, strip tag, lowercase, tanpa spasi).
   */
  public static function normalizeBakuMutuEqualText($value): string
  {
    $s = html_entity_decode(strip_tags((string) ($value ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = str_replace("\xc2\xa0", ' ', $s);
    $s = preg_replace('/\s+/u', '', mb_strtolower(trim($s), 'UTF-8'));

    return $s ?? '';
  }

  /**
   * Cocokkan equal baku mutu, termasuk hasil "(+) NamaJenis" vs equal "(+)" / "Pos 1 (+)".
   * Nama jenis diabaikan; yang dibandingkan adalah grade (+)/(++)/(+++).
   */
  public static function bakuMutuEqualMatches($value, $equal): bool
  {
    $normalizedValue = self::normalizeBakuMutuEqualText($value);
    $normalizedEqual = self::normalizeBakuMutuEqualText($equal);

    if ($normalizedValue !== '' && $normalizedValue === $normalizedEqual) {
      return true;
    }

    $valueGrade = self::extractUrinalisaGradeToken($value);
    $equalGrade = self::extractUrinalisaGradeToken($equal);

    if ($valueGrade !== null && $equalGrade !== null) {
      return $valueGrade === $equalGrade;
    }

    return false;
  }

  /**
   * Satu batch: 2 query mengganti banyak kali {@see self::getJenisSampelFromParameter}.
   * Setiap id input selalu ada di return; tanpa parameter/satuan → [].
   *
   * @param list<string>|array<int, string> $permohonanIds
   * @return array<string, list<string>>
   */
  public static function bulkJenisSampelFromParameter(array $permohonanIds): array
  {
    $permohonanIds = array_values(array_unique($permohonanIds));
    $out = [];
    foreach ($permohonanIds as $id) {
      $out[$id] = [];
    }

    if ($permohonanIds === []) {
      return $out;
    }

    try {
      $params = \Smt\Masterweb\Models\PermohonanUjiParameterKlinik::query()
        ->whereIn('permohonan_uji_klinik', $permohonanIds)
        ->whereNull('deleted_at')
        ->whereNotNull('parameter_satuan_klinik')
        ->get(['permohonan_uji_klinik', 'parameter_satuan_klinik']);

      if ($params->isEmpty()) {
        return $out;
      }

      $satuanIds = $params->pluck('parameter_satuan_klinik')->unique()->filter()->values()->all();

      $selectCols = ['id_parameter_satuan_klinik', 'jenis_sampel'];
      try {
        if (\Illuminate\Support\Facades\Schema::hasColumn('ms_parameter_satuan_klinik', 'jenis_sampel_haji')) {
          $selectCols[] = 'jenis_sampel_haji';
        }
      } catch (\Throwable $e) {
        // ignore
      }

      $satRows = \Smt\Masterweb\Models\ParameterSatuanKlinik::query()
        ->whereIn('id_parameter_satuan_klinik', $satuanIds)
        ->whereNull('deleted_at')
        ->get($selectCols);

      $jenisBySatuanId = [];
      foreach ($satRows as $row) {
        $jenisBySatuanId[$row->id_parameter_satuan_klinik] = $row;
      }

      $hajiFlags = \Smt\Masterweb\Models\PermohonanUjiKlinik2::query()
        ->whereIn('id_permohonan_uji_klinik', $permohonanIds)
        ->pluck('is_haji', 'id_permohonan_uji_klinik');

      $paramsByPu = $params->groupBy('permohonan_uji_klinik');

      foreach ($paramsByPu as $puId => $rows) {
        $isHaji = (int) ($hajiFlags[$puId] ?? 0);
        $rawTypes = [];
        foreach ($rows as $pr) {
          $sid = $pr->parameter_satuan_klinik;
          if ($sid && isset($jenisBySatuanId[$sid])) {
            $rawTypes[] = self::pickJenisSampelRawForContext($jenisBySatuanId[$sid], $isHaji);
          }
        }
        if ($rawTypes !== []) {
          $out[$puId] = self::canonicalJenisListFromParameterRawTypes($rawTypes);
        }
      }
    } catch (\Throwable $e) {
      Log::error('Error bulk calculating jenis sampel: ' . $e->getMessage());
    }

    return $out;
  }

  /**
   * Infer jenis sampel dari nama paket pemeriksaan (fallback bila parameter belum punya jenis_sampel).
   *
   * @return list<string>
   */
  public static function inferJenisSampleFromPaketNames($id_permohonan_uji_klinik): array
  {
    try {
      $pakets = \Smt\Masterweb\Models\PermohonanUjiPaketKlinik::where('permohonan_uji_klinik', $id_permohonan_uji_klinik)
        ->whereNull('deleted_at')
        ->with('parameterpaketklinik')
        ->get();

      $result = [];
      foreach ($pakets as $paket) {
        $name = strtolower((string) optional($paket->parameterpaketklinik)->name_parameter_paket_klinik);
        if ($name === '') {
          continue;
        }
        if (strpos($name, 'darah') !== false || strpos($name, 'hemat') !== false) {
          $result[] = 'Darah';
        } elseif (strpos($name, 'urin') !== false || strpos($name, 'urine') !== false) {
          $result[] = 'Urine';
        } elseif (strpos($name, 'feses') !== false || strpos($name, 'faec') !== false || strpos($name, 'feces') !== false) {
          $result[] = 'Feses';
        } elseif (strpos($name, 'swab') !== false) {
          $result[] = 'Swab';
        }
      }

      return array_values(array_unique($result));
    } catch (\Throwable $e) {
      Log::error('Error inferring jenis sampel from paket: ' . $e->getMessage());
      return [];
    }
  }

  /**
   * Jenis sampel untuk cetak formulir / informed consent.
   * Prioritas: data pengambilan → parameter satuan → nama paket.
   *
   * @param mixed $pengambilanSample
   * @return list<string>
   */
  public static function resolveJenisSampleForPermohonan($id_permohonan_uji_klinik, $pengambilanSample = null): array
  {
    $saved = [];

    if ($pengambilanSample !== null && isset($pengambilanSample->jenis_sample) && $pengambilanSample->jenis_sample !== '') {
      $raw = $pengambilanSample->jenis_sample;
      if (is_array($raw)) {
        $saved = array_values(array_filter($raw));
      } elseif (is_string($raw)) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && !empty($decoded)) {
          $saved = array_values(array_filter($decoded));
        } else {
          $trimmed = trim($raw);
          if ($trimmed !== '' && $trimmed[0] !== '[') {
            $saved = [$trimmed];
          }
        }
      }
    }

    if (!empty($saved)) {
      $canonical = self::canonicalJenisListFromParameterRawTypes($saved);
      if (!empty($canonical)) {
        return $canonical;
      }
      return array_values(array_unique(array_map('trim', $saved)));
    }

    $fromParam = self::getJenisSampelFromParameter($id_permohonan_uji_klinik);
    if (!empty($fromParam)) {
      return $fromParam;
    }

    return self::inferJenisSampleFromPaketNames($id_permohonan_uji_klinik);
  }

  /**
   * Mendapatkan jenis sampel berdasarkan mayoritas dari parameter
   * 
   * @param string $id_permohonan_uji_klinik ID permohonan uji klinik
   * @param array|null $jenis_sampel_saved Jenis sampel yang sudah disimpan (optional)
   * @return array Array jenis sampel yang direkomendasikan
   */
  public static function getJenisSampelFromParameter($id_permohonan_uji_klinik, $jenis_sampel_saved = null)
  {
    // Prioritaskan yang disimpan, jika tidak ada hitung dari parameter
    if (!empty($jenis_sampel_saved) && is_array($jenis_sampel_saved)) {
      return array_values(array_unique($jenis_sampel_saved));
    }

    try {
      $isHaji = (int) \Smt\Masterweb\Models\PermohonanUjiKlinik2::where(
        'id_permohonan_uji_klinik',
        $id_permohonan_uji_klinik
      )->value('is_haji');

      $paramIds = \Smt\Masterweb\Models\PermohonanUjiParameterKlinik::where(
        'permohonan_uji_klinik',
        $id_permohonan_uji_klinik
      )
        ->whereNull('deleted_at')
        ->whereNotNull('parameter_satuan_klinik')
        ->pluck('parameter_satuan_klinik');

      if ($paramIds && count($paramIds) > 0) {
        $selectCols = ['id_parameter_satuan_klinik', 'jenis_sampel'];
        try {
          if (\Illuminate\Support\Facades\Schema::hasColumn('ms_parameter_satuan_klinik', 'jenis_sampel_haji')) {
            $selectCols[] = 'jenis_sampel_haji';
          }
        } catch (\Throwable $e) {
          // ignore
        }

        $rows = \Smt\Masterweb\Models\ParameterSatuanKlinik::whereIn(
          'id_parameter_satuan_klinik',
          $paramIds
        )
          ->whereNull('deleted_at')
          ->get($selectCols);

        $rawTypes = [];
        foreach ($rows as $row) {
          $rawTypes[] = self::pickJenisSampelRawForContext($row, $isHaji);
        }

        return self::canonicalJenisListFromParameterRawTypes($rawTypes);
      }
    } catch (\Throwable $e) {
      // Jika error, return empty array
      Log::error('Error calculating jenis sampel: ' . $e->getMessage());
    }

    return [];
  }

  private static function parseBakuMutuNumber($value): ?float
  {
    if ($value === null || $value === '') {
      return null;
    }

    $s = trim(preg_replace('/\s+/', '', (string) $value));
    if ($s === '' || $s === '-') {
      return null;
    }

    if (strpos($s, ',') !== false && strpos($s, '.') === false) {
      $s = str_replace(',', '.', $s);
    } else {
      $s = str_replace(',', '', $s);
    }

    if (!is_numeric($s)) {
      return null;
    }

    return (float) $s;
  }

  /**
   * Parse hasil yang bisa berupa:
   * - range (mis. "0-1", "3 - 6")
   * - ketidaksamaan (mis. "<1", "> 5", "≤5", ">=10")
   * - angka tunggal
   * Untuk sedimen: abnormal hanya jika batas atas melebihi max baku mutu.
   *
   * @return array{low: float, high: float, is_range: bool, operator: ?string, threshold: ?float}|null
   */
  private static function parseResultRange($value): ?array
  {
    if ($value === null || $value === '' || $value === '-') {
      return null;
    }

    $s = trim(preg_replace('/\s+/', ' ', (string) $value));
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = str_replace(['&lt;', '&gt;', '&le;', '&ge;'], ['<', '>', '≤', '≥'], $s);

    // Inequality: <1, > 5, ≤5, >= 10
    if (preg_match('/^(<=|>=|≤|≥|<|>)\s*([\d.,]+)/u', $s, $m)) {
      $threshold = self::parseBakuMutuNumber($m[2]);
      if ($threshold === null) {
        return null;
      }
      $operator = $m[1];
      if ($operator === '≤') {
        $operator = '<=';
      }
      if ($operator === '≥') {
        $operator = '>=';
      }

      return [
        'low' => $threshold,
        'high' => $threshold,
        'is_range' => true,
        'operator' => $operator,
        'threshold' => $threshold,
      ];
    }

    if (preg_match('/^([\d.,]+)\s*-\s*([\d.,]+)/u', $s, $m)) {
      $low = self::parseBakuMutuNumber($m[1]);
      $high = self::parseBakuMutuNumber($m[2]);
      if ($low !== null && $high !== null) {
        return [
          'low' => $low,
          'high' => $high,
          'is_range' => true,
          'operator' => null,
          'threshold' => null,
        ];
      }
    }

    $n = self::parseBakuMutuNumber($s);
    if ($n !== null) {
      return [
        'low' => $n,
        'high' => $n,
        'is_range' => false,
        'operator' => null,
        'threshold' => null,
      ];
    }

    return null;
  }

  /**
   * Bandingkan hasil vs min/max baku mutu.
   * Range "a-b": normal hanya jika seluruh range di dalam [min, max].
   * "< n" / "≤ n": melewati jika di bawah min atau ambang > max.
   * "> n" / "≥ n": melewati jika ambang >= max.
   * Skalar: cek interval min/max seperti biasa.
   *
   * @return bool|null true = melewati, false = normal, null = tidak bisa dievaluasi
   */
  private static function evaluateBakuMutuRange($value, $min = null, $max = null): ?bool
  {
    $parsed = self::parseResultRange($value);
    if ($parsed === null) {
      return null;
    }

    $hasMin = $min !== null && $min !== '';
    $hasMax = $max !== null && $max !== '';
    $numMin = $hasMin ? self::parseBakuMutuNumber($min) : null;
    $numMax = $hasMax ? self::parseBakuMutuNumber($max) : null;

    if (!empty($parsed['operator'])) {
      $t = $parsed['threshold'];
      $op = $parsed['operator'];

      if ($op === '<' || $op === '<=') {
        $belowMin = false;
        $aboveMax = false;
        if ($hasMin && $numMin !== null) {
          $belowMin = $op === '<' ? ($t <= $numMin) : ($t < $numMin);
        }
        if ($hasMax && $numMax !== null) {
          $aboveMax = $t > $numMax;
        }
        if (!$hasMin && !$hasMax) {
          return null;
        }
        return $belowMin || $aboveMax;
      }

      if ($op === '>' || $op === '>=') {
        if ($hasMax && $numMax !== null) {
          // >5 / ≥5 vs max=5 → abnormal; >4 vs max=5 → normal
          return $t >= $numMax;
        }
        if ($hasMin && $numMin !== null) {
          return false;
        }
        return null;
      }
    }

    // Numeric range "a-b": harus sepenuhnya di dalam [min, max]
    if (!empty($parsed['is_range'])) {
      if ($hasMin && $hasMax && $numMin !== null && $numMax !== null) {
        [$numMin, $numMax] = self::alignBakuMutuBounds($parsed['high'], $numMin, $numMax);
        return ($parsed['low'] < $numMin || $parsed['high'] > $numMax);
      }
      if ($hasMax && $numMax !== null) {
        $numMax = self::alignBakuMutuBound($parsed['high'], $numMax);
        return $parsed['high'] > $numMax;
      }
      if ($hasMin && $numMin !== null) {
        $numMin = self::alignBakuMutuBound($parsed['low'], $numMin);
        return ($parsed['low'] < $numMin || $parsed['high'] < $numMin);
      }
      return null;
    }

    $numValue = $parsed['low'];
    if ($hasMin && $hasMax && $numMin !== null && $numMax !== null) {
      [$numMin, $numMax] = self::alignBakuMutuBounds($numValue, $numMin, $numMax);
      return ($numValue < $numMin || $numValue > $numMax);
    }
    if ($hasMin && $numMin !== null) {
      $numMin = self::alignBakuMutuBound($numValue, $numMin);
      return $numValue < $numMin;
    }
    if ($hasMax && $numMax !== null) {
      $numMax = self::alignBakuMutuBound($numValue, $numMax);
      return $numValue > $numMax;
    }

    return null;
  }

  /**
   * Selaraskan min/max yang disimpan ter-skala (mis. 1005-1030 untuk 1.005-1.030).
   */
  private static function alignBakuMutuBounds(?float $numValue, ?float $numMin, ?float $numMax): array
  {
    if ($numValue === null || $numMin === null || $numMax === null) {
      return [$numMin, $numMax];
    }

    if ($numValue > 0 && $numValue < 100 && $numMin > $numValue && $numMax > $numValue) {
      foreach ([1000, 100, 10] as $divisor) {
        $scaledMin = $numMin / $divisor;
        $scaledMax = $numMax / $divisor;
        if ($scaledMin > $scaledMax) {
          continue;
        }
        if ($scaledMax >= ($numValue / 10) && $scaledMin <= ($numValue * 10)) {
          return [$scaledMin, $scaledMax];
        }
      }
    }

    return [$numMin, $numMax];
  }

  private static function alignBakuMutuBound(?float $numValue, ?float $bound): ?float
  {
    if ($numValue === null || $bound === null) {
      return $bound;
    }

    if ($numValue > 0 && $numValue < 100 && $bound > (100 * $numValue)) {
      foreach ([1000, 100, 10] as $divisor) {
        $scaled = $bound / $divisor;
        if ($scaled >= ($numValue / 10) && $scaled <= ($numValue * 10)) {
          return $scaled;
        }
      }
    }

    return $bound;
  }

  /**
   * Check if result exceeds baku mutu (quality standard)
   * This is PHP equivalent of JavaScript checkBakuMutu function
   * 
   * @param string $value Result value to check
   * @param float|null $min Minimum allowed value
   * @param float|null $max Maximum allowed value
   * @param string|null $equal Exact value that must match
   * @param string $offset_baku_mutu Manual override: 'default', 'true' (melewati), 'false' (tidak melewati)
   * @param array|null $multipleBakuMutu Array of multiple baku mutu objects
   * @param string $kesimpulanBakuMutu Conclusion text to display
   * @param int|null $pasienUmur Patient age for baku mutu selection
   * @param string|null $pasienGender Patient gender for baku mutu selection
   * @param string|null $parameterName Parameter name for special rules (e.g. urinalisa lain-lain)
   * @param int|null $isNormal 1 = batas normal (mis. &lt; max), 0 = batas non-normal (mis. &le; max)
   * @return string HTML badge string (empty if no value)
   */
  public static function normalizeOffsetBakuMutu($offset): string
  {
    if ($offset === true) {
      return 'true';
    }
    if ($offset === false) {
      return 'false';
    }

    $v = strtolower(trim((string) ($offset ?? 'default')));
    if (in_array($v, ['true', '1', 'yes'], true)) {
      return 'true';
    }
    if (in_array($v, ['false', 'no'], true)) {
      return 'false';
    }

    return 'default';
  }

  public static function hasKlinikHasilValue($value): bool
  {
    if ($value === null || $value === false) {
      return false;
    }

    $plain = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain = preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', (string) $plain));
    $plain = trim((string) $plain);

    return $plain !== '' && $plain !== '-';
  }

  public static function checkBakuMutu($value, $min = null, $max = null, $equal = null, $offset_baku_mutu = 'default', $multipleBakuMutu = null, $kesimpulanBakuMutu = '', $pasienUmur = null, $pasienGender = null, $parameterName = null, $isNormal = null)
  {
    if (!self::hasKlinikHasilValue($value) && $value !== 0 && $value !== '0') {
      return '';
    }

    // Manual override harus menang sebelum aturan khusus (urinalisa lain-lain, min/max).
    $offset_baku_mutu = self::normalizeOffsetBakuMutu($offset_baku_mutu);
    if ($offset_baku_mutu === 'false') {
      return self::createResultBadgeHtml(self::formatUrinalisaFindingsHtml($value), 'success');
    }
    if ($offset_baku_mutu === 'true') {
      return self::createResultBadgeHtml(self::formatUrinalisaFindingsHtml($value), 'danger');
    }

    $findings = self::splitUrinalisaDualFindings((string) $value);
    if (count($findings) > 1) {
      $anyDanger = false;
      foreach ($findings as $finding) {
        $subBadge = self::checkBakuMutu(
          $finding,
          $min,
          $max,
          $equal,
          $offset_baku_mutu,
          $multipleBakuMutu,
          $kesimpulanBakuMutu,
          $pasienUmur,
          $pasienGender,
          $parameterName,
          $isNormal
        );
        if (
          strpos($subBadge, 'badge-danger') !== false
          || strpos($subBadge, 'hasil-melewati-baku-mutu') !== false
        ) {
          $anyDanger = true;
        }
      }

      return self::createResultBadgeHtml(
        self::formatUrinalisaFindingsHtml($value),
        $anyDanger ? 'danger' : 'success',
        $kesimpulanBakuMutu
      );
    }

    if ($parameterName !== null && self::isUrinalisaLainLainNormal($parameterName, $value)) {
      return self::createResultBadgeHtml(self::formatUrinalisaFindingsHtml($value), 'success', $kesimpulanBakuMutu);
    }

    if ($parameterName !== null && (stripos($parameterName, 'lain-lain') !== false || stripos($parameterName, 'lain lain') !== false)) {
      return self::createResultBadgeHtml(self::formatUrinalisaFindingsHtml($value), 'danger', $kesimpulanBakuMutu);
    }

    $melewati = false;
    $hasMultipleBakuMutu = is_array($multipleBakuMutu) && count($multipleBakuMutu) > 1;
    $isOutsideNormalRange = false;
    $kesimpulanBakuMutu = $kesimpulanBakuMutu ?? '';

    // Hasil multi-baris: baris tambahan adalah catatan, jadi baku mutu dinilai
    // dari baris yang mengandung nilainya ($value tetap dipakai untuk tampilan).
    $evalValue = self::klinikHasilEvaluationValue($value);

    // Default: Check automatically based on min/max/equal
    $numValue = null;
      $isNormalFlag = $isNormal !== null
        ? (int) $isNormal
        : (int) (is_array($multipleBakuMutu) && count($multipleBakuMutu) === 1
          ? ($multipleBakuMutu[0]['is_normal'] ?? 0)
          : 0);

      // Prioritas: cocokkan ke SEMUA baris baku mutu bila >1 (Negatif + Pos 1 (+), dll)
      if ($hasMultipleBakuMutu) {
        $specificBakuMutu = array_values(array_filter($multipleBakuMutu, function ($bm) use ($pasienGender, $pasienUmur) {
          $hasGenderFilter = !empty($bm['gender_baku_mutu']);
          $hasUmurFilter = isset($bm['minimal_umur_baku_mutu']) && $bm['minimal_umur_baku_mutu'] !== ''
            && isset($bm['maksimal_umur_baku_mutu']) && $bm['maksimal_umur_baku_mutu'] !== '';

          if (!$hasGenderFilter && !$hasUmurFilter) {
            return false;
          }

          $genderOk = !$hasGenderFilter || $bm['gender_baku_mutu'] === $pasienGender;
          $umurOk = !$hasUmurFilter
            || ($pasienUmur >= floatval($bm['minimal_umur_baku_mutu']) && $pasienUmur <= floatval($bm['maksimal_umur_baku_mutu']));

          return $genderOk && $umurOk;
        }));

        // Baris terpilih via filter gender/umur = rentang normal spesifik pasien
        // (mis. baku mutu haji L: 13-16, P: 12-14). Rentang ini menandakan normal,
        // meski kolom is_normal-nya 0 (data haji sering tidak menandai is_normal=1).
        // Hanya jika filter gender/umur mempersempit kandidat (mis. haji L vs P),
        // bukan ketika semua baris punya rentang umur yang sama (mis. HbA1c 18–99).
        $usedDemographicSpecific = count($specificBakuMutu) > 0
          && count($specificBakuMutu) < count($multipleBakuMutu);
        $candidateBakuMutu = $usedDemographicSpecific ? $specificBakuMutu : $multipleBakuMutu;
        $rangeParsed = self::parseResultRange($evalValue);
        $numValue = $rangeParsed !== null ? $rangeParsed['high'] : self::parseBakuMutuNumber($evalValue);
        $isHasilRange = $rangeParsed !== null && !empty($rangeParsed['is_range']);
        $normalizedValue = self::normalizeBakuMutuEqualText($evalValue);

        $matchedBakuMutu = null;
        // Utamakan match ke baris is_normal=1 bila beberapa equal cocok
        $candidatesOrdered = $candidateBakuMutu;
        usort($candidatesOrdered, function ($a, $b) {
          return ((int) ($b['is_normal'] ?? 0)) <=> ((int) ($a['is_normal'] ?? 0));
        });

        foreach ($candidatesOrdered as $bm) {
          $isWithinThisRange = false;

          if (!empty($bm['equal'])) {
            $isWithinThisRange = self::bakuMutuEqualMatches($evalValue, $bm['equal']);
          }

          if (!$isWithinThisRange && $isHasilRange) {
            // Hasil range: cocok jika batas atas tidak melebihi max baris baku mutu
            $bmMin = !empty($bm['min']) ? self::parseBakuMutuNumber($bm['min']) : null;
            $bmMax = !empty($bm['max']) ? self::parseBakuMutuNumber($bm['max']) : null;
            $eval = self::evaluateBakuMutuRange($evalValue, $bm['min'] ?? null, $bm['max'] ?? null);
            // evaluate returns melewati; within = not melewati
            if ($eval !== null) {
              $isWithinThisRange = !$eval;
            } elseif ($bmMin === null && $bmMax === null) {
              $isWithinThisRange = false;
            }
          } elseif (!$isWithinThisRange && $numValue !== null) {
            $bmMin = !empty($bm['min']) ? self::parseBakuMutuNumber($bm['min']) : null;
            $bmMax = !empty($bm['max']) ? self::parseBakuMutuNumber($bm['max']) : null;

            if ($bmMin !== null && $bmMax !== null) {
              [$bmMin, $bmMax] = self::alignBakuMutuBounds($numValue, $bmMin, $bmMax);
              if ($bmMin == $bmMax) {
                // Ambang batas bawah saja, mis. Diabetes >= 6.5 (min=max di master).
                $isWithinThisRange = ($numValue >= $bmMin);
              } else {
                $isWithinThisRange = ($numValue >= $bmMin && $numValue <= $bmMax);
              }
            } elseif ($bmMin !== null) {
              $bmMin = self::alignBakuMutuBound($numValue, $bmMin);
              $isWithinThisRange = ($numValue >= $bmMin);
            } elseif ($bmMax !== null) {
              $bmMax = self::alignBakuMutuBound($numValue, $bmMax);
              $isWithinThisRange = ($numValue <= $bmMax);
            }
          }

          if ($isWithinThisRange) {
            $matchedBakuMutu = $bm;
            break;
          }
        }

        if ($matchedBakuMutu) {
          $matchedHasEqual = !empty($matchedBakuMutu['equal']);
          $matchedIsRange = !$matchedHasEqual && (
            (isset($matchedBakuMutu['min']) && $matchedBakuMutu['min'] !== '' && $matchedBakuMutu['min'] !== null)
            || (isset($matchedBakuMutu['max']) && $matchedBakuMutu['max'] !== '' && $matchedBakuMutu['max'] !== null)
          );

          if ($usedDemographicSpecific && $matchedIsRange) {
            // Rentang normal per gender/umur pasien: nilai di dalam rentang = normal,
            // bukan klasifikasi bertingkat, jadi abaikan flag is_normal.
            $melewati = false;
          } else {
            $melewati = !isset($matchedBakuMutu['is_normal']) || (int) $matchedBakuMutu['is_normal'] !== 1;
          }

          if (!empty($matchedBakuMutu['kesimpulan_baku_mutu'])) {
            $kesimpulanBakuMutu = $matchedBakuMutu['kesimpulan_baku_mutu'];
          }
        } else {
          $melewati = true;
        }

        $isOutsideNormalRange = $melewati;
      } elseif (!empty($equal)) {
        $melewati = !self::bakuMutuEqualMatches($evalValue, $equal);
      } else {
        $rangeResult = self::parseResultRange($evalValue);
        $isHasilRange = $rangeResult !== null && !empty($rangeResult['is_range']);

        if ($isHasilRange) {
          // Hasil tipe range (mis. "0-1"): abnormal hanya jika batas atas > max baku mutu
          $eval = self::evaluateBakuMutuRange($evalValue, $min, $max);
          if ($eval !== null) {
            $melewati = $eval;
          }
        } elseif (!empty($min) && !empty($max)) {
          $numValue = self::parseBakuMutuNumber($evalValue);
          $numMin = self::parseBakuMutuNumber($min);
          $numMax = self::parseBakuMutuNumber($max);
          if ($numValue !== null && $numMin !== null && $numMax !== null) {
            [$numMin, $numMax] = self::alignBakuMutuBounds($numValue, $numMin, $numMax);
            $melewati = ($numValue < $numMin || $numValue > $numMax);
          }
        } else if (!empty($min)) {
          $numValue = self::parseBakuMutuNumber($evalValue);
          $numMin = self::alignBakuMutuBound($numValue, self::parseBakuMutuNumber($min));
          if ($numValue !== null && $numMin !== null) {
            $melewati = $isNormalFlag === 1
              ? ($numValue <= $numMin)
              : ($numValue < $numMin);
          }
        } else if (!empty($max)) {
          $numValue = self::parseBakuMutuNumber($evalValue);
          $numMax = self::alignBakuMutuBound($numValue, self::parseBakuMutuNumber($max));
          if ($numValue !== null && $numMax !== null) {
            $melewati = $isNormalFlag === 1
              ? ($numValue >= $numMax)
              : ($numValue > $numMax);
          }
        }
      }

      // Jika belum di-set kesimpulan dan tidak ada multiple baku mutu, gunakan dari parameter
      if (!$hasMultipleBakuMutu && empty($kesimpulanBakuMutu)) {
        $kesimpulanBakuMutu = $kesimpulanBakuMutu ?? '';
      }

      $status = $melewati ? 'danger' : 'success';
      $formattedValue = self::toFormatHtml($value ?? '');
      $badge = self::createResultBadgeHtml($formattedValue, $status, $kesimpulanBakuMutu);
      
      // Clean up any "undefined" strings in badge HTML
      if ($badge) {
        $badge = str_replace('undefined', '', $badge);
      }

      // Hapus notifikasi "Di luar semua range normal" - tidak perlu ditampilkan

      // Final cleanup: remove any "undefined" strings before returning
      if ($badge) {
        $badge = str_replace('undefined', '', $badge);
      }

    return $badge;
  }

  /**
   * Format value for display (convert ^() to HTML)
   * 
   * @param string $value Value to format
   * @return string Formatted HTML string
   */
  public static function toFormatHtml($value)
  {
    if ($value === null || $value === false) {
      return '';
    }

    $value = (string) $value;
    if ($value === '') {
      return '';
    }

    // TinyMCE sering menyimpan Shift+Enter / multi-baris sebagai blok HTML
    // (<p>...</p> atau <div>...</div>). Untuk hasil klinik, blok tersebut harus
    // dibaca sebagai pindah baris, bukan dirapatkan jadi satu kalimat.
    // Paragraf/div kosong = baris kosong (jangan dibuang)
    $value = preg_replace('/<p[^>]*>(?:\s|&nbsp;|<br[^>]*>)*<\/p>/iu', '<br>', $value);
    $value = preg_replace('/<div[^>]*>(?:\s|&nbsp;|<br[^>]*>)*<\/div>/iu', '<br>', $value);
    $value = preg_replace('/<\/p>\s*<p[^>]*>/iu', '<br>', $value);
    $value = preg_replace('/<\/div>\s*<div[^>]*>/iu', '<br>', $value);
    $value = preg_replace('/<p[^>]*>/iu', '', $value);
    $value = preg_replace('/<\/p>/iu', '', $value);
    $value = preg_replace('/<div[^>]*>/iu', '', $value);
    $value = preg_replace('/<\/div>/iu', '', $value);

    // Data lama kadang menyimpan operator "≥" menjadi "?" (encoding mismatch).
    $value = preg_replace('/(^|[\s,(;])\?\s*(?=\d)/u', '$1≥ ', $value);
    
    // Convert Unicode superscript characters to <sup> tags FIRST
    // This handles characters like ³, ², ¹, etc.
    $value = str_replace('¹', '<sup>1</sup>', $value);
    $value = str_replace('²', '<sup>2</sup>', $value);
    $value = str_replace('³', '<sup>3</sup>', $value);
    $value = str_replace('⁴', '<sup>4</sup>', $value);
    $value = str_replace('⁵', '<sup>5</sup>', $value);
    $value = str_replace('⁶', '<sup>6</sup>', $value);
    $value = str_replace('⁷', '<sup>7</sup>', $value);
    $value = str_replace('⁸', '<sup>8</sup>', $value);
    $value = str_replace('⁹', '<sup>9</sup>', $value);
    $value = str_replace('⁰', '<sup>0</sup>', $value);
    
    $value = preg_replace('/\^\(([^\)]*)\)/', '<sup>$1</sup>', $value);
    $value = preg_replace('/\_\(([^\)]*)\)/', '<sub>$1</sub>', $value);

    // DomPDF + Arial tidak punya glyph ≥/≤ — wrap DejaVu + numeric entity
    $dv = "font-family: 'dejavu sans', sans-serif;";
    $value = str_replace('≥', '<span style="' . $dv . '">&#8805;</span>', $value);
    $value = str_replace('≤', '<span style="' . $dv . '">&#8804;</span>', $value);
    $value = str_replace('&ge;', '<span style="' . $dv . '">&#8805;</span>', $value);
    $value = str_replace('&le;', '<span style="' . $dv . '">&#8804;</span>', $value);
    $value = str_replace('±', '&plusmn;', $value);
    $value = preg_replace("/\r\n|\r|\n/", '<br>', $value);
    $value = preg_replace('/^(?:<br\s*\/?>\s*)+/i', '', $value);
    $value = preg_replace('/(?:<br\s*\/?>\s*)+$/i', '', $value);

    return self::wrapKlinikPrintMultilineHasil(trim($value));
  }

  /**
   * Bungkus hasil multi-baris agar <br> dirender benar di DomPDF.
   */
  public static function isKlinikPrintMultilineHasilHtml(string $html): bool
  {
    return (bool) preg_match('/<table\b[^>]*class="[^"]*\bhasil-multi-line\b/i', $html);
  }

  public static function wrapKlinikPrintMultilineHasil(string $html): string
  {
    $html = trim($html);
    if ($html === '' || stripos($html, '<br') === false) {
      return $html;
    }

    if (self::isKlinikPrintMultilineHasilHtml($html)) {
      return $html;
    }

    if (preg_match('/^<span\b[^>]*class="[^"]*\bhasil-multi-line\b[^"]*"/i', $html)) {
      return $html;
    }

    // Gunakan <table> satu-kolom agar DomPDF memastikan setiap <br> benar-benar
    // pindah baris, terlepas dari CSS `display:inline` yang diwarisi dari td:nth-child(2) *.
    // Tabel kosong padding/border agar tidak mengubah tampilan tabel luar.
    $lines = preg_split('/<br\s*\/?>/i', $html);
    $normalizedLines = [];
    foreach ($lines as $part) {
      $part = trim((string) $part);
      $plain = trim(preg_replace('/<[^>]*>/', '', html_entity_decode($part, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
      $normalizedLines[] = [
        'html' => $part,
        'empty' => ($plain === '' && $part === ''),
      ];
    }

    while (count($normalizedLines) > 1 && $normalizedLines[count($normalizedLines) - 1]['empty']) {
      array_pop($normalizedLines);
    }

    if (count($normalizedLines) <= 1) {
      return $html;
    }

    $rows = '';
    foreach ($normalizedLines as $line) {
      $cell = $line['empty'] ? '&nbsp;' : $line['html'];
      $rows .= '<tr><td style="padding:0;margin:0;border:none;text-align:center;line-height:1.3;">' . $cell . '</td></tr>';
    }

    return '<table class="hasil-multi-line" style="margin:0 auto;padding:0;border:none;border-collapse:collapse;width:auto;">'
      . $rows
      . '</table>';
  }

  /**
   * Tambahkan tanda abnormal (*) di dalam sel pertama tabel hasil multi-baris.
   */
  public static function appendAbnormalAsteriskToFirstTableCell(string $html, string $mark = '&nbsp;*'): string
  {
    return preg_replace(
      '/(<tr[^>]*>\s*<td[^>]*>)(.*?)(<\/td>)/is',
      '$1<span style="white-space:nowrap;">$2' . $mark . '</span>$3',
      $html,
      1
    ) ?? ($html . $mark);
  }

  /**
   * Apakah hasil mengandung pemisah baris (Shift+Enter / paragraf TinyMCE).
   */
  public static function hasKlinikHasilLineBreaks($value): bool
  {
    $value = (string) $value;
    if ($value === '') {
      return false;
    }

    return (bool) preg_match('/<br\s*\/?>/i', $value)
      || (bool) preg_match('/<\/?(p|div)\b/i', $value)
      || self::isKlinikPrintMultilineHasilHtml($value)
      || (bool) preg_match("/\r\n|\r|\n/", $value);
  }

  /**
   * Nilai yang dipakai untuk mengevaluasi baku mutu pada hasil multi-baris.
   * Baris tambahan biasanya catatan, jadi ambil baris pertama yang bisa dinilai
   * (angka / range) agar hasil seperti "1.100 + catatan" tetap dicek.
   */
  public static function klinikHasilEvaluationValue($value)
  {
    if ($value === null || $value === '' || !self::hasKlinikHasilLineBreaks($value)) {
      return $value;
    }

    $normalized = preg_replace('/<\/(p|div)>\s*<(p|div)[^>]*>/iu', '<br>', (string) $value);
    $normalized = preg_replace('/<\/?(p|div)[^>]*>/iu', '<br>', $normalized);
    $normalized = preg_replace("/\r\n|\r|\n/", '<br>', $normalized);

    $lines = preg_split('/<br\s*\/?>/i', $normalized);
    $firstLine = null;

    foreach ($lines as $line) {
      $line = trim((string) $line);
      if ($line === '' || trim(strip_tags($line)) === '') {
        continue;
      }

      if ($firstLine === null) {
        $firstLine = $line;
      }

      if (self::parseBakuMutuNumber($line) !== null || self::parseResultRange($line) !== null) {
        return $line;
      }
    }

    return $firstLine !== null ? $firstLine : $value;
  }

  /**
   * Create result badge HTML based on status
   * 
   * @param string $value Formatted value to display
   * @param string $status 'success' or 'danger'
   * @param string $kesimpulanBakuMutu Conclusion text
   * @return string HTML badge string
   */
  public static function createResultBadgeHtml($value, $status, $kesimpulanBakuMutu = '')
  {
    $kesimpulan = trim((string) ($kesimpulanBakuMutu ?? ''));
    $kesimpulanEsc = $kesimpulan !== '' ? htmlspecialchars($kesimpulan, ENT_QUOTES, 'UTF-8') : '';

    if ($status === 'danger') {
      $kesimpulanHtml = $kesimpulanEsc !== ''
        ? '<br><small class="bm-kesimpulan-hasil" style="font-size: 12px; font-weight: normal; opacity: 0.95;">' . $kesimpulanEsc . '</small>'
        : '';

      return '<span class="badge badge-danger hasil-melewati-baku-mutu" style="font-size: 14px; padding: 8px 12px; font-weight: 700; white-space: normal; text-align: left; display: inline-block; line-height: 1.35;">'
        . '<strong>' . self::appendAbnormalAsteriskToFirstLine($value, '<span class="bintang-baku-mutu"> *</span>') . '</strong>'
        . $kesimpulanHtml
        . '</span>';
    }

    $kesimpulanHtml = $kesimpulanEsc !== ''
      ? ' <small style="font-size: 12px; font-weight: normal; opacity: 0.95;">' . $kesimpulanEsc . '</small>'
      : '';

    return '<span class="badge badge-success font-weight-bold" style="font-size: 14px; padding: 8px 12px; white-space: normal; text-align: left; display: inline-block; line-height: 1.35;">'
      . '<i class="fa fa-check-circle mr-1"></i>'
      . $value
      . $kesimpulanHtml
      . '</span>';
  }

  /**
   * Bangun konteks cetak sub-parameter dari array permohonan + konteks parent opsional.
   */
  public static function buildKlinikPrintContextFromSubItem(array $itemSub, array $parentContext = []): array
  {
    // Jangan memakai multiple_baku_mutu milik parameter induk untuk menilai
    // sub-parameter. Contoh: hasil Leukosit "3-6" harus dibandingkan dengan
    // detail Leukosit 0-5, bukan min/max baku mutu parameter Sedimen Urine.
    // Hanya gunakan multiple range bila memang disiapkan khusus untuk detail.
    $multipleBakuMutu = $itemSub['multiple_normal_baku_mutu'] ?? null;
    $hasMultipleBakuMutu = is_array($multipleBakuMutu)
      && count($multipleBakuMutu) > 1;

    return [
      'min' => $itemSub['min_baku_mutu_detail_parameter_klinik'] ?? null,
      'max' => $itemSub['max_baku_mutu_detail_parameter_klinik'] ?? null,
      'equal' => $itemSub['equal_baku_mutu_detail_parameter_klinik'] ?? null,
      'offset_baku_mutu' => $itemSub['offset_baku_mutu'] ?? 'default',
      'kesimpulan_baku_mutu' => $itemSub['kesimpulan_baku_mutu'] ?? ($parentContext['kesimpulan_baku_mutu'] ?? ''),
      'nama_parameter_satuan_klinik' => $parentContext['nama_parameter_satuan_klinik'] ?? ($itemSub['nama_parameter_sub_satuan_klinik_id'] ?? null),
      'number_format' => $parentContext['number_format'] ?? ($itemSub['number_format'] ?? 'en'),
      'pasien_umur' => $parentContext['pasien_umur'] ?? null,
      'pasien_gender' => $parentContext['pasien_gender'] ?? null,
      'is_normal' => $itemSub['is_normal'] ?? ($parentContext['is_normal'] ?? null),
      'has_multiple_baku_mutu' => $hasMultipleBakuMutu,
      'multiple_baku_mutu' => $hasMultipleBakuMutu ? $multipleBakuMutu : null,
    ];
  }

  /**
   * Format hasil sub-parameter untuk cetak PDF (selaras dengan checkBakuMutu di halaman analis).
   */
  public static function formatHasilSubForKlinikPrint($value, array $itemSub, array $parentContext = []): string
  {
    return self::formatHasilForKlinikPrint(
      $value,
      self::buildKlinikPrintContextFromSubItem($itemSub, $parentContext)
    );
  }

  /**
   * Format hasil untuk cetak PDF: tebal + asterisk jika melewati baku mutu.
   * Memakai logika yang sama dengan checkBakuMutu (halaman analis).
   */
  public static function formatHasilForKlinikPrint($value, array $context = []): string
  {
    if (!self::hasKlinikHasilValue($value) && $value !== 0 && $value !== '0') {
      return '';
    }

    $offset = self::normalizeOffsetBakuMutu($context['offset_baku_mutu'] ?? 'default');
    if ($offset === 'true') {
      return '<strong>' . self::appendAbnormalAsteriskToFirstLine(self::formatUrinalisaFindingsHtml($value)) . '</strong>';
    }
    if ($offset === 'false') {
      return (string) self::formatUrinalisaFindingsHtml($value);
    }

    $multipleBakuMutu = null;
    if (
      !empty($context['has_multiple_baku_mutu'])
      && isset($context['multiple_baku_mutu'])
      && is_array($context['multiple_baku_mutu'])
      && count($context['multiple_baku_mutu']) > 1
    ) {
      $multipleBakuMutu = $context['multiple_baku_mutu'];
    }

    $pasienGender = $context['pasien_gender'] ?? null;
    if ($pasienGender !== null) {
      $pasienGender = BakuMutuPermohonanKlinikHelper::normalizePasienGender($pasienGender);
    }

    $badge = self::checkBakuMutu(
      $value,
      $context['min'] ?? null,
      $context['max'] ?? null,
      $context['equal'] ?? null,
      $context['offset_baku_mutu'] ?? 'default',
      $multipleBakuMutu,
      $context['kesimpulan_baku_mutu'] ?? '',
      $context['pasien_umur'] ?? null,
      $pasienGender,
      $context['nama_parameter_satuan_klinik'] ?? null,
      $context['is_normal'] ?? null
    );

    if ($badge === '') {
      return (string) self::formatUrinalisaFindingsHtml($value);
    }

    $formatted = self::formatUrinalisaFindingsHtml($value);
    $isMulti = count(self::splitUrinalisaDualFindings((string) $value)) > 1;
    $isMultiLine = $isMulti
      || self::hasKlinikHasilLineBreaks((string) $value)
      || self::isKlinikPrintMultilineHasilHtml($formatted);

    if (strpos($badge, 'badge-danger') !== false || strpos($badge, 'hasil-melewati-baku-mutu') !== false) {
      if (self::isKlinikPrintMultilineHasilHtml($formatted)) {
        return '<strong>' . self::appendAbnormalAsteriskToFirstTableCell($formatted) . '</strong>';
      }

      if ($isMultiLine) {
        $wrapped = self::wrapKlinikPrintMultilineHasil($formatted);
        if (self::isKlinikPrintMultilineHasilHtml($wrapped)) {
          return '<strong>' . self::appendAbnormalAsteriskToFirstTableCell($wrapped) . '</strong>';
        }

        return '<strong>' . self::appendAbnormalAsteriskToFirstLine($wrapped) . '</strong>';
      }

      return '<strong style="white-space:nowrap;">' . self::appendAbnormalAsteriskToFirstLine($formatted) . '</strong>';
    }

    return (string) $formatted;
  }

  /**
   * Teks disclaimer BSrE untuk footer halaman terakhir PDF hasil klinik.
   */
  public static function bsreElektronikDisclaimerText(): string
  {
    return 'Dokumen ini ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara';
  }

  /**
   * Gambar disclaimer BSrE hanya di halaman terakhir PDF (tidak di alur HTML,
   * jadi tidak membuat halaman baru saat konten mepet).
   *
   * @param  \Barryvdh\DomPDF\PDF|\Dompdf\Dompdf  $pdf
   * @return void
   */
  public static function attachBsreLastPageFooter($pdf): void
  {
    $dompdf = method_exists($pdf, 'getDomPDF') ? $pdf->getDomPDF() : $pdf;
    if (!is_object($dompdf) || !method_exists($dompdf, 'setCallbacks')) {
      return;
    }

    $line1 = 'Dokumen ini ditandatangani secara elektronik menggunakan Sertifikat Elektronik yang diterbitkan oleh';
    $line2 = 'Balai Sertifikasi Elektronik (BSrE) Badan Siber dan Sandi Negara';

    $dompdf->setCallbacks([
      [
        'event' => 'end_document',
        'f' => function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($line1, $line2) {
          if ((int) $pageNumber !== (int) $pageCount) {
            return;
          }

          try {
            $font = $fontMetrics->getFont('Helvetica', 'italic');
          } catch (\Throwable $e) {
            $font = $fontMetrics->getFont('Helvetica', 'normal');
          }
          if (!$font) {
            return;
          }

          $size = 7.5;
          $x = 42;
          $h = $canvas->get_height();
          $canvas->text($x, $h - 42, $line1, $font, $size);
          $canvas->text($x, $h - 32, $line2, $font, $size);
        },
      ],
    ]);
  }

  /**
   * Stream PDF inline dengan footer BSrE di halaman terakhir saja.
   *
   * @param  \Barryvdh\DomPDF\PDF  $pdf
   * @param  string  $filename
   * @param  array  $extraHeaders
   * @return \Illuminate\Http\Response
   */
  public static function streamPdfWithBsreLastPageFooter($pdf, $filename = 'document.pdf', array $extraHeaders = [])
  {
    self::attachBsreLastPageFooter($pdf);

    $output = $pdf->output();
    $headers = array_merge([
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="' . $filename . '"',
      'Content-Length' => strlen($output),
    ], $extraHeaders);

    return new \Illuminate\Http\Response($output, 200, $headers);
  }

}
