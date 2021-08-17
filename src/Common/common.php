<?php

namespace A\Common;

class common
{
    static function request($method = 'GET', $url, $param = [], $contentType = 1, $otherHeaders = [], $addressIp = '')
    {
        $ch = curl_init($url); //请求的URL地址

        if ($contentType == 1 || $contentType == 8) {
            $headersArray[] = "Content-Type: application/x-www-form-urlencoded";
            if (count($otherHeaders)) {
                foreach ($otherHeaders as $key => $value) {
                    $headersArray[] = $value;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headersArray);
        } else if ($contentType == 2 || $contentType == 6) {
            $headers[] = "Content-Type: application/json";
            if (count($otherHeaders)) {
                foreach ($otherHeaders as $key => $value) {
                    $headers[] = $value;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else if ($contentType == 3) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $otherHeaders);
            curl_setopt($ch, CURLOPT_PORT, 443);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
            curl_setopt($ch, CURLOPT_SSLKEY, app_path('ssl.key'));
            curl_setopt($ch, CURLOPT_SSLCERT, app_path('ssl.pem'));
        } else if ($contentType == 4) {
            $headers[] = "Content-Type: application/xml";
            if (count($otherHeaders)) {
                foreach ($otherHeaders as $key => $value) {
                    $headers[] = $value;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else if ($contentType == 5) {
            $headers[] = "Content-Type: text/xml";
            if (count($otherHeaders)) {
                foreach ($otherHeaders as $key => $value) {
                    $headers[] = $value;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        } else if ($contentType == 7) {
            $headers[] = "Content-Type: ";
            if (count($otherHeaders)) {
                foreach ($otherHeaders as $key => $value) {
                    $headers[] = $value;
                }
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method == 'POST' && ($contentType == 1 || $contentType == 2)) {
            $currParam = http_build_query($param);
            $currParam = str_replace('stestplayer=0', 'stestplayer=false', $currParam);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $currParam); //$post_data JSON类型字符串
        }

        if ($method == 'POST' && ($contentType == 7 || $contentType == 8)) {
            $str = '';
            foreach ($param as $key => $value) {
                $str .= $key . '=' . $value . '&';
            }
            $str = rtrim($str, '&');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        }

        if ($method == 'POST' && ($contentType == 4 || $contentType == 5)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param); //$post_data JSON类型字符串
        }

        if ($method == 'POST' && $contentType == 6) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param)); //$post_data JSON类型字符串
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($addressIp != '') {
            curl_setopt($ch, CURLOPT_INTERFACE, $addressIp);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $output   = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!empty($error)) {
            return ['httpCode' => $httpCode, 'output' => false];
        } else {
            return ['httpCode' => $httpCode, 'output' => $output];
        }
    }

}