<?php if (!defined('XXOO')) {exit('No direct script access allowed');}

require_once ROOT . 'funcs/redis_event.fn.php';

class JDPhoneControl extends Control {

    const TRIGGER_TIME = 5;

    const LOG_INFO = 'JD_info';

    public function __construct() {
        parent::__construct();
    }

    public function index() {
		$orderId = 'P1615040121993151';

		// // 查詢話費訂單
        $sql  = "select tel, money from hf_order where pt_order_id=?s and order_status=1";
        $orderData = DB::getLine($sql, [$orderId]);

		// 查詢話費訂單池
        $sql  = "select * from hf_order_pool where pt_order_id=?s and status=0 and is_use=0";
        $order_pool = DB::getLine($sql, [$orderId]);

		// 隨機從 ip 池中取 ip
		$sql  = "select ip, port from ip_pool order by rand() limit 1";
        $ip_pool = DB::getLine($sql);

		// 查詢有效小號資料
        $sql  = "select * from card where status=1 limit 1";
        $card = DB::getLine($sql);
		// $jddata = $this->kcpaytwo($orderData['tel'], $orderData['money'], $order_pool, $ip_pool['ip'], $ip_pool['port'], $card, '1');
		$jddata = $this->kcpay($orderData[ 'tel' ], $orderData[ 'money' ], $ip_pool['ip'], $ip_pool['port'], $card);

        // 訂閱事件
        // $route = "JDPhone:payCallBack:{$data}";
        // redis_event_add($route, self::TRIGGER_TIME);

		$this->resp($jddata);
    }

    /**
     * 生成訂單號
     */
    public function generateOrderId() {
    }

    /**
     * 下單
     */
    public function pay() {
    }

    /**
     * 去 JD 查單
     */
    public function query() {
    }

    /**
     * 回調
     */
    public function notify() {
    }

	/**
	 * 
	 */
    public function kcpay( $tel, $money, $proxy_ip, $proxy_port, $card ) {
		$ct = date( "Y-m-d H:i:s", time() );
		
		//$ua = "Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1";
		$ua = 'Mozilla/5.0 (Linux; U; Android 9; zh-cn; vivo X21i A Build/P00610) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/10.9 Mobile Safari/537.36'; 
		
		$csrfToken = strtoupper( md5( uniqid( mt_rand() ) ) );
		$eid = 'QLFHATSSHGICEOZTUUMMLZFUCKZ3YTXCFEC4DJ5GI2RVAPOBIZD4U4LZVB' . $csrfToken;

		$webcookie = $this->cookiearry( $this->trimall($card['webcookie']) );
		
		$cookie = "pt_pin=".$webcookie['pt_pin'].";pt_key=".$webcookie['pt_key'].";csrfToken=".$csrfToken.";3AB9D23F7A4B3C9B=".$eid.";";
		
		$proxyip = $proxy_ip . ':' . $proxy_port;
		
		$Jdcobe = array(
			'code' => '0'
		);
		
		return $cookie;

		$product = json_decode( $this->sendPostproduct($tel, $proxyip, $cookie, $ua), true);
		if  ($product == null ) {
		    $this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---号码检验失败");
		    //$Jdcobe['code']='2';//小号过期
		    //file_put_contents( $file, $ct . "---小号".$card['id']."---已过期", FILE_APPEND ); 
		    return $Jdcobe;
		}

		if ( $product[ 'skuPrice' ][ 'skuList' ] == null ) {
		    $this->log($ct . "---手机".$tel."---暂不支持该号段");
		    $Jdcobe['code'] = '3';//暂不支持该号段
		    return $Jdcobe;
		}

		$Order = $this->sendGetsubmit($tel, $proxyip, $cookie, $money, $product, $csrfToken, $eid, $ua);
		if ( $Order['code'] == '0' ) { 
		    $this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---获取不到订单号".$Order['msg']);
		    
			if ( $Order['msg'] == null ) {
		        $Jdcobe['code'] = '2';//小号过期
		        $this->log($ct . "---小号".$card['id']."---已过期");  
		    }

		    $needle = "销售火爆";//判断是否包含a这个字符
			$tmparray = explode($needle,$Order['msg']);
		    if ( count($tmparray) > 1 ) {
		        $Jdcobe['code'] = '2';//小号过期
		        $this->log($ct . "---小号".$card['id']."---已过期");
		    }

		    $needles = "运营商";//判断是否包含a这个字符
			$tmparrays = explode($needles,$Order['msg']);
		    if ( count($tmparrays) > 1 ) {
		        $Jdcobe['code'] = '3';//运营商升级
		        $this->log($ct . "---手机".$tel."---运营商升级");
		    }

		    return $Jdcobe;
		}

		$payId = $this->sendGetgoPay($tel, $proxyip, $cookie, $Order, $ua);
		if ( $payId == null ) {
		    $this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---获取不到京东PID");
		    return $Jdcobe;
		}
		
		for($x = 0; $x <= 5; ++$x) {
			$index = $this->sendPostindex($tel, $proxyip, $cookie, $Order, $payId, $ua);
			if ( $index ) {
				break;
			}
		}
		
		if ( $index == null ) {
		    $this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---请求京东收银台失败");
		    return $Jdcobe; 
		}

		for($x = 0; $x <= 5; ++$x) {
		    if( $x % 2 === 0 ) { 
		        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1';  
		    }
			else {
		        $ua = 'Mozilla/5.0 (Linux; U; Android 9; zh-cn; vivo X21i A Build/P00610) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/10.9 Mobile Safari/537.36';     
		    }
		    
		    $wapWeiXinPay = $this->sendGetindex($tel, $proxyip, $cookie, $Order, $payId, $ua);
		    
		    if ( $wapWeiXinPay[ 'code' ] == '0' ) {
		        break;
		    }
		}

		if ($wapWeiXinPay[ 'code' ] == '0' ) {
		    $Jdcobe = array(
		        'code' => "1",
		        'tel' => $tel,
		        'card_id' => $card[ 'id' ],
		        'orderId' => $Order['orderId'],
		        'payId' => $payId,
		        'pay_urls' => $wapWeiXinPay[ 'deepLink' ],
		        'jdPrePayId' => $wapWeiXinPay[ 'jdPrePayId' ]
			);
			$this->log($ct . "---小号".$card['id']."---下单成功");	
		}
		else if ( $wapWeiXinPay[ 'code' ] == '2' ) {
		    $Jdcobe['code']='4'; // 并发控制
		    $this->log($ct . "---小号".$card['id']."---获取不到链接2");
		}
		else {
		    $Jdcobe['code'] = '4'; // 并发控制
		    $this->log($ct . "---小号".$card['id']."---获取不到链接");
		}

		return $Jdcobe;
	}

	public function cookiearry($query) {
		$queryParts = explode(';', $query);
		$params = array();
		foreach ($queryParts as $param) {
			$item = explode('=', $param);
			$params[$item['0']] = $item['1'];
		}

		return $params;
	}

	/**
	 * 删除空格
	 */
	public function trimall($str) {
	    $oldchar = array(" ","　","\t","\n","\r");
	    $newchar = array("","","","","");
	    return str_replace($oldchar, $newchar, $str);
	}

	/**
	 * 無法使用
	 */
	public function kcpaytwo($tel,$money, $order_pool, $proxy_ip, $proxy_port, $card, $type) {
		$ct = date('Y-m-d H:i:s', time());

		$no = null;
		if ( $no % 2 === 0 ) {
			$ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1';
		}
		else {
			$ua = 'Mozilla/5.0 (Linux; U; Android 9; zh-cn; vivo X21i A Build/P00610) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/10.9 Mobile Safari/537.36';
		}
		
		$csrfToken = md5( uniqid( mt_rand() ) );
		$eid = 'QLFHATSSHGICEOZTUUMMLZFUCKZ3YTXCFEC4DJ5GI2RVAPOBIZD4U4LZVB' . $csrfToken;

		$cookie = $card['webcookie'] . ';csrfToken=' . $csrfToken . ';3AB9D23F7A4B3C9B=' . $eid . ';';
		
		$proxyip = $proxy_ip . ':' . $proxy_port;

		$Jdcobe = array(
			'code' => '0'
		);

		if ( $type === '2' ) {
			$Order = array(
				'orderId' => $order_pool['jd_order_id'],
				'jdPrice' => $order_pool['money'] * 100,
				'url' 	  => 'https://newcz.m.jd.com/newcz/list.action?sid=1&sceneval=2&jxsid=16127881161083524619'
			);

			$payId = $this->sendGetgoPay($tel, $proxyip, $cookie, $Order, $ua);

			if ( $payId == null ) {
				$this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---二次下单获取不到京东PID");
				return $Jdcobe;
			}

			$this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---第".$order_pool['xz_fall']."次获取PID成功");

			$index = null;
			for ($x = 0; $x <= 5; ++$x) {
				if ( $index = $this->sendPostindex($tel, $proxyip, $cookie, $Order, $payId, $ua) ) {
					break;
				}
			}
			
			if ( $index == null ) {
				$this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---二次下单请求京东收银台失败");
				return $Jdcobe; 
			}
		}
		else {
		    $payId = $order_pool['jdpayid'];
		}

		for ( $x = 0; $x <= 5; ++$x ) {
		    $wapWeiXinPay = $this->sendGetindex($tel, $proxyip, $cookie, $order_pool, $payId, $ua);
			$this->log($wapWeiXinPay);

		    if ( $wapWeiXinPay[ 'code' ] == '0' ) {
		        break;
		    }
		}

		if ( $wapWeiXinPay['code'] == '0' ) {
			$this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---第".$order_pool['xz_fall']."次获取微信成功");

			$Jdcobe = array(
		        'code' => "1",
		        'card_id' => $card[ 'id' ],
		        'orderId' => $order_pool[ 'pay_order_id' ],
		        'payId' => $payId,
		        'pay_urls' => $wapWeiXinPay[ 'deepLink' ],
		        'jdPrePayId' => $wapWeiXinPay[ 'jdPrePayId' ]
				);
		}
		else {
			$this->log($ct . "---小号".$card['id']."---使用代理IP".$proxyip."---下单".$tel."---第".$order_pool['xz_fall']."次获取微信失败" . var_export($wapWeiXinPay, true) );
		}

		return $Jdcobe;
	}

	/**
	 * 無法使用
	 */
	public function sendGetindex($tel, $proxyip, $cookie, $Order, $payId, $ua) {
		$url = 'https://pay.m.jd.com/index.action?functionId=wapWeiXinPay&body=%7B%22payId%22%3A%22' . $payId . '%22%2C%22appId%22%3A%22jd_m_chongzhi%22%7D&appId=jd_m_chongzhi&payId=' . $payId . '&_format_=JSON';

		$proxy = 'http://' . $proxyip;

		$Referer = 'https://pay.m.jd.com/cpay/newPay-index.html?appId=jd_m_chongzhi&payId=' . $payId;
		$headerArray = array(
			'Referer:' . $Referer,
			'Cookie:' . $cookie,
			'User-Agent:' . $ua
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, false);
		
		curl_setopt($ch, CURLOPT_PROXYTYPE, 0); // http
		curl_setopt($ch, CURLOPT_PROXY, $proxy); // 代理服务器
		curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); // 设置验证信息

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$res = curl_exec($ch);
		curl_close($ch);

		if ( $res === null ) {
		    return $res;
		}

		return json_decode($res,true);
	}

	/**
	 * 無法使用
	 */
	public function sendGetgoPay($tel, $proxyip, $cookie, $Order, $ua) {
	    
		$Referer = 'https://newcz.m.jd.com';

		// $url = "https://newcz.m.jd.com/newcz/goPay.action?orderId=".$Order['orderId']."&onlinePay=".$Order['jdPrice']* 0.01."&origin=&mobile=".$tel;
		$url = 'https://newcz.m.jd.com/newcz/goPay.action?orderId=139234043669&onlinePay=100.00&origin=&mobile=15111435056';
		
		$ch = curl_init();
		$headerArray = array(
			'Referer:' . $Referer,
			'Cookie:' . $cookie,
			'User-Agent:' . $ua
		);
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $ch, CURLOPT_POST, false );

		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_NOBODY, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 ); // 302 redirect

		curl_setopt( $ch, CURLOPT_PROXYTYPE, 0 ); //http
		curl_setopt( $ch, CURLOPT_PROXY, "http://" . $proxyip ); //代理服务器
		curl_setopt( $ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC ); //设置验证信息

		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headerArray );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 3 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
		$res = curl_exec( $ch );
		$Headers = curl_getinfo( $ch );
		curl_close( $ch );

		// $data = array(
		// 	'$res' 	   => $res,
		// 	'$Headers' => $Headers
		// );
		// return $data;

		if ( $res !== $Headers ) {
		    if( $Headers['url'] !== $url ){
		        $arr = parse_url($Headers['url']);
		        $arr_query = $this->convertUrlQuery($arr['query']);
		        $payId = $arr_query['payId'];
		        return $payId;
		    }
		}

		return null;
	}

	public function convertUrlQuery($query) {
		$queryParts = explode('&', $query);

		$item = null;
		$params = [];
		foreach($queryParts as $param) {
			$item = explode('=', $param);
			$params[ $item['0'] ] = $item['1'];
		}

		return $params;
	}

	public function sendPostindex($tel, $proxyip, $cookie, $Order, $payId, $ua) {
		$data = array(
			"lastPage" => $Order['url'],
			"appId" => "jd_m_chongzhi",
			"payId" => $payId,
			"_format_" => "JSON",
		);
		$Referer="https://pay.m.jd.com/cpay/newPay-index.html?appId=jd_m_chongzhi&payId=" . $payId;
		$curl = curl_init();
		$headerArray = array(
			"Referer:" . $Referer,
			"Cookie:" . $cookie,
			"User-Agent:".$ua
		);

		curl_setopt( $curl, CURLOPT_URL, "https://pay.m.jd.com/newpay/index.action" );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "POST" );

		curl_setopt( $curl, CURLOPT_PROXYTYPE, 0 ); //http
		curl_setopt( $curl, CURLOPT_PROXY, "http://" . $proxyip ); //代理服务器
		curl_setopt( $curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC ); //设置验证信息

		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headerArray );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 3 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, 5 );

		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $data ) );
		
		$output = curl_exec( $curl );
		curl_close( $curl );
		return $output;
	}

	/**
     * log
     */
    public function log($data) {        
        $log_dir  = $GLOBALS['app']['log_dir'] . date('Ym') . '/' . date('d') . '/';
        $log_file = $log_dir . self::LOG_INFO . '.log';

        if( !file_exists($log_file) ) {
            $this->mkDirs($log_dir);
            $this->mkFile($log_file);
        }
        
		$data = var_export($data, true) . PHP_EOL;
        file_put_contents($log_file, $data, FILE_APPEND);
    }

    private function mkDirs($dir) {
        if( !is_dir($dir) )	{
            if( ! self::mkdirs( dirname($dir) ) ) {
                return false;
            }
            if( ! mkdir($dir, 0777) ) {
                return false;
            }
        }
        return true;
    }

    private function mkFile($file_path) {
        $file = fopen($file_path, 'a');
        fclose($file);
        @chmod($file_path, 0777);
    }
}
