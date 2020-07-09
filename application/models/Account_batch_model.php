<?php

class Account_batch_model extends University_Model
{
    private $colAccountBatch = 'AccountBatch';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @name        公用返回
     * @author      kevin
     * @date        2020/1/4
     * @return      array
     */
    private function communalReturn($condition, $limit, $result, $col)
    {
        $count = $this->mongo_db
            ->where($condition)
            ->count($col);

        $totalPage = ceil($count / $limit);

        return array(
            'data' => $result,
            'count' => $count,
            'totalPage' => $totalPage,
        );
    }

    function getAccountBatchList($modelParam)
    {
        $page = isset($modelParam['Page']) ? (int)($modelParam['Page']) : 1;
        $limit = isset($modelParam['Limit']) ? (int)($modelParam['Limit']) : 20;
        $skip = ($page - 1) * $limit;

        $project = array(
            'AccCheckId',
            'BatchNo',
            'AccCheckStatus',
            'AdjedReceiveMoney',
            'ConsumeMoney',
            'SumPostServiceFee',
            'StartDate',
            'EndDate',
            'PayCode',
            'SubAccCheckInfoList',
            'UniversityCid',
        );

        $condition = array(
            "UniversityCid" => $modelParam['UniversityCid'],
        );

        if (isset($modelParam['StartDateStart']) && !empty($modelParam['StartDateStart'])) {
            $condition['StartIsoDate']['$gte'] = new MongoDate(strtotime($modelParam['StartDateStart']));
        }

        if (isset($modelParam['StartDateEnd']) && !empty($modelParam['StartDateEnd'])) {
            $condition['StartIsoDate']['$lte'] = new MongoDate(strtotime($modelParam['StartDateEnd']));
        }

        if (isset($param['debug']) && !empty($param['debug'])) {
            echo json_encode($condition,320);
        }

        $result = $this->mongo_db
            ->select($project)
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->limit($limit)
            ->offset($skip)
            ->get($this->colAccountBatch);

        return $this->communalReturn($condition, $limit, $result, $this->colAccountBatch);
    }

    /**
     * @name        确认批次
     * @author      kevin
     * @date        2019/12/31
     * @param
     */
    public function confirmAccountBatch($param)
    {
        $BatchNoList = array();
        $res = str_replace('，', ',', $param['BatchNoList']);
        $res = explode(',', $res);
        foreach ($res as $index => $v) {
            $BatchNoList[] = (string)$v;
        }

        $condition = array(
            'BatchNo' => array(
                '$in' => $BatchNoList,
            ),
        );

        if (isset($param['debug']) && !empty($param['debug'])) {
            echo json_encode($condition,320);
        }

        $operation = array(
            '$set' => array(
                'AccCheckStatus' => (int)$param['Status']
            ),
        );

        $this->mongo_db
            ->where($condition)
            ->update_all('AccountBatch', $operation);
    }

}
