<?php
//https://qliksense.ivan-shamaev.ru/loading-data-from-mango-office-to-qlik-sense-using-php/

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/vendor/autoload.php';

$url_request = 'https://app.mango-office.ru/vpbx/stats/request';
$url_result = 'https://app.mango-office.ru/vpbx/stats/result';

$client = new \RetailCrm\ApiClient(
    $urlCrm,
    $crmApiKey,
    \RetailCrm\ApiClient::V5
);

$statuses = [];
$statusesRequest = $client->request->statusesList();
foreach ($statusesRequest['statuses'] as $status) {
    if (in_array($status['group'], ['cancel', 'complete']))
        $statuses[] = $status['code'];
}

$ordersListAll = $client->request->ordersList([], 1, 100);
$totalPageCount = $ordersListAll['pagination']['totalPageCount'];
for ($i = 1; $i <= $totalPageCount; $i++) {
    $ordersList = $client->request->ordersList([], $i, 100);
    foreach ($ordersList['orders'] as $order) {
        if (in_array($order['status'], $statuses))
            continue;

        $start_date_timestamp = format_timestamp(date('Y-m-d', strtotime($order['createdAt'])), "start");
        $end_date_timestamp = format_timestamp(date('Y-m-d', strtotime($order['createdAt'])), "end");
        $number = str_replace(['(', '+', '-', ')', ' '], '', $order['phone']);
        if ($number[0] == 8)
            $number[0] = 7;
        //========================================================
        // НАСТРОЙКА ПАРАМЕТРОВ ЗАПРОСА (можно править)
        //========================================================
        //Перечень полей, которые будет выводить API Mango Office
        $header_row = [
            'records', 'start', 'finish',
            'answer', 'from_extension', 'from_number',
            'to_extension', 'to_number', 'disconnect_reason',
            'line_number', 'location'
        ];
        $callsTo = getCall($url_request, $url_result, $api_key, $api_salt, [
            "date_from" => $start_date_timestamp,
            "date_to" => $end_date_timestamp,
            "fields" => implode(",", $header_row),
            "to" => array(
                "number" => $number
            )
        ]);
        $callsFrom = getCall($url_request, $url_result, $api_key, $api_salt, ["date_from" => $start_date_timestamp,
            "date_to" => $end_date_timestamp,
            "fields" => implode(",", $header_row),
            "from" => array(
                "number" => $number
            )]);
        $allCalls = array_merge($callsTo, $callsFrom);
        $callCount = count($allCalls);

        $orderHistoryStart = $client->request->ordersHistory([
            'orderId' => $order['id']
        ], 1, 100);
        $historyTotalPageCount = $orderHistoryStart['pagination']['totalPageCount'];
        $orderHistoryArray = [];
        $statusCounter = 0;
        for ($i = 1; $i <= $historyTotalPageCount; $i++) {
            $orderHistory = $client->request->ordersHistory([
                'orderId' => $order['id']
            ], $i, 100);

            foreach ($orderHistory['history'] as $history) {
                $orderHistoryArray[] = $history;
            }
        }

        foreach ($orderHistoryArray as $history)
            if ($history['field'] == 'status' and in_array($history['newValue']['code'], $neededStatuses))
                $statusCounter++;

        if ($statusCounter == 0) {
            logger('errors.log', [
                'error' => 'Нет переходов в нужные статусы для заказа ' . $order['number']
            ]);
            continue;
        }

        if ($callCount < $statusCounter) {
            logger('errors.log', [
                'error' => 'Не хватает звонков ' . $order['number']
            ]);

            $orderEdit = lockOrder($client, $order);

            logger('orderEdit.log', [
                'date' => date('Y-m-d H:i:s'),
                'callData' => print_r([
                    'id' => $order['id'],
                    'statuses' => $statusCounter,
                    'calls' => $callCount
                ], true),
                'data' => print_r($orderEdit, true)
            ]);
        }
    }
}