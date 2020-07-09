<?php

class Invoice_model extends University_Model
{

    private $col_invoice = 'invoice';
    private $col_invoice_log = 'invoice_log';
    private $col_op_invoice_log = 'op_invoice_log';

    function __construct()
    {
        parent::__construct();
    }


    /**
     * @name        查询可开票金额
     * @author      kevin
     * @date        2020/1/8
     * @return      array
     */
    function queryInvoiceAmount($param = array())
    {
        $condition = array(
            'BatchNo' => @(string)$param['BatchNo']
        );

        $project = array(
            'Cid',
            'OrderID',
            'BatchNo',
            'AmountTotal',
            'AmountPayable',
            'AmountInvoiced',
            'AmountTotalDetail',
            'AmountPayableDetail',
            'AmountInvoicedDetail',
        );

        $result = $this->mongo_db
            ->select($project)
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->get($this->col_invoice);

        return $result;
    }

    /**
     * @name        申请开票添加记录
     * @author      kevin
     * @date        2020/1/9
     * @return      array
     */
    public function insertLog($param)
    {
        $param['CreateTime'] = new MongoDate();

        return $this->mongo_db->insert($this->col_invoice_log, $param);

    }

    /**
     * @name        申请开票更新记录
     * @author      kevin
     * @date        2020/1/9
     */
    public function updateLog($param, $_id='', $RecordID='')
    {
        if ($_id) {
            $condition = array(
                '_id' => new MongoId($_id)
            );
        }

        if ($RecordID) {
            $condition = array(
                'RpcResultID' => $RecordID
            );
        }

        $this->mongo_db
            ->where($condition)
            ->update($this->col_invoice_log, array('$set' => $param));
    }

    /**
     * @name        红冲后还原开票金额
     * @author      kevin
     * @date        2020/1/9
     * @param
     * @return
     */
    public function updateInvoiceAmountAfterCancel($RecordID = '')
    {
        $condition = array(
            'RpcResultID' => (string)$RecordID
        );

        $result = $this->mongo_db
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->get($this->col_invoice_log);

        $result = $result[0];

        foreach ($result['OrderIDLList'] as $index => $item) {
            $this->updateAmountPayable($item, $result['InvoiceType'], -1);
        }
    }

    /**
     * @name        回调后更新开票记录
     * @author      kevin
     * @date        2020/1/9
     */
    public function updateLogWhenNotify($param)
    {
        $condition = array(
            'RpcResultID' => $param['businessid'],
        );

        if ((string)$param['type'] == 'WriteOn') {
            $update = array(
                'NotifyData.WriteOn' => $param
            );
        } else {
            $update = array(
                'NotifyData.WriteOff' => $param
            );
        }

        $this->mongo_db
            ->where($condition)
            ->update($this->col_invoice_log, array('$set' => $update));
    }
    
    /**
     * @name        开票/红冲后 更新开票金额
     * @author      kevin
     * @date        2020/1/9
     */
    public function updateAmountPayable($orderID, $type = 1, $action = 1)
    {
        $condition = array(
            'OrderID' => (int)$orderID
        );

        $result = $this->mongo_db
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->get($this->col_invoice);

        $result = $result[0];

        $condition = array(
            'OrderID' => (int)$orderID,
        );

        if (-1 == $action) {
            $fromIndex = 'AmountInvoicedDetail';
        } else {
            $fromIndex = 'AmountPayableDetail';
        }
        switch((int)$type)
        {
            case 1:
                $amount = $result[$fromIndex]['AgentTicketPayment'];
                $update = array(
                    'AmountPayableDetail.AgentTicketPayment' => $result['AmountPayableDetail']['AgentTicketPayment'] + $amount * (-1) * $action,
                    'AmountInvoicedDetail.AgentTicketPayment' => $result['AmountInvoicedDetail']['AgentTicketPayment'] + $amount * $action,
                );
                break;
            case 2:
                $amount = $result[$fromIndex]['AgencyCommission'];
                $update = array(
                    'AmountPayableDetail.AgencyCommission' => $result['AmountPayableDetail']['AgencyCommission'] + $amount * (-1) * $action,
                    'AmountInvoicedDetail.AgencyCommission' => $result['AmountInvoicedDetail']['AgencyCommission'] + $amount * $action,
                );
                break;

            case 3:
                $amount = $result[$fromIndex]['TicketRefund'];
                $update = array(
                    'AmountPayableDetail.TicketRefund' => $result['AmountPayableDetail']['TicketRefund'] + $amount * (-1) * $action,
                    'AmountInvoicedDetail.TicketRefund' => $result['AmountInvoicedDetail']['TicketRefund'] + $amount * $action,
                );
                break;
            case 4:
                $amount = $result[$fromIndex]['BookingHotelFees'];
                $update = array(
                    'AmountPayableDetail.BookingHotelFees' => $result['AmountPayableDetail']['BookingHotelFees'] + $amount * (-1) * $action,
                    'AmountInvoicedDetail.BookingHotelFees' => $result['AmountInvoicedDetail']['BookingHotelFees'] + $amount * $action,
                );
                break;
            case 5:
                $amount = $result[$fromIndex]['BookingCarFees'];
                $update = array(
                    'AmountPayableDetail.BookingCarFees' => $result['AmountPayableDetail']['BookingCarFees'] + $amount * (-1) * $action,
                    'AmountInvoicedDetail.BookingCarFees' => $result['AmountInvoicedDetail']['BookingCarFees'] + $amount * $action,
                );
                break;
            case 6:
                $amount = $result[$fromIndex]['AgentServiceFee'];
                $update = array(
                    'AmountPayableDetail.AgentServiceFee' => $result['AmountPayableDetail']['AgentServiceFee'] + $amount * (-1) * $action,
                    'AmountInvoicedDetail.AgentServiceFee' => $result['AmountInvoicedDetail']['AgentServiceFee'] + $amount * $action,
                );
                break;
            default:
                break;
        }

        $update['AmountPayable'] = $result['AmountPayable'] + $amount * (-1) * $action;
        $update['AmountInvoiced'] = $result['AmountInvoiced'] + $amount * $action;

        $opParam = array(
            'Amount' => $amount * $action,
            'OrderID' => (string)$orderID,
            'Type' => $type,
            'Action' => $action,
            'InvoiceData' => $result,
        );
        $this->addOpInvoiceLog($opParam);

        $this->mongo_db
            ->where($condition)
            ->update($this->col_invoice, array('$set' => $update));
    }


    /**
     * @name        添加操作开票记录
     * @author      kevin
     * @date        2020/1/12
     */
    public function addOpInvoiceLog($param)
    {
        $param['CreateTime'] = new MongoDate();

        $this->mongo_db->insert($this->col_op_invoice_log, $param);
    }

    /**
     * @name        查询开票结果
     * @author      kevin
     * @date        2020/1/10
     * @return      array
     */
    public function queryInvoiceResult($param)
    {
        $condition = array(
            'Cid' => @(string)$param['Cid'],
            'OrderIDLList' => (string)$param['OrderID'],
        );

        $project = array(
            'Cid',
            'OrderIDLList',
            'CreateTime',
            'InvoiceType',
            'InvoiceStatus',
            'NotifyData',
        );

        $result = $this->mongo_db
            # ->select($project)
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->get($this->col_invoice_log);

        if (empty($result)) {
            $result = array();
        } else {
            $result = $result[0];
        }

        return $result;
    }
    
    /**
     * @name        获取一条 开票记录
     * @author      kevin
     * @date        2020/1/10
     * @return      array
     */
    public function getInvoiceLogOne($param, $project = array())
    {
        $condition = array();

        if (isset($param['RecordID']) && !empty($param['RecordID'])) {
            $condition['RpcResultID'] = (string)$param['RecordID'];
        }

        if (empty($condition)) {
            return array();
        }

        if (empty($project)) {
            $project = array(
                'Cid',
                'CreateTime',
                'InvoiceType',
                'InvoiceStatus',
                'NotifyData',
            );
        }

        $result = $this->mongo_db
            ->select($project)
            ->where($condition)
            ->order_by(array("_id" => "desc"))
            ->get($this->col_invoice_log);

        if (empty($result)) {
            return array();
        } else {
            return $result[0];
        }
    }

}
