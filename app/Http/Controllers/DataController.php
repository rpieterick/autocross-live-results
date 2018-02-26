<?php

namespace App\Http\Controllers;

use App\Libraries\Data as Data;
use App\Libraries\Helpers;
use App\Libraries\Timing as Timing;
use Illuminate\Http\Request;

class DataController extends Controller
{
    const COLUMNS = ['Car', 'Color', 'Runs', 'Index', 'Pax', 'Diff', '-1st', 'PaxP'];
    const GROUPINGS = ['Category', 'Class', 'Overall'];
    const OPTIONS = ['GrpRow', 'Ladies', 'PaxRuns', 'Search'];
    const SORTS = ['Pax', 'Raw'];

    use Helpers;

    private function getGrouping($request)
    {
        return $this->getValid(
            $request,
            'Class',
            'grouping',
            self::GROUPINGS
        );
    }

    private function getOptions($request, $grouping)
    {
        $options = ['GrpRow'];
        if ($grouping == 'Category') {
            $options[] = 'Ladies';
        }

        if ($request->has('options')) {
            $options = explode(' ', $request->input('options'));
        } elseif (isset($_COOKIE[$grouping.'-options'])) {
            $options = json_decode($_COOKIE[$grouping.'-options']);
        }

        $valid = self::OPTIONS;
        $options = array_filter($options, function ($val) use ($valid) {
            return self::inArray($val, $valid);
        });

        $options = array_map(function ($val) use ($valid) {
            $pos = self::arraySearch($val, $valid);
            if ($pos !== false) {
                return $valid[$pos];
            } else {
                return $val;
            }
        }, $options);

        return $options;
    }

    private function getSort($request, $grouping)
    {
        return $this->getValid(
            $request,
            'Raw',
            'sort',
            self::SORTS,
            $grouping.'-'
        );
    }

    private function getSource($request)
    {
        return $this->getValid(
            $request,
            array_keys(config('timing.source', ["localhost" => ""]))[0],
            'source',
            array_keys(config('timing.source', ["localhost" => ""]))
        );
    }

    private function getValid($request, $default, $key, $valid, $grouping = "")
    {
        $val = $default;
        if ($request->has($key) and self::inArray($request->input($key), $valid)) {
            $pos = self::arraySearch($request->input($key), $valid);
            $val = $valid[$pos];
        } elseif (isset($_COOKIE[$grouping.$key]) and self::inArray($_COOKIE[$grouping.$key], $valid)) {
            $pos = self::arraySearch($_COOKIE[$grouping.$key], $valid);
            $val = $valid[$pos];
        }

        return $val;
    }

    public function index(Request $request, $class = '')
    {
        \Log::debug('begin index() '.microtime());
        $group = strtolower(trim($class));
        $class = strtolower(trim($class));
        $idx_id = Timing::idxClassId($class);
        $src = $this->getSource($request);
        $results = Timing::results($src);
        $summary = $results['summary'];
        $grouping = $this->getGrouping($request);
        $options = $this->getOptions($request, $grouping);
        $sort = $this->getSort($request, $grouping);

        if ($request->wantsJson()) {
            ob_start('ob_gzhandler');
            \Log::debug('get results '.microtime());
            $collection = collect($results['drivers']);
            
            if ($grouping != 'Class') {
                \Log::debug('reGroup '.microtime());
                $collection = Data::reGroup($collection, $grouping, $options);
            }

            if ($request->path() != '/') {
                if (starts_with($request->path(), 'class/')) {
                    if ($grouping == 'Class') {
                        \Log::debug('filter by class '.microtime());
                        if ($idx_id != '') {
                            $collection = $collection->filter(
                                function ($row, $key) use ($idx_id) {
                                    return Timing::idxClassId($row['class']) == $idx_id;
                                }
                            );
                        } else {
                            $collection = $collection->where('class', $class);
                        }
                    }
                } elseif (starts_with($request->path(), 'group/')) {
                    if ($grouping == 'Category' or in_array('Ladies', $options)) {
                        \Log::debug('filter by group '.microtime());
                        $collection = $collection->filter(
                            function ($row, $key) use ($group) {
                                return strtolower($row['group']) == $group;
                            }
                        );
                    }
                }
            }

            \Log::debug('unmap '.microtime());
            $collection = Data::unmap($collection, $summary['runs'], $grouping, $options, $sort);

            if ($grouping == 'Category' or in_array('Ladies', $options)) {
                \Log::debug('construct and order '.microtime());
                $data = \Datatables::of($collection)->order(
                    function ($engine) use ($grouping) {
                        Data::order($engine, $grouping);
                    }
                );
            } else {
                \Log::debug('construct '.microtime());
                $data = \Datatables::of($collection);
            }

            if ($grouping != 'Class') {
                \Log::debug('reIndex '.microtime());
                Data::reIndex($data, $request->input('order'), $summary['runs']);
            }

            \Log::debug('edit columns '.microtime());
            $data->editColumn(
                'name',
                function ($row) use ($grouping, $sort, $src) {
                    if (strlen($row['name']) > 0) {
                        if (strlen($row['class']) > 0) {
                            $url = '/class/'.rawurlencode($row['class']);
                        } else {
                            $url = '/class/'.rawurlencode(' ');
                        }

                        $url .= '/number/'.rawurlencode($row['number']).'?name='.urlencode($row['name']);
                        $url .= '&grouping='.$grouping.'&source='.urlencode($src).'&sort='.$sort;
                        return '<a class="driver" href="'.$url.'" onclick="return false;">'.$row['name'].'</a>';
                    } else {
                        return $row['name'];
                    }
                }
            );

            $rawcols = ['class', 'name'];
            for ($i=1; $i <= $summary['runs']; $i++) {
                $rawcols[] = 'run'.$i;
            }
            $data->rawColumns($rawcols);

            \Log::debug('make json '.microtime());
            $json = $data->with('summary', $summary)->make();

            \Log::debug('return json '.microtime());
            return $json;
        } else {
            if (starts_with($request->path(), 'group/')) {
                if ((($group == 'overall' or $group == 'overall ladies') and $grouping != 'Overall')
                    or (($group != 'overall' and $group != 'overall ladies') and $grouping == 'Overall')
                ) {
                    $querystr = '?grouping='.$grouping;
                    $querystr .= '&source='.urlencode($src);
                    $querystr .= '&options='.implode('+', $options);
                    if ($grouping != 'Class') {
                        $querystr .= '&sort='.$sort;
                    }

                    return redirect('/'.$querystr);
                }
            }

            $requrl = $request->url();
            if ($request->path() == '/') {
                $requrl .= '/';
            }

            return view('timing.index', [
                'COLUMNS' => self::COLUMNS,
                'GROUPINGS' => self::GROUPINGS,
                'OPTIONS' => self::OPTIONS,
                'SORTS' => self::SORTS,
                'class' => $class,
                'request' => $request,
                'requrl' => $requrl,
                'summary' => $summary
            ]);
        }
    }

    public function show(Request $request, $class = '')
    {
        return $this->index($request, $class);
    }
    
    public function showDriver(Request $request, $class, $number)
    {
        \Log::debug('begin show_driver() '.microtime());
        $class = strtolower(trim($class));
        $number = trim($number);
        $grouping = $this->getGrouping($request);
        $options = $this->getOptions($request, $grouping);
        $sort = $this->getSort($request, $grouping);

        \Log::debug('get results '.microtime());
        $src = $this->getSource($request);
        $results = Timing::results($src);
        $collection = collect($results['drivers']);
        $summary = $results['summary'];
        if ($sort == 'Pax') {
            $criteria = [
                ['column' => 2, 'direction' => 'asc'],
                ['column' => 31, 'direction' => 'asc']
            ];
        } else {
            $criteria = [
                ['column' => 2, 'direction' => 'asc'],
                ['column' => 29, 'direction' => 'asc']
            ];
        }

        if ($grouping != 'Class') {
            \Log::debug('reGroup '.microtime());
            $collection = Data::reGroup($collection, $grouping, $options);
        }

        if ($grouping != 'Class') {
            \Log::debug('construct and order '.microtime());
            $data = \Datatables::of($collection)->order(
                function ($engine) use ($criteria, $grouping) {
                    Data::order($engine, $grouping, $criteria);
                }
            );
        } else {
            \Log::debug('construct '.microtime());
            $data = \Datatables::of($collection);
        }

        $data->make();
        if ($grouping != 'Class') {
            \Log::debug('reIndex '.microtime());
            $collection = Data::reIndexCollection($data->collection, $criteria, $summary['runs']);
        } else {
            $collection = $data->collection;
        }

        \Log::debug('filter '.microtime());
        if ($request->has('name')) {
            $name = strtolower(trim($request->input('name')));
            $items = $collection->filter(
                function ($item, $key) use ($class, $number, $name) {
                    return (
                        $item['class'] == $class
                        and $item['number'] == $number
                        and strtolower($item['name']) == $name
                    );
                }
            );
        } else {
            $items = $collection->filter(
                function ($item, $key) use ($class, $number) {
                    return $item['class'] == $class and $item['number'] == $number;
                }
            );
        }

        \Log::debug('first '.microtime());
        $item = $items->first();
        $idx_id = Timing::idxClassId($item['class']);
        $requrl = $request->url();
        if ($request->path() == '/') {
            $requrl .= '/';
        }

        if ($item) {
            \Log::debug('return view '.microtime());
            return view('timing.driver', [
                'grouping' => $grouping,
                'idx_id' => $idx_id,
                'item' => $item,
                'request' => $request,
                'requrl' => $requrl,
                'options' => $options,
                'sort' => $sort,
                'src' => $src,
                'summary' => $summary
            ]);
        } else {
            $item = ['class' => $class, 'number' => $number, 'name' => 'No Data'];
            if ($request->has('name')) {
                $item['name'] = trim($request->input('name'));
            }
            return view('timing.nodata', [
                'grouping' => $grouping,
                'idx_id' => $idx_id,
                'item' => $item,
                'request' => $request,
                'requrl' => $requrl,
                'options' => $options,
                'sort' => $sort,
                'src' => $src,
                'summary' => $summary
            ]);
        }
    }
}
