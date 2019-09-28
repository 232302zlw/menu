<?php

namespace App\Http\Controllers\wechat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;

class MenuController extends Controller
{
    public $redis;
    public function __construct()
    {
        $this->redis = new \Redis;
        $this->redis->connect('127.0.0.1','6379');
    }

    public function create_menu()
    {
        return view('wechat.create_menu');
    }

    public function save_menu(Request $request)
    {
        $req = $request->all();
        unset($req['_token']);
        if (!empty($req['name2'])) {
            $req['button_type'] = 2;
        }else{
            $req['button_type'] = 1;
        }
        $res = DB::table('menu')->insert($req);
        dd($res);
    }

    public function file_menu()
    {
        $data = [];
        $first = DB::table('menu')->select('name1')->groupBy('name1')->get();
        foreach($first as $vv) {
            $info = DB::table('menu')->where('name1',$vv->name1)->get();
            $menu = [];
            foreach($info as $v){
                $menu[] = (array)$v;
            }
            $arr = [];
            foreach($menu as $v){
                if ($v['button_type'] == 1){
                    if ($v['type'] == 1){
                        $arr = [
                            'type' => 'click',
                            'name' => $v['name1'],
                            'key'  => $v['event_value']
                        ];
                    }elseif($v['type'] ==2 ){
                        $arr = [
                            'type' => 'view',
                            'name' => $v['name1'],
                            'key'  => $v['event_value']
                        ];
                    }elseif($v['type'] == 3){
                        $arr = [
                            'type' => 'pic_weixin',
                            'name' => $v['name1'],
                            'key'  => $v['event_value']
                        ];
                    }
                }elseif($v['button_type'] == 2){
                    $arr['name'] = $v['name1'];
                    if ($v['type'] == 1){
                        $button_arr = [
                            'type' => 'click',
                            'name' => $v['name2'],
                            'key'  => $v['event_value']
                        ];
                    }elseif($v['type'] ==2 ){
                        $button_arr = [
                            'type' => 'view',
                            'name' => $v['name2'],
                            'key'  => $v['event_value']
                        ];
                    }elseif($v['type'] == 3){
                        $button_arr = [
                            'type' => 'pic_weixin',
                            'name' => $v['name2'],
                            'key'  => $v['event_value']
                        ];
                    }
                    $arr['sub_button'][] = $button_arr;
                }
            }
            $data['button'][] = $arr;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->get_access_token();
        $res = $this->curl_post($url,$data);
        $result = json_decode($res,1);
        dd($result);
    }










    public function get_access_token()
    {
        $token = 'token';
        if ($this->redis->exists($token)){
            return $this->redis->get($token);
        }else{
            $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxf3c63fea45354eec&secret=6ccc59fd6ec3879bad2ad8d420536da3');
            $result = json_decode($res,1);
            $this->redis->set($token,$result['access_token'],$result['expires_in']);
            return $result['access_token'];
        }
    }

    public function curl_post($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);  //发送post
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $data = curl_exec($curl);
        $errno = curl_errno($curl);  //错误码
        $err_msg = curl_error($curl); //错误信息
        curl_close($curl);
        return $data;
    }
}
