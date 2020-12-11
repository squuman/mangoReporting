<?php

function send_request_to_api($url, $data, $api_key, $api_salt)
{

    $json = json_encode($data);

    $sign = hash('sha256', $api_key . $json . $api_salt);

    $postdata = array(
        'vpbx_api_key' => $api_key,
        'sign' => $sign,
        'json' => $json
    );

    $post = http_build_query($postdata);

    $opts = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $post
        )
    );
    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);

    $result = json_decode($response, TRUE);
    $key = $result['key'];
    return $key;
}

function get_result_to_api($url, $key, $api_key, $api_salt)
{

    $data = array(
        "key" => $key
    );

    $json = json_encode($data);

    $sign = hash('sha256', $api_key . $json . $api_salt);

    $postdata = array(
        'vpbx_api_key' => $api_key,
        'sign' => $sign,
        'json' => $json
    );

    $post = http_build_query($postdata);

    $opts = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $post
        )
    );
    $context = stream_context_create($opts);

    $stats_result = null;
    $i_try_max = 20;
    $i_try = 1;

    while (empty($stats_result) and $i_try <= $i_try_max) {
        try {
            $stats_result = file_get_contents($url, false, $context);
        } catch (Exception $e) {
            $stats_result = null;
        }
        $i_try++;
        sleep(1);
    }

    //Empty array means none calls
    return $stats_result == null ? [] : $stats_result;
}

function put_csv_to_array($result_data, $header_dates, $header_row)
{

    //Парсим строку $result_data
    $result_array = array();
    $temp = array();
    $i_flag = 0;

    //Дробим ответ от сервера на отдельные строки с разделителем \n
    $big_parts = explode("\n", $result_data);


    foreach ($big_parts as $big_part) {

        $temp = explode(';', $big_part);

        foreach ($temp as $key_temp => $value_temp) {
            if (in_array($header_row[$key_temp], $header_dates)) {
                $header_name = $header_row[$key_temp];
                $header_name = preg_replace('/[^a-zA-Zа-яА-Я0-9 \:]/ui', '', $header_name);
                $result_array[$i_flag][$header_name] = date("Y-m-d H:i:s", $value_temp);
            } else {
                $result_array[$i_flag][$header_row[$key_temp]] = $value_temp;
            }
        }

        $i_flag++;

    }
    return $result_array;
}

function format_timestamp($date, $start_end_property)
{

    if ($start_end_property === "start") {
        $calc_date = new DateTime($date . ' 00:00:00', new DateTimeZone('Europe/Moscow'));
        $return_date = $calc_date->getTimestamp();
    } elseif ($start_end_property === "end") {
        $calc_date = new DateTime($date . ' 23:59:59', new DateTimeZone('Europe/Moscow'));
        $return_date = $calc_date->getTimestamp();
    }
    return $return_date;
}


function set_dates_from_url()
{

    global $start_date, $end_date;

    if (isset($_GET['start_date']) and isset($_GET['end_date'])) {
        $start_date = htmlspecialchars($_GET["start_date"]);
        $end_date = htmlspecialchars($_GET["end_date"]);
    } else {
        die('error');
    }
}

function logger($filename = 'file.log',$data = array()) {
    if (!is_dir(__DIR__ .'/logs'))
        mkdir(__DIR__ .'/logs');
    $fd = fopen(__DIR__ .'/logs/' . $filename,'a');
    fwrite($fd,print_r($data,true));
    fclose($fd);
}

function lockOrder($client,$order) {
    $orderEdit = $client->request->ordersEdit([
        'id' => $order['id'],
        'customFields' => [
            'lock' => true
        ]
    ], 'id', $order['site']);

    return $orderEdit;
}

function getCall($url_request,$url_result,$apiKey,$apiSalt,$request_data) {
    //========================================================
    // ФОРМИРОВАНИЕ ЗАПРОСА: POST /stats/request
    //========================================================
    $key = send_request_to_api($url_request, $request_data, $apiKey, $apiSalt);
    //=====================================================
    // ЗАБИРАЕМ ДАННЫЕ: POST /stats/result
    //=====================================================

    $result_data = get_result_to_api($url_result, $key, $apiKey, $apiSalt);
    $resultArray = [];
    if (empty($result_data))
        return [];
    foreach (explode("\n", $result_data) as $res) {
        if ($res == '')
            continue;
        $resultArray[] = explode(';', $res);
    }
    return $resultArray;
}