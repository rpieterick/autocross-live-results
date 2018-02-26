<?php

namespace App\Libraries;

class Data
{
    public static function classToGroup($class, $grouping, $options)
    {
        if ($grouping == 'Overall' and !in_array('Ladies', $options)) {
            return $grouping;
        }

        $class = strtolower(trim($class));
        $append = '';
        $idx_id = Timing::idxClassId($class);
        if ($idx_id != '') {
            $class = substr($class, strlen($idx_id));
            $idx_mod = Timing::idxClassMod($class);
            if ($idx_mod != '') {
                $class = substr($class, 0, -strlen($idx_mod));
            }
        }

        if (ends_with($class, 'l')) {
            $class = substr($class, 0, -1);
            if (in_array('Ladies', $options)) {
                $append = ' Ladies';
            }
        }

        if ($grouping == 'Overall') {
            $group = $grouping.$append;
        } else {
            if (\Config::has('timing.category.'.$class)) {
                $group = config('timing.category.'.$class).$append;
            } else {
                $group = "Unknown";
            }
        }

        return $group;
    }
    
    protected function groupPlace($group, $grouplist)
    {
        $offset = 0;
        if (ends_with($group, ' Ladies')) {
            $group = substr($group, 0, -7);
            $offset = 100;
        }

        $place = array_search($group, $grouplist);
        if ($place !== false) {
            $place += $offset;
        } else {
            $place = 999;
        }

        return $place;
    }
    
    public static function order($engine, $grouping, $criteria = null)
    {
        if (is_null($criteria)) {
            $criteria = $engine->request->orderableColumns();
        }

        if ($criteria) {
            if ($grouping == 'Overall') {
                $grouplist = ['Overall'];
            } else {
                $grouplist = array_unique(array_values(config('timing.category')));
            }

            $comparer = function ($a, $b) use ($criteria, $engine, $grouplist) {
                $tp = new Data;
                foreach ($criteria as $orderable) {
                    $column = $engine->getColumnNameByIndex($orderable['column']);
                    if (array_key_exists($column, $a) and array_key_exists($column, $b)) {
                        $direction = $orderable['direction'];
                        if ($direction === 'desc') {
                            $first = $b;
                            $second = $a;
                        } else {
                            $first = $a;
                            $second = $b;
                        }

                        if ($column == 'group') {
                            $first['group'] = $tp->groupPlace($first['group'], $grouplist);
                            $second['group'] = $tp->groupPlace($second['group'], $grouplist);
                        }

                        if ($engine->isCaseInsensitive()) {
                            $cmp = strnatcasecmp($first[$column], $second[$column]);
                        } else {
                            $cmp = strnatcmp($first[$column], $second[$column]);
                        }

                        if ($cmp != 0) {
                            return $cmp;
                        }
                    }
                }

                return 0;
            };

            $engine->collection = $engine->collection->sort($comparer);
        }
    }
    
    public static function unmap($collection, $runs, $grouping, $options, $sort)
    {
        if ($grouping != 'Class' and $sort == 'Pax' and in_array('PaxRuns', $options)) {
            $keep = 'runpax';
            $discard = 'run';
        } else {
            $keep = 'run';
            $discard = 'runpax';
        }
        
        return $collection->map(function ($item, $key) use ($runs, $keep, $discard) {
            for ($i = ($runs + 1); $i <= 10; $i++) {
                unset($item[$keep.$i]);
            }

            for ($i = 1; $i <= 10; $i++) {
                unset($item[$discard.$i]);
            }

            return $item;
        });
    }
    
    public static function reGroup($collection, $grouping, $options)
    {
        return $collection->filter(function ($item, $key) {
            return ($item['class'] != '' or $item['number'] != '');
        })->map(function ($item, $key) use ($grouping, $options) {
            $item['group'] = self::classToGroup($item['class'], $grouping, $options);
            return $item;
        });
    }
    
    public static function reIndex($data, $order, $runs)
    {
        $count = $data->collection->count();
        $dir = $order[0]['dir'];

        if (isset($order[1])) {
            $grpcol = $order[1]['column'];
        } else {
            $grpcol = 2;
        }

        if ($grpcol == $runs + 9) {
            $tkey = 'best_raw';
        } elseif ($grpcol == $runs + 11) {
            $tkey = 'best_pax';
        } else {
            $tkey = 'total';
        }

        $i = 0;
        $prevrow = [];
        $data->editColumn('rank', function ($row) use ($count, $dir, $grpcol, &$i, &$prevrow, $tkey) {
            if ($prevrow and $row['group'] != $prevrow['group']) {
                $i = 0;
            }

            if ($dir == 'desc') {
                if ($i != 0 and $row[$tkey] == $prevrow[$tkey]) {
                    $rank = $prevrow['rank'];
                } else {
                    $rank = $count - $i;
                }
            } else {
                if ($i != 0 and $row[$tkey] == $prevrow[$tkey]) {
                    $rank = $prevrow['rank'];
                } else {
                    $rank = $i + 1;
                }
            }

            $i++;
            $prevrow = $row;
            $prevrow['rank'] = $rank;
            return "$rank";
        });

        $lastrow = [];
        $data->editColumn('difference', function ($row) use ($dir, $grpcol, &$lastrow, $tkey) {
            if ($lastrow and $row['group'] != $lastrow['group']) {
                $lastrow = [];
            }

            if ($dir == 'desc') {
                if ($lastrow and is_numeric($lastrow[$tkey]) and is_numeric($row[$tkey])) {
                    $difference = number_format($row[$tkey] - $lastrow[$tkey], 3, '.', '');
                } else {
                    $difference = '';
                }
            } else {
                if ($lastrow and is_numeric($lastrow[$tkey]) and is_numeric($row[$tkey])) {
                    $difference = "+".number_format($row[$tkey] - $lastrow[$tkey], 3, '.', '');
                } else {
                    $difference = '';
                }
            }

            $lastrow = $row;
            return "$difference";
        });

        $firstrow = [];
        $data->editColumn('from_first', function ($row) use ($dir, $grpcol, &$firstrow, $tkey) {
            if ($firstrow and $row['group'] != $firstrow['group']) {
                $firstrow = [];
            }

            if ($dir == 'desc') {
                if ($firstrow and is_numeric($firstrow[$tkey]) and is_numeric($row[$tkey])) {
                    $from_first = number_format($row[$tkey] - $firstrow[$tkey], 3, '.', '');
                } else {
                    $from_first = '';
                }
            } else {
                if ($firstrow and is_numeric($firstrow[$tkey]) and is_numeric($row[$tkey])) {
                    $from_first = "+".number_format($row[$tkey] - $firstrow[$tkey], 3, '.', '');
                } else {
                    $from_first = '';
                }
            }

            if (!$firstrow) {
                $firstrow = $row;
            }

            return "$from_first";
        });
    }

    public static function reIndexCollection($collection, $criteria, $runs)
    {
        $count = $collection->count();
        $dir = $criteria[0]['direction'];

        if (isset($criteria[1])) {
            $grpcol = $criteria[1]['column'];
        } else {
            $grpcol = 2;
        }

        if ($grpcol == 29) {
            $tkey = 'best_raw';
        } elseif ($grpcol == 31) {
            $tkey = 'best_pax';
        } else {
            $tkey = 'total';
        }

        $i = 0;
        $firstrow = [];
        $lastrow = [];
        $prevrow = [];

        return $collection->map(
            function ($row, $key) use ($count, $dir, $grpcol, &$i, &$firstrow, &$lastrow, &$prevrow, $tkey) {
                if ($firstrow and $row['group'] != $firstrow['group']) {
                    $firstrow = [];
                }

                if ($dir == 'desc') {
                    if ($firstrow and is_numeric($firstrow[$tkey]) and is_numeric($row[$tkey])) {
                        $from_first = number_format($row[$tkey] - $firstrow[$tkey], 3, '.', '');
                    } else {
                        $from_first = '';
                    }
                } else {
                    if ($firstrow and is_numeric($firstrow[$tkey]) and is_numeric($row[$tkey])) {
                        $from_first = "+".number_format($row[$tkey] - $firstrow[$tkey], 3, '.', '');
                    } else {
                        $from_first = '';
                    }
                }

                if (!$firstrow) {
                    $firstrow = $row;
                }

                $row['from_first'] = $from_first;
                if ($lastrow and $row['group'] != $lastrow['group']) {
                    $lastrow = [];
                }

                if ($dir == 'desc') {
                    if ($lastrow and is_numeric($lastrow[$tkey]) and is_numeric($row[$tkey])) {
                        $difference = number_format($row[$tkey] - $lastrow[$tkey], 3, '.', '');
                    } else {
                        $difference = '';
                    }
                } else {
                    if ($lastrow and is_numeric($lastrow[$tkey]) and is_numeric($row[$tkey])) {
                        $difference = "+".number_format($row[$tkey] - $lastrow[$tkey], 3, '.', '');
                    } else {
                        $difference = '';
                    }
                }

                $lastrow = $row;
                $row['difference'] = $difference;
                if ($prevrow and $row['group'] != $prevrow['group']) {
                    $i = 0;
                }

                if ($dir == 'desc') {
                    $rank = $count - $i;
                } else {
                    if ($i != 0 and $row[$tkey] == $prevrow[$tkey]) {
                        $rank = $prevrow['rank'];
                    } else {
                        $rank = $i + 1;
                    }
                }

                $i++;
                $prevrow = $row;
                $prevrow['rank'] = $rank;
                $row['rank'] = $rank;

                return $row;
            }
        );
    }
}
