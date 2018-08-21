<?php

namespace Illuminate\Database\Eloquent {

    class Model
    {
        /**
         * Add a basic where clause to the query.
         * @param string|array|\Closure $column
         * @param mixed                 $operator
         * @param mixed                 $value
         * @param string                $boolean
         * @return self
         */
        public static function where($column, $operator = null, $value = null, $boolean = 'and')
        {
            return \Illuminate\Database\Eloquent\Builder::where($column, $operator, $value, $boolean);
        }
    }
}
