<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>政府采购机票查验单</title>
</head>
<body>
<div style="width: 21cm;height: 29.7cm;text-align: center;margin: 0 auto;">
    <h1 style="text-align: center;">政府采购机票查验单</h1>
    <div style="overflow: hidden;width: 100%;clear: none;padding: 20px 0 10px;">
        <span style="float: left;width: 33.33%; font-size: 13px; text-align: center;">查验单号：【<?php echo $data['checkOrderNumder']?>】</span>
        <span style="float: left;width: 33.33%; font-size: 13px; text-align: center;">查验单转态：<?php echo $data['checkOrderStatus']?></span>
        <span style="float: left;width: 33.33%; font-size: 13px; text-align: center;">出票时间：<?php echo $data['ticketTime']?></span>
    </div>
    <table border="1" cellpadding="0" cellspacing="0" style="text-align: center;">
        <tbody>
        <tr>
            <td colspan="2">
                <b>采购人姓名：</b><?php echo $data['purchaserName']?>
            </td>
            <td colspan="3">
                <b>有效证件号码：</b><?php echo $data['cardNum']?>
            </td>
            <td colspan="2">
                <b>验证方式：</b><?php echo $data['verifyType']?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>起降地</b>
            </td>
            <td width="20%" rowspan="2">
                <b>承运人</b>
            </td>
            <td rowspan="2">
                <b>航班号</b>
            </td>
            <td rowspan="2">
                <b>舱位</b>
            </td>
            <td rowspan="2">
                <b>出行日期</b>
            </td>
            <td rowspan="2">
                <b>起飞时间</b>
            </td>
        </tr>
        <tr>
            <td width="15%">FROM</td>
            <td width="15%">TO</td>
        </tr>
        <tr>
            <td><?php echo $data['depCity']?></td>
            <td><?php echo $data['arrCity']?></td>
            <td><?php echo $data['carrierName']?></td>
            <td><?php echo $data['fightNumber']?></td>
            <td><?php echo $data['cabin']?></td>
            <td><?php echo $data['depDate']?></td>
            <td><?php echo $data['depTime']?></td>
        </tr>
        <tr>
            <td colspan="7" align="left">
                <b>电子客票号：</b><?php echo $data['ticketNumber']?>
            </td>
        </tr>
        <tr>
            <td>
                <b>票价</b>
            </td>
            <td>
                <b>民航发展基金</b>
            </td>
            <td>
                <b>燃油附加费</b>
            </td>
            <td>
                <b>其他税费</b>
            </td>
            <td colspan="3">
                <b>合计</b>
            </td>
        </tr>
        <tr>
            <td><?php echo $data['ticketPrice']?></td>
            <td><?php echo $data['developmentMoney']?></td>
            <td><?php echo $data['oilMoney']?></td>
            <td><?php echo $data['otherTax']?></td>
            <td colspan="3">￥<?php echo $data['total']?></td>
        </tr>
        <tr>
            <td colspan="4">
                <b>服务商：</b><?php echo $data['serviceProvider']?>
            </td>
            <td colspan="2">
                <b>结算代码：</b><?php echo $data['settlementCode']?>
            </td>
            <td>
                <b>出票配置：</b><br /><?php echo $data['ticketAllocation']?>
            </td>
        </tr>
        </tbody>
    </table>
    <p style="text-align: right; padding-right: 20px;">电子查验单验真查询网址：www.gpticket.org</p>
</div>
</body>
</html>