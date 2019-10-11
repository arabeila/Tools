<?php
/**
 * Created by PhpStorm.
 * User: satoshi
 * Date: 2019/8/27
 * Time: 9:27
 */

namespace Arabeila\Tools\Services;

use Maatwebsite\Excel\Facades\Excel;

class CategoryService
{
    protected $firstKey = 'first';

    protected $secondKey = 'second';

    protected $thirdKey = 'third';

    protected $titles = [
        'first'  => '一级类目',
        'second' => '二级类目',
        'third'  => '三级类目',
    ];

    protected $results = [];

    public function __construct($titles = [])
    {
        $this->titles = array_merge($this->titles, $titles);
    }

    public function build($file)
    {
        Excel::load($file, function ($reader) {
            $data = $reader->get()->toArray();

            $first = [];

            $second = [];

            foreach ($data as $datum) {
                if (isset($first[$datum[$this->titles[$this->firstKey]]])) {
                    if (!in_array($datum[$this->titles[$this->secondKey]], $first[$datum[$this->titles[$this->firstKey]]])) {
                        array_push($first[$datum[$this->titles[$this->firstKey]]], $datum[$this->titles[$this->secondKey]]);
                    }
                } else {
                    $first[$datum[$this->titles[$this->firstKey]]] = [
                        $datum[$this->titles[$this->secondKey]],
                    ];
                }
            }

            foreach ($data as $datum) {
                if (isset($second[$datum[$this->titles[$this->secondKey]]])) {
                    array_push($second[$datum[$this->titles[$this->secondKey]]], $datum[$this->titles[$this->thirdKey]]);
                } else {
                    $second[$datum[$this->titles[$this->secondKey]]] = [
                        $datum[$this->titles[$this->thirdKey]],
                    ];
                }
            }

            $this->results = [];

            foreach ($first as $key => $value) {
                $children = [];

                foreach ($value as $item) {
                    $child = [];

                    foreach ($second[$item] as $tmp) {
                        $child[] = [
                            'name' => $tmp,
                        ];
                    }

                    $children[] = [
                        'name'     => $item,
                        'children' => $child,
                    ];
                }

                $this->results[] = [
                    'name'     => $key,
                    'children' => $children,
                ];
            }
        });

        return $this->results;
    }
}
