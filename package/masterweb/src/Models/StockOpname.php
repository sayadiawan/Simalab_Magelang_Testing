<?php

namespace Smt\Masterweb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Smt\Masterweb\Models\Product;
use Smt\Masterweb\Traits\Uuid;

class StockOpname extends Model
{
    use SoftDeletes;
    use Uuid;

    protected $table = "tb_stockopname";
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $primaryKey = 'id_stockopname';

    /**
     * Get the user associated with the StockOpname
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id_product', 'product_stockopname');
    }
}
