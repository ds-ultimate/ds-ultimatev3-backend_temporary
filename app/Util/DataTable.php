<?php

namespace App\Util;

use Illuminate\Database\Eloquent\Builder as EqBuilder;
use Illuminate\Database\Query\Builder as DBBuilder;
use Illuminate\Support\Facades\Response;

class DataTable {
    private $data;
    private $builder;
    private $whitelist = [];
    private $searchWhitelist = [];
    private $max_once = 200;
    private $prepHook = null;
    private $filter = null;
    private $clientSide = false;
    
    private static $SORT_ASC = 0;
    private static $SORT_DESC = 1;
    
    public function __construct(EqBuilder|DBBuilder|null $builder, array|null $data) {
        $this->builder = $builder;
        $this->data = $data;
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
    
    public function clientSide() {
        $this->clientSide = true;
        return $this;
    }
    
    /**
     * Tells us that we should render this now and print it to the api endpoint
     */
    public function toJson(callable $conversionFunction=null) {
        //TODO this should maybe a throw Exception?
        abort_if($this->data !== null && !$this->clientSide, 500);
        
        if($this->clientSide) {
            return $this->clientReturn($conversionFunction);
        } else {
            return $this->serverReturn($conversionFunction);
        }
    }
    
    private function clientReturn(callable $conversionFunction=null) {
        if($this->data !== null) {
            $data = $this->data;
        } else {
            $data = $this->builder->get();
        }
        
        if($this->prepHook !== null) {
            ($this->prepHook)($data);
        }
        
        if($conversionFunction !== null) {
            $dataNew = [];
            $idx = 0;
            foreach($data as $d) {
                $dataNew[] = $conversionFunction($d, $idx++);
            }
            $data = $dataNew;
        }

        return Response::json([
            "data" => $data,
            "count" => count($data),
        ]);
    }
    
    private function serverReturn(callable $conversionFunction=null) {
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
            $idx = $params['start'];
            foreach($data as $d) {
                $dataNew[] = $conversionFunction($d, $idx++);
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
    
    public static function generate(EqBuilder|DBBuilder $builder) {
        return new DataTable($builder, null);
    }
    
    public static function of(array $data) {
        return new Datatable(null, $data);
    }
}
