<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use App\Models\WeChatUser;
use App\Models\WeChatIDMap;

/**
 * TokenController 操作
 */
class TokenController extends ControllerBase {
    // 生成token
    public function token_encode(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        $open_id = array_get($args, 'open_id');
        if(empty($open_id)) {
            $open_id = array_get($params, 'open_id');
        }
        $union_id = array_get($args, 'union_id');
        if(empty($union_id)) {
            $union_id = array_get($params, 'union_id');
        }

        $nickname = array_get($args, 'nickname');
        if(empty($nickname)) {
            $nickname = array_get($params, 'nickname');
        }
        $headimgurl = array_get($args, 'headimgurl');
        if(empty($headimgurl)) {
            $headimgurl = array_get($params, 'headimgurl');
        }
        $ret = [];
        $data_ok = !empty($union_id) &&
            !empty($open_id) &&
            !empty($nickname) &&
            !empty($headimgurl);
        if($data_ok) {
            $existed_user = WeChatUser::get_by_union_id($union_id);
            $user_id = null;
            if(empty($existed_user)) {
                // 添加微信用户记录
                $obj = new WeChatUser();
                $obj->union_id = $union_id;
                $obj->nickname = $nickname;
                $obj->headimgurl = $headimgurl;
                $ok_count = $obj->save();
                if($ok_count) {
                    $user_id = $obj->id;
                }
                else {
                    $ret = [
                        'error' => 2,
                        'desc' => '数据操作失败'
                    ];
                }
            }
            else {
                $user_id = array_get($existed_user, 'id');
            }
            if(empty($ret)) {
                $existed_map = WeChatIDMap::get_by_ids($union_id, $open_id);
                if(empty($existed_map)) {
                    // 添加微信ID关联用户记录
                    $obj = new WeChatIDMap();
                    $obj->wechat_user = $user_id;
                    $obj->union_id = $union_id;
                    $obj->open_id = $open_id;
                    $ok_count = $obj->save();
                    if($ok_count) {
                        $map_id = $obj->id;
                    }
                    else {
                        $ret = [
                            'error' => 3,
                            'desc' => '数据操作失败'
                        ];
                    }
                }
                if(empty($ret)) {
                    $secret = $this->ci->get('settings')['secret'];
                    $token = static::generate_token($secret, $open_id, $union_id);
                    $ret = [
                        'error' => 0,
                        'desc' => null,
                        'token' => $token
                    ];
                }
            }
        }
        else {
            $ret = [
                'error' => 1,
                'desc' => '缺少参数'
            ];
        }
        return $response->withJson($ret);
    }

    /**
     * 加密token
     */
    public static function generate_token($secret, $open_id, $union_id) {
        $crypto = getAESCrypt($secret);
        //$crypto->disablePadding(); // 禁用自动padding
        //$token_content = str_pad($token_content, 16, "\0", STR_PAD_RIGHT); // 禁用padding后需要手动填到16位
        // 取得时间戳(含微秒)
        $ts_str = sprintf('%.5f', microtime(true));
        // 生成 MD5
        $sign = md5($ts_str);
        $str = $open_id . '@' . $union_id . '@' . $ts_str;
        $pk = $crypto->encrypt($str);
        $token_str = base64_encode($pk);

        // rtrim($origin, "\0"); // 禁用padding手动填充加密的方式，解密需要手动去掉padding

        return $token_str . '..' . $sign;
    }

    /**
     * 解析token
     */
    public static function parse_token($secret, $token_str) {
        $result = null;
        $token_len = strlen($token_str) - 32 - 2;
        $token = substr($token_str, 0, $token_len);
        $sign_md5 = substr($token_str, - 32);
        $token = base64_decode($token, true);
        if($token != false) {
            $crypto = getAESCrypt($secret);
            $pk = $crypto->decrypt($token);
            if(!empty($pk)) {
                $arr = explode('@', $pk);
                if(count($arr) == 3) {
                    $open_id = $arr[0];
                    $union_id = $arr[1];
                    $sign = $arr[2];
                    // md5 验证时间戳
                    if($sign_md5 == md5($sign)) {
                        $result = [
                            'open_id' => $open_id,
                            'union_id' => $union_id,
                            'sign' => $arr[2]
                        ];
                    }
                }
            }
        }
        return $result;
    }
}