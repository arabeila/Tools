<?php
/**
 * Created by PhpStorm.
 * User: Sakurai
 * Date: 2020/1/7
 * Time: 13:37
 */

namespace Arabeila\Tools\Services;

use Arabeila\Tools\Supports\Help;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;


class CashierService
{
    public $environment = 'dev';
    public $url;
    public $client;

    public function __construct()
    {
        $this->environment = app()->environment();

        if ($this->environment != 'dev') {
            $this->url = 'https://cashier.al8l.com/';
        } else {
            $this->url = 'http://cashier.dev.al8l.com/';
        }

        $this->client = new Client();
    }

    public function redirect($no)
    {
        $form = <<<FORM
        <form id='cashier_form' name='cashier_form' action="$this->url" method='post'>
            <input type='hidden' name='no' value="$no"/>
            <input type='submit' value='ok' style='display:none;'>
        </form>
        <script>document.forms['cashier_form'].submit();</script>
FORM;

        return Response::create($form);
    }

    /**
     * 获取AccessToken
     * @desc 获取AccessToken
     */
    public function getAccessToken()
    {
        $key = Help::key('access-token', 'web', 0);
        $access_token = Redis::get($key);
        $path = 'api/auth/login';

        if ($access_token) {
            return $access_token;
        } else {
            $access_key = config('tools.access_key');
            $secret_key = config('tools.secret_key');

            $flag = 0;//记录 失败 次数
            $seconds = 86340;// 1天 - 1分钟 token 有效期
            $times = 3;//重试 次数

            query:
            $response = $this->client->post($this->url.$path, [
                'headers'     => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36',
                ],
                'form_params' => [
                    'access_key' => $access_key,
                    'secret_key' => $secret_key,
                ],
            ]);

            $res = json_decode((string)$response->getBody(), true);

            if ($res['access_token']) {
                Redis::setex($key, $seconds, $res['access_token']);

                return $res['access_token'];
            } else {
                $flag++;
                if ($flag < $times) {
                    goto query;
                } else {
                    Log::error('请求 access_token 失败');
                    exit();
                }
            }
        }
    }

    /**
     * 获取支付单列表
     * @desc 获取支付单列表
     */
    public function getPayments($request)
    {
        $path = 'api/payments';

        $response = $this->client->get($this->url.$path, [
            'headers' => [
                'access-token' => $this->getAccessToken(),
            ],
            'query'   => [
                'user_id'  => $request['user_id'],
                'began_at' => $request['began_at'],
                'ended_at' => $request['ended_at'],
                'status'   => $request['status'],
                'state'    => $request['state'],
                'gateway'  => $request['gateway'],
                'no'       => $request['no'],
                'page'     => $request['page'],
                'per_page' => $request['per_page'],
                'total'    => $request['total'],
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 获取支付单详情
     * @desc 获取支付单详情
     */
    public function getPayment($no)
    {
        $path = 'api/payments/'.$no;

        $response = $this->client->get($this->url.$path, [
            'headers' => [
                'access-token' => $this->getAccessToken(),
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 添加支付单
     * @desc 添加支付单
     */
    public function postPayment($payment, $orders)
    {
        $info = [];
        $res = [];
        foreach ($orders as $order) {
            $info[$order['no']] = $order['order_price'];
            array_push($res, $info);
        }

        $path = 'api/payments';

        $response = $this->client->post($this->url.$path, [
            'headers'     => [
                'access-token' => $this->getAccessToken(),
            ],
            'form_params' => [
                'user_id'       => $payment->user_id,
                'no'            => $payment->no,
                'total_amount'  => $payment->total_amount,
                'payment_price' => $payment->payment_price,
                'order_info'    => $res,
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 获取退款单列表
     * @desc 获取退款单列表
     */
    public function getRefunds($request)
    {
        $path = 'api/refunds';

        $response = $this->client->get($this->url.$path, [
            'headers' => [
                'access-token' => $this->getAccessToken(),
            ],
            'query'   => [
                'page'     => $request['page'],
                'per_page' => $request['per_page'],
                'total'    => $request['total'],
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 获取退款单详情
     * @desc 获取退款单详情
     */
    public function getRefund($no)
    {
        $path = 'api/refunds/'.$no;

        $response = $this->client->get($this->url.$path, [
            'headers' => [
                'access-token' => $this->getAccessToken(),
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * 添加退款单
     * @desc 添加退款单
     */
    public function postRefund($refund)
    {
        $path = 'api/refunds';

        $response = $this->client->post($this->url.$path, [
            'headers'     => [
                'access-token' => $this->getAccessToken(),
            ],
            'form_params' => [
                'target_id'  => $refund->user_id,
                'refund_no'  => $refund->no,
                'payment_no' => $refund->payment->no,
                'price'      => $refund->price,
                'desc'       => $refund->desc,
                'order_no'   => $refund->order->no,
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }
}