<?php

namespace App\Http\Controllers\Api;

use App\Models\NojsLogger;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Api\ServiceCallsController;

class NojsLoggersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'time_local' => 'required',
            'nojs' => 'required',
            // 'eh1' => 'required',
            // 'eh2' => 'required',
            // 'vsat_curr' => 'required',
            // 'bts_curr' => 'required',
            // 'load3' => 'required|string',
            // 'batt_volt1' => 'required',
            // 'batt_volt2' => 'required',
            // 'edl1' => 'required',
            // 'edl2' => 'required',
            // 'pms_state' => 'required',
        ]);
        $dataLogger = NojsLogger::create($request->all());
        $this->ceckService($request);
        return response($dataLogger, 201);
    }

    public function loggers(Request $request)
    {
        $nojs = $request->nojs;
        $limit = $request->limit;
        $sdate = $request->sdate;
        $edate = $request->edate;
        $calculate = $request->calculate;
        $processing = $request->processing;
        $single = $request->single;

        if (($nojs && $limit) && !$sdate) {
            if ($calculate === "true") {
                if ($single === "true") {
                    $datas = NojsLogger::where('nojs', $nojs)
                        ->orderBy('time_local', 'desc')
                        ->limit($limit + 36)
                        ->get();
                    $data = $this->dataCalculate($datas);
                    (count($data) === 0)  ?  $data = [] : $data = [$data[0]];
                } else {
                    $datas = NojsLogger::where('nojs', $nojs)
                        ->orderBy('time_local', 'desc')
                        ->limit($limit + 1)
                        ->get();
                    $data = $this->dataCalculate($datas);
                }
            } else {
                $datas = NojsLogger::where('nojs', $nojs)
                    ->orderBy('time_local', 'desc')
                    ->limit($limit)
                    ->get();
                $data = $datas;
            }
        } elseif ($sdate && $edate && !$nojs) {
            $datas = NojsLogger::whereBetween('time_local', [$sdate, $edate])
                ->orderBy('time_local', 'desc')
                ->get();
            $data = $datas;
        } elseif ($sdate && $edate && $nojs) {
            if ($calculate === "true") {
                if ($single === "true") {
                    $newsdate = (new Carbon($sdate))->subHours(3)->format('Y-m-d H:i:s');
                    $datas = NojsLogger::where('nojs', $nojs)
                        ->whereBetween('time_local', [$newsdate, $edate])
                        ->orderBy('time_local', 'desc')
                        ->get();
                    $data = $this->dataCalculate($datas);
                    (count($data) === 0)  ?  $data = [] : $data = [$data[0]];
                } else {
                    $newsdate = (new Carbon($sdate))->subMinutes(5)->format('Y-m-d H:i:s');
                    $datas = NojsLogger::where('nojs', $nojs)
                        ->whereBetween('time_local', [$newsdate, $edate])
                        ->orderBy('time_local', 'desc')
                        ->get();
                    $data = $this->dataCalculate($datas);
                }
            } else {
                $datas = NojsLogger::where('nojs', $nojs)
                    ->whereBetween('time_local', [$sdate, $edate])
                    ->orderBy('time_local', 'desc')
                    ->get();
                $data = $datas;
            }
            if ($processing) {
                $datas = NojsLogger::where('nojs', $nojs)
                    ->whereBetween('time_local', [$sdate, $edate])
                    ->orderBy('time_local', 'asc')
                    ->get();
                $data = $this->rawDataProcessing($datas);
            }
        } else {
            $data = ["Error" => "parameter not found"];
        }
        return response($data, 200);
    }

    public function dataCalculate($datas)
    {
        $valueError = null;
        if (count($datas) != 0) {
            if (count($datas) > 1) {
                for ($i = 0; $i < count($datas) - 1; $i++) {
                    $array[$i]['time_local'] = $datas[$i]->time_local;
                    $array[$i]['nojs'] = $datas[$i]->nojs;

                    if (($datas[$i]->eh1 !== $valueError) && ($datas[$i + 1]->eh1 !== $valueError)) {
                        $array[$i]['eh1'] = $datas[$i]->eh1 - $datas[$i + 1]->eh1;
                    } else if (($datas[$i]->eh1 !== $valueError) && ($datas[$i + 1]->eh1 === $valueError)) {
                        $array[$i]['eh1'] =  $this->missedData($i, $datas, 'eh1');
                    } else if (($datas[$i]->eh1 === $valueError) && ($datas[$i + 1]->eh1 !== $valueError) || ($datas[$i]->eh1 == $valueError) && ($datas[$i + 1]->eh1 === $valueError)) {
                        $array[$i]['eh1'] = $valueError;
                    }

                    if (($datas[$i]->eh2 !== $valueError) && ($datas[$i + 1]->eh2 !== $valueError)) {
                        $array[$i]['eh2'] = $datas[$i]->eh2 - $datas[$i + 1]->eh2;
                    } else if (($datas[$i]->eh2 !== $valueError) && ($datas[$i + 1]->eh2 === $valueError)) {
                        $array[$i]['eh2'] =  $this->missedData($i, $datas, 'eh2');
                    } else if (($datas[$i]->eh2 === $valueError) && ($datas[$i + 1]->eh2 !== $valueError) || ($datas[$i]->eh2 === $valueError) && ($datas[$i + 1]->eh2 === $valueError)) {
                        $array[$i]['eh2'] = $valueError;
                    }

                    if (($datas[$i]->batt_volt1 !== $valueError)) {
                        $array[$i]['batt_volt1'] = $datas[$i]->batt_volt1 / 100;
                    } else {
                        $array[$i]['batt_volt1'] = $valueError;
                    }

                    if (($datas[$i]->edl1 !== $valueError) && ($datas[$i + 1]->edl1 !== $valueError)) {
                        $array[$i]['edl1'] = ($datas[$i]->edl1 - $datas[$i + 1]->edl1) * -1;
                    } else if (($datas[$i]->edl1 !== $valueError) && ($datas[$i + 1]->edl1 === $valueError)) {
                        $array[$i]['edl1'] =  $this->missedData($i, $datas, 'edl1');
                    } else if (($datas[$i]->edl1 === $valueError) && ($datas[$i + 1]->edl1 !== $valueError) || ($datas[$i]->edl1 === $valueError) && ($datas[$i + 1]->edl1 === $valueError)) {
                        $array[$i]['edl1'] = $valueError;
                    }

                    if (($datas[$i]->edl2 !== $valueError) && ($datas[$i + 1]->edl2 !== $valueError)) {
                        $array[$i]['edl2'] = ($datas[$i]->edl2 - $datas[$i + 1]->edl2) * -1;
                    } else if (($datas[$i]->edl2 !== $valueError) && ($datas[$i + 1]->edl2 === $valueError)) {
                        $array[$i]['edl2'] =  $this->missedData($i, $datas, 'edl2');
                    } else if (($datas[$i]->edl2 === $valueError) && ($datas[$i + 1]->edl2 !== $valueError) || ($datas[$i]->edl2 === $valueError) && ($datas[$i + 1]->edl2 === $valueError)) {
                        $array[$i]['edl2'] = $valueError;
                    }

                    if (($datas[$i]->pms_state !== $valueError)) {
                        $array[$i]['pms'] =  $this->pmsconvert($datas[$i]->pms_state);
                        $array[$i]['pms_state'] =  $datas[$i]->pms_state;
                    } else {
                        $array[$i]['pms'] = $valueError;
                        $array[$i]['pms_state'] = $valueError;
                    }
                }
            } else {
                $array['time_local'] = $datas[0]->time_local;
                $array['nojs'] = $datas[0]->nojs;
                $array['eh1'] = (($datas[0]->eh1) != $valueError) ? $datas[0]->eh1 : $valueError;
                $array['eh2'] = (($datas[0]->eh1) != $valueError) ? $datas[0]->eh2 : $valueError;
                $array['batt_volt1'] = (($datas[0]->eh1) != $valueError) ? ($datas[0]->batt_volt1 / 100) : $valueError;
                $array['edl1'] = (($datas[0]->eh1) != $valueError) ? $datas[0]->edl1 : $valueError;
                $array['edl2'] = (($datas[0]->eh1) != $valueError) ? $datas[0]->edl2 : $valueError;
                $array['pms_state'] = (($datas[0]->pms_state) != $valueError) ?  $datas[0]->pms_state : $valueError;
                $array['pms'] = (($datas[0]->pms_state) != $valueError) ?  $this->pmsConvert($datas[0]->pms_state) : $valueError;
                return  [$array];
            }
        } else {
            $array = [];
        }
        return $array;
    }

    public static function pmsConvert($pms)
    {
        $count = 0;
        for ($i = 0; $i < strlen($pms); $i++) {
            if ($pms[$i] == 3) {
                $count += 1;
            }
        }
        return $count;
    }

    public function missedData($loop, $datas, $data)
    {
        $valueError = null;
        $n = 0;

        for ($i = $loop + 1; $i < count($datas) - 1; $i++) {
            $n += 1;
            if ($datas[$i]->$data !=  $valueError) {
                return ($datas[$loop]->$data - $datas[$i]->$data) / $n;
            }
        }
        return $datas[$loop]->$data;
    }

    public function ceckService($data)
    {
        ServiceCallsController::ceckService($data);
    }

    public function rawDataProcessing($datas)
    {
        $valueError = null;
        $tempEh1 = 0;
        $tempEh2 = 0;
        $tempBv = 0;
        $tempEdl1 = 0;
        $tempEdl2 = 0;

        $array = [];
        if (count($datas) != 0) {
            if (count($datas) > 1) {
                for ($i = 0; $i < count($datas); $i++) {
                    $time_local = $datas[$i]->time_local;
                    $nojs = $datas[$i]->nojs;
                    $eh1 = $datas[$i]->eh1;
                    $eh2 = $datas[$i]->eh2;
                    $batt_volt1 = $datas[$i]->batt_volt1;
                    $edl1 = $datas[$i]->edl1;
                    $edl2 = $datas[$i]->edl2;

                    if ($i != 0) {
                        if ($datas[$i]->batt_volt1 !== $valueError) {
                            $tempEh1 = $eh1;
                            $tempEh2 = $eh2;
                            $tempBv = $batt_volt1;
                            $tempEdl1 = $edl1;
                            $tempEdl2 = $edl2;

                            array_push($array, [
                                "time_local" => $time_local,
                                "nojs" => $nojs,
                                "eh1" => $eh1,
                                "eh2" => $eh2,
                                "batt_volt1" => $batt_volt1,
                                "edl1" => $edl1,
                                "edl2" => $edl2
                            ]);
                        } else {
                            if ($tempBv !== 0) {
                                array_push($array, [
                                    "time_local" => $time_local,
                                    "nojs" => $nojs,
                                    "eh1" => $tempEh1,
                                    "eh2" => $tempEh2,
                                    "batt_volt1" => $tempBv,
                                    "edl1" => $tempEdl1,
                                    "edl2" => $tempEdl2
                                ]);
                            }
                        }
                    } else {
                        if ($datas[$i]->batt_volt1 !== $valueError) {
                            array_push($array, [
                                "time_local" => $time_local,
                                "nojs" => $nojs,
                                "eh1" => $eh1,
                                "eh2" => $eh2,
                                "batt_volt1" => $batt_volt1,
                                "edl1" => $edl1,
                                "edl2" => $edl2
                            ]);
                        }
                    }
                }
                $array = $this->deltaV($array);
            }
        } else {
            $array = [];
        }

        return $array;
    }

    public function deltaV($datas)
    {
        $loop = 3;
        $maxDelta = 30;
        for ($i = 0; $i < count($datas); $i++) {
            $array[$i]['time_local'] = $datas[$i]["time_local"];
            $date = (new Carbon($datas[$i]["time_local"]))->format('Y-m-d');
            $array[$i]['date'] = $date;
            $array[$i]['eh1'] = $datas[$i]["eh1"];
            $array[$i]['eh2'] = $datas[$i]["eh2"];
            $array[$i]['edl1'] = $datas[$i]["edl1"];
            $array[$i]['edl2'] = $datas[$i]["edl2"];

            if ($i !== 0) {
                if ($i === 1) {
                    if ((abs($datas[$i]["batt_volt1"] - $datas[$i - 1]["batt_volt1"])) > $maxDelta) {
                        $array[$i]['batt_volt1'] = $datas[$i - 1]["batt_volt1"];
                    } else {
                        $array[$i]['batt_volt1'] = $datas[$i]["batt_volt1"];
                        $temp = $datas[$i]["batt_volt1"];
                    }
                } elseif ($i === 2) {
                    if ((abs($datas[$i]["batt_volt1"] - $datas[$i - 2]["batt_volt1"])) > $maxDelta) {
                        $array[$i]['batt_volt1'] = $datas[$i - 1]["batt_volt1"];
                    } else {
                        $array[$i]['batt_volt1'] = $datas[$i]["batt_volt1"];
                        $temp = $datas[$i]["batt_volt1"];
                    }
                } else {
                    if ((abs($datas[$i]["batt_volt1"] - $datas[$i - $loop]["batt_volt1"])) > $maxDelta) {
                        $array[$i]['batt_volt1'] = $temp;
                    } else {
                        $temp = $datas[$i]["batt_volt1"];
                        $array[$i]['batt_volt1'] = $datas[$i]["batt_volt1"];
                    }
                }
            } else {
                $array[$i]['batt_volt1'] = $datas[$i]["batt_volt1"];
            }
        }
        $days =  $this->groupBy("date", $array);
        $result = [];
        foreach ($days as $key => $data) {
            array_push($result, $this->result($data));
            // $result[$key] = $this->result($data);
        }
        return [
            "nojs" => $datas[0]["nojs"],
            "days" => $result,
            "loggers" => $array
        ];
    }

    public function groupBy($key, $data)
    {
        $result = [];
        foreach ($data as $val) {
            if (array_key_exists($key, $val)) {
                $result[$val[$key]][] = $val;
            } else {
                $result[""][] = $val;
            }
        }
        return $result;
    }

    public function result($datas)
    {
        $result = [];
        $eh1 = [];
        $eh2 = [];
        $batt_volt1 = [];
        $edl1 = [];
        $edl2 = [];
        foreach ($datas as $data) {
            array_push($eh1, $data["eh1"]);
            array_push($eh2, $data["eh2"]);
            array_push($batt_volt1, $data["batt_volt1"]);
            array_push($edl1, $data["edl1"]);
            array_push($edl2, $data["edl2"]);
        }
        $result["eh1"] = [
            "date" => $datas[0]["date"],
            "min" => min($eh1),
            "max" => max($eh1),
            "avg" => array_sum($eh1) / count($eh1)
        ];
        $result["eh2"] = [
            "date" => $datas[0]["date"],
            "min" => min($eh2),
            "max" => max($eh2),
            "avg" => array_sum($eh2) / count($eh2)
        ];
        $result["batt_volt1"] = [
            "date" => $datas[0]["date"],
            "min" => min($batt_volt1),
            "max" => max($batt_volt1),
            "avg" => array_sum($batt_volt1) / count($batt_volt1)
        ];
        $result["edl1"] = [
            "date" => $datas[0]["date"],
            "min" => min($edl1),
            "max" => max($edl1),
            "avg" => array_sum($edl1) / count($edl1)
        ];
        $result["edl2"] = [
            "date" => $datas[0]["date"],
            "min" => min($edl2),
            "max" => max($edl2),
            "avg" => array_sum($edl2) / count($edl2)
        ];
        return $result;
    }
}