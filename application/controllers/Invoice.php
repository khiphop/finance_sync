<?php 
/**
* 开票相关
*/

class Invoice extends University_controller
{

    private $AT = '10000002';
    private $NotifyUrl = 'http://121.199.39.2:7998/Invoice/invoiceNotify';
    private $mapping = array();

    function __construct()
    {
        parent::__construct();

        $this->load->model('Invoice_model');

        $this->mapping = array(
            '1' => 'SumAgentTicketPayment',
            '2' => 'SumAgencyCommission',
            '3' => 'SumTicketRefund',
            '4' => 'SumBookingHotelFees',
            '5' => 'SumBookingCarFees',
            '6' => 'SumAgentServiceFee',
        );
    }


    /**
     * @name        查询可开票金额
     * @url         Invoice/queryInvoiceAmount
     * @author      kevin
     * @date        2020/1/8
     */
    public function queryInvoiceAmount()
    {
        $param = $this->get_params();
        $needParam = array(
            'BatchNo' => '批次号',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $result = $this->Invoice_model->queryInvoiceAmount($param);

        $defaultReturn = array(
            'BatchNo' => (string)$param['BatchNo'],
            'OrderCount' => 0,
            'SumAmountTotal' => 0,
            'SumAmountPayable' => 0,
            'SumAmountInvoiced' => 0,
            'OrderInvoiceDetail' => $result,
        );

        foreach ($result as $index => $item) {
            $defaultReturn['OrderCount'] = count($result);
            $defaultReturn['SumAmountTotal'] += $item['AmountTotal'];
            $defaultReturn['SumAmountPayable'] += $item['AmountPayable'];
            $defaultReturn['SumAmountInvoiced'] += $item['AmountInvoiced'];
        }

        $final = array(
            'Result'=> $defaultReturn,
        );

        echo $this->success_with_data($final);
    }

    /**
     * @name        获取公司发票抬头列表
     * @url         Invoice/getInvoiceTitleList
     * @author      kevin
     * @date        2020/1/9
     */
    public function getInvoiceTitleList()
    {
        $param = $this->get_params();
        if (!isset($param['Token']) || $param['Token'] != 'iflying') {
            echo $this->fail('Token 错误', 2);
            exit;
        }

        $list = [
            array(
                'Cid'=> 'qihang1001',
                'UniversityName'=> '北京工业大学',
                'GMF_MC'=> '北京工业大学',
                'GMF_NSRSBH'=> '12110000400687411U',
            ),
            array(
                'Cid'=> 'qihang1002',
                'UniversityName'=> '重庆大学',
                'GMF_MC'=> '重庆大学',
                'GMF_NSRSBH'=> '12100000400002697C',
            )
        ];

        $final = array(
            'Result'=> $list,
        );

        echo $this->success_with_data($final);
    }



    /**
     * @name        检查开票金额是否超限
     * @author      kevin
     * @date        2020/1/8
     * @return      array
     */
    private function checkInvoiceLimit($records, $param)
    {
        $orderIDList = [];
        foreach ($records as $index => $record) {
            $orderIDList[] = (int)$record['OrderID'];
        }
        $condition = array(
            'OrderID' => array(
                '$in' => $orderIDList,
            ),
            'Cid' => $param['Cid'],
        );

        $pipeline = array(
            array(
                '$match' => $condition
            ),
            array(
                '$group' => array(
                    '_id' => null,
                    'BatchNo' => array('$first' => '$BatchNo'),
                    'OrderCount' => array('$sum' => 1),
                    'SumAgentTicketPayment' => array('$sum' => '$AmountPayableDetail.AgentTicketPayment'),
                    'SumAgencyCommission' => array('$sum' => '$AmountPayableDetail.AgencyCommission'),
                    'SumTicketRefund' => array('$sum' => '$AmountPayableDetail.TicketRefund'),
                    'SumBookingHotelFees' => array('$sum' => '$AmountPayableDetail.BookingHotelFees'),
                    'SumBookingCarFees' => array('$sum' => '$AmountPayableDetail.BookingCarFees'),
                    'SumAgentServiceFee' => array('$sum' => '$AmountPayableDetail.AgentServiceFee'),
                )
            )
        );
        $result = $this->mongo_db->aggregate_simple('invoice', $pipeline);

        if (empty($result['result'])) {
            return array(
                'Status' => 2,
                'Msg' => '查询不到相关开票订单',
            );
        }

        $result = $result['result'][0];
        unset($result['_id']);

        $thisInvoiceAmount = array(
            'SumAgentTicketPayment' => array(
                'Sum' => 0,
                'OrderIDLIST' => array(),
            ),
            'SumAgencyCommission' => array(
                'Sum' => 0,
                'OrderIDLIST' => array(),
            ),
            'SumTicketRefund' => array(
                'Sum' => 0,
                'OrderIDLIST' => array(),
            ),
            'SumBookingHotelFees' => array(
                'Sum' => 0,
                'OrderIDLIST' => array(),
            ),
            'SumBookingCarFees' => array(
                'Sum' => 0,
                'OrderIDLIST' => array(),
            ),
            'SumAgentServiceFee' => array(
                'Sum' => 0,
                'OrderIDLIST' => array(),
            ),
        );
        foreach ($records as $index => $record) {
            $InvoiceAmount = $record['InvoiceAmount'];

            $mapping = array(
                '1' => 'SumAgentTicketPayment',
                '2' => 'SumAgencyCommission',
                '3' => 'SumTicketRefund',
                '4' => 'SumBookingHotelFees',
                '5' => 'SumBookingCarFees',
                '6' => 'SumAgentServiceFee',
            );

            $temp_index = $mapping[(string)$record['InvoiceClass']];

            switch($record['InvoiceClass'])
            {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    $thisInvoiceAmount[$temp_index]['Sum'] += $InvoiceAmount;
                    $thisInvoiceAmount[$temp_index]['OrderIDLIST'][] = (string)$record['OrderID'];
                    break;
                default:
                    return array(
                        'Status' => 3,
                        'Msg' => 'OrderID:' . $record['OrderID'] . ' InvoiceClass 开票类目不正确',
                    );
                    break;
            }
        }

        $temp_list = array(
            'SumAgentTicketPayment' => '代理机票款',
            'SumAgencyCommission' => '代理手续费',
            'SumTicketRefund' => '机票退票费',
            'SumBookingHotelFees' => '代订酒店费',
            'SumBookingCarFees' => '代订车费',
            'SumAgentServiceFee' => '代理服务费',
        );

        foreach ($temp_list as $index => $item) {
            if ($result[(string)$index] < $thisInvoiceAmount[(string)$index]['Sum']) {
                return array(
                    'Status' => 4,
                    'Msg' => $temp_list[(string)$index] . ' 开票金额超限, 上限:' . $result[(string)$index] . ' 开票金额:' . $thisInvoiceAmount[(string)$index]['Sum'],
                );
            }
        }

        return array(
            'Status' => 1,
            'Msg' =>'开票金额未超限',
            'Data' => $thisInvoiceAmount,
        );
    }

    /**
     * @title   开票
     * @param
     *      1    代理机票款   是票面价+改期费+燃油税
     *      2    代理手续费   1.是服务费（每项都有服务费，但是北工大的服务费为0） 2.火车的退票费
     *      3    机票退票费   是退票费
     *      4    代订酒店费   是酒店总价
     *      5    代订车费     是车费总价
     *      6    代理服务费   是保险费
     * @remark
     *      最大开票金额      99999   超出分票
     */
    public function makeOutInvoice()
    {
        $param = $this->get_params();

        if (!IS_ENCRYPT) {
            $param['Records'] = json_decode($param['Records'], true);
        }

        $temp_res = $this->checkInvoiceLimit($param['Records'], $param);

        if ($temp_res['Status'] !== 1) {
            echo $this->fail($temp_res['Msg'], 2);
            exit;
        }

        $thisInvoiceAmount = $temp_res['Data'];

        $i = 0;
        $tempSum = 0;

        foreach ($thisInvoiceAmount as $index => $item) {
            $i++;
            $tempSum += $item['Sum'];
            if ($item['Sum'] == 0) {
                continue;
            }

            $invoiceResult = $this->doInvoice($item, $param, $i);

            if (isset($invoiceResult['code']) && $invoiceResult['code'] >= 700) {
                foreach ($thisInvoiceAmount[$this->mapping[(string)$i]]['OrderIDLIST'] as $OrderIDLISTindex => $OrderIDLISTitem) {
                    $this->Invoice_model->updateAmountPayable($OrderIDLISTitem, $i);
                }
            }
        }

        if (empty($tempSum)) {
            echo $this->fail('所有类目可开票金额为0', 2);
            exit;
        }

        $final = array(
            'Result'=> '开票成功',
        );

        echo $this->success_with_data($final);
        exit();
    }

    /**
     * @name        调用开票接口
     * @author      kevin
     * @date        2020/1/9
     * @return      array
     */
    private function doInvoice($item, $param, $i)
    {
        $OrderIDListString = '';
        $OrderIDLList = array();
        foreach ($item['OrderIDLIST'] as $index2 => $item2) {
            $OrderIDListString .= (string)$item2.' ';
            $OrderIDLList[] = (string)$item2;
        }

        $Records = [];
        $temp_record = array();
        $temp_record['RecordID'] = (string)$this->getMillisecond() . (string)rand(10, 99);
        $temp_record['RecordNo'] = $temp_record['RecordID'];
        $temp_record['RecordTitle'] = 'Cid:' . (string)$param['Cid'] . ' 开票金额:' . (string)$item['Sum'] . ' 订单id:' . $OrderIDListString;
        $temp_record['TicketTypeNo'] = '30408029900000000000' . (string)$i;
        $temp_record['XMSL'] = 1;
        $temp_record['XMDJ'] = $item['Sum'];
        $temp_record['TotalPrice'] = $item['Sum'];
        $temp_record['Price'] = $item['Sum'];

        $Records[] = $temp_record;

        $requestData = array(
            'FunctionId' => '8',
            'CreateUserIP' => '121.199.39.2',
            'CreateUser' => array(
                'ForeignKeyID' => $param['Cid'],
                'EmployeeName' => $param['UniversityName'],
            ),
            'ValidateSign' => '380',
            'TicketSourceID' => 70,
            'ETCTicketUserName' => $param['ETCTicketUserName'],
            'GMF_NSRSBH' => $param['GMF_NSRSBH'],
            'GMF_MC' => $param['GMF_MC'],
            'GMF_DZDH' => isset($param['GMF_DZDH'])?$param['GMF_DZDH']:'',
            'GMF_YHZH' => isset($param['GMF_YHZH'])?$param['GMF_YHZH']:'',
            'GMF_SJH' => $param['GMF_SJH'],
            'GMF_DZYX' => $param['GMF_DZYX'],
            'FPT_ZH' => isset($param['FPT_ZH'])?$param['FPT_ZH']:'',
            'SKR' => isset($param['SKR'])?$param['SKR']:'',
            'TotalTicketPrice' => $item['Sum'],
            'NotifyUrl' => $this->NotifyUrl,
            'Records' => $Records,
        );

        $requestData = json_encode($requestData,320);

        $postData = array(
            'AT' => $this->AT,
            'PD' => '0GoSN9mNTj4WnzT6fRhoNo+xH8dCYsJ60XT2JTAWc2K0sDZ+oo0kkvNV5IlejOmX',
            'TT' => date("Y-m-d H:i:s", time()),
            'type' => "ETCTicket.WriteOn.BaiWang",
            'requestData' => $requestData,
        );

        $insertLog = array(
            'Url' => PayActionUrl,
            'PostData' => $postData,
            'Cid' => (string)$param['Cid'],
            'OrderIDLList' => $OrderIDLList,
        );
        $insertLogRes = $this->Invoice_model->insertLog($insertLog);

        $curlResult = $this->util->http_post_data(PayActionUrl, $postData, false);

        $updateLog = array(
            'InvoiceType' => (int)$i,
            'InvoiceStatus' => 1,
            'RpcResult' => $curlResult,
        );

        $curlResult = @json_decode($curlResult, true);
        if (isset($curlResult['data']['Result']['_id']) && !empty($curlResult['data']['Result']['_id'])) {
            $updateLog['RpcResultID'] = $curlResult['data']['Result']['_id'];
        }
        $this->Invoice_model->updateLog($updateLog, $insertLogRes->{'$id'});

        return $curlResult;
    }

    /**
     * @name        查询开票结果
     * @url         Invoice/queryInvoiceResult
     * @author      kevin
     * @date        2020/1/10
     */
    public function queryInvoiceResult()
    {
        $param = $this->get_params();
        $needParam = array(
            'Cid' => 'Cid',
            'OrderID' => '订单id',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $result = $this->Invoice_model->queryInvoiceResult($param);

        $retun = array(
            'Cid' => @$result['Cid'],
            'CreateTime' => @$result['CreateTime'],
            'RecordID' => @$result['RpcResultID'],
            'PostOrderID' => @$param['OrderID'],
            'OrderIDLList' => @$result['OrderIDLList'],
            'InvoiceType' => @$result['InvoiceType'],
            'InvoiceStatus' => @$result['InvoiceStatus'],
            'ticketcode' => @$result['NotifyData']['WriteOn']['ticketcode'],
            'ticketno' => @$result['NotifyData']['WriteOn']['ticketno'],
            'ticketcheckcode' => @$result['NotifyData']['WriteOn']['ticketcheckcode'],
            'tickettime' => @$result['NotifyData']['WriteOn']['tickettime'],
            'pdf' => @$result['NotifyData']['WriteOn']['pdf'],
        );

        $final = array(
            'Result'=> $retun,
        );

        echo $this->success_with_data($final);
    }

    /**
     * @name        红冲
     * @url         Invoice/invoiceCancel
     * @author      kevin
     * @date        2020/1/8
     */
    public function invoiceCancel()
    {
        $param = $this->get_params();
        $needParam = array(
            'Cid' => 'Cid',
            'RecordID' => '开票记录主键',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo $this->fail('参数缺失：' . $checkRes['param'], 2);
            exit;
        }

        $check = array(
            'RecordID' => $param['RecordID'],
        );
        $oldData = $this->Invoice_model->getInvoiceLogOne($check);
        if (isset($oldData['InvoiceStatus']) && $oldData['InvoiceStatus'] == 2) {
            echo $this->fail('发票已红冲', 2);
            exit;
        }

        $requestData = array(
            'RecordID' => (string)$param['RecordID'],
            'FunctionId' => '9',
            'RemoveUserIP' => '121.199.39.2',
            'RemoveUser' => array(
                'ForeignKeyID' => $param['Cid'],
            ),
            'ValidateSign' => '390',
        );

        $requestData = json_encode($requestData,320);

        $postData = array(
            'AT' => $this->AT,
            'PD' => '0GoSN9mNTj4WnzT6fRhoNo+xH8dCYsJ60XT2JTAWc2K0sDZ+oo0kkvNV5IlejOmX',
            'TT' => date("Y-m-d H:i:s", time()),
            'type' => "ETCTicket.WriteOff.BaiWang",
            'requestData' => $requestData,
        );

        $result = $this->util->http_post_data(PayActionUrl, $postData, false);

        $result = json_decode($result, true);

        if (isset($result['code']) && $result['code'] >= 700) {
            $updateLog = array(
                'InvoiceStatus' => 2,
                'CancelRpcResult' => $result,
            );

            $this->Invoice_model->updateLog($updateLog, '', (string)$param['RecordID']);

            $this->Invoice_model->updateInvoiceAmountAfterCancel((string)$param['RecordID']);

            echo $this->success_with_data(array('result' => '红冲成功'));
            exit;
        } else {
            echo $this->fail($result['message'], 2);
            exit;
        }
    }

    /**
     * @name        开票/红冲 回调
     * @author      kevin
     * @date        2020/1/9
     */
    public function invoiceNotify()
    {
        $indexList = array(
            'businessid',
            'etcticketno',
            'status',
            'message',
            'ticketcode',
            'ticketno',
            'ticketcheckcode',
            'tickettime',
            'pdf',
            'type',
        );

        $param = array();
        foreach ($indexList as $index => $item) {
            $param[$item] = $this->input->post_get($item);
        }

        $needParam = array(
            'businessid' => 'businessid',
            'status' => 'status',
        );
        $checkRes = $this->util->checkParamMissing($param, $needParam);
        if (2 === $checkRes['status']) {
            echo json_encode(array(
                'code' => 602,
                'message' => '参数缺失',
            ));
            exit;
        }

        $this->Invoice_model->updateLogWhenNotify($param);

        echo json_encode(array(
            'code' => 700,
            'message' => '操作成功',
        ));
    }





}