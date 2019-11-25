<?php
/**
 * Created by PhpStorm.
 * User: satoshi
 * Date: 2019/8/27
 * Time: 9:27
 */

namespace Arabeila\Tools\Services;

use Maatwebsite\Excel\Facades\Excel;

/**
 * 分类导入模板
 * @example $service = new CatalogService();
 *           $data = $service->build();
 *           Excel 文件模板 标题 请使用 0 1 2 3
 *           参考 storage/catalogs.xlsx
 * Class CatalogService
 * @package Arabeila\Tools\Services
 */
class CatalogService
{
    protected $results = [];

    public function build($file)
    {
        Excel::load($file, function ($reader) {
            $data = $reader->get()->toArray();

            $index = 1;
            $parentId = null;
            $name = null;

            $temp = [];

            $count = count($data[0]);

            for ($i = 0; $i < $count; $i++) {
                for ($j = 0; $j < count($data); $j++) {
                    $row = $data[$j];

                    if ($i == 0) {
                        $parentId = 0;
                    } else {
                        $parentName = $row[$i - 1];

                        $parent = current(collect($temp)->filter(function ($item) use ($parentName) {
                            return $item['name'] == $parentName;
                        })->toArray());

                        $parentId = $parent['id'];
                    }

                    $name = $row[$i];

                    if (collect($temp)->filter(function ($item) use ($name) {
                        return $item['name'] == $name;
                    })->values()->isEmpty()) {
                        array_push($temp, [
                            'id'           => $index++,
                            'name'         => $name,
                            'parent_id'    => $parentId,
                            'is_directory' => $i === ($count - 1) ? 0 : 1,
                            'level'        => $i,
                            'path'         => isset($parent) ? $parent['path'].$index.'-' : '-',
                            'is_show'      => 1,
                            'sort'         => 0,
                        ]);
                    }
                }
            }

            $this->results = $temp;
        });

        return $this->results;
    }
}
