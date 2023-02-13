<?php

namespace App\Util;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;

class DataTable {
    private $builder;
    private $whitelist = [];
    private $max_once = 200;
    
    private static $SORT_ASC = 0;
    private static $SORT_DESC = 1;
    
    public function __construct(Builder $builder) {
        $this->builder = $builder;
    }
    
    public function setWhitelist($whitelist) {
        $this->whitelist = $whitelist;
        return $this;
    }
    
    public function setMaxOnce($maxOnce) {
        $this->max_once = $maxOnce;
        return $this;
    }
    
    /**
     * Tells us that we should render this now and print it to the api endpoint
     */
    public function toJson() {
        $count = $this->builder->count();
        $params = $this->verifyParameters();
        
        $sortedBuilder = $this->builder;
        if(isset($params['sort'])) {
            foreach($params['sort'] as $sortBy) {
                if($sortBy[1] == static::$SORT_ASC) {
                    $sortedBuilder = $sortedBuilder->orderBy($sortBy[0], "ASC");
                } else if($sortBy[1] == static::$SORT_DESC) {
                    $sortedBuilder = $sortedBuilder->orderBy($sortBy[0], "DESC");
                }
            }
        }
        $data = $sortedBuilder->limit($params['length'])->skip($params['start'])->get();

        return Response::json([
            "data" => $data,
            "count" => $count,
        ]);
    }
    
    /**
     * This function performs a validation of the parameters given
     */
    private function verifyParameters() {
        $config = request()->validate([
            'length' => 'required|numeric|integer|min:1|max:'. $this->max_once,
            'start' => 'required|numeric|integer',
            'sort' => 'array',
            'sort.*' => 'array|min:2|max:2',
            'sort.*.0' => [\Illuminate\Validation\Rule::in($this->whitelist)],
            'sort.*.1' => 'min:0|max:1',
            /*
            'columns' => 'required|array',
            'columns.*.searchable' => ['required', new \App\Rules\BooleanText],
            'columns.*.orderable' => ['required', new \App\Rules\BooleanText],
            'columns.*.search.value' => '', //string ??
            'columns.*.search.regex' => ['required', new \App\Rules\BooleanText],
            'columns.*.name' => ['nullable', \Illuminate\Validation\Rule::in($whitelistColumns)],
            'columns.*.data' => ['required', \Illuminate\Validation\Rule::in($whitelistColumns)],
            'order' => 'array',
            'order.*.column' => 'required|integer',
            'order.*.dir' => ['required', \Illuminate\Validation\Rule::in(['asc', 'desc'])],
            'search.value' => 'string|nullable',
            'search.regex' => [new \App\Rules\BooleanText],
             */
        ]);
        
            /*
        $colKeys = validator(array_keys($dat['columns']), [
            '*' => 'numeric|integer',
        ])->validate();
        
        request()->validate([
            'order.*.column' => ['required', 'integer', \Illuminate\Validation\Rule::in($colKeys)]
        ]);
             */
        return $config;
    }
    
    public static function generate(Builder $builder) {
        return new DataTable($builder);
    }
}
