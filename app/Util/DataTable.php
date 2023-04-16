<?php

namespace App\Util;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;

class DataTable {
    private $builder;
    private $whitelist = [];
    private $searchWhitelist = [];
    private $max_once = 200;
    private $prepHook = null;
    private $filter = null;
    
    private static $SORT_ASC = 0;
    private static $SORT_DESC = 1;
    
    public function __construct(Builder $builder) {
        $this->builder = $builder;
    }
    
    public function setSearchWhitelist($searchWhitelist) {
        $this->searchWhitelist = $searchWhitelist;
        return $this;
    }
    
    public function setWhitelist($whitelist) {
        $this->whitelist = $whitelist;
        return $this;
    }
    
    public function setMaxOnce($maxOnce) {
        $this->max_once = $maxOnce;
        return $this;
    }
    
    public function prepareHook(callable $prepHook) {
        $this->prepHook = $prepHook;
        return $this;
    }
    
    public function setFilter(callable $filter) {
        $this->filter = $filter;
        return $this;
    }
    
    /**
     * Tells us that we should render this now and print it to the api endpoint
     */
    public function toJson(callable $conversionFunction=null) {
        $count = $this->builder->count();
        $params = $this->verifyParameters();
        
        $filteredBuilder = $this->builder;
        $filteredCount = $count;
        if(isset($params['search'])) {
            $search = BasicFunctions::likeSaveEscape($params['search']);
            $searchWL = $this->searchWhitelist;
            $filteredBuilder = $filteredBuilder->where(function($query) use($searchWL, $search) {
                foreach($searchWL as $s) {
                    $query = $query->orWhere($s, 'LIKE', "%$search%");
                }
                return $query;
            });
            $filteredCount = -1;
        }
        if($this->filter !== null) {
            ($this->filter)($filteredBuilder);
            $filteredCount = -1;
        }
        if($filteredCount == -1) {
            $filteredCount = $filteredBuilder->count();
        }
        
        $sortedBuilder = $filteredBuilder;
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
        
        if($this->prepHook !== null) {
            ($this->prepHook)($data);
        }
        
        if($conversionFunction !== null) {
            $dataNew = [];
            foreach($data as $d) {
                $dataNew[] = $conversionFunction($d);
            }
            $data = $dataNew;
        }

        return Response::json([
            "data" => $data,
            "count" => $count,
            "filtered" => $filteredCount,
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
            'search' => 'string|nullable',
        ]);
        return $config;
    }
    
    public static function generate(Builder $builder) {
        return new DataTable($builder);
    }
}
