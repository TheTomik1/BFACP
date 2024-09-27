<?php

namespace BFACP\Battlefield;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Overall.
 */
class Emblem extends Model
{
    /**
     * Should model handle timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'bfacp_emblems';

    /**
     * Table primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Date fields to convert to carbon instances.
     *
     * @var array
     */
    protected $dates = [];
}
