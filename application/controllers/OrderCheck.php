<?php
/**
 *
 */

require_once APPPATH.'third_party/vendor/autoload.php';

class OrderCheck extends University_controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Order_check_model');
    }


    /**
     * @name        查验单下载
     * @url         orderCheck/orderCheckPdfInfo
     * @author      kevin
     * @date        2020/1/3
     * @throws
     */
    public function orderCheckPdfInfo()
    {
        $checkOrderNumber = $this->input->post_get('checkOrderNumber');

        if(empty($checkOrderNumber)){
            echo $this->fail("无法找到文件");
            return;
        }

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        $config = [
            'mode' => '+aCJK',
            'fontdata' => $fontData + [
                    'simsun' => [
                        'R' => 'simsun.ttf',
                        'I' => 'simsun.ttf',
                    ],
                ],
            'default_font' => 'simsun'
        ];
        $mpdf=new \Mpdf\Mpdf($config);
        $stylesheet = '
            table tbody tr td {
                padding: 12px 5px;
            }
        ';
        $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);

        $mpdf->autoLangToFont = true;

        $strContent = $this->getOrderCheckPage($checkOrderNumber);

        if ($strContent == "无法找到查验单") {
            echo "无法找到查验单-A";
            return;
        }

        $mpdf->WriteHTML($strContent);

        $mpdf->Output('政府采购机票查验单.pdf', 'D'); //I 查看；D 下载

    }

    private function getOrderCheckPage($checkOrderNumber)
    {
        $result = $this->Order_check_model->getOrderCheckPage($checkOrderNumber);

        if(sizeof($result) <= 0){
            echo "无法找到查验单-B";
            exit();
        }

        $tmp = json_decode($result[0]['Zhengcai']['PostResultJson'], true);

        $data = array(
            "data" => $tmp['data'][0]
        );

        $this->load->view('order_check', $data);

        return $this->output->get_output();
    }


}