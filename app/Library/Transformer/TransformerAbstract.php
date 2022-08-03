<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 8/11/17
 * Time: 4:29 PM.
 */

namespace App\Library\Transformer;

use Illuminate\Database\Eloquent\Collection;

abstract class TransformerAbstract
{
    protected $data;

    public function __construct($format = null)
    {
        $this->format = $format;

        return $this;
    }

    public function transform($data = null)
    {
        if ($data != null) {
            $this->data = $data;
        }
        if ($this->data instanceof Collection) {
            if ($this->format == null || ! method_exists($this, $this->format)) {
                return $this->data->map([$this, 'transformModel']);
            } else {
                return $this->data->map([$this, $this->format]);
            }
        }

        if ($this->format == null || method_exists($this, $this->format) && $this->data != null) {
            return $this->{$this->format}($this->data);
        }

        if ($this->data != null) {
            return $this->transformModel($this->data);
        }

        return false;
    }

    abstract public function transformModel($model);
}
